<?php
session_start();
session_unset(); // Hapus semua session
session_destroy(); // Hancurkan session
header("Location: index.php"); // Kembali ke index
exit();
?>
