SOUND — PHP + MySQL Setup
==========================

1. Install a local server stack (XAMPP / WAMP / LAMP) with PHP 8+ and MySQL.

2. Copy this whole "sound-php" folder into your server's web root
   (e.g. C:\xampp\htdocs\sound-php  or  /var/www/html/sound-php).

3. Create the database and tables:
   - Open phpMyAdmin (or the mysql CLI) and import schema.sql
     mysql -u root -p < schema.sql

4. If your MySQL username/password differ from the defaults (root / no
   password), edit config.php and update $DB_USER / $DB_PASS.

5. In your browser, visit:
   http://localhost/sound-php/seed.php
   This inserts the demo admin account, a demo user, and sample
   Music/Video/Reviews. It only needs to be run once.

6. Visit http://localhost/sound-php/index.php to use the site.

Demo logins
-----------
Admin:  admin@sound.com / admin123
User:   ayesha@example.com / user123

Project structure
-----------------
sound-php/
  config.php          DB connection + session_start()
  index.php, music.php, video.php, detail.php, login.php, register.php, logout.php, admin.php
  seed.php            One-time demo data (run once in browser)
  schema.sql          Database schema (run first)
  css/style.css       All site styling
  includes/
    header.php        Shared <head> + nav (login/logout aware)
    footer.php        Shared footer + closing tags
    functions.php      e(), currentUser(), requireAdmin(), categoryValues(),
                       avgRating(), reviewCount(), starString(), renderCard()

Pages
-----
index.php     Home — latest 5 Music + 5 Video
music.php     Browse/search Music (by title, artist, album, genre, language, year)
video.php     Browse/search Video (same filters)
detail.php    Single item page — add/update your review & star rating
login.php     Log in
register.php  Create account (name, email, phone, address required)
logout.php    Ends the session
admin.php     Admin Panel — add/delete Music & Video, manage Categories, view Users

Notes
-----
- Passwords are stored using PHP's password_hash()/password_verify() — never in plain text.
- All database queries use PDO prepared statements to prevent SQL injection.
- Only logged-in users can submit reviews; only admins can reach admin.php (enforced server-side).
