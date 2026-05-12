<?php 
include 'db.php'; 

// Provjera prijave
if (!isset($_SESSION['user_id'])) {
    header("Location: prijava.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Logika za brisanje iz liste
if (isset($_GET['obrisi'])) {
    $del_id = $_GET['obrisi'];
    $stmt = $conn->prepare("DELETE FROM zeljeni_filmovi WHERE id = ? AND korisnik_id = ?");
    $stmt->bind_param("ii", $del_id, $user_id);
    $stmt->execute();
    header("Location: osobna_videoteka.php");
}
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Moja Lista - Videoteka</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <header>
        <h1>Moja Osobna Lista</h1>
        <nav>
            <ul style="display: flex; gap: 20px; list-style: none; background: #333; padding: 10px;">
                <li><a href="index.php" style="color: white;">Svi Filmovi</a></li>
                <li><a href="odjava.php" style="color: white;">Odjava</a></li>
            </ul>
        </nav>
    </header>

    <main class="glavni-kontejner">
        <h2>Filmovi koje želim pogledati:</h2>
        <table border="1" style="width: 100%; border-collapse: collapse;">
            <thead style="background: #444; color: white;">
                <tr>
                    <th>Naslov</th><th>Žanr</th><th>Ocjena</th><th>Akcija</th>
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

                if ($result->num_rows == 0) echo "<tr><td colspan='4'>Vaša lista je prazna.</td></tr>";

                while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['naslov']) ?></td>
                        <td><?= $row['zanr'] ?></td>
                        <td><?= $row['ocjena'] ?></td>
                        <td><a href="osobna_videoteka.php?obrisi=<?= $row['lista_id'] ?>" style="color: red;">Ukloni</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</body>
</html>