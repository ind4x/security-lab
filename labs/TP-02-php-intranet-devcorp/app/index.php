<?php
// Intranet de l'entreprise - page d'accueil et routeur
session_start();
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
        <a href="index.php?page=pages/accueil.php">Accueil</a> |
        <a href="index.php?page=pages/contact.php">Contact</a> |
        <a href="login.php">Espace membres</a> |
        <a href="ping.php">Supervision reseau</a> |
        <a href="upload.php">Depot de CV</a> |
        <a href="livre.php">Livre d'or</a> |
        <a href="secret.php">Zone direction</a>
    </nav>
    <hr>
    <?php
    // Chargement de la page demandee
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    } else {
        $page = 'pages/accueil.php';
    }
    include($page);
    ?>
</body>
</html>
