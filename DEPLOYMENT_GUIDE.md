# Gloriolux E-Commerce: Go-Live & Hosting Checklist

Congratulations on completing the development of Gloriolux! When you are ready to move from your local XAMPP environment to a live web hosting server, you must follow this sequence to ensure the website functions securely and correctly.

## 1. Hosting Requirements
Ensure your chosen hosting provider (e.g., Hostinger, Bluehost, Namecheap, or a VPS like DigitalOcean) meets these specifications:
*   **PHP:** Version 8.0 or higher.
*   **Database:** MySQL or MariaDB.
*   **Security:** A valid SSL Certificate (HTTPS) is **mandatory** for Stripe payments and secure user logins.

## 2. Database Migration
1. Open **phpMyAdmin** in your local XAMPP environment.
2. Select your `gloriolux_db` database and click **Export** to download the `.sql` file.
3. Log into your live hosting control panel (like cPanel) and create a new MySQL Database and Database User. Assign all privileges to the user.
4. Open the live server's phpMyAdmin, select your new database, and click **Import** to upload your `.sql` file.

## 3. Update Database Credentials
You must connect the live codebase to your new live database.
1. Open the file: `includes/db.php`
2. Update the connection variables with the credentials provided by your web host:
```php
$host = 'localhost'; // Usually 'localhost', but your host might specify an IP
$dbname = 'your_live_database_name';
$user = 'your_live_database_user';
$pass = 'your_live_secure_password';
```

## 4. The Path Adjustment (Crucial Step)
Currently, your local code uses `/Gloriolux/` as the root path (e.g., `<a href="/Gloriolux/index.php">`). When you host this on a live domain (like `www.gloriolux.com`), the root path becomes `/`. 

**How to fix this:**
Before uploading your files, open your project folder in your code editor (like VS Code) and perform a **Global Search and Replace**:
*   **Find:** `/Gloriolux/`
*   **Replace with:** `/`

*If you skip this step, all of your images, CSS files, and page links will break on the live server!*

## 5. Stripe Payment Configuration (Going Live)
Currently, your store is using `mock` testing keys. To accept real credit cards:
1. Log into your [Stripe Dashboard](https://dashboard.stripe.com/).
2. Toggle off "Test Mode" in the top right corner.
3. Go to **Developers > API Keys**.
4. Log into your live Gloriolux Admin Dashboard.
5. Go to **Settings > Stripe Integration** and paste in your **Live Publishable Key** and **Live Secret Key**.

## 6. Email Configuration
The website currently uses the standard PHP `@mail()` function for order confirmations and contact forms. 
*   On most shared hosting platforms (like cPanel), this will work automatically once your domain is live.
*   Make sure you have created the actual email inbox for `admin@gloriolux.com` (or whatever you set in your Admin Settings) through your hosting provider so you can receive the messages.

## 7. Security Best Practices
*   **Admin Password:** Ensure your live admin account has a highly secure, complex password.
*   **File Permissions:** Ensure your `/uploads/` directory has `755` permissions so images can be saved securely, and files like `includes/db.php` have `644` permissions so they cannot be accessed maliciously.
