<?php 
include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: prijava.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Logika za sigurno brisanje iz liste
if (isset($_GET['obrisi']) && is_numeric($_GET['obrisi'])) {
    $del_id = intval($_GET['obrisi']);
    $stmt = $conn->prepare("DELETE FROM zeljeni_filmovi WHERE id = ? AND korisnik_id = ?");
    $stmt->bind_param("ii", $del_id, $user_id);
    $stmt->execute();
    header("Location: osobna_videoteka.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moja Lista - Videoteka</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <header>
        <h1>Virtualna Videoteka - Moja Lista</h1>
        
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

    <main style="padding:20px;">
        <h2>Filmovi koje želim pogledati:</h2>
        <table>
            <thead>
                <tr>
                    <th>Naslov</th>
                    <th>Žanr</th>
                    <th>Ocjena</th>
                    <th>Akcija</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT z.id as lista_id, f.naslov, f.zanr, f.ocjena 
                        FROM zeljeni_filmovi z 
                        JOIN filmovi f ON z.film_id = f.id 
                        WHERE z.korisnik_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows == 0) {
                    echo "<tr><td colspan='4' style='text-align: center;'>Vaša lista je prazna.</td></tr>";
                }

                while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['naslov']) ?></strong></td>
                        <td><?= htmlspecialchars($row['zanr']) ?></td>
                        <td><?= $row['ocjena'] ?> ⭐</td>
                        <td><a href="osobna_videoteka.php?obrisi=<?= $row['lista_id'] ?>" style="color: red;">Ukloni</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> - Virtualna Videoteka. Sva prava pridržana.</p>
    </footer>
</body>
</html>