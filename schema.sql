-- ============================================================
-- SOUND — Music & Video Entertainment Website
-- Database schema (MySQL)
-- Run this first:  mysql -u root -p < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS sound_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sound_db;

-- ---------- Users ----------
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  phone VARCHAR(30) NOT NULL,
  address VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- Categories (Genre / Language / Year) ----------
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('genre','language','year') NOT NULL,
  value VARCHAR(60) NOT NULL,
  UNIQUE KEY uniq_cat (type, value)
) ENGINE=InnoDB;

-- ---------- Music ----------
CREATE TABLE IF NOT EXISTS music (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  artist VARCHAR(120) NOT NULL,
  album VARCHAR(120) NOT NULL,
  year VARCHAR(10) NOT NULL,
  genre VARCHAR(60) NOT NULL,
  language VARCHAR(60) NOT NULL,
  description TEXT,
  media_file VARCHAR(255) NULL,
  image VARCHAR(255) NULL,
  is_new TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- Video ----------
CREATE TABLE IF NOT EXISTS video (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  artist VARCHAR(120) NOT NULL,
  album VARCHAR(120) NOT NULL,
  year VARCHAR(10) NOT NULL,
  genre VARCHAR(60) NOT NULL,
  language VARCHAR(60) NOT NULL,
  description TEXT,
  media_file VARCHAR(255) NULL,
  image VARCHAR(255) NULL,
  is_new TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- Reviews & Ratings ----------
CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  item_type ENUM('music','video') NOT NULL,
  item_id INT NOT NULL,
  user_id INT NOT NULL,
  rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  review_text TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_review (item_type, item_id, user_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Starter categories (Admin can add more from the Admin Panel) ----------
INSERT IGNORE INTO categories (type, value) VALUES
('genre','Pop'),('genre','Rock'),('genre','Qawwali'),('genre','Classical'),('genre','Hip-Hop'),('genre','Folk'),
('language','English'),('language','Urdu'),('language','Punjabi'),('language','Sindhi'),
('year','2023'),('year','2024'),('year','2025'),('year','2026');

-- Note: user accounts (admin + demo user) and sample Music/Video/Reviews
-- are inserted by seed.php, so passwords are stored properly hashed
-- with PHP's password_hash() instead of being hardcoded here.
