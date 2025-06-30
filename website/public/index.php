<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

define( "IN_INDEX", true );

require_once __DIR__ . '/../vendor/autoload.php';

include( "config.php" );
include( "helpers.php" );

// login
if( isset( $_POST['login'] ) && isset( $_POST['password'] )){
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
    } else {
        TwigHelper :: addMsg( 'Błędy login lub hasło', 'error' );
        //header( 'Location: /home' );
    }
}

$allowed_pages = [ 'home', 'offers', 'about', 'opinions', 'logout', 'panel', 'offer', 'buy', 'error' ];

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
} elseif( defined("LOCATION") )
{
    include( LOCATION . '.php' );
} elseif( !isset( $_GET['page'] ))
{
    include( 'home.php' );
}

?>
