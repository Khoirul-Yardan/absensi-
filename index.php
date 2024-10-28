<?php
session_start();

// Cek apakah pengguna sudah login
if (isset($_SESSION['username'])) {
    // Jika sudah login, arahkan ke dashboard yang sesuai
    if ($_SESSION['role'] === 'admin') {
        header("Location: login.php");
        exit();
    } else {
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Selamat Datang di Sistem Absensi</h1>
        <p>Silakan login atau registrasi untuk melanjutkan.</p>
    </header>

    <div class="container">
        <a href="login.php" class="btn">Login</a>
        <a href="register.php" class="btn">Registrasi</a>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> by khoirul yardan</p>
    </footer>
</body>
</html>
