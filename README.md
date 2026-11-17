# CampusEvents – College Event Booking System (PHP)

A complete, working PHP project: users browse and book college events, admins manage events and view bookings and revenue.

## Features
- User registration, login and logout (`password_hash`, session regeneration)
- Browse and search upcoming events, with live seats-left count
- Book 1–10 seats per booking, with overbooking protection (DB transaction)
- "My Bookings" page with cancel option (seats return to the pool)
- Admin panel: dashboard stats, add, edit and delete events, view all bookings
- Security: PDO prepared statements, CSRF tokens on every form, output escaping
- Responsive UI (no frameworks needed)

## Requirements
- PHP 7.4+ (8.x recommended) with the `pdo_sqlite` extension (enabled by default in XAMPP and WAMP)
- No MySQL setup needed: the SQLite database is created automatically on first run.

## How to run
**Option A – PHP built-in server (quickest)**
```
cd eventbook
php -S localhost:8000
```
Open http://localhost:8000

**Option B – XAMPP / WAMP**
Copy the `eventbook` folder into `htdocs` (XAMPP) or `www` (WAMP), start Apache, and open http://localhost/eventbook

## Demo logins (created on first run)
| Role    | Email                | Password   |
|---------|----------------------|------------|
| Admin   | admin@college.edu    | admin123   |
| Student | student@college.edu  | student123 |

To reset everything, delete the `data/` folder; it is re-created with sample events.

## Project structure
```
eventbook/
├── config.php          DB connection, auto-creates tables, seeds demo data
├── functions.php       helpers (escape, CSRF, flash messages, auth guards)
├── index.php           event listing + search
├── event.php           event details + booking form
├── login.php / register.php / logout.php
├── my_bookings.php     user's bookings + cancel
├── includes/           header.php, footer.php
├── admin/              index.php, events.php, event_form.php, bookings.php
├── assets/style.css
└── data/app.db         SQLite database (auto-generated)
```

## Database tables
- `users` (id, name, email, password, role, created_at)
- `events` (id, title, description, venue, event_date, price, total_seats, created_at)
- `bookings` (id, user_id, event_id, seats, total_amount, status, created_at)
