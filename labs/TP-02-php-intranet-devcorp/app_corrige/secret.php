<?php
// Zone direction (version corrigee)
$message = '';
$acces = false;

// Correction : hachage SHA-256 de la cle, comparaison avec hash_equals
// (la cle est definie en variable d'environnement, plus de hash en dur)
$hash_cle = getenv('CLE_DIRECTION_HASH') ?: hash('sha256', 'changer-moi-en-production');

if (isset($_POST['cle'])) {
    if (hash_equals($hash_cle, hash('sha256', $_POST['cle']))) {
        $acces = true;
    } else {
        $message = "Cle incorrecte.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Zone direction - Intranet DevCorp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Zone direction</h1>
    <p><a href="index.php">Retour a l'accueil</a></p>
    <?php if ($acces): ?>
        <h2>Documents confidentiels</h2>
        <p>Budget previsionnel 2027 : valide.<br>
        Plan de depart volontaire : reunion le 12.<br>
        Racheter le concurrent AtlasSoft : negociation en cours.</p>
    <?php else: ?>
        <?php if ($message): ?>
            <p class="info"><?php echo $message; ?></p>
        <?php endif; ?>
        <p>Cette zone est reservee a la direction generale. Saisissez la cle d'acces.</p>
        <form method="post">
            <label>Cle d'acces : <input type="text" name="cle"></label>
            <button type="submit">Entrer</button>
        </form>
    <?php endif; ?>
</body>
</html>
