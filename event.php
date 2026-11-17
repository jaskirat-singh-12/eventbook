<?php
require_once __DIR__ . '/config.php';

$id = (int)($_GET['id'] ?? $_POST['event_id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM events WHERE id = ?');
$st->execute([$id]);
$event = $st->fetch();
if (!$event) {
    http_response_code(404);
    $title = 'Not found';
    require 'includes/header.php';
    echo '<p class="center">Event not found. <a href="index.php">Back to events</a></p>';
    require 'includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    require_login();
    $seats = (int)($_POST['seats'] ?? 0);

    // Transaction prevents overbooking when two people book at the same time.
    $pdo->exec('BEGIN IMMEDIATE');
    try {
        $free = seats_left($pdo, $id);
        if (strtotime($event['event_date']) < time()) {
            throw new RuntimeException('This event has already taken place.');
        }
        if ($seats < 1 || $seats > 10) {
            throw new RuntimeException('You can book between 1 and 10 seats.');
        }
        if ($seats > $free) {
            throw new RuntimeException("Only $free seat(s) left.");
        }
        $ins = $pdo->prepare('INSERT INTO bookings (user_id, event_id, seats, total_amount) VALUES (?, ?, ?, ?)');
        $ins->execute([$_SESSION['user']['id'], $id, $seats, $seats * $event['price']]);
        $pdo->exec('COMMIT');
        flash('Booking confirmed! Booking ID #' . $pdo->lastInsertId(), 'success');
        redirect('my_bookings.php');
    } catch (RuntimeException $ex) {
        $pdo->exec('ROLLBACK');
        flash($ex->getMessage(), 'error');
        redirect('event.php?id=' . $id);
    }
}

$free = seats_left($pdo, $id);
$title = $event['title'];
require 'includes/header.php';
?>
<a class="muted" href="index.php">← All events</a>
<div class="detail">
    <div>
        <h1><?= e($event['title']) ?></h1>
        <p class="muted">📅 <?= e(fmt_date($event['event_date'])) ?></p>
        <p class="muted">📍 <?= e($event['venue']) ?></p>
        <p class="lead"><?= nl2br(e($event['description'])) ?></p>
    </div>
    <aside class="panel">
        <h3>Book your seats</h3>
        <p class="price"><?= money((float)$event['price']) ?> <small>per seat</small></p>
        <p class="muted"><?= $free ?> of <?= (int)$event['total_seats'] ?> seats available</p>
        <?php if (strtotime($event['event_date']) < time()): ?>
            <p class="alert alert-error">This event has ended.</p>
        <?php elseif ($free <= 0): ?>
            <p class="alert alert-error">Sold out.</p>
        <?php elseif (!is_logged_in()): ?>
            <a class="btn btn-block" href="login.php">Log in to book</a>
        <?php else: ?>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="event_id" value="<?= $id ?>">
                <label>Number of seats
                    <input type="number" name="seats" value="1" min="1" max="<?= min(10, $free) ?>" required>
                </label>
                <button class="btn btn-block">Confirm booking</button>
            </form>
        <?php endif; ?>
    </aside>
</div>
<?php require 'includes/footer.php'; ?>
