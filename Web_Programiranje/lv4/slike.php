<?php
include 'db.php'; 

// --- AUTOMATSKO PREPOZNAVANJE POSTOJEĆIH SLIKA IZ MAPE IMAGES ---
$mapa_slika = 'images/';
if (is_dir($mapa_slika)) {
    // Pročitaj sve datoteke iz mape koje završavaju na .jpg, .jpeg ili .png
    $slike_u_mapi = glob($mapa_slika . "*.{jpg,jpeg,png}", GLOB_BRACE);
    
    foreach ($slike_u_mapi as $putanja_slike) {
        $naziv_datoteke = basename($putanja_slike); // npr. slika1.jpg
        
        // Provjeri postoji li već ta slika u bazi podataka
        $stmt_provjera = $conn->prepare("SELECT id FROM slike WHERE naziv_datoteke = ?");
        $stmt_provjera->bind_param("s", $naziv_datoteke);
        $stmt_provjera->execute();
        $rezultat_provjere = $stmt_provjera->get_result();
        
        // Ako slika postoji u mapi, a nema je u bazi, automatski je zapiši u bazu
        if ($rezultat_provjere->num_rows === 0) {
            $stmt_unos = $conn->prepare("INSERT INTO slike (naziv_datoteke, putanja) VALUES (?, ?)");
            $stmt_unos->bind_param("ss", $naziv_datoteke, $putanja_slike);
            $stmt_unos->execute();
        }
    }
}

// Dohvaćanje slika iz baze
$res = $conn->query("SELECT * FROM slike");
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galerija i Ocjenjivanje</title>
    <link rel="stylesheet" href="style/style.css"> 
    <link rel="stylesheet" href="style/style_slike.css"> 
</head>
<body>
    <header>
        <h1>Virtualna Videoteka - Galerija</h1>
        
        <input type="checkbox" id="menu-toggle">
        <label for="menu-toggle" class="hamburger">&#9776;</label>
        
        <ul class="menu">
            <li><a href="index.php">Početna</a></li>
            <li><a href="osobna_videoteka.php">Moja Videoteka</a></li>
            <li><a href="slike.php">Galerija Slika</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if(isset($_SESSION['uloga']) && $_SESSION['uloga'] == 'administrator'): ?>
                    <li><a href="upravljanje_filmovima.php" style="color: gold; font-weight: bold;">Admin Panel</a></li>
                <?php endif; ?>
                <li><a href="odjava.php">Odjava (<?= htmlspecialchars($_SESSION['username']) ?>)</a></li>
            <?php else: ?>
                <li><a href="prijava.php">Prijava / Registracija</a></li>
            <?php endif; ?>
        </ul>
    </header>
    
    <div class="img-gallery" style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px; justify-content: center;">
        <?php while($row = $res->fetch_assoc()): 
            $s_id = $row['id'];
            
            // Pripremljeni upit za izračun prosjeka i broja ocjena (brže i sigurnije)
            $stmt_avg = $conn->prepare("SELECT AVG(ocjena) as prosjek, COUNT(*) as broj_ocjena FROM ocjene_slika WHERE id_slika = ?");
            $stmt_avg->bind_param("i", $s_id);
            $stmt_avg->execute();
            $podaci_ocjena = $stmt_avg->get_result()->fetch_assoc();
            $avg = round($podaci_ocjena['prosjek'], 1);
        ?>
            <figure style="border: 1px solid #ccc; padding: 15px; width: 260px; text-align: center; background: #fff; border-radius: 6px;">
                <img src="<?php echo htmlspecialchars($row['putanja']); ?>" style="max-width: 100%; height: auto; max-height: 150px; border-radius: 4px;">
                <figcaption style="margin-top: 10px;">
                    <h4><?php echo htmlspecialchars($row['naziv_datoteke']); ?></h4>
                    <p>Prosjek: <?php echo $avg ? $avg : "0.0"; ?> ⭐ (<?= $podaci_ocjena['broj_ocjena'] ?> ocjena)</p>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <form action="ocijeni.php" method="POST">
                            <input type="hidden" name="id_slika" value="<?php echo $s_id; ?>">
                            <select name="ocjena" style="padding: 5px;">
                                <option value="5">5 - Izvrsno</option>
                                <option value="4">4 - Vrlo dobro</option>
                                <option value="3">3 - Dobro</option>
                                <option value="2">2 - Dovoljno</option>
                                <option value="1">1 - Loše</option>
                            </select>
                            <button type="submit" name="submit_rating">Ocijeni</button>
                        </form>
                    <?php else: ?>
                        <p><small><a href="prijava.php">Prijavite se</a> za ocjenjivanje.</small></p>
                    <?php endif; ?>
                </figcaption>
            </figure>
        <?php endwhile; ?>
    </div>

    <?php if(isset($_SESSION['user_id'])): ?>
    <section style="margin: 40px auto; background: #fdfdfd; padding: 20px; border: 1px solid #ddd; border-radius: 6px; max-width: 400px;">
        <h3>Dodaj novu filmsku sliku</h3>
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="slika" required><br><br>
            <button type="submit" name="submit" style="padding: 8px 15px; background: #333; color: #fff; border: none; cursor: pointer;">Učitaj sliku</button>
        </form>
    </section>
    <?php endif; ?>
</body>
</html>