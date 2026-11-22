<?php
include 'auth.php';
cek_owner();
include 'koneksi.php';

$pesan = "";
$edit = null;

// Mengambil data barang untuk diedit
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM barang WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// Proses tambah dan edit barang
if (isset($_POST['simpan'])) {
    $kode_barang = trim($_POST['kode_barang']);
    $nama_barang = trim($_POST['nama_barang']);
    $harga = (float) $_POST['harga'];
    $stok = (int) $_POST['stok'];
    $supplier_id = $_POST['supplier_id'] == "" ? null : (int) $_POST['supplier_id'];

    if (isset($_POST['id']) && $_POST['id'] != "") {
        $id = (int) $_POST['id'];

        $stmt = mysqli_prepare($koneksi, "UPDATE barang SET kode_barang=?, nama_barang=?, harga=?, stok=?, supplier_id=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssdiii", $kode_barang, $nama_barang, $harga, $stok, $supplier_id, $id);
    } else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO barang (kode_barang, nama_barang, harga, stok, supplier_id) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssdii", $kode_barang, $nama_barang, $harga, $stok, $supplier_id);
    }

    if (mysqli_stmt_execute($stmt)) {
        header("Location: barang.php");
        exit;
    } else {
        $pesan = "Data barang gagal disimpan. Pastikan kode barang belum digunakan.";
    }
}

// Proses hapus barang
if (isset($_POST['hapus'])) {
    $id = (int) $_POST['id'];

    $stmt = mysqli_prepare($koneksi, "DELETE FROM barang WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: barang.php");
        exit;
    } else {
        $pesan = "Barang tidak dapat dihapus karena sudah digunakan dalam transaksi.";
    }
}

$data = mysqli_query($koneksi, "SELECT barang.*, supplier.nama AS nama_supplier FROM barang LEFT JOIN supplier ON barang.supplier_id=supplier.id ORDER BY barang.id");

$data_supplier = mysqli_query($koneksi, "SELECT * FROM supplier ORDER BY nama");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Barang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="isi">
<div class="kotak">
    <a class="tombol" href="index.php">Kembali ke Dashboard</a>
    <h2>Data Barang</h2>

    <?php if ($pesan != "") { ?>
        <div class="pesan"><?php echo htmlspecialchars($pesan); ?></div>
    <?php } ?>

    <h3><?php echo $edit ? 'Edit Barang' : 'Tambah Barang'; ?></h3>

    <form method="POST">

        <?php if ($edit) { ?>
            <input type="hidden" name="id" value="<?php echo $edit['id']; ?>">
        <?php } ?>

        <label>Kode Barang</label>
        <input type="text" name="kode_barang" required
            value="<?php echo htmlspecialchars($edit['kode_barang'] ?? ''); ?>">

        <label>Nama Barang</label>
        <input type="text" name="nama_barang" required
            value="<?php echo htmlspecialchars($edit['nama_barang'] ?? ''); ?>">

        <label>Harga</label>
        <input type="number" name="harga" min="0" step="0.01" required
            value="<?php echo htmlspecialchars($edit['harga'] ?? ''); ?>">

        <label>Stok</label>
        <input type="number" name="stok" min="0" required
            value="<?php echo htmlspecialchars($edit['stok'] ?? ''); ?>">

        <label>Supplier</label>
        <select name="supplier_id">
            <option value="">-- Pilih Supplier --</option>

            <?php while ($s = mysqli_fetch_assoc($data_supplier)) { ?>
                <option value="<?php echo $s['id']; ?>"
                    <?php if ($edit && $edit['supplier_id'] == $s['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($s['nama']); ?>
                </option>
            <?php } ?>
        </select>

        <?php if ($edit) { ?>
            <button type="submit" name="simpan">Simpan Perubahan</button>
            <a class="tombol" href="barang.php">Batal</a>
        <?php } else { ?>
            <button type="submit" name="simpan">Tambah Barang</button>
        <?php } ?>

    </form>

    <h3>Daftar Barang</h3>

    <table>
        <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Nama Barang</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Supplier</th>
            <th>Aksi</th>
        </tr>

        <?php $no = 1; while ($b = mysqli_fetch_assoc($data)) { ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($b['kode_barang']); ?></td>
                <td><?php echo htmlspecialchars($b['nama_barang']); ?></td>
                <td>Rp <?php echo number_format($b['harga'], 0, ',', '.'); ?></td>
                <td><?php echo $b['stok']; ?></td>
                <td><?php echo htmlspecialchars($b['nama_supplier'] ?? '-'); ?></td>
                <td>
                    <a class="edit" href="barang.php?edit=<?php echo $b['id']; ?>">Edit</a>

                    <form method="POST" style="display:inline"
                        onsubmit="return confirm('Hapus barang ini?')">
                        <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
                        <button type="submit" name="hapus" class="hapus">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>

</div>
</div>

</body>
</html>
