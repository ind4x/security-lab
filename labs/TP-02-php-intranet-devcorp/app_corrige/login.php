<?php
session_start();
require_once __DIR__ . '/inc/db.php';

$message = '';

if (isset($_REQUEST['username'])) {
    // Correction : requete preparee, les entrees ne sont plus du SQL
    $stmt = db()->prepare("SELECT * FROM users WHERE username = :u");
    $stmt->execute([':u' => $_REQUEST['username']]);
    $ligne = $stmt->fetch();

    // Correction : hachage robuste et comparaison sure
    if ($ligne && password_verify($_REQUEST['password'], $ligne['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = $ligne['username'];
        $_SESSION['role'] = $ligne['role'];
        $message = "Connexion reussie. Bienvenue " . htmlspecialchars($ligne['username']) . ".";
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
        <p>Vous etes connecte en tant que <strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong>.</p>
    <?php else: ?>
        <form method="post">
            <label>Identifiant : <input type="text" name="username"></label><br>
            <label>Mot de passe : <input type="password" name="password"></label><br>
            <button type="submit">Se connecter</button>
        </form>
    <?php endif; ?>
</body>
</html>
