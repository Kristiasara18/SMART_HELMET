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
    if (err) return res.status(500).json({ error: err.message });
    res.json(result);
  });
});
