<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('IN_INDEX')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    include( 'config.php' );
    include( 'helpers.php' );
}

if( !isset($_POST['status']) && !isset($_POST['offer_id']) &&
    ( !isset($_POST['delivery_info']) || $_POST['status'] === 'abandoned' )){
    print TwigHelper::getInstance() -> render( 'error.html', []);
    exit();
}

if( $_POST['status'] === 'paid' ){
    echo "<br><p>Zakupiono</p>";
} else {
    $stmt = DB::getInstance()->prepare("
        UPDATE Offers
        SET status = 'active'
        WHERE OfferID = ?
        AND status = 'pending'
    ");
    $stmt->execute([$_POST['offer_id']]);
    header( 'Location: /offers' );
    exit();
}

?>
