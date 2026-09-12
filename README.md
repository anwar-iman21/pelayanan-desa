# 🏢 Sistem Pelayanan Surat Digital Balai Desa

Aplikasi web sederhana untuk digitalisasi layanan pengajuan surat keterangan di tingkat desa. Warga bisa mengajukan surat dan mengecek statusnya secara mandiri cukup dengan NIK, tanpa perlu membuat akun. Admin desa mengelola pengajuan, data warga, dan mencetak surat resmi lewat satu dashboard.

> ⚠️ **Catatan:** Project ini dibuat untuk keperluan latihan/portofolio. Data desa (nama desa, kecamatan, kepala desa) yang muncul di surat cetak masih menggunakan data contoh/fiktif (Desa Sukamaju).

---

## ✨ Fitur

**Sisi Warga (publik, tanpa login):**
- Form pengajuan surat (SKTM, Pengantar SKCK, Keterangan Domisili) — validasi NIK harus warga terdaftar
- Cek status pengajuan surat secara mandiri lewat NIK

**Sisi Admin (login diperlukan):**
- Login admin berbasis database dengan password ter-hash (bcrypt)
- Dashboard daftar seluruh pengajuan surat + ubah status (Pending → Diproses → Selesai/Ditolak)
- CRUD data warga (tambah, edit, hapus)
- Cetak surat resmi (format kop surat + tanda tangan) yang bisa langsung disimpan sebagai PDF lewat browser, tersedia untuk surat berstatus "Selesai"

**Keamanan:**
- Prepared statement (PDO) di semua query — aman dari SQL Injection
- Password admin di-hash dengan `password_hash()` / `password_verify()`, bukan plain text
- CSRF token di semua form POST
- Validasi input (NIK wajib 16 digit angka) di sisi server
- Pesan error database tidak ditampilkan mentah ke pengguna (di-log, bukan di-*echo*)

---

## 🛠️ Tech Stack

- **Backend:** PHP native (tanpa framework)
- **Database:** MySQL / MariaDB (PDO)
- **Frontend:** HTML, CSS custom (tidak bergantung pada framework CSS eksternal)
- **Environment:** XAMPP / Laragon / WAMP (Apache + PHP + MySQL)

---

## 📁 Struktur File

```
pelayanan-desa/
├── index.php          # Halaman publik warga (ajuin surat & cek status)
├── login.php          # Halaman login admin
├── admin.php          # Dashboard admin — daftar pengajuan & update status
├── admin_warga.php    # CRUD data warga
├── cetak_surat.php    # Halaman cetak surat resmi (print-friendly)
├── koneksi.php        # Koneksi ke database (PDO)
├── csrf.php           # Helper CSRF token
└── db_desa.sql        # Struktur database + data contoh
```

---

## 🚀 Cara Menjalankan (Lokal)

1. Clone/download repo ini, taruh di folder `htdocs` XAMPP (atau folder web root aplikasi server lokal lain)
   ```
   C:\xampp\htdocs\pelayanan-desa\
   ```
2. Jalankan **Apache** dan **MySQL** lewat XAMPP Control Panel
3. Buka `http://localhost/phpmyadmin`, klik **Import**, pilih file `db_desa.sql`, klik **Go**
4. Cek `koneksi.php`, sesuaikan `$host`, `$user`, `$pass`, `$db` kalau setup lokalmu beda dari default XAMPP
5. Akses aplikasinya lewat browser:
   - Sisi warga: `http://localhost/pelayanan-desa/index.php`
   - Sisi admin: `http://localhost/pelayanan-desa/login.php`

### Akun Admin Default
| Username     | Password       |
|--------------|----------------|
| `admindesa`  | `admindesa123` |

> Ganti password ini kalau project pernah dipakai untuk keperluan yang lebih serius. Cara generate hash baru ada di komentar dalam `db_desa.sql`.

---

## 📸 Screenshot

### Halaman Warga — Form Pengajuan Surat
<!-- ![Form Pengajuan Surat](screenshots/index.png) -->

### Halaman Warga — Cek Status Surat
screenshots/cek-status.png

### Login Admin
<!-- ![Login Admin](screenshots/login.png) -->

### Dashboard Admin — Daftar Pengajuan Surat
<!-- ![Dashboard Admin](screenshots/admin.png) -->

### Kelola Data Warga
<!-- ![Kelola Data Warga](screenshots/admin-warga.png) -->

### Contoh Surat yang Dicetak
<!-- ![Cetak Surat](screenshots/cetak-surat.png) -->

> Buat folder `screenshots/` di root repo, taruh gambar-gambar tangkapan layar di sana, lalu hapus tanda komentar (`<!-- -->`) di atas biar gambarnya muncul di README GitHub.

---

## 🗺️ Rencana Pengembangan Selanjutnya (Opsional)

- [ ] Sistem akun/login khusus warga (saat ini akses warga hanya berbasis kecocokan NIK, tanpa password)
- [ ] Notifikasi status surat via WhatsApp/Email
- [ ] Export laporan pengajuan surat (Excel/PDF)
- [ ] Multi-jenis surat yang bisa dikelola dinamis oleh admin (saat ini masih hardcode 3 jenis)
- [ ] Role admin bertingkat (Super Admin, Admin Biasa)

---

## 📄 Lisensi

Project ini dibuat untuk keperluan pembelajaran/portofolio pribadi. Bebas digunakan, dimodifikasi, atau dikembangkan lebih lanjut.
