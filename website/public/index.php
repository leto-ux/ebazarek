<?php
    require_once __DIR__ . '/../vendor/autoload.php';

    $loader = new \Twig\Loader\Filesystem( __DIR__ . '/../templates' );
    $twig = new \Twig\Environment( $loader );

    echo $twig -> render( 'index.html', [
        'foo' => 'bar'
    ]);

?>
