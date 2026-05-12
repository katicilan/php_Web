CREATE DATABASE IF NOT EXISTS videoteka;
USE videoteka;

-- Tablica za filmove (podaci iz tvog CSV-a će ići ovdje)
CREATE TABLE IF NOT EXISTS filmovi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naslov VARCHAR(255),
    zanr VARCHAR(100),
    godina INT,
    trajanje_min INT,
    ocjena DECIMAL(3,1),
    reziser VARCHAR(255),
    zemlja_porijekla VARCHAR(100)
);

-- Tablica za korisnike (za autentifikaciju)
CREATE TABLE IF NOT EXISTS korisnici (
    id INT AUTO_INCREMENT PRIMARY KEY,
    korisnicko_ime VARCHAR(50) UNIQUE,
    lozinka VARCHAR(255)
);

-- Tablica za slike (Zadatak 2)
CREATE TABLE IF NOT EXISTS slike (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naziv_datoteke VARCHAR(255),
    putanja VARCHAR(255)
);

-- Tablica za ocjene slika (Zadatak 2)
CREATE TABLE IF NOT EXISTS ocjene_slika (
    id_korisnik INT,
    id_slika INT,
    ocjena INT,
    PRIMARY KEY (id_korisnik, id_slika),
    FOREIGN KEY (id_korisnik) REFERENCES korisnici(id),
    FOREIGN KEY (id_slika) REFERENCES slike(id)
);