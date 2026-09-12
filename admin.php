<?php
session_start();
include 'koneksi.php';
include 'csrf.php';

// Keamanan: Proteksi Session. Jika belum login, tendang balik ke login.php
if (!isset($_SESSION['login_admin'])) {
    header("Location: login.php");
    exit;
}

// Fitur Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$notif = "";

if (isset($_POST['update_status'])) {
    if (!csrf_verify()) {
        $notif = "<div class='alert alert-danger'>Sesi form kedaluwarsa, silakan coba lagi.</div>";
    } else {
        $id_pengajuan = htmlspecialchars(strip_tags($_POST['id_pengajuan']));
        $status       = htmlspecialchars(strip_tags($_POST['status']));

        $status_valid = ['Pending', 'Diproses', 'Selesai', 'Ditolak'];
        if (in_array($status, $status_valid, true) && is_numeric($id_pengajuan)) {
            $update = $koneksi->prepare("UPDATE pengajuan_surat SET status = :status WHERE id_pengajuan = :id_pengajuan");
            $update->bindParam(':status', $status);
            $update->bindParam(':id_pengajuan', $id_pengajuan);
            $update->execute();
            $notif = "<div class='alert alert-success'>✅ Status berhasil diperbarui.</div>";
        }
    }
}

$query = $koneksi->query
("SELECT pengajuan_surat.*, warga.nama FROM pengajuan_surat JOIN warga ON pengajuan_surat.nik_warga = warga.nik ORDER BY tanggal_request DESC");
$data_surat = $query->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin Balai Desa</title>
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
        .navbar-brand {
            color: #fff;
            font-weight: bold;
            font-size: 1.4rem;
            text-decoration: none;
        }
        .nav-right { display: flex; align-items: center; gap: 20px; }
        .nav-links a {
            color: #d1fae5;
            text-decoration: none;
            font-weight: 600;
            margin-right: 18px;
            transition: 0.2s;
        }
        .nav-links a:hover { color: #fff; }
        .btn-logout {
            background: linear-gradient(90deg, #dc3545, #b02a37);
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: bold;
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.2s;
        }
        .btn-logout:hover { opacity: 0.9; transform: translateY(-1px); }

        .container { max-width: 1140px; margin: 0 auto; padding: 0 20px; }

        .alert { padding: 14px 18px; margin: 25px 0 0; border-radius: 10px; font-weight: bold; text-align: center; }
        .alert-success { color: #0f5132; background-color: #d1f5ea; border: 1px solid #9fe6cf; }
        .alert-danger { color: #842029; background-color: #f8d7da; border: 1px solid #f5c2c7; }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(13, 148, 136, 0.12);
            overflow: hidden;
            margin-top: 25px;
            margin-bottom: 40px;
        }
        .card-header {
            padding: 20px 25px;
            color: #fff;
            font-weight: bold;
            font-size: 1.1rem;
            background: linear-gradient(120deg, #0dcaf0 0%, #14b8a6 55%, #198754 100%);
        }
        .card-body { padding: 25px; }

        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            text-align: left;
            padding: 12px 10px;
            color: #135e4c;
            background: #eafaf6;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        thead th:first-child { border-radius: 10px 0 0 10px; }
        thead th:last-child { border-radius: 0 10px 10px 0; }
        tbody td {
            padding: 14px 10px;
            border-bottom: 1px solid #eef2f1;
            vertical-align: middle;
            font-size: 0.92rem;
        }
        tbody tr:hover { background: #f7fefc; }

        .nama-warga { font-weight: bold; color: #212529; }
        .nik-warga { color: #6c757d; }
        .jenis-surat { font-weight: bold; color: #0dcaf0; }
        .tanggal { color: #6c757d; font-size: 0.85rem; }

        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            color: #fff;
            font-size: 0.78rem;
            font-weight: bold;
        }
        .bg-pending  { background-color: #6c757d; }
        .bg-proses   { background: linear-gradient(90deg, #ffc107, #fd7e14); color: #212529; }
        .bg-selesai  { background: linear-gradient(90deg, #198754, #14b8a6); }
        .bg-ditolak  { background-color: #dc3545; }

        .aksi-cell { min-width: 230px; }
        .form-aksi { display: flex; gap: 8px; margin-bottom: 8px; }
        .form-select {
            flex: 1;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1.5px solid #d7ede8;
            font-size: 0.85rem;
            background: #fbfffe;
        }
        .form-select:focus { outline: none; border-color: #14b8a6; }
        .btn-update {
            background: linear-gradient(90deg, #0dcaf0, #14b8a6);
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 0.85rem;
            cursor: pointer;
            white-space: nowrap;
        }
        .btn-update:hover { opacity: 0.92; }
        .btn-cetak {
            display: block;
            text-align: center;
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1.5px solid #14532d;
            color: #14532d;
            font-weight: bold;
            font-size: 0.85rem;
            text-decoration: none;
            transition: 0.2s;
        }
        .btn-cetak:hover { background: #14532d; color: #fff; }

        .empty-state { text-align: center; padding: 60px 20px; color: #6c757d; font-size: 1.1rem; }
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
            📋 Daftar Pengajuan Surat Warga (Halo, Admin <?php echo htmlspecialchars($_SESSION['login_admin']); ?>!)
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Warga</th>
                            <th>NIK</th>
                            <th>Jenis Surat</th>
                            <th>Keperluan</th>
                            <th>Tanggal Masuk</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($data_surat) > 0): ?>
                            <?php foreach ($data_surat as $surat): ?>
                                <tr>
                                    <td class="nama-warga"><?php echo htmlspecialchars($surat['nama']); ?></td>
                                    <td class="nik-warga"><?php echo htmlspecialchars($surat['nik_warga']); ?></td>
                                    <td class="jenis-surat"><?php echo htmlspecialchars($surat['jenis_surat']); ?></td>
                                    <td><?php echo htmlspecialchars($surat['keperluan']); ?></td>
                                    <td class="tanggal"><?php echo htmlspecialchars($surat['tanggal_request']); ?></td>
                                    <td>
                                        <?php
                                        $badge = 'bg-pending';
                                        if ($surat['status'] == 'Diproses') $badge = 'bg-proses';
                                        if ($surat['status'] == 'Selesai') $badge = 'bg-selesai';
                                        if ($surat['status'] == 'Ditolak') $badge = 'bg-ditolak';
                                        ?>
                                        <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($surat['status']); ?></span>
                                    </td>
                                    <td class="aksi-cell">
                                        <form action="" method="POST" class="form-aksi">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="id_pengajuan" value="<?php echo htmlspecialchars($surat['id_pengajuan']); ?>">
                                            <select name="status" class="form-select" required>
                                                <option value="Pending" <?php if($surat['status']=='Pending') echo 'selected'; ?>>Pending</option>
                                                <option value="Diproses" <?php if($surat['status']=='Diproses') echo 'selected'; ?>>Diproses</option>
                                                <option value="Selesai" <?php if($surat['status']=='Selesai') echo 'selected'; ?>>Selesai</option>
                                                <option value="Ditolak" <?php if($surat['status']=='Ditolak') echo 'selected'; ?>>Ditolak</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn-update">Update</button>
                                        </form>
                                        <?php if ($surat['status'] == 'Selesai'): ?>
                                            <a href="cetak_surat.php?id=<?php echo (int) $surat['id_pengajuan']; ?>" target="_blank" class="btn-cetak">🖨️ Cetak Surat</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="empty-state">📭 Belum ada pengajuan surat dari warga.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>