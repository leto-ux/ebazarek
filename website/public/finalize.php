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
    // sprawdzic czy na pewno doszledl hajs
    $isMoney = True;

    if( $isMoney ){
        $stmt = DB::getInstance()->prepare("
            UPDATE Offers
            SET status = 'paid',
            delivery = :delivery
            WHERE OfferID = :offerId
            AND status = 'pending'
            ");
        $stmt->execute([
            ':offerId' => $_POST['offer_id'],
            ':delivery' => $_POST['delivery_info']
        ]);
        header( 'Location: /offers' );
        exit();


    }
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
