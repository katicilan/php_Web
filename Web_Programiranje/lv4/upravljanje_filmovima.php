<?php
include 'db.php';

// Zaštita: Samo administrator smije vidjeti ovu stranicu
if (!isset($_SESSION['user_id']) || $_SESSION['uloga'] !== 'administrator') {
    die("Pristup odbijen! Samo administratori mogu upravljati filmovima. <a href='index.php'>Povratak</a>");
}

$poruka = "";
$tip_poruke = "";

// LOGIKA ZA BRISANJE FILMA
if (isset($_GET['obrisi']) && is_numeric($_GET['obrisi'])) {
    $id_filma = intval($_GET['obrisi']);
    $stmt = $conn->prepare("DELETE FROM filmovi WHERE id = ?");
    $stmt->bind_param("i", $id_filma);
    $stmt->execute();
    header("Location: upravljanje_filmovima.php?status=deleted");
    exit;
}

// LOGIKA ZA UNOS FILMA (Uključuje serversku validaciju iz opisa)
if (isset($_POST['unesi_film'])) {
    $naslov = trim($_POST['naslov']);
    $zanr = trim($_POST['zanr']);
    $godina = intval($_POST['godina']);
    $trajanje = intval($_POST['trajanje_min']);
    $ocjena = floatval($_POST['ocjena']);
    $reziser = trim($_POST['reziser']);
    $zemlja = trim($_POST['zemlja_porijekla']);

    // SERVERSKA VALIDACIJA (PHP Form Validation)
    if (empty($naslov) || empty($zanr)) {
        $poruka = "Naslov i žanr su obavezni!";
        $tip_poruke = "red";
    } elseif ($godina < 1888 || $godina > intval(date("Y"))) {
        $poruka = "Neispravna godina filma (Mora biti između 1888. i " . date("Y") . ".)";
        $tip_poruke = "red";
    } elseif ($trajanje <= 0 || $trajanje > 600) {
        $poruka = "Trajanje filma mora biti logičan broj minuta (1 - 600)!";
        $tip_poruke = "red";
    } elseif ($ocjena < 1.0 || $ocjena > 10.0) {
        $poruka = "Ocjena mora biti u rasponu od 1.0 do 10.0!";
        $tip_poruke = "red";
    } else {
        // Sve je u redu, spremanje u bazu preko Prepared Statementa
        $stmt = $conn->prepare("INSERT INTO filmovi (naslov, zanr, godina, trajanje_min, ocjena, reziser, zemlja_porijekla) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiidss", $naslov, $zanr, $godina, $trajanje, $ocjena, $reziser, $zemlja);
        
        if ($stmt->execute()) {
            $poruka = "Film je uspješno dodan u bazu podataka!";
            $tip_poruke = "green";
        } else {
            $poruka = "Greška prilikom unosa u bazu.";
            $tip_poruke = "red";
        }
    }
}

$svi_filmovi = $conn->query("SELECT * FROM filmovi ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Upravljanje filmovima</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <header>
        <h1>Admin Panel - Upravljanje filmovima</h1>
        <nav><a href="index.php">Povratak na Početnu</a></nav>
    </header>
    <main style="padding:20px; display: flex; gap: 30px;">
        
        <section style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 6px;">
            <h3>Unos novog filma</h3>
            <?php if($poruka): ?>
                <div style="background: <?= $tip_poruke ?>; color: white; padding: 10px; margin-bottom: 10px; border-radius:4px;"><?= $poruka ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <label>Naslov filma*:</label><br><input type="text" name="naslov" required style="width:100%; padding:5px;"><br><br>
                <label>Žanr*:</label><br><input type="text" name="zanr" required style="width:100%; padding:5px;"><br><br>
                <label>Godina proizvodnje*:</label><br><input type="number" name="godina" required style="width:100%; padding:5px;"><br><br>
                <label>Trajanje (u minutama)*:</label><br><input type="number" name="trajanje_min" required style="width:100%; padding:5px;"><br><br>
                <label>Ocjena (1.0 - 10.0)*:</label><br><input type="number" step="0.1" name="ocjena" required style="width:100%; padding:5px;"><br><br>
                <label>Režiser:</label><br><input type="text" name="reziser" style="width:100%; padding:5px;"><br><br>
                <label>Zemlja porijekla:</label><br><input type="text" name="zemlja_porijekla" style="width:100%; padding:5px;"><br><br>
                <button type="submit" name="unesi_film" style="background: green; color: white; padding: 10px; border:none; cursor:pointer; width: 100%;">Spremi film</button>
            </form>
        </section>

        <section style="flex: 2; background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 6px;">
            <h3>Trenutni popis filmova u bazi</h3>
            <table border="1" style="width:100%; border-collapse:collapse;">
                <tr style="background:#eee;">
                    <th>ID</th><th>Naslov</th><th>Godina</th><th>Akcija</th>
                </tr>
                <?php while($f = $svi_filmovi->fetch_assoc()): ?>
                <tr>
                    <td><?= $f['id'] ?></td>
                    <td><?= htmlspecialchars($f['naslov']) ?></td>
                    <td><?= $f['godina'] ?>.</td>
                    <td><a href="upravljanje_filmovima.php?obrisi=<?= $f['id'] ?>" style="color:red;" onclick="return confirm('Sigurno želiš obrisati ovaj film?');">Obriši</a></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </section>
    </main>
</body>
</html>