# Real Estate Marketplace — Local Run Instructions

This project is a small PHP/MySQL real estate marketplace web app. Below are the steps to run it locally on Windows (two options): using XAMPP/WAMP or the PHP built-in server with MySQL running separately.

---

## Requirements

- PHP 7.4+ (mysqli extension enabled)
- MySQL or MariaDB server
- A web browser
- Optionally: XAMPP, WAMP, or similar for an easy all-in-one stack

---

## 1) Setup the database

The project contains the database schema at `realestate_db.sql`.

Using MySQL command-line client (make sure `mysql.exe` is in PATH) — run this from PowerShell or CMD:

```powershell
# Create the database and import schema
mysql -u root -p < realestate_db.sql
# It will prompt for password; press Enter if you have no password for root
```

If you use XAMPP / phpMyAdmin:
- Start **MySQL** from the XAMPP control panel.
- Open http://localhost/phpmyadmin
- Create a database named `real_estate_db` or run `realestate_db.sql` from the Import tab.

---

## 2) Configure DB credentials

Open `includes/config.php` and update these constants if your MySQL credentials differ:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'real_estate_db');
```

If you're using a non-default username/password, change them accordingly.

---

## 3) Static files and uploads

The project expects assets in `assets/` and uploads in `uploads/`. Ensure `uploads/` is writable by the web server if using file uploads.

---

## 4) Run the app (Option A) — XAMPP or WAMP (recommended for Windows)

1. Put this project folder under your web root (for XAMPP that's `C:\xampp\htdocs\mini-project`) or configure a Virtual Host pointing to the project folder.
2. Start Apache and MySQL from the XAMPP control panel.
3. In your browser go to: http://localhost/mini-project/index.php (or the configured host)

---

## 4b) Run the app (Option B) — PHP built-in web server

If you prefer not to use a full stack like XAMPP, you can run the built-in server, but you must have MySQL running (e.g., as a Windows service or via XAMPP's MySQL):

Open PowerShell, navigate to the project folder, and run:

```powershell
cd C:\Users\<YOUR-USER>\OneDrive\Desktop\mini-project
php -S 127.0.0.1:8000
```

Then open your browser to http://127.0.0.1:8000/index.php

Note: the built-in server is for development only and does not replace Apache/Nginx in production.

---

## 5) Common troubleshooting

- "Can’t connect to database" — check DB credentials in `includes/config.php` and ensure MySQL is running.
- "Page not found" using built-in server — confirm you ran `php -S` from the project root.
- If images or uploads don’t display — verify `uploads/` has the expected files and PHP has permission to read them.

---

## 6) Next steps / improvements

- Add a `.env` file or environment-based config instead of hardcoded values in `includes/config.php`.
- Use Composer for dependency management if adding packages.
- Add sample seed data for users and properties if you want to test the UI quickly.

---

If you'd like, I can also add a sample seed SQL file (with a test buyer, seller, and a few properties) and wire up a `.env`-based config — tell me which option you'd prefer and I'll add it. 🎯
