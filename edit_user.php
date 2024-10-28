<?php
require 'config.php';
session_start();

// Pastikan admin sudah login
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil data pengguna berdasarkan ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $user_result = $conn->query("SELECT * FROM users WHERE id = $id");
    $user = $user_result->fetch_assoc();

    // Jika pengguna tidak ditemukan, redirect ke halaman admin
    if (!$user) {
        header("Location: admin_dashboard.php");
        exit();
    }
} else {
    header("Location: admin_dashboard.php");
    exit();
}

// Proses edit pengguna jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $role = $_POST['role'];

    // Update data pengguna di database
    $stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $role, $id);

    if ($stmt->execute()) {
        // Redirect ke halaman admin setelah update berhasil
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Terjadi kesalahan saat mengupdate data pengguna.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengguna</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Link ke file CSS -->
</head>
<body>
    <div class="container">
        <h1>Edit Pengguna</h1>

        <!-- Tampilkan pesan error jika ada -->
        <?php if (isset($error)): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <!-- Form Edit Pengguna -->
        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

            <label for="role">Role:</label>
            <select name="role" required>
                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>

            <button type="submit">Update Pengguna</button>
        </form>

        <!-- Tombol Kembali ke Halaman Admin -->
        <button onclick="window.location.href='admin_dashboard.php'">Kembali</button>
    </div>
</body>
</html>
