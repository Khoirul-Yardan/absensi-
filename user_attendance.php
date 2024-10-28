<?php
require 'config.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Ambil pengaturan waktu absensi
$settings_result = $conn->query("SELECT * FROM attendance_settings WHERE id = 1");
$settings = $settings_result->fetch_assoc();

// Mendapatkan waktu saat ini
$current_time = date("H:i");

// Memeriksa apakah waktu absensi valid
$absen_valid = $current_time >= $settings['start_time'] && $current_time <= $settings['end_time'];

// Jika waktu tidak valid, izinkan pengguna untuk tetap absen menggunakan waktu default
if (!$absen_valid) {
    echo "<script>alert('Waktu absensi telah berlalu. Anda masih dapat mengisi absensi.');</script>";
}

// Variabel untuk menyimpan informasi foto
$photo_data = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_photo'])) {
    $description = $_POST['description'];

    // Menyimpan foto base64 ke file
    if (!empty($_POST['photo_data'])) {
        $photo_data = $_POST['photo_data'];
        $target_dir = "uploads/";
        $filename = uniqid() . '.png';
        $target_file = $target_dir . $filename;

        // Memisahkan header dari data base64
        list($type, $photo_data) = explode(';', $photo_data);
        list(, $photo_data)      = explode(',', $photo_data);

        // Decode dan simpan file
        file_put_contents($target_file, base64_decode($photo_data));

        // Menyimpan data absensi ke database
        $stmt = $conn->prepare("INSERT INTO attendance (user_id, description, photo, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $_SESSION['user_id'], $description, $target_file);
        $stmt->execute();
        $stmt->close();

        // Arahkan pengguna ke index.php setelah sukses
        echo "<script>alert('Foto berhasil diunggah dan absensi tercatat.'); window.location.href='index.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Attendance</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        #video {
            width: 100%;
            max-width: 500px;
            height: auto;
        }
        #photoPreview {
            width: 200px;
            display: none;
        }
    </style>
</head>
<body>
    <header>
        <h1>User Attendance</h1>
        <a href="logout.php">Logout</a>
    </header>

    <div class="container">
        <h2>Absensi</h2>
        <form id="attendanceForm" method="POST">
            <label for="description">Keterangan:</label>
            <input type="text" name="description" value="<?= htmlspecialchars($description) ?>" required>

            <label for="camera">Ambil Foto:</label>
            <video id="video" autoplay></video>
            <button type="button" id="snap">Ambil Foto</button>
            <canvas id="canvas" style="display:none;"></canvas>
            <img id="photoPreview" src="" alt="Preview Foto"/>

            <button type="submit" name="submit_photo">Kirim Absensi</button>
        </form>
    </div>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const photoPreview = document.getElementById('photoPreview');
        const snapButton = document.getElementById('snap');

        // Meminta akses ke kamera
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
            .then(stream => {
                video.srcObject = stream;
            })
            .catch(err => {
                console.error("Error accessing camera: " + err);
            });

        // Mengambil foto
        snapButton.addEventListener('click', function() {
            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Menampilkan foto preview
            const data = canvas.toDataURL('image/png');
            photoPreview.src = data;
            photoPreview.style.display = 'block';

            // Menyimpan data foto dalam bentuk base64 ke input tersembunyi
            const photoInput = document.createElement('input');
            photoInput.type = 'hidden';
            photoInput.name = 'photo_data';
            photoInput.value = data;
            document.forms[0].appendChild(photoInput);
        });
    </script>
</body>
</html>
