$( document ).ready( function() {
    const $themeLink = $( '#theme_style' );
    const savedTheme = localStorage.getItem( 'theme' );

    if( savedTheme ) {
        $themeLink.attr( 'href', `/styles/style_${savedTheme}.css` );
    }

    $( '#toggle_theme' ).on( 'click', function () {
        const currentHref = $themeLink.attr( 'href' );
        const currentTheme = currentHref.includes( 'dark' ) ? 'dark' : 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';

        $themeLink.attr( 'href', `/styles/style_${newTheme}.css` );
        localStorage.setItem( 'theme', newTheme );
    });
});
