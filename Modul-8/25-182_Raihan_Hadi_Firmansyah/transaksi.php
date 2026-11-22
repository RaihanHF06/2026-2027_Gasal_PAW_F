<?php
include 'auth.php';
include 'koneksi.php';

$pesan = "";

// Proses transaksi
if (isset($_POST['simpan'])) {

    $pelanggan = $_POST['pelanggan_id'];
    $barang = (int) $_POST['barang_id'];
    $qty = (int) $_POST['qty'];
    $metode = $_POST['metode'];
    $tanggal = date('Y-m-d');

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM barang WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $barang);
    mysqli_stmt_execute($stmt);
    $data_barang = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$data_barang || $qty < 1) {
        $pesan = "Barang atau jumlah pembelian tidak valid.";
    } elseif ($data_barang['stok'] < $qty) {
        $pesan = "Stok barang tidak mencukupi.";
    } else {

        $harga = $data_barang['harga'];
        $total = $harga * $qty;
        $nama_barang = $data_barang['nama_barang'];
        $keterangan = "Pembelian " . $nama_barang;

        mysqli_begin_transaction($koneksi);

        // Menyimpan transaksi
        $stmt = mysqli_prepare($koneksi, "INSERT INTO transaksi (waktu_transaksi, keterangan, total, pelanggan_id) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssds", $tanggal, $keterangan, $total, $pelanggan);
        $query1 = mysqli_stmt_execute($stmt);

        if ($query1) {

            $id_transaksi = mysqli_insert_id($koneksi);

            // Menyimpan detail transaksi
            $stmt = mysqli_prepare($koneksi, "INSERT INTO transaksi_detail (transaksi_id, barang_id, harga, qty) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iidi", $id_transaksi, $barang, $harga, $qty);
            $query2 = mysqli_stmt_execute($stmt);

            // Mengurangi stok barang
            $stmt = mysqli_prepare($koneksi, "UPDATE barang SET stok = stok - ? WHERE id=? AND stok >= ?");
            mysqli_stmt_bind_param($stmt, "iii", $qty, $barang, $qty);
            $query3 = mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0;

            // Menyimpan pembayaran
            $stmt = mysqli_prepare($koneksi, "INSERT INTO pembayaran (waktu_bayar, total, metode, transaksi_id) VALUES (NOW(), ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "dsi", $total, $metode, $id_transaksi);
            $query4 = mysqli_stmt_execute($stmt);

            if ($query2 && $query3 && $query4) {
                mysqli_commit($koneksi);
                $pesan = "Transaksi berhasil disimpan.";
            } else {
                mysqli_rollback($koneksi);
                $pesan = "Transaksi gagal disimpan.";
            }

        } else {
            mysqli_rollback($koneksi);
            $pesan = "Transaksi gagal dibuat.";
        }
    }
}

// Mengambil data pelanggan
$pelanggan = mysqli_query($koneksi, "SELECT * FROM pelanggan ORDER BY nama");

// Mengambil barang yang masih tersedia
$barang = mysqli_query($koneksi, "SELECT * FROM barang WHERE stok > 0 ORDER BY nama_barang");

// Mengambil daftar transaksi
$data = mysqli_query($koneksi, "SELECT transaksi.*, pelanggan.nama AS nama_pelanggan, pembayaran.metode
FROM transaksi
LEFT JOIN pelanggan ON transaksi.pelanggan_id=pelanggan.id
LEFT JOIN pembayaran ON transaksi.id=pembayaran.transaksi_id
ORDER BY transaksi.id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Transaksi Penjualan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="isi">
<div class="kotak">

    <a class="tombol" href="index.php">Kembali ke Dashboard</a>

    <h2>Transaksi Penjualan</h2>

    <?php if ($pesan != "") { ?>
        <div class="pesan"><?php echo htmlspecialchars($pesan); ?></div>
    <?php } ?>

    <h3>Tambah Transaksi</h3>

    <form method="POST">

        <label>Pelanggan</label>
        <select name="pelanggan_id" required>
            <option value="">Pilih Pelanggan</option>

            <?php while ($p = mysqli_fetch_assoc($pelanggan)) { ?>
                <option value="<?php echo htmlspecialchars($p['id']); ?>">
                    <?php echo htmlspecialchars($p['nama']); ?>
                </option>
            <?php } ?>
        </select>

        <label>Barang</label>
        <select name="barang_id" required>
            <option value="">Pilih Barang</option>

            <?php while ($b = mysqli_fetch_assoc($barang)) { ?>
                <option value="<?php echo $b['id']; ?>">
                    <?php echo htmlspecialchars($b['nama_barang']); ?>
                    - Rp <?php echo number_format($b['harga'], 0, ',', '.'); ?>
                    (Stok: <?php echo $b['stok']; ?>)
                </option>
            <?php } ?>
        </select>

        <label>Jumlah</label>
        <input type="number" name="qty" min="1" required>

        <label>Metode Pembayaran</label>
        <select name="metode" required>
            <option value="">Pilih Metode</option>
            <option value="Tunai">Tunai</option>
            <option value="Transfer">Transfer</option>
        </select>

        <button type="submit" name="simpan">Simpan Transaksi</button>

    </form>

    <h3>Daftar Transaksi</h3>

    <table>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Pelanggan</th>
            <th>Keterangan</th>
            <th>Total</th>
            <th>Pembayaran</th>
        </tr>

        <?php $no = 1; while ($row = mysqli_fetch_assoc($data)) { ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($row['waktu_transaksi']); ?></td>
                <td><?php echo htmlspecialchars($row['nama_pelanggan'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['keterangan'] ?? '-'); ?></td>
                <td>Rp <?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                <td><?php echo htmlspecialchars($row['metode'] ?? '-'); ?></td>
            </tr>
        <?php } ?>
    </table>

</div>
</div>

</body>
</html>