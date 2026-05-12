<?php
include 'db.php'; // Veza na bazu i session_start()

// Logika za spremanje/ažuriranje ocjene
if (isset($_POST['submit_rating']) && isset($_SESSION['user_id'])) {
    $id_korisnik = $_SESSION['user_id'];
    $id_slika = $_POST['id_slika'];
    $ocjena = $_POST['rating'];

    // Koristimo ON DUPLICATE KEY UPDATE za ažuriranje postojeće ocjene
    $stmt = $conn->prepare("INSERT INTO ocjene_slika (id_korisnik, id_slika, ocjena) VALUES (?, ?, ?) 
                            ON DUPLICATE KEY UPDATE ocjena = VALUES(ocjena)");
    $stmt->bind_param("iii", $id_korisnik, $id_slika, $ocjena);
    $stmt->execute();
}

$rezultat_slika = $conn->query("SELECT * FROM slike");
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Galerija Filmova - Ocjenjivanje</title>
    <link rel="stylesheet" href="style/style.css"> </head>
<body>

<header>
    <h1>Ocjenjivanje filmskih slika</h1>
    <nav>
        <a href="index.php">Povratak na Filmove</a>
    </nav>
</header>

<main class="gallery-grid" style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;">
    <?php while($slika = $rezultat_slika->fetch_assoc()): 
        // Izračun prosječne ocjene za svaku sliku
        $stmt_avg = $conn->prepare("SELECT AVG(ocjena) as prosjek, COUNT(*) as broj_ocjena FROM ocjene_slika WHERE id_slika = ?");
        $stmt_avg->bind_param("i", $slika['id']);
        $stmt_avg->execute();
        $podaci_ocjena = $stmt_avg->get_result()->fetch_assoc();
        $prosjek = round($podaci_ocjena['prosjek'], 1);
    ?>
        <div class="photo-card" style="border: 1px solid #ccc; padding: 10px; width: 300px; text-align: center;">
            <img src="<?= $slika['putanja'] ?>" alt="<?= $slika['naziv_datoteke'] ?>" style="width: 100%; height: auto;">
            <h4><?= htmlspecialchars($slika['naziv_datoteke']) ?></h4>
            
            <p><strong>Prosječna ocjena: <?= $prosjek ? $prosjek : "Nema ocjena" ?> ⭐</strong> (<?= $podaci_ocjena['broj_ocjena'] ?>)</p>

            <?php if(isset($_SESSION['user_id'])): ?>
                <form method="POST">
                    <input type="hidden" name="id_slika" value="<?= $slika['id'] ?>">
                    <select name="rating">
                        <option value="5">5 - Izvrsno</option>
                        <option value="4">4 - Vrlo dobro</option>
                        <option value="3">3 - Dobro</option>
                        <option value="2">2 - Dovoljno</option>
                        <option value="1">1 - Loše</option>
                    </select>
                    <button type="submit" name="submit_rating">Ocijeni</button>
                </form>
            <?php else: ?>
                <p><small><a href="prijava.php">Prijavite se</a> da biste ocijenili.</small></p>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</main>

</body>
</html>