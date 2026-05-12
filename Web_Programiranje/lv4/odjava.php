<?php
// Uključujemo db.php samo da bismo imali pristup sesiji (session_start)
include 'db.php';

// Brišemo sve varijable sesije
$_SESSION = array();

// Ako želite potpuno uništiti sesiju, obrišite i session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Konačno, uništavamo sesiju
session_destroy();

// Preusmjeravamo korisnika na početnu stranicu
header("Location: index.php");
exit;
?>