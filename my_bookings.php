<?php
require_once __DIR__ . '/config.php';
require_login();
$title = 'My Bookings';
$uid = (int)$_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    verify_csrf();
    $bid = (int)($_POST['booking_id'] ?? 0);
    $st = $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id = ? AND user_id = ? AND status='confirmed'
                         AND event_id IN (SELECT id FROM events WHERE event_date > ?)");
    $st->execute([$bid, $uid, date('Y-m-d H:i')]);
    flash($st->rowCount() ? 'Booking cancelled.' : 'Could not cancel this booking.', $st->rowCount() ? 'success' : 'error');
    redirect('my_bookings.php');
}

$st = $pdo->prepare('SELECT b.*, e.title, e.venue, e.event_date FROM bookings b JOIN events e ON e.id = b.event_id
                     WHERE b.user_id = ? ORDER BY b.created_at DESC, b.id DESC');
$st->execute([$uid]);
$rows = $st->fetchAll();

require 'includes/header.php';
?>
<h1>My Bookings</h1>
<?php if (!$rows): ?>
    <p class="muted">You have no bookings yet. <a href="index.php">Browse events</a></p>
<?php else: ?>
<div class="table-wrap">
<table>
    <thead><tr><th>ID</th><th>Event</th><th>When</th><th>Seats</th><th>Amount</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td>#<?= (int)$r['id'] ?></td>
            <td><strong><?= e($r['title']) ?></strong><br><span class="muted"><?= e($r['venue']) ?></span></td>
            <td><?= e(fmt_date($r['event_date'])) ?></td>
            <td><?= (int)$r['seats'] ?></td>
            <td><?= money((float)$r['total_amount']) ?></td>
            <td><span class="badge <?= $r['status'] === 'confirmed' ? 'badge-free' : 'badge-red' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
            <td>
                <?php if ($r['status'] === 'confirmed' && strtotime($r['event_date']) > time()): ?>
                <form method="post" onsubmit="return confirm('Cancel this booking?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="booking_id" value="<?= (int)$r['id'] ?>">
                    <button class="btn btn-sm btn-danger">Cancel</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
