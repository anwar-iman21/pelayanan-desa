<?php
session_start();
include 'koneksi.php';
include 'csrf.php';

$pesan = "";
$status_warga = null;
$nik_dicari = "";

// 1. PROSES KIRIM PENGAJUAN SURAT
if (isset($_POST['kirim'])) {
    if (!csrf_verify()) {
        $pesan = "<div class='alert alert-danger'>⚠️ Sesi form kedaluwarsa, silakan refresh halaman lalu coba lagi.</div>";
    } else {
        $nik         = trim(strip_tags($_POST['nik']));
        $jenis_surat = htmlspecialchars(strip_tags($_POST['jenis_surat']));
        $keperluan   = htmlspecialchars(strip_tags($_POST['keperluan']));

        if (!preg_match('/^\d{16}$/', $nik)) {
            $pesan = "<div class='alert alert-danger'>❌ NIK harus terdiri dari 16 digit angka.</div>";
        } else {
            $cek_warga = $koneksi->prepare("SELECT * FROM warga WHERE nik = :nik");
            $cek_warga->bindParam(':nik', $nik);
            $cek_warga->execute();

            if ($cek_warga->rowCount() > 0) {
                $query = $koneksi->prepare("INSERT INTO pengajuan_surat (nik_warga, jenis_surat, keperluan) VALUES (:nik, :jenis_surat, :keperluan)");
                $query->bindParam(':nik', $nik);
                $query->bindParam(':jenis_surat', $jenis_surat);
                $query->bindParam(':keperluan', $keperluan);

                if ($query->execute()) {
                    $pesan = "<div class='alert alert-success'>✨ Pengajuan surat berhasil dikirim! Silakan cek status surat Anda secara berkala di kolom bawah.</div>";
                } else {
                    $pesan = "<div class='alert alert-danger'>❌ Gagal mengirim pengajuan. Silakan coba lagi.</div>";
                }
            } else {
                $pesan = "<div class='alert alert-danger'>🚫 Akses Ditolak! NIK Anda tidak terdaftar di database Balai Desa.</div>";
            }
        }
    }
}

// 2. PROSES CEK STATUS SURAT MANDIRI VIA NIK
if (isset($_POST['cek_status'])) {
    if (!csrf_verify()) {
        $pesan = "<div class='alert alert-danger'>⚠️ Sesi form kedaluwarsa, silakan refresh halaman lalu coba lagi.</div>";
    } else {
        $nik_dicari = trim(strip_tags($_POST['nik_cek']));

        if (!preg_match('/^\d{16}$/', $nik_dicari)) {
            $pesan = "<div class='alert alert-danger'>❌ NIK harus terdiri dari 16 digit angka.</div>";
        } else {
            $query_status = $koneksi->prepare("SELECT pengajuan_surat.*, warga.nama FROM pengajuan_surat JOIN warga ON pengajuan_surat.nik_warga = warga.nik WHERE pengajuan_surat.nik_warga = :nik ORDER BY tanggal_request DESC");
            $query_status->bindParam(':nik', $nik_dicari);
            $query_status->execute();
            $status_warga = $query_status->fetchAll();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelayanan Mandiri Balai Desa</title>
    <style>
        * { box-sizing: border-box; }
        body {
            background: linear-gradient(160deg, #e8fbf7 0%, #e6f4fb 45%, #eef9f1 100%);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 40px 0;
            display: flex;
            justify-content: center;
            min-height: 100vh;
        }
        .container { width: 100%; max-width: 620px; padding: 0 15px; }

        .card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 12px 35px rgba(13, 148, 136, 0.15);
            overflow: hidden;
            border: none;
            margin-bottom: 30px;
        }
        .card-header {
            padding: 24px;
            text-align: center;
            color: #fff;
            background: linear-gradient(120deg, #0dcaf0 0%, #14b8a6 55%, #198754 100%);
            position: relative;
        }
        .card-header h3 { margin: 0 0 6px 0; font-size: 1.5rem; letter-spacing: .3px; }
        .card-header small { opacity: .95; }

        .card-header.tracking {
            background: linear-gradient(120deg, #198754 0%, #14b8a6 55%, #0dcaf0 100%);
        }

        .card-body { padding: 28px; }
        .mb-4 { margin-bottom: 22px; }
        .form-label { display: block; font-weight: bold; margin-bottom: 8px; color: #135e4c; }

        .form-control, .form-select {
            width: 100%;
            padding: 13px;
            border-radius: 10px;
            border: 1.5px solid #d7ede8;
            box-sizing: border-box;
            font-size: 1rem;
            transition: 0.2s;
            background: #fbfffe;
        }
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #14b8a6;
            box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.15);
        }

        .btn-primary {
            width: 100%;
            background: linear-gradient(90deg, #0dcaf0, #14b8a6);
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-weight: bold;
            color: #fff;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: .5px;
            transition: 0.25s;
            box-shadow: 0 6px 16px rgba(20, 184, 166, 0.35);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 22px rgba(20, 184, 166, 0.45); }

        .btn-success {
            width: 100%;
            background: linear-gradient(90deg, #198754, #14b8a6);
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-weight: bold;
            color: #fff;
            cursor: pointer;
            transition: 0.25s;
            box-shadow: 0 6px 16px rgba(25, 135, 84, 0.3);
        }
        .btn-success:hover { transform: translateY(-2px); box-shadow: 0 10px 22px rgba(25, 135, 84, 0.4); }

        .alert { padding: 15px; margin-bottom: 20px; border-radius: 10px; font-weight: bold; text-align: center; }
        .alert-success { color: #0f5132; background-color: #d1f5ea; border: 1px solid #9fe6cf; }
        .alert-danger { color: #842029; background-color: #f8d7da; border: 1px solid #f5c2c7; }

        .table-tracking { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table-tracking th, .table-tracking td { padding: 12px 10px; border-bottom: 1px solid #e6f2ef; text-align: left; font-size: 0.9rem; }
        .table-tracking th { background-color: #eafaf6; color: #135e4c; }

        .badge { display: inline-block; padding: 6px 12px; font-weight: bold; border-radius: 20px; color: #fff; font-size: 0.78rem; }
        .bg-pending { background-color: #6c757d; }
        .bg-proses { background: linear-gradient(90deg, #ffc107, #fd7e14); color: #212529; }
        .bg-selesai { background: linear-gradient(90deg, #198754, #14b8a6); }
        .bg-ditolak { background-color: #dc3545; }
    </style>
</head>
<body>

<div class="container">
    <!-- FORM PENGAJUAN SURAT -->
    <div class="card">
        <div class="card-header">
            <h3>📝 Form Pelayanan Surat Digital</h3>
            <small>Balai Desa Online — Akses Mandiri & Cepat</small>
        </div>
        <div class="card-body">
            <?php echo $pesan; ?>
            <form action="" method="POST">
                <?php echo csrf_field(); ?>
                <div class="mb-4">
                    <label class="form-label">Nomor Induk Kependudukan (NIK)</label>
                    <input type="text" name="nik" class="form-control" placeholder="Masukkan 16 digit NIK Anda" maxlength="16" pattern="\d{16}" title="NIK harus 16 digit angka" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Jenis Surat Keterangan</label>
                    <select name="jenis_surat" class="form-select" required>
                        <option value="">-- Pilih Jenis Surat --</option>
                        <option value="Surat Keterangan Tidak Mampu (SKTM)">Surat Keterangan Tidak Mampu (SKTM)</option>
                        <option value="Surat Pengantar SKCK">Surat Pengantar SKCK</option>
                        <option value="Surat Keterangan Domisili">Surat Keterangan Domisili</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label">Keperluan / Alasan Pengajuan</label>
                    <textarea name="keperluan" class="form-control" rows="3" placeholder="Contoh: Persyaratan beasiswa sekolah" required></textarea>
                </div>
                <button type="submit" name="kirim" class="btn-primary">Kirim Pengajuan Surat</button>
            </form>
        </div>
    </div>

    <!-- TRACKING STATUS SURAT -->
    <div class="card">
        <div class="card-header tracking">
            <h3>🔍 Cek Status Surat Mandiri</h3>
            <small>Masukkan NIK Anda untuk memantau proses surat</small>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <?php echo csrf_field(); ?>
                <div class="mb-4">
                    <input type="text" name="nik_cek" class="form-control" placeholder="Masukkan NIK Anda..." value="<?php echo htmlspecialchars($nik_dicari); ?>" maxlength="16" pattern="\d{16}" title="NIK harus 16 digit angka" required>
                </div>
                <button type="submit" name="cek_status" class="btn-success">Lacak Surat Saya</button>
            </form>

            <?php if (isset($_POST['cek_status'])): ?>
                <div style="margin-top: 25px; border-top: 2px dashed #d7ede8; padding-top: 15px;">
                    <h4 style="margin: 0 0 10px 0; color: #135e4c;">Hasil Pencarian Warga:</h4>
                    <?php if ($status_warga && count($status_warga) > 0): ?>
                        <p>Nama Warga: <strong><?php echo htmlspecialchars($status_warga[0]['nama']); ?></strong></p>
                        <table class="table-tracking">
                            <thead>
                                <tr>
                                    <th>Jenis Surat</th>
                                    <th>Tanggal Masuk</th>
                                    <th>Status Surat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($status_warga as $sw): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sw['jenis_surat']); ?></td>
                                        <td><?php echo htmlspecialchars($sw['tanggal_request']); ?></td>
                                        <td>
                                            <?php
                                            $kelas_badge = 'bg-pending';
                                            if ($sw['status'] == 'Diproses') $kelas_badge = 'bg-proses';
                                            if ($sw['status'] == 'Selesai') $kelas_badge = 'bg-selesai';
                                            if ($sw['status'] == 'Ditolak') $kelas_badge = 'bg-ditolak';
                                            ?>
                                            <span class="badge <?php echo $kelas_badge; ?>"><?php echo htmlspecialchars($sw['status']); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php elseif ($status_warga !== null): ?>
                        <div class="alert alert-danger" style="margin-top: 10px;">❌ Tidak ada riwayat pengajuan surat untuk NIK tersebut.</div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>