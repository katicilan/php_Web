<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db   = 'tvoje_ime_baze'; // PROMIJENI OVO
$user = 'root';           // PROMIJENI OVO (često 'root')
$pass = '';               // PROMIJENI OVO (često prazno)

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Povezivanje nije uspjelo: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>