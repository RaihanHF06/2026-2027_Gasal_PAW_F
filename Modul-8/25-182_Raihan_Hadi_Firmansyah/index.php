<?php
session_start();
include 'koneksi.php';

$pesan = "";

// Proses login
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM user WHERE username = ? AND password = ?");
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);

    if ($hasil && mysqli_num_rows($hasil) > 0) {
        $data = mysqli_fetch_assoc($hasil);

        $_SESSION['login'] = true;
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['level'] = $data['level'];

        mysqli_stmt_close($stmt);

        header("Location: index.php");
        exit;
    } else {
        $pesan = "Username atau password salah!";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Penjualan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php if (!isset($_SESSION['login']) || $_SESSION['login'] != true) { ?>

    <!-- Halaman Login -->
    <div class="login-box">

        <h2>Login Sistem Penjualan</h2>

        <?php if ($pesan != "") { ?>
            <div class="pesan">
                <?php echo htmlspecialchars($pesan); ?>
            </div>
        <?php } ?>

        <form method="POST" action="">

            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" name="login">Login</button>

        </form>

    </div>

<?php } else { ?>

    <!-- Navbar -->
    <div class="navbar">

        <div class="logo">
            Sistem Penjualan
        </div>

        <div class="menu">

            <a href="index.php">Home</a>

            <?php if ($_SESSION['level'] == 1) { ?>

                <div class="dropdown">

                    <a href="#">Data Master ▾</a>

                    <div class="dropdown-menu">
                        <a href="barang.php">Data Barang</a>
                        <a href="supplier.php">Data Supplier</a>
                        <a href="pelanggan.php">Data Pelanggan</a>
                        <a href="user.php">Data User</a>
                    </div>

                </div>

            <?php } ?>

            <a href="transaksi.php">Transaksi</a>
            <a href="laporan.php">Laporan</a>

        </div>

        <div class="kanan">
            <?php echo htmlspecialchars($_SESSION['nama']); ?>
            |
            <a href="logout.php">Logout</a>
        </div>

    </div>

    <!-- Dashboard -->
    <div class="isi">

        <div class="kotak">

            <h2>Dashboard Sistem Penjualan</h2>

            <p>
                Selamat datang,
                <b><?php echo htmlspecialchars($_SESSION['nama']); ?></b>.
            </p>

            <p>
                Level akun:
                <b>
                    <?php echo $_SESSION['level'] == 1 ? 'Owner' : 'Kasir'; ?>
                </b>
            </p>

            <p>
                Silakan pilih menu pada navigasi untuk melanjutkan.
            </p>

        </div>

    </div>

<?php } ?>
</body>
</html>