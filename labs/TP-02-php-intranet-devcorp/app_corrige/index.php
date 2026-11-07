<?php
// Intranet de l'entreprise - page d'accueil et routeur (version corrigee)
session_start();

// Correction LFI : liste blanche stricte des pages autorisees
$pages_autorisees = [
    'accueil' => 'pages/accueil.php',
    'contact' => 'pages/contact.php',
];
$cle = $_GET['page'] ?? 'accueil';
$page = $pages_autorisees[$cle] ?? $pages_autorisees['accueil'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Intranet DevCorp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Intranet DevCorp</h1>
    <nav>
        <a href="index.php?page=accueil">Accueil</a> |
        <a href="index.php?page=contact">Contact</a> |
        <a href="login.php">Espace membres</a> |
        <a href="ping.php">Supervision reseau</a> |
        <a href="upload.php">Depot de CV</a> |
        <a href="livre.php">Livre d'or</a> |
        <a href="secret.php">Zone direction</a>
    </nav>
    <hr>
    <?php include($page); ?>
</body>
</html>
