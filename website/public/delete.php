<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('IN_INDEX')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    include('config.php');
    include('helpers.php');
}

$id = $_GET['id'] ?? null;
$userID = $_SESSION['id'] ?? null;

if (!$id || !$userID) {
    TwigHelper::addMsg('Nieprawidłowe żądanie usunięcia.', 'error');
    header("Location: /panel");
    exit;
}

// Pobierz ofertę, żeby sprawdzić uprawnienia
$stmt = DB::getInstance()->prepare("SELECT * FROM Offers WHERE OfferID = ?");
$stmt->execute([$id]);
$offer = $stmt->fetch();

if (!$offer || $offer['UserID'] !== $userID) {
    TwigHelper::addMsg('Nie znaleziono oferty lub brak uprawnień.', 'error');
    header("Location: /panel");
    exit;
}

// (opcjonalnie) usuń plik zdjęcia z serwera
/*
$upload_dir = 'uploads/';
if (!empty($offer['photo_path']) && file_exists($upload_dir . $offer['photo_path'])) {
    unlink($upload_dir . $offer['photo_path']);
}
*/

try {
    $stmt = DB::getInstance()->prepare("DELETE FROM Offers WHERE OfferID = ?");
    $stmt->execute([$id]);

    TwigHelper::addMsg('Oferta została usunięta.', 'success');
} catch (PDOException $e) {
    TwigHelper::addMsg('Błąd bazy danych: ' . $e->getMessage(), 'error');
}

header("Location: /panel");
exit;
