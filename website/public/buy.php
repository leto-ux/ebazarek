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

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    print TwigHelper::getInstance() -> render( 'error.html', []);
    exit();
}

$stmt = DB::getInstance()->prepare("SELECT * FROM Offers WHERE OfferID = ?");
$stmt->execute([$id]);
$offer = $stmt->fetch();

if ( !$offer || $offer['status'] !== 'active' ){
    print TwigHelper::getInstance() -> render( 'error.html', []);
    exit();
}

$stmt = DB::getInstance()->prepare("UPDATE Offers SET status = 'pending', modified = datetime('now') WHERE OfferID = ?");
$stmt -> execute([$id]);


print TwigHelper::getInstance()->render('buy.html', [
    'offer' => $offer
]);
?>
