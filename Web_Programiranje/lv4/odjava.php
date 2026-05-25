<?php
include 'db.php';

// Pražnjenje svih sesijskih varijabli
$_SESSION = array();

// Uništavanje sesijskog kolačića unutar preglednika
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Konačno uništavanje sesije
session_destroy();

header("Location: index.php");
exit;
?>