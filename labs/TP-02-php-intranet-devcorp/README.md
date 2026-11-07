# Lab PHP : Vulnérabilités Web Antiques & Non-Remédiation

Ce laboratoire pratique du module **Sécurité des SI et Cybersécurité** porte sur l'application « Intranet DevCorp », un portail d'entreprise écrit en PHP resté inchangé depuis 2019. L'application intègre six vulnérabilités classiques du monde web PHP ainsi qu'un environnement d'exploitation de failles récentes (PHP2Shell CVE-2026-109618 et CVE-2026-109619).

> **Avertissement :** Cette application est volontairement vulnérable. Ne la déployez jamais sur un serveur exposé à Internet. Son usage est strictement réservé à un cadre pédagogique en environnement local.

---

## Structure du Laboratoire

```text
TP-02-php-intranet-devcorp/
├── app/                        # Application cible vulnérable (port 8000)
├── app_corrige/                # Application assainie et corrigée (port 8001)
├── php2shell/                  # Environnement d'exploitation PHP2Shell
├── scripts/                    # Scripts d'attaque et démonstrations PoC
├── guide-exploitation.md       # Guide pas-à-pas de découverte et d'exploitation
├── guide-remediation.md        # Guide de remédiation et de correction du code
├── Dockerfile                  # Configuration multi-serveur conteneurisée
├── start.sh                    # Script de démarrage des serveurs PHP
└── README.md                   # Documentation technique et guide d'exécution
```

---

## Cartographie des Vulnérabilités

| Vulnérabilité | Vecteur / Fichier affecté | Méthode d'Exploitation | Impact & Risque |
| :--- | :--- | :--- | :--- |
| **SQL Injection** | `login.php`, `livre.php` | Injection de payload dans les requêtes non préparées (`' OR '1'='1`) | Contournement d'authentification, fuite de base de données |
| **IDOR / BOLA** | `secret.php?id=X` | Falsification de l'identifiant utilisateur dans la requête | Accès non autorisé aux fiches confidentielles de tiers |
| **Path Traversal / LFI** | `pages/view.php?page=...` | Traversal de répertoires (`../../../../etc/passwd`) | Lecture arbitraire de fichiers système et de configuration |
| **Unrestricted File Upload** | `upload.php` | Téléversement d'un script `.php` sans vérification de type MIME/extension | Exécution de code à distance (RCE) via Web Shell |
| **Authentication Bypass** | Cookie de session / paramètre d'URL | Manipulation de cookies non signés et variables de session faibles | Usurpation de session administrateur |
| **Command Injection (PHP2Shell)** | `php2shell/` (CVE-2026-109618 / CVE-2026-109619) | Exploitation des spécificités du parseur CGI/PHP | Exécution arbitraire de commandes système sur l'hôte |

---

## Guide d'Exécution

### Option 1 : Déploiement Conteneurisé via Docker (Recommandé)

Construire et exécuter l'image Docker multi-services :

```bash
# Construction de l'image
docker build -t lab-php .

# Lancement du conteneur
docker run --rm -p 8000:8000 -p 8001:8001 --name lab-php lab-php
```

- **Application vulnérable :** [http://localhost:8000](http://localhost:8000)
- **Application corrigée :** [http://localhost:8001](http://localhost:8001)

### Option 2 : Lancement Local sans Docker (Serveur Intégré PHP)

#### Prérequis
- PHP 8.0 ou supérieur avec extension `pdo_sqlite` activée (`php -v`)
- Client HTTP `curl`

#### Démarrage de l'Application Vulnérable
```bash
php -S 127.0.0.1:8000 -t app
```

#### Démarrage de l'Application Corrigée
Dans un second terminal :
```bash
php -S 127.0.0.1:8001 -t app_corrige
```

Pour réinitialiser les bases de données et les fichiers téléversés :
```bash
rm -rf app/data/* app/uploads/* app_corrige/data/* app_corrige/uploads/*
```

---

## Documentation Complète

- **[Guide d'Exploitation](./guide-exploitation.md)** : Procédure pas-à-pas pour identifier et exploiter chacune des failles.
- **[Guide de Remédiation](./guide-remediation.md)** : Bonnes pratiques de sécurisation, requêtes préparées PDO, validation des types MIME et listes blanches d'inclusion.

---

## Technologies Utilisées

- **Langage & Runtime :** PHP 8+, SQLite3 PDO
- **Conteneurisation :** Docker (Alpine Linux)
- **Outils d'Audit :** cURL, Burp Suite, Web Shell scripts

---

## Author

- **Author:** Youssef Fellah ([ind4x_](https://github.com/ind4x))
- **Institution:** Mundiapolis University, Engineering Cycle
