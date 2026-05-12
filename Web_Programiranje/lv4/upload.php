<?php
include 'db.php';

if (isset($_POST['submit']) && isset($_SESSION['user_id'])) {
    $file = $_FILES['slika'];
    $target_dir = "images/"; // Provjeri da ovaj folder postoji!
    $file_name = basename($file["name"]);
    $target_file = $target_dir . $file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // 1. Provjera formata
    $dozvoljeni = array("jpg", "jpeg", "png");
    if (!in_array($file_type, $dozvoljeni)) {
        die("Dozvoljeni su samo JPG i PNG formati.");
    }

    // 2. Provjera veličine (5MB = 5242880 bytes)
    if ($file["size"] > 5242880) {
        die("Datoteka je prevelika (Max 5MB).");
    }

    // 3. Premještanje i spremanje u bazu
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO slike (naziv_datoteke, putanja) VALUES (?, ?)");
        $stmt->bind_param("ss", $file_name, $target_file);
        $stmt->execute();
        header("Location: slike.php?upload=success");
    } else {
        echo "Greška pri prijenosu datoteke.";
    }
}
?>