# Deployment Guide (Public Server + Proxmox + Local Testing)

This project is a plain PHP + MySQL app. There is no build step.

## Before you deploy (important)

- **Use HTTPS** in production.
- **Do not publish SQL dumps** as web-accessible files.
  - This repo now includes `.htaccess` rules to deny access to `*.sql` and the `data/` folder when using Apache.
- **Rotate default credentials** (the sample SQL includes a default admin user).
- Configure DB credentials via environment variables:
  - `QUIZ_DB_HOST`, `QUIZ_DB_PORT`, `QUIZ_DB_USER`, `QUIZ_DB_PASS`, `QUIZ_DB_NAME`

Defaults (when env vars are not set) are defined in `class/db_connect.php`:

- `QUIZ_DB_HOST=127.0.0.1`
- `QUIZ_DB_PORT=3306`
- `QUIZ_DB_USER=root`
- `QUIZ_DB_NAME=quiz_database`

If you import `data/quizsystem.sql`, you have two valid options:

1) Keep the code defaults: create a DB named `quiz_database` and import into it.
2) Keep the SQL DB name: create/import `quizsystem` and set:
    - `QUIZ_DB_NAME=quizsystem`
    - `QUIZ_DB_PORT=3306` (or whatever port your MySQL/MariaDB is actually listening on)

Note: the SQL dump does not include `CREATE DATABASE` or `USE`. The `-- Database: quizsystem` line is a comment from phpMyAdmin, so you can import the tables into any DB you select (phpMyAdmin) or specify (CLI).

## Option A — Localhost (quick check)

### A1) Using XAMPP/WAMP/Laragon (recommended for Windows)

1. Install XAMPP (Apache + MySQL) or Laragon.
2. Put the project folder in the web root:
   - Example (XAMPP): `C:\xampp\htdocs\Quiz-main\Quiz-main`
3. Create/import the database:
   - Open phpMyAdmin
   - Create DB named `quiz_database` (matches the app defaults)
   - Import `data/quizsystem.sql` into `quiz_database`
4. Configure DB env vars
   - Easiest: edit `class/db_connect.php` defaults, OR
   - Better: set env vars in Apache config (varies by stack)
5. Visit:
   - `http://localhost/Quiz-main/Quiz-main/index.php`
   - `http://localhost/Quiz-main/Quiz-main/admin/login.php`

### A2) Using PHP’s built-in server (fastest, but not identical to Apache)

From PowerShell:

- `cd "e:\PROGRAMMING PROJECTS\Quiz-main\Quiz-main"`
- `php -S 127.0.0.1:8000 -t .`

Then visit `http://127.0.0.1:8000/index.php`.

Notes:

- `.htaccess` rules are not applied with the built-in server.
- You still need MySQL running and your DB name/port to match.

## Local performance check

This app is simple; performance issues are usually DB-related or PHP config.

### 1) Enable OPcache (production and local if possible)

In `php.ini`:

- `opcache.enable=1`
- `opcache.memory_consumption=128`
- `opcache.validate_timestamps=1` (dev)
- `opcache.validate_timestamps=0` (prod)

### 2) Quick load test

If you have ApacheBench (`ab`) installed:

- `ab -n 200 -c 10 http://127.0.0.1:8000/index.php`

If you don’t, you can still do a crude test:

- Open DevTools → Network → reload pages and watch TTFB
- Run multiple refreshes and watch DB CPU/RAM usage in Task Manager

## Option B — Public deployment on a Linux server (no Proxmox)

These steps apply to any Debian/Ubuntu VPS.

### 1) Install packages

Debian/Ubuntu:

- `sudo apt update`
- `sudo apt install -y apache2 libapache2-mod-php php-mysqli php-opcache mariadb-server`

### 2) Create DB + user

- `sudo mysql`

Example:

```sql
CREATE DATABASE quizsystem CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'quizapp'@'localhost' IDENTIFIED BY 'CHANGEME_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON quizsystem.* TO 'quizapp'@'localhost';
FLUSH PRIVILEGES;
```

Import schema:

- `mysql -u quizapp -p quizsystem < /path/to/Quiz-main/Quiz-main/data/quizsystem.sql`

Then set Apache env vars to match:

- `QUIZ_DB_NAME=quizsystem`
- `QUIZ_DB_PORT=3306` (typical default)

### 3) Deploy code

Copy the folder to something like:

- `/var/www/quizapp`

Apache site config (example):

- `sudo nano /etc/apache2/sites-available/quizapp.conf`

```apache
<VirtualHost *:80>
  ServerName example.com
  DocumentRoot /var/www/quizapp

  <Directory /var/www/quizapp>
    AllowOverride All
    Require all granted
  </Directory>

  ErrorLog ${APACHE_LOG_DIR}/quizapp_error.log
  CustomLog ${APACHE_LOG_DIR}/quizapp_access.log combined
</VirtualHost>
```

Enable site:

- `sudo a2enmod rewrite headers`
- `sudo a2ensite quizapp`
- `sudo systemctl reload apache2`

### 4) Configure environment variables

Apache example:

- `sudo nano /etc/apache2/envvars`

Add:

```bash
export QUIZ_DB_HOST=127.0.0.1
export QUIZ_DB_PORT=3306
export QUIZ_DB_USER=quizapp
export QUIZ_DB_PASS=CHANGEME_STRONG_PASSWORD
export QUIZ_DB_NAME=quizsystem
```

Restart Apache:

- `sudo systemctl restart apache2`

### 5) Add HTTPS

Recommended: Let’s Encrypt

- `sudo apt install -y certbot python3-certbot-apache`
- `sudo certbot --apache -d example.com`

## Option C — Proxmox deployment

You have two good choices: **LXC** (lighter) or **VM** (more isolated).

### C1) Proxmox LXC (recommended for simple PHP apps)

1. In Proxmox UI: Create CT
   - Template: Debian 12 or Ubuntu 22.04
   - CPU: 1–2 cores
   - RAM: 1–2 GB
   - Disk: 8–20 GB
   - Network: bridged (vmbr0)
2. Start CT and shell in.
3. Install Apache/PHP/MariaDB using the same steps as “Option B”.
4. Upload code to `/var/www/quizapp`.
5. Point a DNS name to the CT’s IP.
6. Add HTTPS with Certbot.

**Pro tip**: Take a Proxmox snapshot before big changes.

### C2) Proxmox VM (if you prefer full isolation)

1. Create VM
   - ISO: Debian/Ubuntu Server
   - CPU: 2 cores
   - RAM: 2 GB
   - Disk: 20 GB
2. Install OS, then follow “Option B” inside the VM.

### C3) Reverse proxy on Proxmox (common pattern)

If you want a single public IP and many services:

- Run a reverse proxy (Nginx Proxy Manager / Caddy / Nginx) in a separate VM/CT.
- Proxy `example.com` → your quiz CT/VM `http://10.0.0.X:80`.

In that setup:

- TLS terminates at the proxy.
- Your quiz server can remain HTTP-only internally.

## Post-deploy checklist

- Confirm `https://…/admin/login.php` loads.
- Confirm DB env vars are applied (login works, quiz fetch works).
- Confirm `quizsystem.sql` is NOT downloadable from the web.
- Change default admin credentials.
- Ensure server error display is OFF in production (`display_errors=0`) and logs are enabled.
