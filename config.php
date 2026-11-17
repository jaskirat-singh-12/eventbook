<?php
// Core config: session, SQLite database (auto-created), helpers.
session_start();
date_default_timezone_set('Asia/Kolkata');

define('APP_NAME', 'CampusEvents');
define('DB_DIR', __DIR__ . '/data');
define('DB_FILE', DB_DIR . '/app.db');

if (!is_dir(DB_DIR)) {
    mkdir(DB_DIR, 0775, true);
}

$firstRun = !file_exists(DB_FILE);

try {
    $pdo = new PDO('sqlite:' . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
} catch (PDOException $e) {
    die('Database error: ' . htmlspecialchars($e->getMessage()) . '. Make sure the pdo_sqlite extension is enabled in php.ini.');
}

$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'user',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    venue TEXT NOT NULL,
    event_date TEXT NOT NULL,
    price REAL NOT NULL DEFAULT 0,
    total_seats INTEGER NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    event_id INTEGER NOT NULL,
    seats INTEGER NOT NULL,
    total_amount REAL NOT NULL,
    status TEXT NOT NULL DEFAULT 'confirmed',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);
");

// Seed demo data on first run
if ($firstRun) {
    $st = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
    $st->execute(['Admin', 'admin@college.edu', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
    $st->execute(['Demo Student', 'student@college.edu', password_hash('student123', PASSWORD_DEFAULT), 'user']);

    $ev = $pdo->prepare('INSERT INTO events (title, description, venue, event_date, price, total_seats) VALUES (?, ?, ?, ?, ?, ?)');
    $ev->execute(['Annual Tech Fest', 'Hackathon, robotics and coding contests with prizes worth Rs. 1 lakh.', 'Main Auditorium', date('Y-m-d H:i', strtotime('+10 days 10:00')), 200, 150]);
    $ev->execute(['Cultural Night', 'Music, dance and drama performances by students.', 'Open Air Theatre', date('Y-m-d H:i', strtotime('+20 days 18:00')), 100, 300]);
    $ev->execute(['Guest Lecture: AI in Industry', 'Talk by an industry expert followed by Q&A.', 'Seminar Hall B', date('Y-m-d H:i', strtotime('+5 days 14:00')), 0, 80]);
    $ev->execute(['Inter-College Cricket Cup', 'Knockout cricket tournament, finals on the last day.', 'College Ground', date('Y-m-d H:i', strtotime('+30 days 09:00')), 50, 500]);
}

require_once __DIR__ . '/functions.php';
