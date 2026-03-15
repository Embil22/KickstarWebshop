<?php
session_start();

// Munkamenet törlése
$_SESSION = array();

// Cookie törlése
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Munkamenet megsemmisítése
session_destroy();

// Átirányítás a bejelentkező oldalra
header('Location: index.php?logout=success');
exit;
?>