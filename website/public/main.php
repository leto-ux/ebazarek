<?php
    $loader = new \Twig\Loader\FilesystemLoader( __DIR__ . '/templates' );
    $twig = new \Twig\Environment( $loader );

    echo $twig -> render( 'main.html', [
        'foo' => 'bar'
    ]);
?>
