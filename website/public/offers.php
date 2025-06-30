<?php
if( !defined('IN_INDEX')){
    define( 'LOCATION', 'offers' );
    include( 'index.php' );
}

$stmt = DB::getInstance()->prepare("
    UPDATE Offers
    SET status = 'active'
    WHERE status = 'pending'
      AND modified IS NOT NULL
      AND modified <= datetime('now', '-5 minutes')
");
$stmt->execute();

$stmt = DB::getInstance()->prepare("
    SELECT OfferID, title, description, category, photo_path, price, subwallet
    FROM Offers
    WHERE status = 'active'
    ORDER BY OfferID DESC
");
$stmt->execute();
$offers = $stmt->fetchAll();

print TwigHelper::getInstance() -> render( 'offers.html', [
    'offers' => $offers
] );
?>
