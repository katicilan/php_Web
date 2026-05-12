let sviFilmovi = [];
let omiljeni = [];

fetch('/filmovi.csv')
    .then(res => res.text())
    .then(csv => {
        const rezultat = Papa.parse(csv, { header: true, skipEmptyLines: true });
        const ocisti = (v) => v ? v.toString().replace(/""/g, '').replace(/"/g, '').trim() : "";

        sviFilmovi = rezultat.data.map(film => ({
            title: ocisti(film.Naslov),
            year: ocisti(film.Godina),
            genre: ocisti(film.Zanr),
            duration: ocisti(film.Trajanje_min),
            country: ocisti(film.Zemlja_porijekla),
            avg_vote: parseFloat(ocisti(film.Ocjena)) || 0
        }));
        prikaziTablicu(sviFilmovi.slice(0, 30));
    });

function prikaziTablicu(filmovi) {
    const tbody = document.querySelector('#filmovi-tablica tbody');
    tbody.innerHTML = '';
    filmovi.forEach(film => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${film.title}</td>
            <td>${film.year}</td>
            <td>${film.genre}</td>
            <td>${film.duration} min</td>
            <td>${film.country}</td>
            <td>${film.avg_vote}</td>
            <td><button onclick="dodajUOmiljene('${film.title.replace(/'/g, "\\'")}')">Dodaj</button></td>
        `;
        tbody.appendChild(row);
    });
}

function dodajUOmiljene(naslov) {
    if (!omiljeni.find(f => f.title === naslov)) {
        const film = sviFilmovi.find(f => f.title === naslov);
        omiljeni.push(film);
        osvjeziFavorite();
    }
}

function osvjeziFavorite() {
    const lista = document.getElementById('lista-favorita');
    const poruka = document.getElementById('prazna-poruka');
    lista.innerHTML = '';
    poruka.style.display = omiljeni.length === 0 ? 'block' : 'none';
    
    omiljeni.forEach((film, index) => {
        const li = document.createElement('li');
        li.innerHTML = `${film.title} <button onclick="ukloniIzFavorita(${index})">❌</button>`;
        li.style.cssText = "display:flex; justify-content:space-between; margin-bottom:5px; background:white; padding:5px; border-radius:4px;";
        lista.appendChild(li);
    });
}

function ukloniIzFavorita(index) {
    omiljeni.splice(index, 1);
    osvjeziFavorite();
}

// Slider i filter logic
document.getElementById('filter-rating').addEventListener('input', (e) => {
    document.getElementById('rating-value').textContent = e.target.value;
});

document.getElementById('primijeni-filtere').addEventListener('click', () => {
    const naslov = document.getElementById('filter-naslov').value.toLowerCase();
    const zanr = document.getElementById('filter-zanr').value.toLowerCase();
    const ocjena = parseFloat(document.getElementById('filter-rating').value);

    const filtrirani = sviFilmovi.filter(f => 
        f.title.toLowerCase().includes(naslov) && 
        f.genre.toLowerCase().includes(zanr) && 
        f.avg_vote >= ocjena
    );
    prikaziTablicu(filtrirani);
});