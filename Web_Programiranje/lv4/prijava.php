<?php
include 'db.php';

$poruka = "";
$tip_poruke = ""; 

// LOGIKA ZA REGISTRACIJU
if (isset($_POST['registracija'])) {
    $user = trim($_POST['user']);
    $pass = $_POST['pass'];
    $pass_confirm = $_POST['pass_confirm'];

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
        // Provjera zauzetosti korisničkog imena preko Prepared Statementa
        $stmt = $conn->prepare("SELECT id FROM korisnici WHERE korisnicko_ime = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $poruka = "Korisničko ime je već zauzeto!";
            $tip_poruke = "red";
        } else {
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO korisnici (korisnicko_ime, lozinka, uloga) VALUES (?, ?, 'korisnik')");
            $stmt->bind_param("ss", $user, $hashed_pass);
            
            if ($stmt->execute()) {
                $poruka = "Registracija uspješna! Sada se možete prijaviti.";
                $tip_poruke = "green";
            } else {
                $poruka = "Greška pri registraciji.";
                $tip_poruke = "red";
            }
        }
    }
}

// LOGIKA ZA PRIJAVU 
if (isset($_POST['prijava'])) {
    $user = trim($_POST['user']);
    $pass = $_POST['pass'];
    
    // POVLAČIMO I STUPAC ULOGA!
    $stmt = $conn->prepare("SELECT id, lozinka, uloga FROM korisnici WHERE korisnicko_ime = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        if (password_verify($pass, $row['lozinka'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $user;
            $_SESSION['uloga'] = $row['uloga']; 
            header("Location: index.php");
            exit;
        } else { 
            $poruka = "Pogrešna lozinka!"; 
            $tip_poruke = "red";
        }
    } else { 
        $poruka = "Korisnik ne postoji!"; 
        $tip_poruke = "red";
    }
}
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Prijava / Registracija</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <div style="max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; background:#fff; border-radius:6px; font-family: Arial, sans-serif;">
        <h3>Prijava / Registracija</h3>
        
        <?php if($poruka): ?>
            <div style="padding: 10px; margin-bottom: 15px; color: white; background: <?= $tip_poruke ?>; text-align:center; border-radius:4px;">
                <?= $poruka ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <label>Korisničko ime:</label><br>
            <input type="text" name="user" style="width:100%; padding:8px; margin:5px 0; box-sizing: border-box;" required><br>
            
            <label>Lozinka:</label><br>
            <input type="password" name="pass" style="width:100%; padding:8px; margin:5px 0; box-sizing: border-box;" required><br>
            
            <label>Potvrdi lozinku (samo za registraciju):</label><br>
            <input type="password" name="pass_confirm" style="width:100%; padding:8px; margin:5px 0; box-sizing: border-box;"><br><br>
            
            <button type="submit" name="prijava" style="padding:10px 15px; background:green; color:white; border:none; cursor:pointer; border-radius:4px;">Prijavi se</button>
            <button type="submit" name="registracija" style="padding:10px 15px; background:#333; color:white; border:none; cursor:pointer; border-radius:4px;">Registriraj se</button>
        </form>
    </div>
</body>
</html>