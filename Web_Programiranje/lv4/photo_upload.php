<?php
include 'db.php';

if (isset($_POST['upload_image']) && isset($_SESSION['user_id'])) {
    $file = $_FILES['nova_slika'];
    $allowed_types = ['image/jpeg', 'image/png'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
        $upload_dir = 'slike/';
        $target_file = $upload_dir . basename($file['name']);

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO slike (naziv_datoteke, putanja) VALUES (?, ?)");
            $stmt->bind_param("ss", $file['name'], $target_file);
            $stmt->execute();
            echo "Slika uspješno učitana!";
        }
    } else {
        echo "Pogrešan format ili prevelika datoteka (Max 5MB, JPEG/PNG).";
    }
}
?>

<form method="POST" enctype="multipart/form-data" style="margin: 20px auto; max-width: 400px;">
    <h3>Dodaj novu filmsku sliku</h3>
    <input type="file" name="nova_slika" required>
    <button type="submit" name="upload_image">Učitaj sliku</button>
</form>