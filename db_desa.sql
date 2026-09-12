-- ============================================================
-- Database: db_desa
-- Sistem Pelayanan Surat Digital Balai Desa
-- Import file ini lewat phpMyAdmin (tab "Import") atau:
--   mysql -u root -p < db_desa.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS db_desa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_desa;

-- --------------------------------------------------------
-- Tabel: warga  (data penduduk yang boleh mengajukan surat)
-- --------------------------------------------------------
CREATE TABLE warga (
    nik        CHAR(16) NOT NULL,
    nama       VARCHAR(100) NOT NULL,
    alamat     VARCHAR(255) DEFAULT NULL,
    no_hp      VARCHAR(20)  DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (nik)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabel: pengajuan_surat (riwayat & status pengajuan)
-- --------------------------------------------------------
CREATE TABLE pengajuan_surat (
    id_pengajuan     INT AUTO_INCREMENT PRIMARY KEY,
    nik_warga        CHAR(16) NOT NULL,
    jenis_surat      VARCHAR(100) NOT NULL,
    keperluan        TEXT NOT NULL,
    status           ENUM('Pending','Diproses','Selesai','Ditolak') NOT NULL DEFAULT 'Pending',
    tanggal_request  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pengajuan_warga FOREIGN KEY (nik_warga)
        REFERENCES warga(nik) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabel: admin (dipakai login.php buat autentikasi admin)
-- --------------------------------------------------------
CREATE TABLE admin (
    id_admin   INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,   -- WAJIB simpan hasil password_hash(), jangan plain text
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Data dummy untuk testing
-- --------------------------------------------------------
INSERT INTO warga (nik, nama, alamat, no_hp) VALUES
('3201010101010001', 'Budi Santoso', 'Dusun I RT 01/RW 01', '081234567890'),
('3201010101010002', 'Siti Aminah',  'Dusun II RT 02/RW 01', '081234567891'),
('3201010101010003', 'Ahmad Fauzi',  'Dusun III RT 01/RW 02', '081234567892');

INSERT INTO pengajuan_surat (nik_warga, jenis_surat, keperluan, status) VALUES
('3201010101010001', 'Surat Keterangan Tidak Mampu (SKTM)', 'Persyaratan beasiswa sekolah', 'Diproses'),
('3201010101010002', 'Surat Pengantar SKCK', 'Melamar pekerjaan', 'Selesai'),
('3201010101010003', 'Surat Keterangan Domisili', 'Syarat pindah alamat KTP', 'Pending');

-- Akun admin default.
-- Username : admindesa
-- Password : admindesa123   (sudah di-hash bcrypt di bawah, JANGAN diubah manual jadi plain text)
INSERT INTO admin (username, password) VALUES
('admindesa', '$2b$10$DJsmJ3x/yFuq2wWt7aIO6eWfMk47Uz41dkrXg8YzHDrBeRkUfMPFa');

-- Kalau mau ganti password admin nanti, generate hash baru pakai PHP:
--   <?php echo password_hash('password_baru_kamu', PASSWORD_DEFAULT); ?>
-- terus UPDATE tabel admin SET password = 'hasil_hash_nya' WHERE username = 'admindesa';
