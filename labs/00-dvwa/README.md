# DVWA (Damn Vulnerable Web Application) Multi-Container Setup

A Docker Compose environment running DVWA decoupled from its MariaDB database backend.

---

## Architecture

- **`dvwa-web`**: PHP 8.1 Apache container running DVWA (`cytopia/dvwa:php-8.1`).
- **`dvwa-db`**: MariaDB 10.5 database backend (`mariadb:10.5`) with healthchecks and persistent storage.
- **`initdb/`**: Automatic SQL initialization script (`01-init.sql`) to grant required database recreation privileges to the DVWA user.

```text
+-----------------------+              +-----------------------+
|       dvwa-web        |  Port 3306   |        dvwa-db        |
|  cytopia/dvwa:php-8.1 | -----------> |     mariadb:10.5      |
|  Host: 127.0.0.1:8080 |              |     Volume: db_data   |
+-----------------------+              +-----------------------+
           ^                                       ^
           |                                       |
    Browser Access                          initdb/01-init.sql
```

---

## Quick Start

### 1. Start the Stack

```bash
docker compose up -d
```

### 2. Access the Application

- **URL:** [http://localhost:8080](http://localhost:8080)
- **Default Credentials:**
  - **Username:** `admin`
  - **Password:** `password`

### 3. Database Setup / Reset

If the database needs to be re-initialized at any point:
1. Navigate to [http://localhost:8080/setup.php](http://localhost:8080/setup.php)
2. Click **Create / Reset Database**

### 4. Stop the Stack

```bash
# Stop containers (preserves database data)
docker compose down

# Stop and wipe database volume
docker compose down -v
```

---

## Configuration

Environment variables can be customized in `.env`:

| Variable | Default | Description |
| :--- | :--- | :--- |
| `DVWA_PORT` | `8080` | Host port mapped to container port 80 |
| `SECURITY_LEVEL` | `low` | Vulnerability difficulty (`low`, `medium`, `high`, `impossible`) |
| `MYSQL_ROOT_PASSWORD` | `rootpass` | Root password for MariaDB |
| `MYSQL_DATABASE` | `dvwa` | Database name |
| `MYSQL_USER` | `dvwa` | Database username |
| `MYSQL_PASSWORD` | `p@ssw0rd` | Database user password |

---

## Technology Stack

- **Containerization:** Docker, Docker Compose
- **Web Server & Runtime:** Apache, PHP 8.1
- **Database Backend:** MariaDB 10.5

---

## Author

- **Author:** Youssef Fellah ([ind4x_](https://github.com/ind4x))
- **Institution:** Mundiapolis University, Engineering Cycle
