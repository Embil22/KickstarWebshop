<?php
require_once 'database.php';

// Teszt jelszó
$password = 'admin123';
$stored_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

// Ellenőrizzük a hash-t
if (password_verify($password, $stored_hash)) {
    echo "✅ A jelszó helyes!<br>";
} else {
    echo "❌ A jelszó NEM megfelelő!<br>";
}

// Ellenőrizzük az adatbázisban lévő admin jelszavát
$stmt = $pdo->query("SELECT * FROM admins WHERE username = 'admin'");
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "<br>📝 Adatbázisban található admin:<br>";
    echo "ID: " . $admin['id'] . "<br>";
    echo "Felhasználónév: " . $admin['username'] . "<br>";
    echo "Email: " . $admin['email'] . "<br>";
    echo "Hash: " . $admin['password'] . "<br>";
    
    if (password_verify('admin123', $admin['password'])) {
        echo "✅ Az adatbázisban lévő jelszó is helyes!<br>";
    } else {
        echo "❌ Az adatbázisban lévő jelszó NEM megfelelő!<br>";
        
        // Javítsuk ki
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
        $update->execute([$new_hash]);
        echo "✅ Jelszó javítva! Új hash: " . $new_hash;
    }
} else {
    echo "❌ Nincs 'admin' felhasználó az adatbázisban!<br>";
    
    // Hozzuk létre
    $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $insert = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, 'superadmin')");
    $insert->execute(['admin', $new_hash]);
    echo "✅ Admin felhasználó létrehozva!";
}
?>