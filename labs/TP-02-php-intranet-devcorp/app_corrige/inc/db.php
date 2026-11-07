<?php
// Connexion a la base SQLite de l'intranet (version corrigee)
function db() {
    $chemin = __DIR__ . '/../data/intranet.sqlite';
    $nouvelle = !file_exists($chemin);
    $pdo = new PDO('sqlite:' . $chemin);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if ($nouvelle) {
        $pdo->exec("CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT,
            password TEXT,
            role TEXT
        )");
        // Correction : hachage bcrypt au lieu de MD5
        $pdo->exec("INSERT INTO users (username, password, role) VALUES
            ('admin', '" . password_hash('Adm1n_DevCorp!2019', PASSWORD_BCRYPT) . "', 'admin'),
            ('jsaad', '" . password_hash('julie2024', PASSWORD_BCRYPT) . "', 'employe'),
            ('mbennani', '" . password_hash('mehdi2024', PASSWORD_BCRYPT) . "', 'employe')");
    }
    return $pdo;
}
