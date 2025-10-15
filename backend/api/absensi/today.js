const express = require("express");
const router = express.Router();
const db = require("../server").db; // akses koneksi db dari server.js

router.get("/", (req, res) => {
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

module.exports = router;
