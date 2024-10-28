<?php
require 'fpdf/fpdf.php';
require 'config.php';

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Rekap Absensi', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Ambil data absensi dari database
$result = $conn->query("SELECT u.username, a.created_at, a.description, a.photo FROM attendance a JOIN users u ON a.user_id = u.id");

// Set header untuk tabel
$pdf->Cell(30, 10, 'Nama', 1);
$pdf->Cell(40, 10, 'Waktu Absen', 1);
$pdf->Cell(60, 10, 'Keterangan', 1);
$pdf->Cell(30, 10, 'Foto', 1);
$pdf->Ln();

while ($row = $result->fetch_assoc()) {
    // Set tinggi baris berdasarkan ukuran gambar (misal 20px untuk gambar)
    $rowHeight = 20;

    // Menampilkan data pengguna dan absensi
    $pdf->Cell(30, $rowHeight, $row['username'], 1);
    $pdf->Cell(40, $rowHeight, $row['created_at'], 1);
    $pdf->Cell(60, $rowHeight, $row['description'], 1);

    // Menampilkan gambar dengan path yang benar
    $imagePath = $row['photo'];
    
    if (file_exists($imagePath)) {
        // Mendapatkan posisi X dan Y untuk gambar
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        // Tambahkan sel kosong untuk gambar
        $pdf->Cell(30, $rowHeight, '', 1);

        // Tambahkan gambar di dalam sel (dengan posisi manual)
        $pdf->Image($imagePath, $x + 2, $y + 2, 16, 16); // Gambar berukuran 16x16 px, dengan margin 2px dari tepi sel
    } else {
        // Jika gambar tidak ditemukan, tampilkan placeholder teks
        $pdf->Cell(30, $rowHeight, 'No Image', 1);
    }

    // Baris baru
    $pdf->Ln();
}

$pdf->Output();
?>
