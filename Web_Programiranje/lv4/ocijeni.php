<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id']) && isset($_POST['id_slika']) && isset($_POST['ocjena'])) {
    $id_korisnik = $_SESSION['user_id'];
    $id_slika = intval($_POST['id_slika']);
    $ocjena = intval($_POST['ocjena']);

    // Validacija raspona ocjene
    if ($ocjena >= 1 && $ocjena <= 5) {
        $stmt = $conn->prepare("INSERT INTO ocjene_slika (id_korisnik, id_slika, ocjena) 
                                VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE ocjena = VALUES(ocjena)");
        $stmt->bind_param("iii", $id_korisnik, $id_slika, $ocjena);
        
        if ($stmt->execute()) {
            header("Location: slike.php?status=success");
            exit;
        } else {
            echo "Greška pri spremanju ocjene: " . $conn->error;
        }
    } else {
        echo "Neispravna ocjena.";
    }
} else {
    header("Location: slike.php");
    exit;
}
?>