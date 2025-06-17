$( document ).ready( function() {
    const $themeLink = $( '#theme_style' );
    const savedTheme = localStorage.getItem( 'theme' );

    const themes = {
        light: "https://cdn.jsdelivr.net/npm/bootswatch@5.1.3/dist/flatly/bootstrap.min.css",
        dark: "https://cdn.jsdelivr.net/npm/bootswatch@5.1.3/dist/darkly/bootstrap.min.css"
    };

    // Set saved motive
    if( savedTheme && themes[ savedTheme ] ) {
        $themeLink.attr( 'href', themes[ savedTheme ] );
    } else {
        // or default one ( dark )
        $themeLink.attr( 'href', themes.dark );
        localStorage.setItem( 'theme', 'dark' );
    }

    $( '#toggle_theme' ).on( 'click', function () {
        // const currentTheme = localStorage.getItem( 'theme' ) === 'dark' ? 'dark' : 'light';
        // const newTheme = currentTheme === 'light' ? 'dark' : 'light';

        // $themeLink.attr( 'href', themes[ newTheme ]);
        // localStorage.setItem( 'theme', newTheme );
        //
        const currentTheme = localStorage.getItem('theme') === 'dark' ? 'dark' : 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        $themeLink.attr('href', themes[newTheme]);
        localStorage.setItem('theme', newTheme);
        console.log( "Wykonano " );
    });
});
