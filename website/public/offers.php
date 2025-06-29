<?php
if( !defined('IN_INDEX')){
    define( 'LOCATION', 'offers' );
    include( 'index.php' );
}
print TwigHelper::getInstance() -> render( 'offers.html', [] );
?>
