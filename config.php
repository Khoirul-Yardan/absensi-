<?php
// Konfigurasi koneksi database
$DB_SERVER = "localhost"; // Alamat server, biasanya localhost
$DB_USERNAME = "root";    // Username database
$DB_PASSWORD = "";        // Password database (default untuk XAMPP adalah kosong)
$DB_DATABASE = "attendance_system"; // Nama database

// Membuat koneksi
$conn = new mysqli($DB_SERVER, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
