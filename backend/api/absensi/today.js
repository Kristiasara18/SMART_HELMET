const express = require("express");
const router = express.Router();
const { db } = require("../../server"); // gunakan ekspor baru

router.get("/", (req, res) => {
  function getTodayLocal() {
    const now = new Date();
    now.setHours(now.getHours() + 7); // WIB
    const yyyy = now.getFullYear();
    const mm = String(now.getMonth() + 1).padStart(2, "0");
    const dd = String(now.getDate()).padStart(2, "0");
    return `${yyyy}-${mm}-${dd}`;
  }

  const todayStr = getTodayLocal();
  console.log("🕒 Mengecek absensi untuk:", todayStr);

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
      if (err) {
        console.error("❌ Gagal ambil absensi hari ini:", err);
        return res.status(500).json({ error: err.message });
      }
      console.log("📋 Ditemukan", rows.length, "baris untuk", todayStr);
      res.json(rows);
    });
  };

  const checkQuery = `SELECT COUNT(*) AS count FROM absensi WHERE DATE(tanggal) = ?`;
  db.query(checkQuery, [todayStr], (err, result) => {
    if (err) {
      console.error("❌ Error saat cek absensi:", err);
      return res.status(500).json({ error: err.message });
    }

    if (result[0].count === 0) {
      console.log("⚙️ Membuat data baru absensi untuk", todayStr);
      const insertQuery = `
        INSERT INTO absensi (id_pekerja, tanggal, kehadiran)
        SELECT id_pekerja, ?, 'Tidak Hadir' FROM karyawan
      `;
      db.query(insertQuery, [todayStr], (err2, result2) => {
        if (err2) {
          console.error("❌ Gagal insert absensi:", err2);
          return res.status(500).json({ error: err2.message });
        }
        console.log("✅ Berhasil menambah", result2.affectedRows, "baris data");
        fetchToday();
      });
    } else {
      console.log("✅ Data absensi hari ini sudah ada");
      fetchToday();
    }
  });
});

module.exports = router;
