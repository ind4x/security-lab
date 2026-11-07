<?php
// Zone direction : reservee a la direction generale, protegee par une cle
$message = '';
$acces = false;

// Hash de la cle de la direction (genere en 2019, conserve tel quel)
$hash_cle = "0e462097431906509019562988736854";

if (isset($_POST['cle'])) {
    if (md5($_POST['cle']) == $hash_cle) {
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
