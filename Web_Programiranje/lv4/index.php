<?php
include 'db.php';

// Inicijalizacija parametara za filtriranje
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$zanr = isset($_GET['zanr']) ? trim($_GET['zanr']) : '';
$godina = isset($_GET['godina']) ? trim($_GET['godina']) : '';
$zemlja = isset($_GET['zemlja']) ? trim($_GET['zemlja']) : '';

// Izgradnja dinamičkog SQL upita
$sql = "SELECT * FROM filmovi WHERE 1=1";
$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND naslov LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}
if ($zanr !== '') {
    $sql .= " AND zanr = ?";
    $params[] = $zanr;
    $types .= "s";
}
if ($godina !== '') {
    $sql .= " AND godina = ?";
    $params[] = intval($godina);
    $types .= "i";
}
if ($zemlja !== '') {
    $sql .= " AND zemlja_porijekla = ?";
    $params[] = $zemlja;
    $types .= "s";
}

$sql .= " ORDER BY naslov ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Dohvat jedinstvenih žanrova i zemalja za padajuće izbornike
$zanrovi_res = $conn->query("SELECT DISTINCT zanr FROM filmovi WHERE zanr IS NOT NULL AND zanr != ''");
$zemlje_res = $conn->query("SELECT DISTINCT zemlja_porijekla FROM filmovi WHERE zemlja_porijekla IS NOT NULL AND zemlja_porijekla != ''");

// Dohvat filmova iz osobne videoteke (Zadatak: Favoriti/Osobna košarica na istoj stranici ako asajd to traži)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$favoriti_res = null;
if ($user_id > 0) {
    $fav_sql = "SELECT f.naslov, f.ocjena FROM zeljeni_filmovi zf JOIN filmovi f ON zf.film_id = f.id WHERE zf.korisnik_id = ?";
    $stmt_fav = $conn->prepare($fav_sql);
    $stmt_fav->bind_param("i", $user_id);
    $stmt_fav->execute();
    $favoriti_res = $stmt_fav->get_result();
}
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtualna Videoteka - Početna</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>

<header>
    <h1>Virtualna Videoteka</h1>
    
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

<div class="glavni-kontejner layout">
    
    <section id="filteri">
        <h3>Pretraživanje i filtriranje filmova</h3>
        <form method="GET" action="index.php">
            <input type="text" name="search" placeholder="Pretraži po naslovu..." value="<?= htmlspecialchars($search) ?>">
            
            <select name="zanr">
                <option value="">-- Svi žanrovi --</option>
                <?php while($z = $zanrovi_res->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($z['zanr']) ?>" <?= $zanr == $z['zanr'] ? 'selected' : '' ?>><?= htmlspecialchars($z['zanr']) ?></option>
                <?php endwhile; ?>
            </select>

            <input type="number" name="godina" placeholder="Godina (npr. 2023)" value="<?= htmlspecialchars($godina) ?>" style="width: 140px;">

            <select name="zemlja">
                <option value="">-- Sve zemlje --</option>
                <?php while($z = $zemlje_res->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($z['zemlja_porijekla']) ?>" <?= $zemlja == $z['zemlja_porijekla'] ? 'selected' : '' ?>><?= htmlspecialchars($z['zemlja_porijekla']) ?></option>
                <?php endwhile; ?>
            </select>

            <button type="submit">Filtriraj</button>
            <a href="index.php" style="display: inline-block; padding: 8px 12px; background: #bbb; color: black; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: bold;">Poništi</a>
        </form>
    </section>

    <div class="donji-dio">
        
        <main class="tablica-sekcija content">
            <h2>Popis svih filmova</h2>
            <table>
                <thead>
                    <tr>
                        <th>Naslov</th>
                        <th>Žanr</th>
                        <th>Godina</th>
                        <th>Trajanje</th>
                        <th>Zemlja</th>
                        <th>Ocjena</th>
                        <th>Akcija</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows == 0): ?>
                        <tr><td colspan="7" style="text-align: center;">Nema pronađenih filmova za odabrane filtre.</td></tr>
                    <?php endif; ?>
                    <?php while($film = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($film['naslov']) ?></strong></td>
                        <td><?= htmlspecialchars($film['zanr']) ?></td>
                        <td><?= $film['godina'] ?>.</td>
                        <td><?= $film['trajanje_min'] ?> min</td>
                        <td><?= htmlspecialchars($film['zemlja_porijekla']) ?></td>
                        <td><?= $film['ocjena'] ?> ⭐</td>
                        <td><a href="videoteku.php?id=<?= $film['id'] ?>">Dodaj u listu</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>

        <aside id="favoriti-sekcija">
            <h3>Moja brza lista</h3>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <p style="font-size: 14px; color: #666;">Prijavite se kako biste vidjeli svoje favorite.</p>
            <?php elseif ($favoriti_res && $favoriti_res->num_rows > 0): ?>
                <ul style="padding-left: 20px; font-size: 15px;">
                    <?php while($fav = $favoriti_res->fetch_assoc()): ?>
                        <li style="margin-bottom: 8px;">
                            <strong><?= htmlspecialchars($fav['naslov']) ?></strong> (<?= $fav['ocjena'] ?> ⭐)
                        </li>
                    <?php endwhile; ?>
                </ul>
                <p style="text-align: center; margin-top: 15px;"><a href="osobna_videoteka.php" style="font-size: 13px; font-weight: bold;">Uredi listu &rarr;</a></p>
            <?php else: ?>
                <p style="font-size: 14px; color: #666;">Vaša lista je trenutno prazna.</p>
            <?php endif; ?>
        </aside>

    </div>
</div>

<footer>
    <p>&copy; <?= date("Y") ?> - Virtualna Videoteka. Sva prava pridržana.</p>
</footer>

</body>
</html>