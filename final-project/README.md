# Projet Intégrateur DevSecOps : Audit, Remédiation & Hardening

Ce projet constitue le projet intégrateur semestriel du module **Sécurité des SI et Cybersécurité** (3ACI INFO GL / 5e année Génie Logiciel, Université Mundiapolis). Il s'articule autour d'une démarche d'ingénierie DevSecOps appliquée à un service web réel : **auditer** une architecture vulnérable existante, **corriger** les failles sans régression fonctionnelle, et **prouver** l'éradication des vulnérabilités au moyen d'exploits reproductibles (PoC).

---

## Structure du Projet

```text
final-project/
├── api-taches-etudiants/              # Service backend Spring Boot 3.2 (application cible)
│   ├── pom.xml                        # Descripteur de dépendances Maven
│   ├── src/main/java/                 # Code source Java (contrôleurs, modèles, sécurité)
│   └── src/main/resources/            # Configuration applicative (application.properties)
├── docs/
│   ├── architecture.md                # Dossier technique complet d'architecture et de remédiation
│   └── projet-integrateur-devsecops.docx # Cahier des charges officiel du projet
├── api-taches-etudiants.zip           # Archive de distribution initiale du service
└── README.md                          # Guide de référence et synthèse du projet
```

---

## Application Cible : API Tâches Étudiants

L'application **API Tâches** est un service backend RESTful de gestion collaborative de tâches académiques pour étudiants comprenant :
- **Authentification & Gestion d'identité :** Inscription, connexion, génération de jetons d'accès JWT.
- **Cycle de vie des tâches :** Création, lecture, recherche textuelle et suppression de tâches.
- **Espace collaboratif :** Ajout de commentaires et discussions sur les tâches.
- **Administration :** Consultation de l'annuaire des utilisateurs et des privilèges.

---

## Modélisation des Menaces (STRIDE) & Matrice OWASP Top 10

| Faille OWASP | Composant / Fichier | Emplacement | Vulnérabilité & Risque | Remédiation DevSecOps |
| :--- | :--- | :--- | :--- | :--- |
| **A03:2021 - Injection** | `TaskController.java` | `/search` | Concaténation SQL directe (`SELECT * FROM tasks WHERE title LIKE '%" + title + "%'`) | Remplacement par requêtes dérivées Spring Data JPA paramétrées |
| **A01:2021 - Broken Access Control (BOLA/IDOR)** | `TaskController.java` | `/{id}`, `/delete/{id}` | Absence de vérification du propriétaire de la tâche face au porteur du jeton | Contrôle systématique `task.getOwner().equals(authenticatedUser)` |
| **A01:2021 - Broken Access Control** | `AdminController.java` | `/users` | Vérification de rôle basée sur l'en-tête client arbitraire `X-Role: ADMIN` | Extraction et validation du rôle directement depuis les claims du JWT |
| **A02:2021 - Cryptographic Failures** | `PasswordUtil.java` | Algorithme MD5 | Hachage obsolète `MD5` sans sel cryptographique | Migration vers `BCryptPasswordEncoder` (work factor 12) avec sel unique |
| **A07:2021 - Identification & Auth Failures** | `JwtUtil.java` | Mécanisme de signature | Clé secrète statique faible, absence de date d'expiration (`exp`) | Signature HMAC-SHA256 robuste, expiration à 24h, extraction sécurisée |
| **A03:2021 - Injection (Stored XSS)** | `CommentController.java` | `/comments` | Stockage brut de contenu HTML/JavaScript sans filtrage | Validation des entrées, nettoyage contextuel et encodage strict des caractères |
| **A01:2021 - Sensitive Data Exposure** | `AuthController.java`, `UserController.java` | Réponses JSON | Sérialisation directe de l'entité `User` incluant le hash du mot de passe | Introduction de DTOs (`UserResponseDTO`) masquant les attributs sensibles |
| **A05:2021 - Security Misconfiguration** | `application.properties` | Paramètres généraux | `server.error.include-stacktrace=always`, CORS permissif (`*`) | Désactivation des stack traces en production, restriction stricte de la politique CORS |

---

## Instructions de Lancement

### Prérequis

- **Java Development Kit (JDK) :** Version 17 ou supérieure (`java -version`)
- **Maven :** Version 3.8+ (ou utilisation du wrapper `./mvnw` inclus)
- **Outil d'audit HTTP :** Burp Suite Community Edition ou cURL

### Démarrage de l'API

Depuis le dossier `final-project/api-taches-etudiants` :

```bash
cd api-taches-etudiants

# Compilation et vérification des dépendances
./mvnw clean compile

# Lancement du serveur backend Spring Boot
./mvnw spring-boot:run
```

Le service démarre par défaut sur le port **8080** :
- **Point de terminaison :** `http://localhost:8080`
- **Base de données intégrée :** SQLite / H2

### Exécution des Tests

```bash
./mvnw test
```

---

## Documentation Détaillée

Pour consulter l'analyse architecturale approfondie, les diagrammes de séquence et le rapport de conformité RGPD :
- **[Dossier Technique & Architecture](./docs/architecture.md)**
- **[Cahier des Charges Officiel](./docs/projet-integrateur-devsecops.docx)**

---

## Pile Technologique

- **Langage :** Java 17 (LTS)
- **Framework Backend :** Spring Boot 3.2.0 (Spring MVC, Spring Data JPA)
- **Sécurité & Chiffrement :** Spring Security 6, JJWT (JSON Web Token), BCrypt / Argon2
- **Persistence :** Hibernate ORM, SQLite / H2 Database
- **Build & CI :** Apache Maven 3.8+

---

## Author

- **Author:** Youssef Fellah ([ind4x_](https://github.com/ind4x))
- **Institution:** Mundiapolis University, Engineering Cycle
