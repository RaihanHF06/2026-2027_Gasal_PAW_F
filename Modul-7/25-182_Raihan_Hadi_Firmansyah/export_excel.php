<?php
include 'koneksi.php';

$tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : '';
$tanggal_selesai = isset($_GET['tanggal_selesai']) ? $_GET['tanggal_selesai'] : '';

if ($tanggal_mulai == "" || $tanggal_selesai == "" || $tanggal_mulai > $tanggal_selesai) {
    die("Rentang tanggal tidak valid.");
}

$mulai = mysqli_real_escape_string($koneksi, $tanggal_mulai);
$selesai = mysqli_real_escape_string($koneksi, $tanggal_selesai);

$query = "SELECT DATE(waktu_transaksi) AS tanggal, SUM(total) AS total_harian FROM transaksi WHERE DATE(waktu_transaksi) BETWEEN '$mulai' AND '$selesai' GROUP BY DATE(waktu_transaksi) ORDER BY tanggal ASC";

$hasil = mysqli_query($koneksi, $query);

if (!$hasil) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=laporan_penjualan.xls");

echo "<table border='1'>";

echo "<tr>";
echo "<th>No</th>";
echo "<th>Total Pendapatan</th>";
echo "<th>Tanggal Transaksi</th>";
echo "</tr>";

$no = 1;
$total_pendapatan = 0;

while ($baris = mysqli_fetch_assoc($hasil)) {

    echo "<tr>";

    echo "<td>" . $no++ . "</td>";
    echo "<td>" . $baris['total_harian'] . "</td>";
    echo "<td>" . $baris['tanggal'] . "</td>";

    echo "</tr>";

    $total_pendapatan += $baris['total_harian'];
}

echo "<tr>";
echo "<th>Total Keseluruhan</th>";
echo "<th>" . $total_pendapatan . "</th>";
echo "<th></th>";
echo "</tr>";

echo "</table>";
?>