<?php
/**
 * Database connection settings.
 * Default values match a typical local XAMPP/WAMP setup.
 * Change these to match your own MySQL server before deploying.
 */

// Every page needs the session (login state), so start it here, once.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$DB_HOST = "localhost";
$DB_NAME = "sound_db";
$DB_USER = "root";
$DB_PASS = "";

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "<br>Make sure you have imported schema.sql and run seed.php.");
}
