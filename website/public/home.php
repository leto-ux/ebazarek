<?php
if( !defined('IN_INDEX')){
    define( 'LOCATION', 'home' );
    include( 'index.php' );
}

print TwigHelper::getInstance() -> render( 'home.html', []);
?>
