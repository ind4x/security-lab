# Information Systems Security & Cybersecurity Laboratory Portfolio

This repository hosts practical implementations, vulnerability assessments, penetration testing laboratories, and secure DevSecOps remediation architectures developed for the Information Systems Security and Cybersecurity curriculum at Mundiapolis University.

---

## Laboratory & Project Overview

| Laboratory / Project | Domain & Topic | Core Methodology | Primary Deliverables | Reference |
| :--- | :--- | :--- | :--- | :--- |
| **[00-dvwa](./labs/00-dvwa)** | Decoupled Web Security Testbed | Multi-container Docker Compose architecture, decoupled MariaDB 10.5 database backend, automated privilege initialization scripts | Isolated reproducible pentesting target with configurable security levels | [Lab Root](./labs/00-dvwa/README.md) |
| **[TP-01-burpsuite](./labs/TP-01-burpsuite)** | HTTP Proxy Interception & Request Tampering | Burp Suite Community proxying, TLS MITM interception via custom CA, parameter fuzzing via Repeater, client DOM & response manipulation | 11-page security report, parameter fuzzing matrices, response tampering proofs | [Lab Root](./labs/TP-01-burpsuite/README.md)<br>[Report](./labs/TP-01-burpsuite/docs/rapport_01_secutity.pdf) |
| **[TP-02-php-intranet-devcorp](./labs/TP-02-php-intranet-devcorp)** | Legacy Web Vulnerability Auditing & Hardening | Exploitation and remediation of 6 core PHP vulnerabilities (SQLi, IDOR, Path Traversal, Unrestricted File Upload, Auth Bypass, Command Injection) & PHP2Shell (CVE-2026-109618/109619) | Dual vulnerable/remediated Docker environments, exploit PoC scripts, remediation guide | [Lab Root](./labs/TP-02-php-intranet-devcorp/README.md)<br>[Exploit Guide](./labs/TP-02-php-intranet-devcorp/guide-exploitation.md)<br>[Remediation Guide](./labs/TP-02-php-intranet-devcorp/guide-remediation.md) |
| **[final-project](./final-project)** | DevSecOps Integrator: Spring Boot API Hardening | Threat modeling (STRIDE), full OWASP Top 10 vulnerability audit, migration from unsalted MD5 to BCrypt/Argon2, BOLA/IDOR remediation, JWT hardening, GDPR compliance audit | Audited Spring Boot REST API (`api-taches-etudiants`), architectural dossier, exploitation PoCs, remediation suite | [Project Root](./final-project/README.md)<br>[Architecture Dossier](./final-project/docs/architecture.md) |

---

## General Running Instructions

### Prerequisites

- **Docker & Docker Compose:** Containerized execution environments
- **Java Development Kit (JDK):** Version 17 or higher (`java -version`)
- **PHP:** Version 8.0 or higher with `pdo_sqlite` extension
- **Build Tool:** Apache Maven 3.8+ (or bundled `./mvnw`)
- **Security & Inspection Tools:** PortSwigger Burp Suite Community Edition, cURL, OpenSSL

### Setup and Environment

1. **Clone the repository:**
   ```bash
   git clone git@github.com:ind4x/security-lab.git
   cd security-lab
   ```

2. **Launch the DVWA Environment (Lab 00):**
   ```bash
   cd labs/00-dvwa
   docker compose up -d
   ```
   Navigate to [http://localhost:8080](http://localhost:8080) (`admin` / `password`).

3. **Launch the PHP Intranet Security Lab (TP 02):**
   ```bash
   cd labs/TP-02-php-intranet-devcorp
   docker build -t lab-php .
   docker run --rm -p 8000:8000 -p 8001:8001 --name lab-php lab-php
   ```
   - Vulnerable Target: [http://localhost:8000](http://localhost:8000)
   - Remediated Target: [http://localhost:8001](http://localhost:8001)

4. **Launch the DevSecOps Spring Boot REST API (Final Project):**
   ```bash
   cd final-project/api-taches-etudiants
   ./mvnw spring-boot:run
   ```
   Service starts at [http://localhost:8080](http://localhost:8080).

---

## Technology Stack

- **Application Frameworks:** Spring Boot 3.2 (Java 17), Apache HTTP, PHP 8.1
- **Security & Cryptography:** Spring Security 6, JJWT, BCrypt, Argon2, SHA-256, OpenSSL
- **Database Systems:** MariaDB 10.5, SQLite3, H2 Database, Spring Data JPA / Hibernate
- **Auditing & Pentesting:** PortSwigger Burp Suite Community Edition, cURL, Docker Compose
- **Quality Assurance & DevOps:** STRIDE Threat Modeling, OWASP Top 10, Apache Maven, JUnit 5

---

## Author

- **Author:** Youssef Fellah ([ind4x_](https://github.com/ind4x))
- **Institution:** Mundiapolis University, Engineering Cycle
