<?php
// Livre d'or de l'intranet
$fichier = __DIR__ . '/data/livre.txt';

if (isset($_POST['pseudo']) && isset($_POST['message'])) {
    $ligne = date('d/m/Y H:i') . " | " . $_POST['pseudo'] . " | " . $_POST['message'] . "\n";
    file_put_contents($fichier, $ligne, FILE_APPEND);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Livre d'or - Intranet DevCorp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Livre d'or</h1>
    <p><a href="index.php">Retour a l'accueil</a></p>
    <form method="post">
        <label>Pseudo : <input type="text" name="pseudo"></label><br>
        <label>Message : <input type="text" name="message" size="60"></label><br>
        <button type="submit">Signer le livre d'or</button>
    </form>
    <h2>Messages</h2>
    <div class="livre">
    <?php
    if (file_exists($fichier)) {
        $lignes = file($fichier);
        foreach (array_reverse($lignes) as $ligne) {
            echo "<p>" . $ligne . "</p>\n";
        }
    } else {
        echo "<p>Aucun message pour le moment.</p>";
    }
    ?>
    </div>
</body>
</html>
