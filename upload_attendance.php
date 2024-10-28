<?php
require 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    // Simpan foto dan keterangan ke database
    $photo = $data->photo;
    $description = $data->description;
    $user_id = $_SESSION['user_id'];

    // Mengubah data URL menjadi string base64
    $photo = str_replace('data:image/png;base64,', '', $photo);
    $photo = str_replace(' ', '+', $photo);
    $photo = base64_decode($photo);
    
    // Simpan gambar sebagai file
    $filePath = 'uploads/' . uniqid() . '.png';
    file_put_contents($filePath, $photo);

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO attendance (user_id, photo, description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $filePath, $description);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false]);
}
?>
