<?php
include 'db.php';

if (isset($_POST['submit']) && isset($_SESSION['user_id']) && isset($_FILES['slika'])) {
    $file = $_FILES['slika'];
    $target_dir = "images/"; 

    // Provjera i automatsko kreiranje mape ako ne postoji na poslužitelju
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $original_name = basename($file["name"]);
    // Sigurnosno čišćenje naziva datoteke (mijenja specijalne znakove i razmake u underscore)
    $clean_name = preg_replace("/[^A-Z0-9._-]/i", "_", $original_name);
    
    $target_file = $target_dir . $clean_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // 1. Provjera formata datoteke
    $dozvoljeni = array("jpg", "jpeg", "png");
    if (!in_array($file_type, $dozvoljeni)) {
        die("Dozvoljeni su samo JPG, JPEG i PNG formati.");
    }

    // 2. Provjera maksimalne veličine (5MB = 5242880 bajtova)
    if ($file["size"] > 5242880) {
        die("Datoteka je prevelika (Maksimalno 5MB).");
    }

    // 3. Premještanje na server i zapis u bazu
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO slike (naziv_datoteke, putanja) VALUES (?, ?)");
        $stmt->bind_param("ss", $clean_name, $target_file);
        $stmt->execute();
        header("Location: slike.php?upload=success");
        exit;
    } else {
        echo "Dogodila se greška pri prijenosu datoteke na poslužitelj.";
    }
} else {
    header("Location: slike.php");
    exit;
}
?>