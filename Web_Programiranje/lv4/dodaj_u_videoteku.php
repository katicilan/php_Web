<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Morate se prijaviti! <a href='prijava.php'>Prijava</a>");
}

$film_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Dohvati ocjenu filma
$stmt = $conn->prepare("SELECT naslov, ocjena FROM filmovi WHERE id = ?");
$stmt->bind_param("i", $film_id);
$stmt->execute();
$film = $stmt->get_result()->fetch_assoc();

// Provjera niske ocjene
if ($film['ocjena'] < 5.0 && !isset($_GET['confirm'])) {
    echo "<div style='border: 5px solid red; padding: 20px; text-align: center; margin-top: 50px;'>";
    echo "<h2 style='color: red;'>Upozorenje!</h2>";
    echo "<p>Film <b>{$film['naslov']}</b> ima nisku ocjenu ({$film['ocjena']}).</p>";
    echo "<p>Jeste li sigurni da ga želite dodati u svoju videoteku?</p>";
    echo "<a href='dodaj_u_listu.php?id=$film_id&confirm=true' style='background: green; color: white; padding: 10px;'>DA</a> ";
    echo "<a href='index.php' style='background: gray; color: white; padding: 10px;'>NE</a>";
    echo "</div>";
    exit;
}

// Spremi u bazu (SQL Injection zaštita)
$stmt = $conn->prepare("INSERT INTO zeljeni_filmovi (korisnik_id, film_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $film_id);
$stmt->execute();

header("Location: osobna_videoteka.php");
?>