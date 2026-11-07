<?php
// Connexion a la base SQLite de l'intranet
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
        $pdo->exec("INSERT INTO users (username, password, role) VALUES
            ('admin', '" . md5('Adm1n_DevCorp!2019') . "', 'admin'),
            ('jsaad', '" . md5('julie2024') . "', 'employe'),
            ('mbennani', '" . md5('mehdi2024') . "', 'employe')");
    }
    return $pdo;
}
