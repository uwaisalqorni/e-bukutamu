<?php
session_start();

// Hapus semua session admin
session_unset();
session_destroy();

// Redirect ke halaman login
header("Location: login.php");
exit();
?>
