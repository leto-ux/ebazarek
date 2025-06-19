<?php
    session_start();

    ini_set( 'display_errors', 1 );
    error_reporting( E_ALL );

    define( "IN_INDEX", true );

    require __DIR__ . '/../vendor/autoload.php';

    include( "config.php" );
    include( "helpers.php" );

    include( 'main.php' );
?>
