<?php
session_start();

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

define( "IN_INDEX", true );

require __DIR__ . '/../vendor/autoload.php';

include( "config.php" );
include( "helpers.php" );

// login
$login = $_POST[ 'login' ] ?? '';
$password = $_POST[ 'password' ] ?? '';

$stmt = DB::getInstance() -> prepare( "SELECT * FROM Users WHERE login = :login" );
$stmt -> execute([ ':login' => $login ]);
$user = $stmt -> fetch( PDO::FETCH_ASSOC );

if( $user && hash( 'sha256', $password ) === $user[ 'pass_hash' ]){
    $_SESSION[ 'id' ] = $user[ 'UserID' ];
    $_SESSION[ 'login' ] = $user[ 'login' ];
    TwigHelper :: getInstance() -> addGlobal( '_session', $_SESSION );
    header( 'Location: /home' );
}


$allowed_pages = [ 'home', 'offers', 'about', 'opinions', 'logout', 'error' ];

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
