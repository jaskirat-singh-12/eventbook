<?php
require_once __DIR__ . '/config.php';
$title = 'Upcoming Events';

$q = trim($_GET['q'] ?? '');
$sql = "SELECT e.*, e.total_seats - COALESCE((SELECT SUM(seats) FROM bookings b WHERE b.event_id = e.id AND b.status='confirmed'), 0) AS seats_free
        FROM events e WHERE e.event_date >= :now";
$params = [':now' => date('Y-m-d H:i')];
if ($q !== '') {
    $sql .= " AND (e.title LIKE :q OR e.venue LIKE :q OR e.description LIKE :q)";
    $params[':q'] = "%$q%";
}
$sql .= " ORDER BY e.event_date ASC";
$st = $pdo->prepare($sql);
$st->execute($params);
$events = $st->fetchAll();

require 'includes/header.php';
?>
<section class="hero">
    <h1>Discover &amp; book campus events</h1>
    <p>Fests, lectures, sports and cultural nights — reserve your seat in seconds.</p>
    <form method="get" class="search">
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search events, venues...">
        <button class="btn">Search</button>
    </form>
</section>

<?php if (!$events): ?>
    <p class="muted center">No upcoming events found.</p>
<?php endif; ?>

<div class="grid">
<?php foreach ($events as $ev): ?>
    <article class="card">
        <div class="card-top">
            <span class="badge <?= $ev['price'] > 0 ? '' : 'badge-free' ?>"><?= money((float)$ev['price']) ?></span>
            <?php if ($ev['seats_free'] <= 0): ?><span class="badge badge-red">Sold out</span>
            <?php elseif ($ev['seats_free'] <= 10): ?><span class="badge badge-amber">Only <?= (int)$ev['seats_free'] ?> left</span><?php endif; ?>
        </div>
        <h3><?= e($ev['title']) ?></h3>
        <p class="muted">📅 <?= e(fmt_date($ev['event_date'])) ?></p>
        <p class="muted">📍 <?= e($ev['venue']) ?></p>
        <p><?= e(mb_strimwidth($ev['description'] ?? '', 0, 100, '…')) ?></p>
        <a class="btn btn-block" href="event.php?id=<?= (int)$ev['id'] ?>">View &amp; Book</a>
    </article>
<?php endforeach; ?>
</div>
<?php require 'includes/footer.php'; ?>
