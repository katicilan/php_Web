const express = require("express");
const path = require("path");
const fs = require("fs");

const app = express();
const PORT = 3001;

app.set("view engine", "ejs");
app.use(express.static("public"));

// RUTA ZA SLIKE - prilagođena tvom EJS-u
app.get("/slike", (req, res) => {
    const imagesPath = path.join(__dirname, "public", "images");
    
    fs.readdir(imagesPath, (err, files) => {
        if (err) {
            console.error("Greška pri čitanju slika:", err);
            return res.render("slike", { images: [] });
        }

        // Mapiramo datoteke u objekte s id, url i title kako tvoj EJS traži
        const images = files
            .filter(file => /\.(jpg|jpeg|png|webp)$/i.test(file))
            .map((file, index) => ({
                id: `img${index}`,
                url: `/images/${file}`,
                title: file.split('.')[0] // Uzima ime datoteke kao naslov
            }));

        res.render("slike", { images: images });
    });
});

// OSTALE RUTE
app.get("/filmovi.csv", (req, res) => {
    res.sendFile(path.join(__dirname, "filmovi.csv"));
});

app.get("/indeks", (req, res) => {
    res.render("indeks");
});

app.get("/", (req, res) => { res.redirect("/indeks"); });

app.listen(PORT, () => {
    console.log(`Server radi na http://localhost:${PORT}/indeks`);
});