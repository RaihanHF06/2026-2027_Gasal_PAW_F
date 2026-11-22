<?php
include 'auth.php';
cek_owner();
include 'koneksi.php';

$pesan = "";
$data_edit = null;

// Proses tambah user
if (isset($_POST['tambah'])) {
    $stmt = mysqli_prepare($koneksi, "INSERT INTO user (username, password, nama, alamat, hp, level) VALUES (?, ?, ?, ?, ?, ?)");

    mysqli_stmt_bind_param(
        $stmt,
        "sssssi",
        $_POST['username'],
        $_POST['password'],
        $_POST['nama'],
        $_POST['alamat'],
        $_POST['hp'],
        $_POST['level']
    );

    $pesan = mysqli_stmt_execute($stmt)
        ? "Data user berhasil ditambahkan."
        : "Data user gagal ditambahkan. Pastikan username belum digunakan.";
}

// Proses edit user
if (isset($_POST['ubah'])) {

    if ($_POST['password'] != "") {
        $stmt = mysqli_prepare($koneksi, "UPDATE user SET username=?, password=?, nama=?, alamat=?, hp=?, level=? WHERE id_user=?");

        mysqli_stmt_bind_param(
            $stmt,
            "sssssii",
            $_POST['username'],
            $_POST['password'],
            $_POST['nama'],
            $_POST['alamat'],
            $_POST['hp'],
            $_POST['level'],
            $_POST['id_user']
        );

    } else {
        $stmt = mysqli_prepare($koneksi, "UPDATE user SET username=?, nama=?, alamat=?, hp=?, level=? WHERE id_user=?");

        mysqli_stmt_bind_param(
            $stmt,
            "ssssii",
            $_POST['username'],
            $_POST['nama'],
            $_POST['alamat'],
            $_POST['hp'],
            $_POST['level'],
            $_POST['id_user']
        );
    }

    $pesan = mysqli_stmt_execute($stmt)
        ? "Data user berhasil diubah."
        : "Data user gagal diubah.";
}

// Proses hapus user
if (isset($_GET['hapus_id'])) {
    $id = (int) $_GET['hapus_id'];

    if ($id == $_SESSION['id_user']) {
        $pesan = "Akun yang sedang digunakan tidak dapat dihapus.";
    } else {
        $stmt = mysqli_prepare($koneksi, "DELETE FROM user WHERE id_user=?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        $pesan = mysqli_stmt_execute($stmt)
            ? "Data user berhasil dihapus."
            : "Data user gagal dihapus.";
    }
}

// Mengambil data user untuk diedit
if (isset($_GET['edit_id'])) {
    $id = (int) $_GET['edit_id'];

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM user WHERE id_user=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $data_edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$data = mysqli_query($koneksi, "SELECT * FROM user ORDER BY id_user");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="isi">
<div class="kotak">

    <a class="tombol" href="index.php">Kembali ke Dashboard</a>

    <h2>Data User</h2>

    <?php if ($pesan != "") { ?>
        <div class="pesan"><?php echo htmlspecialchars($pesan); ?></div>
    <?php } ?>

    <h3><?php echo $data_edit ? "Edit User" : "Tambah User"; ?></h3>

    <form method="POST">

        <?php if ($data_edit) { ?>
            <input type="hidden" name="id_user" value="<?php echo $data_edit['id_user']; ?>">
        <?php } ?>

        <label>Username</label>
        <input type="text" name="username" required
            value="<?php echo htmlspecialchars($data_edit['username'] ?? ''); ?>">

        <label>Password <?php echo $data_edit ? '(kosongkan jika tidak diganti)' : ''; ?></label>
        <input type="password" name="password" <?php echo $data_edit ? '' : 'required'; ?>>

        <label>Nama</label>
        <input type="text" name="nama" required
            value="<?php echo htmlspecialchars($data_edit['nama'] ?? ''); ?>">

        <label>Alamat</label>
        <textarea name="alamat"><?php echo htmlspecialchars($data_edit['alamat'] ?? ''); ?></textarea>

        <label>Nomor HP</label>
        <input type="text" name="hp"
            value="<?php echo htmlspecialchars($data_edit['hp'] ?? ''); ?>">

        <label>Level</label>
        <select name="level" required>
            <option value="1" <?php if (($data_edit['level'] ?? '') == 1) echo 'selected'; ?>>Owner</option>
            <option value="2" <?php if (($data_edit['level'] ?? '') == 2) echo 'selected'; ?>>Kasir</option>
        </select>

        <?php if ($data_edit) { ?>
            <button type="submit" name="ubah">Simpan Perubahan</button>
            <a class="tombol" href="user.php">Batal</a>
        <?php } else { ?>
            <button type="submit" name="tambah">Tambah User</button>
        <?php } ?>

    </form>

    <h3>Daftar User</h3>

    <table>
        <tr>
            <th>No</th>
            <th>Username</th>
            <th>Nama</th>
            <th>Alamat</th>
            <th>HP</th>
            <th>Level</th>
            <th>Aksi</th>
        </tr>

        <?php $no = 1; while ($row = mysqli_fetch_assoc($data)) { ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                <td><?php echo htmlspecialchars($row['alamat'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['hp'] ?? '-'); ?></td>
                <td><?php echo $row['level'] == 1 ? 'Owner' : 'Kasir'; ?></td>
                <td>
                    <a class="edit" href="user.php?edit_id=<?php echo $row['id_user']; ?>">Edit</a>

                    <a class="hapus"
                        href="user.php?hapus_id=<?php echo $row['id_user']; ?>"
                        onclick="return confirm('Hapus user ini?')">Hapus</a>
                </td>
            </tr>
        <?php } ?>
    </table>

</div>
</div>

</body>
</html>