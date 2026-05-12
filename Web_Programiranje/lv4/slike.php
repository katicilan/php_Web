<?php
include 'db.php';
session_start();

// Dohvaćanje slika iz baze (koje su učitane iz foldera)
$res = $conn->query("SELECT * FROM slike");
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <link rel="stylesheet" href="style/style_slike.css"> </head>
<body>
    <h1>Galerija i Ocjenjivanje</h1>
    <div class="img-gallery">
        <?php while($row = $res->fetch_assoc()): 
            // Izračun prosječne ocjene
            $s_id = $row['id'];
            $avg_res = $conn->query("SELECT AVG(ocjena) as prosjek FROM ocjene_slika WHERE id_slika = $s_id");
            $avg = round($avg_res->fetch_assoc()['prosjek'], 1);
        ?>
            <figure>
                <img src="<?php echo $row['putanja']; ?>">
                <figcaption>
                    Prosjek: <?php echo $avg ? $avg : "0.0"; ?> ⭐
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <form action="ocijeni.php" method="POST">
                            <input type="hidden" name="id_slika" value="<?php echo $s_id; ?>">
                            <input type="number" name="ocjena" min="1" max="5">
                            <button type="submit">Ocijeni</button>
                        </form>
                    <?php endif; ?>
                </figcaption>
            </figure>
        <?php endwhile; ?>
    </div>

    <?php if(isset($_SESSION['user_id'])): ?>
    <section>
        <h3>Dodaj novu sliku</h3>
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="slika">
            <button type="submit">Učitaj</button>
        </form>
    </section>
    <?php endif; ?>
</body>
</html>