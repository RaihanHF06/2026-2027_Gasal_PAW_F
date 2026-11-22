<?php
include 'auth.php';
include 'koneksi.php';

$tanggal_mulai = isset($_GET['tanggal_mulai'])
    ? $_GET['tanggal_mulai'] : date('Y-m-01');

$tanggal_selesai = isset($_GET['tanggal_selesai'])
    ? $_GET['tanggal_selesai'] : date('Y-m-d');

// Menentukan batas tanggal laporan
$batas_selesai = date('Y-m-d', strtotime($tanggal_selesai . ' +1 day'));

// Mengambil data laporan
$stmt = mysqli_prepare($koneksi, "SELECT transaksi.*,
    pelanggan.nama AS nama_pelanggan,
    pembayaran.metode,
    pembayaran.waktu_bayar
    FROM transaksi
    LEFT JOIN pelanggan ON transaksi.pelanggan_id=pelanggan.id
    LEFT JOIN pembayaran ON transaksi.id=pembayaran.transaksi_id
    WHERE transaksi.waktu_transaksi >= ?
    AND transaksi.waktu_transaksi < ?
    ORDER BY transaksi.waktu_transaksi ASC");

mysqli_stmt_bind_param($stmt, "ss", $tanggal_mulai, $batas_selesai);
mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);

// Menghitung total penjualan
$stmt = mysqli_prepare($koneksi, "SELECT SUM(total) AS jumlah
    FROM transaksi
    WHERE waktu_transaksi >= ?
    AND waktu_transaksi < ?");

mysqli_stmt_bind_param($stmt, "ss", $tanggal_mulai, $batas_selesai);
mysqli_stmt_execute($stmt);
$total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="isi">
<div class="kotak">

    <a class="tombol" href="index.php">Kembali ke Dashboard</a>

    <h2>Laporan Penjualan</h2>

    <h3>Filter Tanggal</h3>

    <form method="GET">

        <label>Tanggal Mulai</label>
        <input type="date" name="tanggal_mulai"
            value="<?php echo htmlspecialchars($tanggal_mulai); ?>" required>

        <label>Tanggal Selesai</label>
        <input type="date" name="tanggal_selesai"
            value="<?php echo htmlspecialchars($tanggal_selesai); ?>" required>

        <button type="submit">Tampilkan Laporan</button>

    </form>

    <h3>
        Periode:
        <?php echo htmlspecialchars($tanggal_mulai); ?>
        sampai
        <?php echo htmlspecialchars($tanggal_selesai); ?>
    </h3>

    <h3>
        Total Penjualan:
        Rp <?php echo number_format($total['jumlah'] ?? 0, 0, ',', '.'); ?>
    </h3>

    <table>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Pelanggan</th>
            <th>Keterangan</th>
            <th>Total</th>
            <th>Metode Pembayaran</th>
        </tr>

        <?php
        $no = 1;

        while ($row = mysqli_fetch_assoc($query)) {
        ?>
            <tr>
                <td><?php echo $no++; ?></td>

                <td>
                    <?php echo htmlspecialchars($row['waktu_transaksi']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($row['nama_pelanggan'] ?? '-'); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($row['keterangan'] ?? '-'); ?>
                </td>

                <td>
                    Rp <?php echo number_format($row['total'], 0, ',', '.'); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($row['metode'] ?? '-'); ?>
                </td>
            </tr>
        <?php } ?>
    </table>

</div>
</div>

</body>
</html>