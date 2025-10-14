// server.js
const express = require("express");
const mysql = require("mysql2");
const cors = require("cors");
const cron = require("node-cron"); // ✅ Tambahkan cron di atas

const app = express();
app.use(cors());
app.use(express.json());

// 🔹 Koneksi ke database
const db = mysql.createConnection({
  host: "localhost",
  user: "root",
  password: "",
  database: "db_karyawan",
});

db.connect((err) => {
  if (err) {
    console.error("❌ Koneksi gagal:", err);
    return;
  }
  console.log("✅ Terhubung ke database MySQL!");
});

// ✅ API GET untuk ambil absensi + data karyawan
app.get("/api/absensi", (req, res) => {
  const query = `
    SELECT 
      a.id_absensi AS id,
      a.tanggal,
      a.kehadiran,
      k.id_pekerja,
      k.nama,
      k.kode_helmet
    FROM absensi a
    JOIN karyawan k ON a.id_pekerja = k.id_pekerja
    ORDER BY a.id_absensi ASC
  `;
  db.query(query, (err, result) => {
    if (err) {
      console.error(err);
      return res.status(500).json({ error: err.message });
    }
    res.json(result);
  });
});

// ✅ API PUT untuk update kehadiran
app.put("/api/absensi/:id", (req, res) => {
  const { id } = req.params;
  const { kehadiran } = req.body;
  const query = "UPDATE absensi SET kehadiran = ? WHERE id_absensi = ?";
  db.query(query, [kehadiran, id], (err, result) => {
    if (err) return res.status(500).json({ error: err.message });
    res.json({ message: "✅ Kehadiran berhasil diperbarui!" });
  });
});


// 🔹 CRON JOB — otomatis membuat absensi tiap hari jam 00:05
cron.schedule("5 0 * * *", () => {
  const today = new Date().toISOString().slice(0, 10); // Format YYYY-MM-DD
  console.log("🕛 Mengecek absensi otomatis untuk:", today);

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
    } else {
      console.log("ℹ️ Absensi hari ini sudah ada:", today);
    }
  });
});

// 🔹 Jalankan server
const PORT = 5000;
app.listen(PORT, () =>
  console.log(`🚀 Server berjalan di http://localhost:${PORT}`)
);
