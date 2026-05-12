<?php
include 'db.php';

$poruka = "";

// LOGIKA ZA REGISTRACIJU
if (isset($_POST['registracija'])) {
    $user = $_POST['user'];
    $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO korisnici (korisnicko_ime, lozinka) VALUES (?, ?)");
    $stmt->bind_param("ss", $user, $pass);
    if($stmt->execute()) $poruka = "Registracija uspješna! Prijavite se.";
    else $poruka = "Greška: Korisničko ime zauzeto.";
}

// LOGIKA ZA PRIJAVU
if (isset($_POST['prijava'])) {
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    
    $stmt = $conn->prepare("SELECT id, lozinka FROM korisnici WHERE korisnicko_ime = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        if (password_verify($pass, $row['lozinka'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $user;
            header("Location: index.php");
        } else { $poruka = "Pogrešna lozinka!"; }
    } else { $poruka = "Korisnik ne postoji!"; }
}
?>

<!DOCTYPE html>
<html lang="hr">
<head><link rel="stylesheet" href="style/style.css"></head>
<body>
    <div style="max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc;">
        <h3>Prijava / Registracija</h3>
        <p style="color: red;"><?= $poruka ?></p>
        <form method="POST">
            <input type="text" name="user" placeholder="Korisničko ime" required><br><br>
            <input type="password" name="pass" placeholder="Lozinka" required><br><br>
            <button type="submit" name="prijava">Prijavi se</button>
            <button type="submit" name="registracija">Registriraj se</button>
        </form>
    </div>
</body>
</html>