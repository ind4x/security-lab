# Lab PHP : guide de remédiation

**Module** : Sécurité des SI et Cybersécurité, 3ACI INFO GL
**Application** : Intranet DevCorp

Ce guide corrige une par une les vulnérabilités du guide d'exploitation.
Pour chaque faille : le code fautif, le code corrigé, pourquoi ça marche, et
le test qui prouve que l'exploit ne fonctionne plus.

La version corrigée complète est fournie dans le dossier `app_corrige/`.
Lancez-la sur un autre port pour comparer les deux :

```
php -S 127.0.0.1:8001 -t app_corrige
```

La démarche attendue : corrigez d'abord vous-même dans `app/`, puis comparez
avec `app_corrige/`, puis rejouez chaque exploit pour vérifier qu'il échoue.

## Remédiation 0 : retirer ce qui n'a rien à faire là

Le fichier `phpinfo.php` est supprimé, tout simplement. Une page `phpinfo()`
donne à un attaquant la version exacte de PHP, les extensions, les chemins
du serveur, les variables d'environnement. C'est de la reconnaissance offerte
gratuitement.

**Vérification** : http://127.0.0.1:8001/phpinfo.php ne renvoie plus aucune
information technique.

**Leçon** : la remédiation n'est pas toujours du code. Parfois, c'est du
ménage : fichiers oubliés, pages de test, comptes de démonstration. Un audit
commence par un inventaire.

## Remédiation 1 : injection SQL

**Code fautif** (`login.php`) :

```php
$sql = "SELECT * FROM users WHERE username = '$u' AND password = '$p'";
$resultat = db()->query($sql);
```

**Code corrigé** :

```php
$stmt = db()->prepare("SELECT * FROM users WHERE username = :u");
$stmt->execute([':u' => $_POST['username']]);
$ligne = $stmt->fetch();

if ($ligne && password_verify($_POST['password'], $ligne['password'])) {
    // connexion
}
```

**Pourquoi ça marche** : avec une requête préparée, la saisie n'est jamais
interprétée comme du SQL. `admin' -- ` devient une simple chaîne de texte,
qui ne correspond à aucun utilisateur. Au passage, le mot de passe n'est plus
haché en MD5 mais avec `password_hash()` (bcrypt) dans `inc/db.php`, et vérifié
avec `password_verify()`. La base est recréée automatiquement au premier
lancement si vous supprimez `data/intranet.sqlite`.

**Test de non ré-exploitation** :

```
curl -X POST http://127.0.0.1:8001/login.php --data-urlencode "username=admin' -- " --data-urlencode "password=x"
```

Résultat attendu : « Identifiants incorrects. » L'injection est morte.

## Remédiation 2 : inclusion de fichiers (LFI)

**Code fautif** (`index.php`) :

```php
$page = $_GET['page'];
include($page);
```

**Code corrigé** :

```php
$pages_autorisees = [
    'accueil' => 'pages/accueil.php',
    'contact' => 'pages/contact.php',
];
$cle = $_GET['page'] ?? 'accueil';
$page = $pages_autorisees[$cle] ?? $pages_autorisees['accueil'];
include($page);
```

**Pourquoi ça marche** : l'utilisateur ne choisit plus un fichier, il choisit
un mot-clé parmi une liste blanche. Tout ce qui n'est pas dans la liste tombe
sur la page d'accueil. Le serveur ne recevra jamais de chemin, jamais de
wrapper `php://`, jamais de traversée de répertoires.

**Test de non ré-exploitation** :

```
curl "http://127.0.0.1:8001/index.php?page=php://filter/convert.base64-encode/resource=secret.php"
curl "http://127.0.0.1:8001/index.php?page=/etc/passwd"
```

Résultat attendu : la page d'accueil normale dans les deux cas. Plus aucune
lecture de fichier.

## Remédiation 3 : upload de fichiers

**Code fautif** (`upload.php`) : aucun contrôle, nom d'origine conservé,
dossier accessible par le web.

**Code corrigé** :

```php
$extensions = ['pdf'];
$types = ['application/pdf'];
$ext = strtolower(pathinfo($nom, PATHINFO_EXTENSION));
$mime = mime_content_type($_FILES['cv']['tmp_name']);

if (!in_array($ext, $extensions, true) || !in_array($mime, $types, true)) {
    $message = "Seuls les fichiers PDF sont acceptes.";
} elseif ($_FILES['cv']['size'] > 2 * 1024 * 1024) {
    $message = "Fichier trop volumineux (2 Mo maximum).";
} else {
    $nouveau_nom = bin2hex(random_bytes(8)) . '.pdf';
    move_uploaded_file($_FILES['cv']['tmp_name'], __DIR__ . '/uploads/' . $nouveau_nom);
}
```

**Pourquoi ça marche** : trois verrous indépendants. L'extension doit être
`.pdf`, le contenu réel du fichier doit être un PDF (vérifié par
`mime_content_type`, pas par ce que déclare le client), et le nom est
régénéré aléatoirement : même si un fichier malveillant passait, l'attaquant
n'en connaîtrait pas l'adresse. Sur un vrai serveur, on ajouterait un
quatrième verrou : interdire l'exécution de PHP dans le dossier d'upload
(configuration du serveur web), ou stocker hors de la racine web.

**Test de non ré-exploitation** :

```
curl -F "cv=@shell.php" http://127.0.0.1:8001/upload.php
curl "http://127.0.0.1:8001/uploads/shell.php?cmd=whoami"
```

Résultat attendu : « Seuls les fichiers PDF sont acceptes. », puis une erreur
404 : le shell n'existe pas, il n'y a rien à exécuter.

## Remédiation 4 : injection de commandes

**Code fautif** (`ping.php`) :

```php
$resultat = shell_exec("ping $option $hote");
```

**Code corrigé** :

```php
if (filter_var($hote, FILTER_VALIDATE_IP) || preg_match('/^[a-zA-Z0-9.-]{1,253}$/', $hote)) {
    $resultat = shell_exec("ping $option " . escapeshellarg($hote));
} else {
    $erreur = "Adresse invalide.";
}
```

**Pourquoi ça marche** : deux lignes de défense. D'abord la validation : on
n'accepte qu'une adresse IP ou un nom d'hôte conforme (lettres, chiffres,
point, tiret, rien d'autre ; pas d'espace, pas de `&`, pas de `;`). Ensuite
l'échappement : `escapeshellarg()` garantit que même une saisie bizarre
restera un seul argument de la commande `ping`, jamais une seconde commande.
La validation seule suffirait ici ; l'échappement est la ceinture en plus des
bretelles, et c'est comme ça qu'on sécurise : en couches.

**Test de non ré-exploitation** :

```
curl -G "http://127.0.0.1:8001/ping.php" --data-urlencode "hote=127.0.0.1&whoami"
```

Résultat attendu : « Adresse invalide. » et aucune commande exécutée.

## Remédiation 5 : type juggling

**Code fautif** (`secret.php`) :

```php
if (md5($_POST['cle']) == $hash_cle) { $acces = true; }
```

**Code corrigé** :

```php
$hash_cle = getenv('CLE_DIRECTION_HASH') ?: hash('sha256', 'changer-moi-en-production');

if (hash_equals($hash_cle, hash('sha256', $_POST['cle']))) {
    $acces = true;
}
```

**Pourquoi ça marche** : trois changements. `hash('sha256', ...)` remplace MD5,
cassé depuis longtemps. `hash_equals()` remplace `==` : comparaison de chaînes
stricte, en temps constant, sans conversion numérique, donc plus de magic hash.
Et le hash de référence sort du code pour passer en variable d'environnement :
le guide d'exploitation vous a montré que le code source peut fuiter (LFI),
rien de secret ne doit donc y vivre.

**Test de non ré-exploitation** :

```
curl -X POST http://127.0.0.1:8001/secret.php --data-urlencode "cle=QNKCDZO"
```

Résultat attendu : « Cle incorrecte. » Le magic hash ne passe plus.

## Remédiation 6 : XSS stockée

**Code fautif** (`livre.php`) :

```php
echo "<p>" . $ligne . "</p>\n";
```

**Code corrigé** :

```php
echo "<p>" . htmlspecialchars($ligne) . "</p>\n";
```

**Pourquoi ça marche** : `htmlspecialchars()` transforme `<script>` en texte
inoffensif `&lt;script&gt;` : le navigateur l'affiche au lieu de l'exécuter.
La règle d'or : on échappe à l'affichage, pas forcément au stockage, car la
même donnée peut être affichée dans des contextes différents (HTML, attribut,
JavaScript) qui demandent des encodages différents. La correction limite aussi
la longueur des messages, ce qui réduit le bruit et les abus.

**Test de non ré-exploitation** :

```
curl -X POST http://127.0.0.1:8001/livre.php --data-urlencode "pseudo=attaquant" --data-urlencode "message=<script>alert(document.cookie)</script>"
curl http://127.0.0.1:8001/livre.php
```

Résultat attendu : le message s'affiche en toutes lettres, visible et
inoffensif, au lieu de s'exécuter. Aucune boîte d'alerte.

## Remédiation 7 : PHP2Shell, ou quand la remédiation n'est pas du code

**La faille** : CVE-2026-109618 et CVE-2026-109619 (21 octobre 2026), deux
exécutions de code à distance dans le moteur PHP, notées 9,2. Aucune
modification du code de l'application ne peut les corriger.

**La remédiation, dans l'ordre** :

1. **Inventaire** : lister toutes les machines qui font tourner PHP, en
   commençant par celles exposées à Internet. C'est l'étape que tout le monde
   sous-estime, et c'est exactement le thème de ce lab : on ne peut pas
   corriger ce qu'on n'a pas recensé.
2. **Mise à jour** : passer à PHP 8.2.35, 8.3.36, 8.4.27 ou 8.5.12 selon la
   branche. Pour les branches 8.1 et antérieures (fin de vie) : paquet
   rétroporté du fournisseur, ou migration vers une branche maintenue.
3. **Redémarrage** : les workers PHP doivent être redémarrés pour que la
   nouvelle version soit réellement chargée.
4. **Configuration d'atténuation** : désactiver `session.upload_progress`
   (`session.upload_progress.enabled = 0` dans le php.ini), mesure
   recommandée par l'équipe PHP, y compris contre CVE-2026-115560, la
   troisième faille annoncée sans correctif fin octobre 2026.
5. **Réduire l'exposition** : désactiver l'en-tête `X-Powered-By`
   (`expose_php = Off` dans php.ini). Cela ne corrige rien, mais cesse
   d'offrir la version exacte du moteur au premier venu.

**Test de non ré-exploitation** : deux preuves, une technique et une
documentaire.

Preuve technique, avec l'environnement `php2shell/` du lab. Appliquez la
configuration atténuée qui désactive `session.upload_progress`, puis
rejouez l'exploit :

```
cd php2shell
docker compose -f docker-compose.yml -f docker-compose.mitigated.yml up -d
python scripts/php2shell_poc.py --monitor --rounds 5 --stall 2
```

Résultat attendu : chaque round affiche « Armed: NO ». Le chemin vulnérable
du moteur n'est plus atteignable, exactement comme pour les six failles
précédentes après correction. Revenez ensuite à la configuration vulnérable
avec `docker compose down` si vous voulez rejouer la démonstration.

Preuve documentaire, sur l'application principale :

```
php -v
curl -I http://127.0.0.1:8001/index.php
```

Résultat attendu : une version supérieure ou égale à la version corrigée de
votre branche, et plus d'en-tête `X-Powered-By` si `expose_php = Off`.
L'équivalent grand public de ce test est de relire l'alerte CERT-FR et de
cocher mentalement chaque mesure appliquée.

**Leçon** : pendant que vous corrigiez six failles applicatives, le sol lui-
même bougeait. La remédiation d'une faille moteur est une question de
**gestion de versions et de cycle de vie**, pas de développement : c'est
pourquoi les organisations qui « ne touchent à rien tant que ça marche »
accumulent un risque qu'aucun audit de code ne voit.

## Tableau de vérification final

| # | Faille | Exploit avant | Exploit après remédiation |
|---|--------|---------------|---------------------------|
| 0 | phpinfo oublié | Configuration complète exposée | Page absente, aucune info |
| 1 | Injection SQL | Connexion admin sans mot de passe | « Identifiants incorrects » |
| 2 | LFI | Code source et fichiers lus | Page d'accueil servie |
| 3 | Webshell | Commandes exécutées (whoami) | Upload refusé, 404 |
| 4 | Injection de commandes | whoami dans la page | « Adresse invalide » |
| 5 | Magic hash | Zone direction ouverte | « Cle incorrecte » |
| 6 | XSS stockée | alert() chez tous les visiteurs | Texte affiché, inoffensif |
| 7 | PHP2Shell (moteur) | Version vulnérable exposée | Moteur à jour, version masquée |

## Question de conclusion

Vous avez corrigé six failles en une séance. L'entreprise fictive DevCorp,
elle, ne l'a pas fait en sept ans. À votre avis, qu'est-ce qui bloque la
remédiation dans les vraies organisations : le coût, la compétence, la peur de
casser l'existant, l'absence d'inventaire, autre chose ? Appuyez votre réponse
sur un exemple réel (fuite de données, rançongiciel) de votre choix.
