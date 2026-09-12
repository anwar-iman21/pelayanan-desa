<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login_admin'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$q = $koneksi->prepare("SELECT pengajuan_surat.*, warga.nama, warga.alamat, warga.nik
                         FROM pengajuan_surat
                         JOIN warga ON pengajuan_surat.nik_warga = warga.nik
                         WHERE pengajuan_surat.id_pengajuan = :id");
$q->bindParam(':id', $id, PDO::PARAM_INT);
$q->execute();
$surat = $q->fetch();

if (!$surat) {
    die("Data surat tidak ditemukan.");
}

if ($surat['status'] !== 'Selesai') {
    die("Surat ini belum berstatus 'Selesai', belum bisa dicetak.");
}

$nomor_surat = str_pad($surat['id_pengajuan'], 3, '0', STR_PAD_LEFT) . '/DESA/' . date('Y', strtotime($surat['tanggal_request']));
$tanggal_surat = date('d F Y', strtotime($surat['tanggal_request']));

$isi = "Adalah benar warga Desa Sukamaju dengan data sebagaimana tersebut di atas, "
     . "dan yang bersangkutan bermaksud mengajukan keperluan: <strong>" . htmlspecialchars($surat['keperluan']) . "</strong>.";

if (stripos($surat['jenis_surat'], 'Tidak Mampu') !== false) {
    $isi = "Adalah benar warga Desa Sukamaju yang termasuk dalam kategori keluarga kurang mampu, "
         . "dengan keperluan: <strong>" . htmlspecialchars($surat['keperluan']) . "</strong>.";
} elseif (stripos($surat['jenis_surat'], 'SKCK') !== false) {
    $isi = "Adalah benar warga Desa Sukamaju dan berkelakuan baik selama tinggal di wilayah ini, "
         . "surat ini digunakan sebagai pengantar untuk keperluan: <strong>" . htmlspecialchars($surat['keperluan']) . "</strong>.";
} elseif (stripos($surat['jenis_surat'], 'Domisili') !== false) {
    $isi = "Adalah benar warga yang berdomisili di Desa Sukamaju sesuai alamat tersebut di atas, "
         . "untuk keperluan: <strong>" . htmlspecialchars($surat['keperluan']) . "</strong>.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak <?php echo htmlspecialchars($surat['jenis_surat']); ?> - <?php echo htmlspecialchars($surat['nama']); ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            background: linear-gradient(160deg, #e8fbf7 0%, #e6f4fb 45%, #eef9f1 100%);
            margin: 0;
            padding: 30px 15px 60px;
            min-height: 100vh;
        }

        .toolbar {
            max-width: 850px;
            margin: 0 auto 25px;
            background: linear-gradient(120deg, #0dcaf0 0%, #14b8a6 55%, #198754 100%);
            padding: 16px 25px;
            border-radius: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: 0 10px 25px rgba(13,148,136,0.25);
        }
        .toolbar .info { color: #fff; font-weight: bold; }
        .toolbar .info small { display: block; font-weight: normal; opacity: .9; }
        .toolbar button {
            background: #fff;
            color: #14532d;
            border: none;
            padding: 10px 22px;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .toolbar button:hover { transform: translateY(-1px); }

        /* ===== Kertas surat: dibuat formal, hitam-putih ===== */
        .kertas {
            background: #fff;
            max-width: 850px;
            margin: 0 auto;
            padding: 50px 60px;
            border-radius: 4px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.12);
            font-family: 'Times New Roman', Times, serif;
            font-size: 13pt;
            color: #000;
            line-height: 1.6;
        }
        .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 25px; }
        .kop h2, .kop h3 { margin: 2px 0; }
        .kop small { display: block; }
        .judul { text-align: center; text-decoration: underline; font-weight: bold; margin: 20px 0; text-transform: uppercase; }
        .nomor { text-align: center; margin-top: -15px; margin-bottom: 20px; }
        table.data-diri td { padding: 3px 0; vertical-align: top; }
        table.data-diri td.label { width: 160px; }
        .isi { text-align: justify; margin: 20px 0; }
        .ttd { margin-top: 60px; width: 260px; margin-left: auto; text-align: center; }
        .ttd .nama-ttd { margin-top: 80px; font-weight: bold; text-decoration: underline; }

        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .kertas { box-shadow: none; border-radius: 0; padding: 20px 40px; }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <div class="info">
            📄 <?php echo htmlspecialchars($surat['jenis_surat']); ?>
            <small>a.n. <?php echo htmlspecialchars($surat['nama']); ?> — Nomor <?php echo htmlspecialchars($nomor_surat); ?></small>
        </div>
        <button onclick="window.print()">🖨️ Cetak / Simpan sebagai PDF</button>
    </div>

    <div class="kertas">
        <div class="kop">
            <h2>PEMERINTAH DESA SUKAMAJU</h2>
            <h3>KECAMATAN SUKAJADI KABUPATEN BANDUNG BARAT</h3>
            <small>Alamat: Jl. Raya Sukamaju No. 12 | Telp. (022) 555-0123 | Email: pemdes.sukamaju@desa.go.id</small>
        </div>

        <div class="judul"><?php echo htmlspecialchars($surat['jenis_surat']); ?></div>
        <div class="nomor">Nomor: <?php echo htmlspecialchars($nomor_surat); ?></div>

        <p>Yang bertanda tangan di bawah ini, Kepala Desa Sukamaju, menerangkan bahwa:</p>

        <table class="data-diri">
            <tr><td class="label">Nama</td><td>: <?php echo htmlspecialchars($surat['nama']); ?></td></tr>
            <tr><td class="label">NIK</td><td>: <?php echo htmlspecialchars($surat['nik']); ?></td></tr>
            <tr><td class="label">Alamat</td><td>: <?php echo htmlspecialchars($surat['alamat']); ?></td></tr>
        </table>

        <p class="isi"><?php echo $isi; ?></p>

        <p>Demikian surat keterangan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>

        <div class="ttd">
            <p>Sukamaju, <?php echo $tanggal_surat; ?></p>
            <p>Kepala Desa Sukamaju</p>
            <p class="nama-ttd">( H. Ahmad Dahlan, S.Sos )</p>
        </div>
    </div>

</body>
</html>