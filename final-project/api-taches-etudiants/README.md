# API Taches

Petite API REST de gestion de taches (Spring Boot, Java). C'est la base de
notre projet integrateur du semestre : elle servira de support aux travaux
d'audit et de correction tout au long du cours.

## Prerequis

### 1. Installer un JDK (17 ou 21)

Aucun JDK n'est fourni avec le projet, il faut en installer un une seule fois :

1. Aller sur https://adoptium.net/
2. Choisir **Temurin 21 (LTS)** et telecharger l'installateur correspondant a
   votre systeme :
   - Windows : installateur `.msi` (laisser les options par defaut)
   - Mac : installateur `.pkg`, ou `brew install --cask temurin` si vous
     utilisez Homebrew
   - Linux : `sudo apt install temurin-21-jdk` (depot Adoptium) ou
     `sudo dnf install java-21-openjdk-devel`
3. Fermer puis rouvrir le terminal, puis verifier :

```
java -version
```

Vous devez voir une version 17 ou 21 (par exemple `openjdk version "21.0.4"`).

### 2. Maven

Rien a installer : le **Maven Wrapper** est inclus dans le projet (`mvnw` /
`mvnw.cmd`). Il telecharge Maven automatiquement au premier lancement.

### 3. Base de donnees

Rien a installer : la base **H2** est embarquee dans l'application et demarre
avec elle. Une console web est disponible sur
http://localhost:8080/h2-console (JDBC URL : `jdbc:h2:mem:tachesdb`,
utilisateur `sa`, mot de passe vide).

## Demarrage

Depuis le dossier du projet :

```
# Linux / Mac
./mvnw spring-boot:run

# Windows
mvnw.cmd spring-boot:run
```

Le premier lancement telecharge les dependances Maven (quelques minutes selon
la connexion). Ensuite l'API ecoute sur **http://localhost:8080**.

## Endpoints

| Methode | URL | Description | Exemple |
|---|---|---|---|
| POST | /api/auth/register | Creer un compte | `curl -X POST http://localhost:8080/api/auth/register -H "Content-Type: application/json" -d "{\"username\":\"carol\",\"password\":\"carol123\",\"email\":\"carol@test.ma\"}"` |
| POST | /api/auth/login | Se connecter, renvoie un JWT | `curl -X POST http://localhost:8080/api/auth/login -H "Content-Type: application/json" -d "{\"username\":\"alice\",\"password\":\"alice123\"}"` |
| GET | /api/tasks?userId=1 | Taches d'un utilisateur | `curl "http://localhost:8080/api/tasks?userId=1"` |
| POST | /api/tasks | Creer une tache | `curl -X POST http://localhost:8080/api/tasks -H "Content-Type: application/json" -d "{\"title\":\"Rapport\",\"description\":\"S2\",\"userId\":\"1\"}"` |
| GET | /api/tasks/{id} | Detail d'une tache | `curl http://localhost:8080/api/tasks/1` |
| DELETE | /api/tasks/{id} | Supprimer une tache | `curl -X DELETE http://localhost:8080/api/tasks/1` |
| GET | /api/tasks/search?title=rap | Recherche par titre | `curl "http://localhost:8080/api/tasks/search?title=rap"` |
| POST | /api/tasks/{id}/comments | Ajouter un commentaire | `curl -X POST http://localhost:8080/api/tasks/1/comments -H "Content-Type: application/json" -d "{\"content\":\"ok\",\"userId\":\"2\"}"` |
| GET | /api/tasks/{id}/comments | Commentaires d'une tache | `curl http://localhost:8080/api/tasks/1/comments` |
| GET | /api/users/{id} | Detail d'un utilisateur | `curl http://localhost:8080/api/users/1` |
| GET | /api/admin/users | Tous les utilisateurs (admin) | `curl -H "X-Role: ADMIN" http://localhost:8080/api/admin/users` |

Vous pouvez rejouer ces requetes dans Burp Repeater : coller la requete HTTP,
l'envoyer, modifier les parametres, observer la reponse.

## Comptes de test

| Utilisateur | Mot de passe | Role |
|---|---|---|
| alice | alice123 | utilisateur |
| bob | bob123 | utilisateur |
| admin | admin123 | admin |

## Cadre du projet

Cette API est un **terrain d'entrainement** ecrit pour le cours. Elle n'a pas
vocation a etre deployee : ne la mettez jamais en ligne en l'etat, et n'y
stockez aucune donnee reelle.
