<?php
session_start();
require_once __DIR__ . '/inc/db.php';

$message = '';

if (isset($_REQUEST['username'])) {
    $u = $_REQUEST['username'];
    $p = md5($_REQUEST['password']);

    $sql = "SELECT * FROM users WHERE username = '$u' AND password = '$p'";
    $resultat = db()->query($sql);
    $ligne = $resultat ? $resultat->fetch() : false;

    if ($ligne) {
        $_SESSION['user'] = $ligne['username'];
        $_SESSION['role'] = $ligne['role'];
        $message = "Connexion reussie. Bienvenue " . $ligne['username'] . " (role : " . $ligne['role'] . ").";
    } else {
        $message = "Identifiants incorrects.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace membres - Intranet DevCorp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Espace membres</h1>
    <p><a href="index.php">Retour a l'accueil</a></p>
    <?php if ($message): ?>
        <p class="info"><?php echo $message; ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['user'])): ?>
        <p>Vous etes connecte en tant que <strong><?php echo $_SESSION['user']; ?></strong>.</p>
    <?php else: ?>
        <form method="post">
            <label>Identifiant : <input type="text" name="username"></label><br>
            <label>Mot de passe : <input type="password" name="password"></label><br>
            <button type="submit">Se connecter</button>
        </form>
    <?php endif; ?>
</body>
</html>
