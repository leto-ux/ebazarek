<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('IN_INDEX')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    include( 'config.php' );
    include( 'helpers.php' );
}

$stat = '';

if( isset($_POST['oid']) && isset($_POST['txid']) ){

    $stmt = DB::getInstance()->prepare("SELECT * FROM Offers WHERE OfferID = ?");
    $stmt->execute([$_POST['oid']]);
    $offer = $stmt->fetch();

    // $verification_success = True;
    // $verification_success = ($offer && $offer['status'] === 'paid');
    // I LOVE HOW SIMPLE THIS WAS
    $verification_success = (
        $offer &&
        $offer['status'] === 'paid' &&
        hash_equals($offer['txid'], $_POST['txid'])
    );

    if( $verification_success ){
        $stmt = DB::getInstance()->prepare("UPDATE Offers SET status = 'delivered' WHERE OfferID = ?");
        $stmt->execute([$_POST['oid']]);

        $stat = 'after_confirmation';
    } else {
        print TwigHelper::getInstance() -> render( 'error.html', []);
        exit();
    }
} elseif( isset( $_GET['id'] ) ){

    $stmt = DB::getInstance()->prepare("SELECT * FROM Offers WHERE OfferID = ?");
    $stmt->execute([$_GET['id']]);
    $offer = $stmt->fetch();

    if( $offer && isset($_SESSION['id']) && $offer['UserID'] === $_SESSION['id'] && $offer['status']=== 'delivered'){
    $stmt = DB::getInstance()->prepare("DELETE FROM Offers WHERE OfferID = ?");
    $stmt->execute([$_GET['id']]);

    $stat = 'after_receiving';
    } else {
        print TwigHelper::getInstance() -> render( 'error.html', []);
        exit();
    }


} else {
    $stat = 'before';
}

print TwigHelper::getInstance() -> render( 'confirm.html', [ 'flag' => $stat ]);
?>
