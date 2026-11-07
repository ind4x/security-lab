<?php
// Outil de supervision : ping d'une machine du reseau interne
$resultat = '';
if (isset($_GET['hote']) && $_GET['hote'] !== '') {
    $hote = $_GET['hote'];
    $option = stripos(PHP_OS, 'WIN') === 0 ? '-n 2' : '-c 2';
    $resultat = shell_exec("ping $option $hote");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Supervision reseau - Intranet DevCorp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Supervision reseau</h1>
    <p><a href="index.php">Retour a l'accueil</a></p>
    <p>Verifiez la disponibilite d'une machine du reseau interne.</p>
    <form method="get">
        <label>Adresse IP ou nom d'hote : <input type="text" name="hote"></label>
        <button type="submit">Ping</button>
    </form>
    <?php if ($resultat !== ''): ?>
        <pre><?php echo $resultat; ?></pre>
    <?php endif; ?>
</body>
</html>
