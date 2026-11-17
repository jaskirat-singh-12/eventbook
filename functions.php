<?php
// Helper functions used across the app.

function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function is_admin(): bool
{
    return is_logged_in() && $_SESSION['user']['role'] === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('Please log in to continue.', 'error');
        redirect(base_url('login.php'));
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        flash('Admin access required.', 'error');
        redirect(base_url('login.php'));
    }
}

// Works whether the app is in the web root or a sub-folder, and from /admin.
function base_url(string $path = ''): string
{
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    if (substr($dir, -6) === '/admin') {
        $dir = substr($dir, 0, -6);
    }
    return rtrim($dir, '/') . '/' . ltrim($path, '/');
}

function flash(string $msg, string $type = 'success'): void
{
    $_SESSION['flash'][] = ['msg' => $msg, 'type' => $type];
}

function get_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ok = isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']);
        if (!$ok) {
            http_response_code(400);
            die('Invalid request (CSRF check failed). Go back and try again.');
        }
    }
}

function money(float $amt): string
{
    return $amt > 0 ? 'Rs. ' . number_format($amt, 2) : 'Free';
}

function fmt_date(string $d): string
{
    return date('D, d M Y · h:i A', strtotime($d));
}

function seats_left(PDO $pdo, int $eventId): int
{
    $st = $pdo->prepare("SELECT e.total_seats - COALESCE(SUM(CASE WHEN b.status='confirmed' THEN b.seats END), 0) AS seats_free
                         FROM events e LEFT JOIN bookings b ON b.event_id = e.id
                         WHERE e.id = ? GROUP BY e.id");
    $st->execute([$eventId]);
    $r = $st->fetch();
    return $r ? (int)$r['seats_free'] : 0;
}
