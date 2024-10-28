<?php
require 'config.php';
session_start();

// Pastikan admin sudah login
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Hapus pengguna
if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    $conn->query("DELETE FROM users WHERE id = $id");
}

// Hapus absensi
if (isset($_GET['delete_attendance'])) {
    $id = intval($_GET['delete_attendance']);
    $conn->query("DELETE FROM attendance WHERE id = $id");
}

// Ambil semua pengguna
$users_result = $conn->query("SELECT * FROM users");

// Ambil semua absensi dengan join ke tabel users
$attendance_result = $conn->query("SELECT a.*, u.username FROM attendance a JOIN users u ON a.user_id = u.id");

// Tambah pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password

    $stmt = $conn->prepare("INSERT INTO users (username, role, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $role, $password);
    $stmt->execute();
    header("Location: admin_attendance.php");
    exit();
}

// Edit pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $role, $id);
    $stmt->execute();
    header("Location: admin_attendance.php");
    exit();
}

// Set Jadwal Absen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance_settings'])) {
    // Ambil data waktu mulai dan akhir dari inputan
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Query SQL untuk memasukkan atau mengupdate jadwal absensi
    $stmt = $conn->prepare("
        INSERT INTO attendance_settings (id, start_time, end_time) 
        VALUES (1, ?, ?) 
        ON DUPLICATE KEY UPDATE start_time = VALUES(start_time), end_time = VALUES(end_time)
    ");

    if ($stmt === false) {
        // Tampilkan error jika query gagal dipersiapkan
        die('Error preparing the SQL statement: ' . $conn->error);
    }

    // Bind parameter ke query (ss untuk string)
    $stmt->bind_param("ss", $start_time, $end_time);

    // Eksekusi query
    if ($stmt->execute()) {
        // Jika berhasil, redirect ke halaman admin attendance
        header("Location: admin_attendance.php");
        exit();
    } else {
        // Tampilkan error jika eksekusi query gagal
        die('Error executing the SQL statement: ' . $stmt->error);
    }
}


// Export PDF
if (isset($_POST['export_pdf'])) {
    header("Location: export_pdf.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Attendance</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Link ke file CSS -->
</head>
<body>
    <div class="container">
        <h1>Admin Attendance</h1>

        <!-- Tombol Logout -->
        <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>

        <!-- Tambah Pengguna -->
        <h2>Tambah Pengguna</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <select name="role" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="add_user">Tambah Pengguna</button>
        </form>

        <!-- Daftar Pengguna -->
        <h2>Daftar Pengguna</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
        <?php while ($user = $users_result->fetch_assoc()): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td>
                    <a href='edit_user.php?id=<?= $user['id'] ?>'>Edit</a> | 
                    <a href='?delete_user=<?= $user['id'] ?>' onclick="return confirm('Yakin ingin menghapus?');">Hapus</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
        </table>

        <!-- Set Jadwal Absen -->
        <h2>Set Jadwal Absen</h2>
        <form method="POST">
            <label for="start_time">Waktu Mulai:</label>
            <input type="time" name="start_time" required>
            <label for="end_time">Waktu Akhir:</label>
            <input type="time" name="end_time" required>
            <button type="submit" name="set_schedule">Atur Jadwal</button>
        </form>

        <!-- Rekap Absensi -->
        <h2>Rekap Absensi</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Waktu Absen</th>
                    <th>Keterangan</th>
                    <th>Foto</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($attendance = $attendance_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $attendance['id'] ?></td>
                        <td><?= htmlspecialchars($attendance['username']) ?></td>
                        <td><?= $attendance['created_at'] ?></td> <!-- Waktu absen -->
                        <td><?= htmlspecialchars($attendance['description']) ?></td>
                        <td>
                            <img src="<?= htmlspecialchars($attendance['photo']) ?>" alt="Foto" width="50" height="50">
                        </td>
                        <td>
                            <a href='?delete_attendance=<?= $attendance['id'] ?>' onclick="return confirm('Yakin ingin menghapus?');">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Export Rekap Absensi ke PDF -->
        <h2>Export Rekap Absensi ke PDF</h2>
        <form method="POST">
            <button type="submit" name="export_pdf">Export</button>
        </form>
    </div>
</body>
</html>
