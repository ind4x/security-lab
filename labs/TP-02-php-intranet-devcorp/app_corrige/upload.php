<?php
// Depot de CV (version corrigee)
$message = '';
if (isset($_FILES['cv'])) {
    // Correction : liste blanche d'extensions et de types
    $extensions = ['pdf'];
    $types = ['application/pdf'];
    $nom = $_FILES['cv']['name'];
    $ext = strtolower(pathinfo($nom, PATHINFO_EXTENSION));
    $mime = mime_content_type($_FILES['cv']['tmp_name']);
    $taille_max = 2 * 1024 * 1024;

    if (!in_array($ext, $extensions, true) || !in_array($mime, $types, true)) {
        $message = "Seuls les fichiers PDF sont acceptes.";
    } elseif ($_FILES['cv']['size'] > $taille_max) {
        $message = "Fichier trop volumineux (2 Mo maximum).";
    } else {
        // Correction : nom de fichier regenere, jamais celui du client
        $nouveau_nom = bin2hex(random_bytes(8)) . '.pdf';
        $fichier = __DIR__ . '/uploads/' . $nouveau_nom;
        if (move_uploaded_file($_FILES['cv']['tmp_name'], $fichier)) {
            $message = "CV recu, merci.";
        } else {
            $message = "Echec de l'envoi.";
        }
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
        <input type="file" name="cv" accept="application/pdf">
        <button type="submit">Envoyer</button>
    </form>
</body>
</html>
