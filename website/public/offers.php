<?php


if( !defined('IN_INDEX')){
    define( 'LOCATION', 'offers' );
    include( 'index.php' );
}

$stmt = DB :: getInstance() -> prepare( "SELECT OfferID, title, description, category, photo_path, price, subwallet FROM Offers WHERE status='active' ORDER BY OfferID desc" );
$stmt -> execute();
$offers = $stmt -> fetchAll();

print TwigHelper::getInstance() -> render( 'offers.html', [
    'offers' => $offers
] );
?>
