<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $id_korisnik = $_SESSION['user_id'];
    $id_slika = $_POST['id_slika'];
    $ocjena = $_POST['ocjena'];

    // Koristimo prepared statement radi zaštite od SQL injekcije
    $stmt = $conn->prepare("INSERT INTO ocjene_slika (id_korisnik, id_slika, ocjena) 
                            VALUES (?, ?, ?) 
                            ON DUPLICATE KEY UPDATE ocjena = VALUES(ocjena)");
    $stmt->bind_param("iii", $id_korisnik, $id_slika, $ocjena);
    
    if ($stmt->execute()) {
        header("Location: slike.php?status=success");
    } else {
        echo "Greška: " . $conn->error;
    }
} else {
    header("Location: slike.php");
}
?>