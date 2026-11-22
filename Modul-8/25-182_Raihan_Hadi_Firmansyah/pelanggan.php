<?php
include 'auth.php';
cek_owner();
include 'koneksi.php';

$pesan = "";
$data_edit = null;

// Proses tambah pelanggan
if (isset($_POST['tambah'])) {
    $stmt = mysqli_prepare($koneksi, "INSERT INTO pelanggan (id, nama, jenis_kelamin, telp, alamat) VALUES (?, ?, ?, ?, ?)");

    mysqli_stmt_bind_param(
        $stmt,
        "sssss",
        $_POST['id'],
        $_POST['nama'],
        $_POST['jenis_kelamin'],
        $_POST['telp'],
        $_POST['alamat']
    );

    $pesan = mysqli_stmt_execute($stmt)
        ? "Data pelanggan berhasil ditambahkan."
        : "Data pelanggan gagal ditambahkan.";
}

// Proses edit pelanggan
if (isset($_POST['ubah'])) {
    $stmt = mysqli_prepare($koneksi, "UPDATE pelanggan SET nama=?, jenis_kelamin=?, telp=?, alamat=? WHERE id=?");

    mysqli_stmt_bind_param(
        $stmt,
        "sssss",
        $_POST['nama'],
        $_POST['jenis_kelamin'],
        $_POST['telp'],
        $_POST['alamat'],
        $_POST['id']
    );

    $pesan = mysqli_stmt_execute($stmt)
        ? "Data pelanggan berhasil diubah."
        : "Data pelanggan gagal diubah.";
}

// Proses hapus pelanggan
if (isset($_GET['hapus_id'])) {
    $id = $_GET['hapus_id'];

    $stmt = mysqli_prepare($koneksi, "SELECT id FROM transaksi WHERE pelanggan_id=?");
    mysqli_stmt_bind_param($stmt, "s", $id);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($hasil) > 0) {
        $pesan = "Pelanggan tidak dapat dihapus karena sudah memiliki transaksi.";
    } else {
        $stmt = mysqli_prepare($koneksi, "DELETE FROM pelanggan WHERE id=?");
        mysqli_stmt_bind_param($stmt, "s", $id);

        $pesan = mysqli_stmt_execute($stmt)
            ? "Data pelanggan berhasil dihapus."
            : "Data pelanggan gagal dihapus.";
    }
}

// Mengambil data pelanggan untuk diedit
if (isset($_GET['edit_id'])) {
    $id = $_GET['edit_id'];

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM pelanggan WHERE id=?");
    mysqli_stmt_bind_param($stmt, "s", $id);
    mysqli_stmt_execute($stmt);

    $data_edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$data = mysqli_query($koneksi, "SELECT * FROM pelanggan ORDER BY id");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Pelanggan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="isi">
<div class="kotak">

    <a class="tombol" href="index.php">Kembali ke Dashboard</a>

    <h2>Data Pelanggan</h2>

    <?php if ($pesan != "") { ?>
        <div class="pesan"><?php echo htmlspecialchars($pesan); ?></div>
    <?php } ?>

    <h3><?php echo $data_edit ? "Edit Pelanggan" : "Tambah Pelanggan"; ?></h3>

    <form method="POST">

        <label>ID Pelanggan</label>
        <input type="text" name="id" required
            value="<?php echo htmlspecialchars($data_edit['id'] ?? ''); ?>"
            <?php echo $data_edit ? 'readonly' : ''; ?>>

        <label>Nama Pelanggan</label>
        <input type="text" name="nama" required
            value="<?php echo htmlspecialchars($data_edit['nama'] ?? ''); ?>">

        <label>Jenis Kelamin</label>
        <select name="jenis_kelamin" required>
            <option value="L" <?php if (($data_edit['jenis_kelamin'] ?? '') == 'L') echo 'selected'; ?>>Laki-laki</option>
            <option value="P" <?php if (($data_edit['jenis_kelamin'] ?? '') == 'P') echo 'selected'; ?>>Perempuan</option>
        </select>

        <label>Telepon</label>
        <input type="text" name="telp"
            value="<?php echo htmlspecialchars($data_edit['telp'] ?? ''); ?>">

        <label>Alamat</label>
        <textarea name="alamat"><?php echo htmlspecialchars($data_edit['alamat'] ?? ''); ?></textarea>

        <?php if ($data_edit) { ?>
            <button type="submit" name="ubah">Simpan Perubahan</button>
            <a class="tombol" href="pelanggan.php">Batal</a>
        <?php } else { ?>
            <button type="submit" name="tambah">Tambah Pelanggan</button>
        <?php } ?>

    </form>

    <h3>Daftar Pelanggan</h3>

    <table>
        <tr>
            <th>No</th>
            <th>ID</th>
            <th>Nama</th>
            <th>Jenis Kelamin</th>
            <th>Telepon</th>
            <th>Alamat</th>
            <th>Aksi</th>
        </tr>

        <?php $no = 1; while ($row = mysqli_fetch_assoc($data)) { ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                <td><?php echo $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                <td><?php echo htmlspecialchars($row['telp'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['alamat'] ?? '-'); ?></td>
                <td>
                    <a class="edit" href="pelanggan.php?edit_id=<?php echo urlencode($row['id']); ?>">Edit</a>

                    <a class="hapus"
                        href="pelanggan.php?hapus_id=<?php echo urlencode($row['id']); ?>"
                        onclick="return confirm('Hapus pelanggan ini?')">Hapus</a>
                </td>
            </tr>
        <?php } ?>
    </table>

</div>
</div>

</body>
</html>