<?php
session_start();

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

define( "IN_INDEX", true );

require __DIR__ . '/../vendor/autoload.php';

include( "config.php" );
include( "helpers.php" );

$allowed_pages = [ 'home', 'offers', 'error' ];


if( isset( $_GET['page'])
    && $_GET['page'] )
{
    $address = substr( $_GET['page'], 1 );
    if( in_array( $address, $allowed_pages )
        && file_exists( $address . '.php' ))
    {
        include( $address . '.php' );
    } else {
        print TwigHelper::getInstance() -> render( 'error.html', []);
    }
} elseif( !isset( $_GET['page'] )) {
    include( 'home.php' );
}

?>
