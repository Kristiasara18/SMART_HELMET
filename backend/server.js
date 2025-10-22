const express = require("express");
const cors = require("cors");
const mysql = require("mysql2");
const cron = require("node-cron");

const app = express();
app.use(cors());
app.use(express.json());

// 🔹 Koneksi database
const db = mysql.createPool({
  host: "localhost",
  user: "root",
  password: "",
  database: "db_karyawan",
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
});

console.log("✅ Pool koneksi MySQL siap digunakan!");

// ✅ Ekspor koneksi supaya router lain bisa pakai
module.exports.db = db;

// 🔹 Ambil semua absensi
app.get("/api/absensi", (req, res) => {
  const query = `
    SELECT a.id_absensi AS id, a.tanggal, a.kehadiran,
           k.id_pekerja, k.nama, k.kode_helmet
    FROM absensi a
    JOIN karyawan k ON a.id_pekerja = k.id_pekerja
    ORDER BY a.id_absensi ASC
  `;
  db.query(query, (err, result) => {
    if (err) return res.status(500).json({ error: err.message });
    res.json(result);
  });
});

// ✅ Import router setelah ekspor db
const absensiTodayRoute = require("./api/absensi/today");
app.use("/api/absensi/today", absensiTodayRoute);

// 🔹 Update kehadiran
app.put("/api/absensi/:id", (req, res) => {
  const { id } = req.params;
  const { kehadiran } = req.body;
  const query = `UPDATE absensi SET kehadiran = ? WHERE id_absensi = ?`;
  db.query(query, [kehadiran, id], (err) => {
    if (err) return res.status(500).json({ error: err.message });
    res.json({ message: "✅ Kehadiran berhasil diperbarui!" });
  });
});

// 🔹 Cron job buat absensi hari baru tiap jam 00:05 WIB
cron.schedule("5 0 * * *", () => {
  const now = new Date();
  now.setHours(now.getHours() + 7); // WIB
  const today = now.toISOString().slice(0, 10);

  const checkQuery = `SELECT COUNT(*) AS count FROM absensi WHERE DATE(tanggal) = ?`;

  db.query(checkQuery, [today], (err, result) => {
    if (err) return console.error("❌ Error cek absensi:", err);

    if (result[0].count === 0) {
      const insertQuery = `
        INSERT INTO absensi (id_pekerja, tanggal, kehadiran)
        SELECT id_pekerja, ?, 'Tidak Hadir' FROM karyawan
      `;
      db.query(insertQuery, [today], (err2) => {
        if (err2) console.error("❌ Gagal insert absensi otomatis:", err2);
        else console.log("✅ Absensi otomatis dibuat untuk", today);
      });
    }
  });
});

// 🔹 API ringkasan absensi untuk dashboard
app.get("/api/dashboard/summary", (req, res) => {
  function getTodayLocal() {
    const now = new Date();
    now.setHours(now.getHours() + 7); // WIB
    const yyyy = now.getFullYear();
    const mm = String(now.getMonth() + 1).padStart(2, "0");
    const dd = String(now.getDate()).padStart(2, "0");
    return `${yyyy}-${mm}-${dd}`;
  }

  const today = getTodayLocal();

  const summaryQuery = `
    SELECT 
      (SELECT COUNT(*) FROM karyawan) AS total_karyawan,
      (SELECT COUNT(*) FROM absensi WHERE DATE(tanggal) = ? AND kehadiran = 'Hadir') AS hadir_hari_ini,
      (SELECT COUNT(*) FROM insiden WHERE DATE(waktu) = ?) AS insiden_hari_ini
  `;

  db.query(summaryQuery, [today, today], (err, results) => {
    if (err) return res.status(500).json({ error: err.message });
    res.json(results[0]);
  });
});

const PORT = 5000;
app.listen(PORT, () =>
  console.log(`🚀 Server berjalan di http://localhost:${PORT}`)
);

