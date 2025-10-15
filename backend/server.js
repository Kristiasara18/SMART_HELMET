// server.js
const express = require("express");
const mysql = require("mysql2");
const cors = require("cors");
const cron = require("node-cron");

const app = express();
app.use(cors());
app.use(express.json());

// 🔹 Koneksi database
const db = mysql.createConnection({
  host: "localhost",
  user: "root",
  password: "",
  database: "db_karyawan",
});

db.connect((err) => {
  if (err) return console.error("❌ Koneksi gagal:", err);
  console.log("✅ Terhubung ke database MySQL!");
});

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

// 🔹 API GET absensi hari ini
app.get("/api/absensi/today", (req, res) => {
  // Ambil tanggal lokal WIB
  function getTodayLocal() {
    const now = new Date();
    now.setHours(now.getHours() + 7); // WIB
    const yyyy = now.getFullYear();
    const mm = String(now.getMonth() + 1).padStart(2, "0");
    const dd = String(now.getDate()).padStart(2, "0");
    return `${yyyy}-${mm}-${dd}`;
  }

  const todayStr = getTodayLocal();

  const fetchToday = () => {
    const selectQuery = `
      SELECT a.id_absensi AS id, DATE(a.tanggal) AS tanggal, a.kehadiran,
             k.id_pekerja, k.nama, k.kode_helmet
      FROM absensi a
      JOIN karyawan k ON a.id_pekerja = k.id_pekerja
      WHERE DATE(a.tanggal) = ?
      ORDER BY a.id_absensi ASC
    `;
    db.query(selectQuery, [todayStr], (err, rows) => {
      if (err) return res.status(500).json({ error: err.message });
      res.json(rows);
    });
  };

  const checkQuery = `SELECT COUNT(*) AS count FROM absensi WHERE DATE(tanggal) = ?`;
  db.query(checkQuery, [todayStr], (err, result) => {
    if (err) return res.status(500).json({ error: err.message });

    if (result[0].count === 0) {
      const insertQuery = `
        INSERT INTO absensi (id_pekerja, tanggal, kehadiran)
        SELECT id_pekerja, ?, 'Tidak Hadir' FROM karyawan
      `;
      db.query(insertQuery, [todayStr], (err2) => {
        if (err2) return res.status(500).json({ error: err2.message });
        fetchToday();
      });
    } else {
      fetchToday();
    }
  });
});

// 🔹 Update kehadiran
app.put("/api/absensi/:id", (req, res) => {
  const { id } = req.params;
  const { kehadiran } = req.body;
  const query = `UPDATE absensi SET kehadiran = ? WHERE id_absensi = ?`;
  db.query(query, [kehadiran, id], (err, result) => {
    if (err) return res.status(500).json({ error: err.message });
    res.json({ message: "✅ Kehadiran berhasil diperbarui!" });
  });
});

// 🔹 Cron job buat absensi hari baru tiap jam 00:05
cron.schedule("5 0 * * *", () => {
  const today = new Date().toISOString().slice(0, 10);

  const checkQuery = `SELECT COUNT(*) AS count FROM absensi WHERE tanggal = ?`;
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

const PORT = 5000;
app.listen(PORT, () => console.log(`🚀 Server berjalan di http://localhost:${PORT}`));
