<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('IN_INDEX')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    include( 'config.php' );
    include( 'helpers.php' );
}
$id = $_GET['id'] ?? null;

if (!$id) {
    print TwigHelper::getInstance() -> render( 'error.html', []);
    exit();
}

$stmt = DB::getInstance()->prepare("
    UPDATE Offers
    SET status = 'active'
    WHERE OfferID = ?
      AND status = 'pending'
      AND modified IS NOT NULL
      AND modified <= datetime('now', '-5 minutes')
");
$stmt->execute([$id]);

$stmt = DB::getInstance()->prepare("SELECT * FROM Offers WHERE OfferID = ?");
$stmt->execute([$id]);
$offer = $stmt->fetch();

if (!$offer || $offer['status'] !== 'active') {
    print TwigHelper::getInstance() -> render( 'error.html', []);
    exit();
}

print TwigHelper::getInstance()->render('offer.html', [
    'offer' => $offer
]);



?>
