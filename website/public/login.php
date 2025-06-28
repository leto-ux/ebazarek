<?php
session_start();

include( 'config.php' );
//include( 'helpers.php' );

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

?>
