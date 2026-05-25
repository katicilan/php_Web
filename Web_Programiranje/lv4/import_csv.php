<?php
include 'db.php';

if (!file_exists('filmovi.csv')) {
    die("Datoteka 'filmovi.csv' ne postoji.");
}

$file = fopen('filmovi.csv', 'r');
fgetcsv($file); // Preskače naslovni red (header)

$brojac = 0;
// Priprema izraza izvan petlje radi znatno bržeg izvođenja
$stmt = $conn->prepare("INSERT INTO filmovi (naslov, zanr, godina, trajanje_min, ocjena, reziser, zemlja_porijekla) VALUES (?, ?, ?, ?, ?, ?, ?)");

while (($row = fgetcsv($file)) !== FALSE) {
    // Provjera ima li redak potreban broj stupaca
    if (count($row) >= 7) {
        $naslov = $row[0];
        $zanr = $row[1];
        $godina = intval($row[2]);
        $trajanje = intval($row[3]);
        $ocjena = floatval($row[4]);
        $reziser = $row[5];
        $zemlja = $row[6];

        $stmt->bind_param("ssiidss", $naslov, $zanr, $godina, $trajanje, $ocjena, $reziser, $zemlja);
        $stmt->execute();
        $brojac++;
    }
}

fclose($file);
echo "Podaci uspješno uvezeni! Ukupno dodano $brojac filmova iz CSV-a u MySQL bazu podataka.";
?>