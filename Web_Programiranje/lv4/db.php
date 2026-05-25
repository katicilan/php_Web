<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db   = 'filmovi';        // Ime baze
$user = 'root';           // Često 'root' na lokalnom serveru
$pass = '';               // Često prazno na lokalnom serveru

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Povezivanje nije uspjelo: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>