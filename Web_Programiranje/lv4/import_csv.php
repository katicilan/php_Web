<?php
include 'db.php';

$file = fopen('filmovi.csv', 'r');
fgetcsv($file); // Preskoči header (naslovni red)

while (($row = fgetcsv($file)) !== FALSE) {
    // Naslov,Zanr,Godina,Trajanje_min,Ocjena,Rezisery,Zemlja_porijekla
    $stmt = $conn->prepare("INSERT INTO filmovi (naslov, zanr, godina, trajanje_min, ocjena, reziser, zemlja_porijekla) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiidss", $row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6]);
    $stmt->execute();
}
fclose($file);
echo "Podaci uspješno uvezeni iz CSV-a u MySQL!";
?>