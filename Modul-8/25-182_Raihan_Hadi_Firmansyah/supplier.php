<?php
include 'auth.php';
cek_owner();
include 'koneksi.php';

$pesan = "";
$data_edit = null;

// Proses tambah supplier
if (isset($_POST['tambah'])) {
    $stmt = mysqli_prepare($koneksi, "INSERT INTO supplier (nama, telp, alamat) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $_POST['nama'], $_POST['telp'], $_POST['alamat']);

    $pesan = mysqli_stmt_execute($stmt)
        ? "Data supplier berhasil ditambahkan."
        : "Data supplier gagal ditambahkan.";
}

// Proses edit supplier
if (isset($_POST['ubah'])) {
    $stmt = mysqli_prepare($koneksi, "UPDATE supplier SET nama=?, telp=?, alamat=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sssi", $_POST['nama'], $_POST['telp'], $_POST['alamat'], $_POST['id']);

    $pesan = mysqli_stmt_execute($stmt)
        ? "Data supplier berhasil diubah."
        : "Data supplier gagal diubah.";
}

// Proses hapus supplier
if (isset($_GET['hapus_id'])) {
    $id = (int) $_GET['hapus_id'];

    $stmt = mysqli_prepare($koneksi, "SELECT id FROM barang WHERE supplier_id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($hasil) > 0) {
        $pesan = "Supplier masih digunakan oleh data barang.";
    } else {
        $stmt = mysqli_prepare($koneksi, "DELETE FROM supplier WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        $pesan = mysqli_stmt_execute($stmt)
            ? "Data supplier berhasil dihapus."
            : "Data supplier gagal dihapus.";
    }
}

// Mengambil data supplier untuk diedit
if (isset($_GET['edit_id'])) {
    $id = (int) $_GET['edit_id'];

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM supplier WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $data_edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$data = mysqli_query($koneksi, "SELECT * FROM supplier ORDER BY id");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Supplier</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="isi">
<div class="kotak">

    <a class="tombol" href="index.php">Kembali ke Dashboard</a>

    <h2>Data Supplier</h2>

    <?php if ($pesan != "") { ?>
        <div class="pesan"><?php echo htmlspecialchars($pesan); ?></div>
    <?php } ?>

    <h3><?php echo $data_edit ? "Edit Supplier" : "Tambah Supplier"; ?></h3>

    <form method="POST">

        <?php if ($data_edit) { ?>
            <input type="hidden" name="id" value="<?php echo $data_edit['id']; ?>">
        <?php } ?>

        <label>Nama Supplier</label>
        <input type="text" name="nama" required
            value="<?php echo htmlspecialchars($data_edit['nama'] ?? ''); ?>">

        <label>Nomor Telepon</label>
        <input type="text" name="telp"
            value="<?php echo htmlspecialchars($data_edit['telp'] ?? ''); ?>">

        <label>Alamat</label>
        <textarea name="alamat"><?php echo htmlspecialchars($data_edit['alamat'] ?? ''); ?></textarea>

        <?php if ($data_edit) { ?>
            <button type="submit" name="ubah">Simpan Perubahan</button>
            <a class="tombol" href="supplier.php">Batal</a>
        <?php } else { ?>
            <button type="submit" name="tambah">Tambah Supplier</button>
        <?php } ?>

    </form>

    <h3>Daftar Supplier</h3>

    <table>
        <tr>
            <th>No</th>
            <th>Nama Supplier</th>
            <th>Telepon</th>
            <th>Alamat</th>
            <th>Aksi</th>
        </tr>

        <?php $no = 1; while ($row = mysqli_fetch_assoc($data)) { ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                <td><?php echo htmlspecialchars($row['telp'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['alamat'] ?? '-'); ?></td>
                <td>
                    <a class="edit" href="supplier.php?edit_id=<?php echo $row['id']; ?>">Edit</a>

                    <a class="hapus"
                        href="supplier.php?hapus_id=<?php echo $row['id']; ?>"
                        onclick="return confirm('Hapus supplier ini?')">Hapus</a>
                </td>
            </tr>
        <?php } ?>
    </table>

</div>
</div>

</body>
</html>