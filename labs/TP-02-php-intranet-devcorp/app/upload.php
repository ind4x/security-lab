<?php
// Depot de CV : les candidats envoient leur CV, les RH les consultent
$message = '';
if (isset($_FILES['cv'])) {
    $dossier = __DIR__ . '/uploads/';
    $fichier = $dossier . basename($_FILES['cv']['name']);
    if (move_uploaded_file($_FILES['cv']['tmp_name'], $fichier)) {
        $message = "Fichier recu : uploads/" . basename($_FILES['cv']['name']);
    } else {
        $message = "Echec de l'envoi.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Depot de CV - Intranet DevCorp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Depot de CV</h1>
    <p><a href="index.php">Retour a l'accueil</a></p>
    <p>Candidats : envoyez votre CV au format PDF. Le service RH le consultera.</p>
    <?php if ($message): ?>
        <p class="info"><?php echo $message; ?></p>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="cv">
        <button type="submit">Envoyer</button>
    </form>
</body>
</html>
