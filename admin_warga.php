<?php
session_start();
include 'koneksi.php';
include 'csrf.php';

if (!isset($_SESSION['login_admin'])) {
    header("Location: login.php");
    exit;
}

$notif = "";
$edit_data = null;

// ================== TAMBAH WARGA ==================
if (isset($_POST['tambah_warga'])) {
    if (!csrf_verify()) {
        $notif = "<div class='alert alert-danger'>Sesi form kedaluwarsa, silakan coba lagi.</div>";
    } else {
        $nik    = trim(strip_tags($_POST['nik']));
        $nama   = htmlspecialchars(strip_tags(trim($_POST['nama'])));
        $alamat = htmlspecialchars(strip_tags(trim($_POST['alamat'])));
        $no_hp  = htmlspecialchars(strip_tags(trim($_POST['no_hp'])));

        if (!preg_match('/^\d{16}$/', $nik)) {
            $notif = "<div class='alert alert-danger'>❌ NIK harus 16 digit angka.</div>";
        } elseif ($nama === '') {
            $notif = "<div class='alert alert-danger'>❌ Nama wajib diisi.</div>";
        } else {
            $cek = $koneksi->prepare("SELECT nik FROM warga WHERE nik = :nik");
            $cek->bindParam(':nik', $nik);
            $cek->execute();

            if ($cek->rowCount() > 0) {
                $notif = "<div class='alert alert-danger'>❌ NIK sudah terdaftar, gak bisa dobel.</div>";
            } else {
                $insert = $koneksi->prepare("INSERT INTO warga (nik, nama, alamat, no_hp) VALUES (:nik, :nama, :alamat, :no_hp)");
                $insert->bindParam(':nik', $nik);
                $insert->bindParam(':nama', $nama);
                $insert->bindParam(':alamat', $alamat);
                $insert->bindParam(':no_hp', $no_hp);
                $insert->execute();
                $notif = "<div class='alert alert-success'>✅ Data warga berhasil ditambahkan.</div>";
            }
        }
    }
}

// ================== UPDATE WARGA ==================
if (isset($_POST['update_warga'])) {
    if (!csrf_verify()) {
        $notif = "<div class='alert alert-danger'>Sesi form kedaluwarsa, silakan coba lagi.</div>";
    } else {
        $nik    = trim(strip_tags($_POST['nik']));
        $nama   = htmlspecialchars(strip_tags(trim($_POST['nama'])));
        $alamat = htmlspecialchars(strip_tags(trim($_POST['alamat'])));
        $no_hp  = htmlspecialchars(strip_tags(trim($_POST['no_hp'])));

        if ($nama === '') {
            $notif = "<div class='alert alert-danger'>❌ Nama wajib diisi.</div>";
        } else {
            $update = $koneksi->prepare("UPDATE warga SET nama = :nama, alamat = :alamat, no_hp = :no_hp WHERE nik = :nik");
            $update->bindParam(':nama', $nama);
            $update->bindParam(':alamat', $alamat);
            $update->bindParam(':no_hp', $no_hp);
            $update->bindParam(':nik', $nik);
            $update->execute();
            $notif = "<div class='alert alert-success'>✅ Data warga berhasil diperbarui.</div>";
        }
    }
}

// ================== HAPUS WARGA ==================
if (isset($_POST['hapus_warga'])) {
    if (!csrf_verify()) {
        $notif = "<div class='alert alert-danger'>Sesi form kedaluwarsa, silakan coba lagi.</div>";
    } else {
        $nik = trim(strip_tags($_POST['nik']));
        $hapus = $koneksi->prepare("DELETE FROM warga WHERE nik = :nik");
        $hapus->bindParam(':nik', $nik);
        $hapus->execute();
        $notif = "<div class='alert alert-success'>🗑️ Data warga (beserta riwayat pengajuannya) berhasil dihapus.</div>";
    }
}

// ================== AMBIL DATA UNTUK MODE EDIT ==================
if (isset($_GET['edit'])) {
    $nik_edit = trim(strip_tags($_GET['edit']));
    $q = $koneksi->prepare("SELECT * FROM warga WHERE nik = :nik");
    $q->bindParam(':nik', $nik_edit);
    $q->execute();
    $edit_data = $q->fetch();
}

$list_warga = $koneksi->query("SELECT * FROM warga ORDER BY nama ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Warga</title>
    <style>
        * { box-sizing: border-box; }
        body {
            background: linear-gradient(160deg, #e8fbf7 0%, #e6f4fb 45%, #eef9f1 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(120deg, #0f172a 0%, #14532d 100%);
            padding: 18px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }
        .navbar .container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .navbar-brand { color: #fff; font-weight: bold; font-size: 1.4rem; text-decoration: none; }
        .nav-right { display: flex; align-items: center; gap: 20px; }
        .nav-links a { color: #d1fae5; text-decoration: none; font-weight: 600; margin-right: 18px; transition: 0.2s; }
        .nav-links a:hover { color: #fff; }
        .btn-logout {
            background: linear-gradient(90deg, #dc3545, #b02a37);
            color: #fff; border: none; padding: 10px 18px; border-radius: 8px;
            font-weight: bold; text-decoration: none; font-size: 0.9rem; transition: 0.2s;
        }
        .btn-logout:hover { opacity: 0.9; transform: translateY(-1px); }

        .container { max-width: 1140px; margin: 0 auto; padding: 0 20px; }

        .alert { padding: 14px 18px; margin: 25px 0 0; border-radius: 10px; font-weight: bold; text-align: center; }
        .alert-success { color: #0f5132; background-color: #d1f5ea; border: 1px solid #9fe6cf; }
        .alert-danger { color: #842029; background-color: #f8d7da; border: 1px solid #f5c2c7; }

        .card {
            background: #fff; border-radius: 16px;
            box-shadow: 0 10px 30px rgba(13, 148, 136, 0.12);
            overflow: hidden; margin-top: 25px; margin-bottom: 30px;
        }
        .card-header {
            padding: 20px 25px; color: #fff; font-weight: bold; font-size: 1.1rem;
            background: linear-gradient(120deg, #0dcaf0 0%, #14b8a6 55%, #198754 100%);
        }
        .card-body { padding: 25px; }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 16px;
        }
        .col-6 { grid-column: span 6; }
        .col-8 { grid-column: span 8; }
        .col-4 { grid-column: span 4; }
        @media (max-width: 700px) {
            .col-6, .col-8, .col-4 { grid-column: span 12; }
        }

        .form-label { display: block; font-weight: bold; margin-bottom: 6px; color: #135e4c; font-size: 0.9rem; }
        .form-control {
            width: 100%; padding: 11px 12px; border-radius: 10px;
            border: 1.5px solid #d7ede8; font-size: 0.95rem; background: #fbfffe;
        }
        .form-control:focus { outline: none; border-color: #14b8a6; box-shadow: 0 0 0 4px rgba(20,184,166,.15); }
        .form-control[readonly] { background: #eef2f1; color: #6c757d; }
        .hint { font-size: 0.78rem; color: #6c757d; display: block; margin-top: 4px; }

        .form-actions { margin-top: 20px; display: flex; gap: 10px; }
        .btn-save {
            background: linear-gradient(90deg, #0dcaf0, #14b8a6);
            color: #fff; border: none; padding: 12px 26px; border-radius: 10px;
            font-weight: bold; cursor: pointer; box-shadow: 0 6px 16px rgba(20,184,166,.3);
        }
        .btn-save:hover { transform: translateY(-1px); }
        .btn-cancel {
            background: #e9ecef; color: #495057; border: none; padding: 12px 26px;
            border-radius: 10px; font-weight: bold; text-decoration: none; display: inline-block;
        }

        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            text-align: left; padding: 12px 10px; color: #135e4c; background: #eafaf6;
            font-size: 0.85rem; text-transform: uppercase; letter-spacing: .3px;
        }
        thead th:first-child { border-radius: 10px 0 0 10px; }
        thead th:last-child { border-radius: 0 10px 10px 0; }
        tbody td { padding: 14px 10px; border-bottom: 1px solid #eef2f1; vertical-align: middle; font-size: 0.92rem; }
        tbody tr:hover { background: #f7fefc; }

        .aksi-cell { display: flex; gap: 8px; }
        .btn-edit, .btn-hapus {
            padding: 7px 14px; border-radius: 8px; font-size: 0.82rem; font-weight: bold;
            text-decoration: none; border: 1.5px solid transparent; cursor: pointer;
        }
        .btn-edit { border-color: #0dcaf0; color: #0dcaf0; background: #fff; }
        .btn-edit:hover { background: #0dcaf0; color: #fff; }
        .btn-hapus { border-color: #dc3545; color: #dc3545; background: #fff; }
        .btn-hapus:hover { background: #dc3545; color: #fff; }

        .empty-state { text-align: center; padding: 50px 20px; color: #6c757d; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="admin.php">🏢 Dashboard Admin Balai Desa</a>
        <div class="nav-right">
            <div class="nav-links">
                <a href="admin.php">📋 Pengajuan Surat</a>
                <a href="admin_warga.php">👥 Data Warga</a>
            </div>
            <a href="admin.php?logout=1" class="btn-logout" onclick="return confirm('Yakin ingin keluar?')">🚪 LOGOUT</a>
        </div>
    </div>
</nav>

<div class="container">
    <?php echo $notif; ?>

    <div class="card">
        <div class="card-header">
            <?php echo $edit_data ? '✏️ Edit Data Warga' : '➕ Tambah Data Warga'; ?>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <?php echo csrf_field(); ?>
                <div class="form-grid">
                    <div class="col-6">
                        <label class="form-label">NIK (16 digit)</label>
                        <input type="text" name="nik" class="form-control" maxlength="16" required
                               value="<?php echo $edit_data ? htmlspecialchars($edit_data['nik']) : ''; ?>"
                               <?php echo $edit_data ? 'readonly' : ''; ?>>
                        <?php if ($edit_data): ?>
                            <span class="hint">NIK tidak bisa diubah. Kalau salah, hapus data lalu tambah ulang.</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" required
                               value="<?php echo $edit_data ? htmlspecialchars($edit_data['nama']) : ''; ?>">
                    </div>
                    <div class="col-8">
                        <label class="form-label">Alamat</label>
                        <input type="text" name="alamat" class="form-control"
                               value="<?php echo $edit_data ? htmlspecialchars($edit_data['alamat']) : ''; ?>">
                    </div>
                    <div class="col-4">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="no_hp" class="form-control"
                               value="<?php echo $edit_data ? htmlspecialchars($edit_data['no_hp']) : ''; ?>">
                    </div>
                </div>
                <div class="form-actions">
                    <?php if ($edit_data): ?>
                        <button type="submit" name="update_warga" class="btn-save">💾 Simpan Perubahan</button>
                        <a href="admin_warga.php" class="btn-cancel">Batal</a>
                    <?php else: ?>
                        <button type="submit" name="tambah_warga" class="btn-save">➕ Tambah Warga</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">👥 Daftar Warga Terdaftar</div>
        <div class="card-body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>No. HP</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($list_warga) > 0): ?>
                            <?php foreach ($list_warga as $w): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($w['nik']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($w['nama']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($w['alamat']); ?></td>
                                    <td><?php echo htmlspecialchars($w['no_hp']); ?></td>
                                    <td class="aksi-cell">
                                        <a href="admin_warga.php?edit=<?php echo urlencode($w['nik']); ?>" class="btn-edit">✏️ Edit</a>
                                        <form action="" method="POST" onsubmit="return confirm('Yakin hapus warga ini? Riwayat pengajuan suratnya juga ikut terhapus.');">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="nik" value="<?php echo htmlspecialchars($w['nik']); ?>">
                                            <button type="submit" name="hapus_warga" class="btn-hapus">🗑️ Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="empty-state">Belum ada data warga.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>