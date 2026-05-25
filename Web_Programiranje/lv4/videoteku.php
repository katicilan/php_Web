<?php
include 'db.php';

// 1. Provjera je li korisnik prijavljen
if (!isset($_SESSION['user_id'])) {
    die("Morate se prijaviti kako biste dodavali filmove! <a href='prijava.php'>Prijava</a>");
}

// 2. Validacija ulaznog ID-ja filma radi sigurnosti
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Neispravan ili nedostajući ID filma.");
}

$film_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// 3. Dohvaćanje podataka o filmu pomoću Prepared Statementa (SQL Injection zaštita)
$stmt = $conn->prepare("SELECT naslov, ocjena FROM filmovi WHERE id = ?");
$stmt->bind_param("i", $film_id);
$stmt->execute();
$film = $stmt->get_result()->fetch_assoc();

if (!$film) {
    die("Odabrani film ne postoji u bazi podataka.");
}

// 4. ZAHTJEV: Upozorenje na nisku prosječnu ocjenu (ispod 5.0)
// Ako film ima nisku ocjenu, a korisnik još nije kliknuo potvrdu ("DA")
if ($film['ocjena'] < 5.0 && !isset($_GET['confirm'])) {
    echo "<div style='border: 5px solid red; padding: 20px; text-align: center; margin: 50px auto; max-width: 500px; font-family: Arial, sans-serif; background-color: #fff5f5; border-radius: 8px;'>";
    echo "<h2 style='color: red; margin-top: 0;'>⚠️ Upozorenje!</h2>";
    echo "<p style='font-size: 16px;'>Film <b>" . htmlspecialchars($film['naslov']) . "</b> ima nisku prosječnu ocjenu (<b>" . $film['ocjena'] . "</b>).</p>";
    echo "<p style='font-size: 15px;'>Jeste li sigurni da ga želite dodati u svoju osobnu videoteku?</p><br>";
    echo "<a href='videoteku.php?id=$film_id&confirm=true' style='background: #2f855a; color: white; padding: 10px 25px; text-decoration: none; margin-right: 15px; font-weight: bold; border-radius: 4px;'>DA</a> ";
    echo "<a href='index.php' style='background: #718096; color: white; padding: 10px 25px; text-decoration: none; font-weight: bold; border-radius: 4px;'>NE</a>";
    echo "</div>";
    exit; // Prekidamo izvršavanje dok korisnik ne klikne DA ili NE
}

// 5. Provjera postoji li film već u korisnikovoj košarici (da izbjegnemo duplikate)
$stmt_check = $conn->prepare("SELECT id FROM zeljeni_filmovi WHERE korisnik_id = ? AND film_id = ?");
$stmt_check->bind_param("ii", $user_id, $film_id);
$stmt_check->execute();
$check_result = $stmt_check->get_result();

if ($check_result->num_rows === 0) {
    // Ako ne postoji, trajno ga spremi u bazu povezanog s korisničkim računom
    $stmt_insert = $conn->prepare("INSERT INTO zeljeni_filmovi (korisnik_id, film_id) VALUES (?, ?)");
    $stmt_insert->bind_param("ii", $user_id, $film_id);
    $stmt_insert->execute();

    // 6. ZAHTJEV: Opcionalno slanje email obavijesti pomoću mail() funkcije
    // Šalje se samo ako je korisnik potvrdio dodavanje filma s ocjenom ispod 5.0
    if ($film['ocjena'] < 5.0 && isset($_GET['confirm'])) {
        $to = "admin@videoteka.com"; // Ovdje upišite e-mail administratora ili profesora
        $subject = "Upozorenje: Dodan film s niskom ocjenom";
        
        $message = "Obavijest sustava:\n\n";
        $message .= "Korisnik (ID: " . $user_id . ") je u svoju osobnu videoteku dodao film s niskom ocjenom.\n";
        $message .= "Film: " . $film['naslov'] . "\n";
        $message .= "Ocjena filma: " . $film['ocjena'] . "\n";
        
        $headers = "From: sustav@videoteka.com" . "\r\n" .
                   "Reply-To: sustav@videoteka.com" . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();
        
        // Znak '@' sprječava prikazivanje greške na ekranu ako lokalni server (XAMPP) nema podešen mail server
        @mail($to, $subject, $message, $headers);
    }
}

// 7. Nakon uspješnog dodavanja, preusmjeri korisnika u njegovu osobnu videoteku
header("Location: osobna_videoteka.php");
exit;
?>