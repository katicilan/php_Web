<?php
include 'db.php';

$poruka = "";
$tip_poruke = ""; // Za CSS (zeleno ili crveno)

if (isset($_POST['registracija'])) {
    $user = trim($_POST['user']);
    $pass = $_POST['pass'];
    $pass_confirm = $_POST['pass_confirm'];

    // Osnovna validacija na serverskoj strani
    if (empty($user) || empty($pass)) {
        $poruka = "Sva polja su obavezna!";
        $tip_poruke = "red";
    } elseif ($pass !== $pass_confirm) {
        $poruka = "Lozinke se ne podudaraju!";
        $tip_poruke = "red";
    } elseif (strlen($pass) < 6) {
        $poruka = "Lozinka mora imati barem 6 znakova!";
        $tip_poruke = "red";
    } else {
        // Provjera postoji li već korisnik
        $stmt = $conn->prepare("SELECT id FROM korisnici WHERE korisnicko_ime = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $poruka = "Korisničko ime je već zauzeto!";
            $tip_poruke = "red";
        } else {
            // Sigurno hashiranje lozinke
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO korisnici (korisnicko_ime, lozinka, uloga) VALUES (?, ?, 'korisnik')");
            $stmt->bind_param("ss", $user, $hashed_pass);
            
            if ($stmt->execute()) {
                $poruka = "Registracija uspješna! Možete se <a href='prijava.php'>prijaviti</a>.";
                $tip_poruke = "green";
            } else {
                $poruka = "Greška pri registraciji: " . $conn->error;
                $tip_poruke = "red";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Registracija - Virtualna Videoteka</title>
    <link rel="stylesheet" href="style/style.css">
    <style>
        .form-container { max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; background: #f9f9f9; }
        .form-container input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .form-container button { width: 100%; padding: 10px; background: #333; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .form-container button:hover { background: #555; }
        .status-msg { padding: 10px; margin-bottom: 10px; text-align: center; border-radius: 4px; }
    </style>
</head>
<body>

<header>
    <nav>
        <ul style="display: flex; gap: 20px; list-style: none; background: #333; padding: 10px;">
            <li><a href="index.php" style="color: white;">Početna</a></li>
            <li><a href="prijava.php" style="color: white;">Prijava</a></li>
        </ul>
    </nav>
</header>

<div class="form-container">
    <h3>Kreiraj novi račun</h3>
    
    <?php if($poruka): ?>
        <div class="status-msg" style="background: <?php echo ($tip_poruke == 'red' ? '#ffcccc' : '#ccffcc'); ?>; color: <?php echo $tip_poruke; ?>;">
            <?php echo $poruka; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="registracija.php">
        <label>Korisničko ime:</label>
        <input type="text" name="user" required>
        
        <label>Lozinka:</label>
        <input type="password" name="pass" required>
        
        <label>Potvrdi lozinku:</label>
        <input type="password" name="pass_confirm" required>
        
        <button type="submit" name="registracija">Registriraj se</button>
    </form>
    <p style="text-align: center; margin-top: 15px;">Već imate račun? <a href="prijava.php">Prijavite se ovdje</a>.</p>
</div>

</body>
</html>