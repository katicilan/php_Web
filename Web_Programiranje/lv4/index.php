<?php 
include 'db.php'; 
session_start();
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Moja Filmoteka</title>
    <link rel="stylesheet" href="style/style.css"> </head>
<body>

<header>
    <h1>Moja Filmoteka</h1>
    <nav>
        <ul class="menu">
            <li><a href="slike.php">Slike</a></li>
            <li><a href="grafikon.php">Grafikon</a></li>
            <li><a href="indeks.php">Indeks</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="odjava.php">Odjava</a></li>
            <?php else: ?>
                <li><a href="prijava.php">Prijava</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<main class="glavni-kontejner">
    <section id="filteri">
        <input type="text" id="filter-naslov" placeholder="Pretraži naslov...">
        <button onclick="filtrirajBazu()">Primijeni filtre</button>
    </section>

    <div class="donji-dio" style="display: flex;">
        <section class="tablica-sekcija" style="flex: 3;">
            <table id="filmovi-tablica">
                <thead>
                    <tr>
                        <th>Naslov</th><th>Godina</th><th>Žanr</th><th>Ocjena</th><th>Akcija</th>
                    </tr>
                </thead>
                <tbody id="prikaz-filmova">
                    </tbody>
            </table>
        </section>

        <aside id="favoriti-sekcija" style="flex: 1;">
            <h2>Favoriti</h2>
            <ul id="lista-favorita"></ul>
        </aside>
    </div>
</main>

<script>
// Umjesto PapaParse, koristimo fetch na tvoj novi PHP API
function ucitajFilmove() {
    fetch('dohvati_filmove.php')
    .then(res => res.json())
    .then(data => {
        const tbody = document.getElementById('prikaz-filmova');
        tbody.innerHTML = data.map(film => `
            <tr>
                <td>${film.naslov}</td>
                <td>${film.godina}</td>
                <td>${film.zanr}</td>
                <td>${film.ocjena}</td>
                <td><button onclick="dodajUFavorite('${film.naslov}')">⭐</button></td>
            </tr>
        `).join('');
    });
}
window.onload = ucitajFilmove;
</script>
</body>
</html>