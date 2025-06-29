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
    http_response_code(400);
    exit("Brak ID oferty.");
}

$stmt = DB::getInstance()->prepare("SELECT * FROM Offers WHERE OfferID = ?");
$stmt->execute([$id]);
$offer = $stmt->fetch();

if (!$offer) {
    http_response_code(404);
    exit("Nie znaleziono oferty.");
}

print TwigHelper::getInstance()->render('offer.html', [
    'offer' => $offer
]);



?>
