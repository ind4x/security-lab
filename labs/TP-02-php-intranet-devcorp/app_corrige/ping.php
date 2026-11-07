<?php
// Outil de supervision : ping d'une machine du reseau interne (version corrigee)
$resultat = '';
$erreur = '';
if (isset($_GET['hote']) && $_GET['hote'] !== '') {
    $hote = trim($_GET['hote']);
    // Correction : on accepte uniquement une IP ou un nom d'hote valide
    if (filter_var($hote, FILTER_VALIDATE_IP) || preg_match('/^[a-zA-Z0-9.-]{1,253}$/', $hote)) {
        $option = stripos(PHP_OS, 'WIN') === 0 ? '-n 2' : '-c 2';
        // Correction : echappement de l'argument shell
        $resultat = shell_exec("ping $option " . escapeshellarg($hote));
    } else {
        $erreur = "Adresse invalide.";
    }
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
    <?php if ($erreur): ?>
        <p class="info"><?php echo $erreur; ?></p>
    <?php endif; ?>
    <form method="get">
        <label>Adresse IP ou nom d'hote : <input type="text" name="hote"></label>
        <button type="submit">Ping</button>
    </form>
    <?php if ($resultat !== '' && $resultat !== null): ?>
        <pre><?php echo htmlspecialchars($resultat); ?></pre>
    <?php endif; ?>
</body>
</html>
