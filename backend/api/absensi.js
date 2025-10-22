import express from "express";
import db from "../db.js";

const router = express.Router();

// 🔹 Ambil semua data absensi
router.get("/", async (req, res) => {
  const today = new Date().toISOString().slice(0, 10); // format YYYY-MM-DD

  try {
    // 1️⃣ Cek apakah sudah ada data absensi untuk hari ini
    const [cek] = await db
      .promise()
      .query("SELECT COUNT(*) AS count FROM absensi WHERE tanggal = ?", [today]);

    // 2️⃣ Kalau belum ada → buat entri baru untuk semua karyawan
    if (cek[0].count === 0) {
      const [karyawan] = await db.promise().query("SELECT id_pekerja FROM karyawan");
      for (const k of karyawan) {
        await db
          .promise()
          .query(
            "INSERT INTO absensi (tanggal, id_pekerja, kehadiran) VALUES (?, ?, 'Tidak Hadir')",
            [today, k.id_pekerja]
          );
      }
      console.log(`✅ Data absensi otomatis dibuat untuk tanggal ${today}`);
    }

    // 3️⃣ Setelah itu kirim semua data absensi ke frontend
    const [rows] = await db
      .promise()
      .query(
        `SELECT 
          a.id_absensi AS id,
          a.tanggal,
          k.nama,
          k.id_pekerja,
          a.kehadiran,
          k.kode_helmet
        FROM absensi a
        JOIN karyawan k ON a.id_pekerja = k.id_pekerja
        ORDER BY a.tanggal DESC, a.id_pekerja ASC`
      );

    res.json(rows);
  } catch (err) {
    console.error("❌ Gagal mengambil data absensi:", err);
    res.status(500).json({ error: "Gagal mengambil data absensi" });
  }
});

export default router;
