jQuery( document ).ready( function( $ ) {
    $.getJSON('https://ipinfo.io', function( data ){
        georedirect( data );
    });

    function georedirect( userPlace ) {
        console.log( userPlace );

        if( referer == null ) {
            var urlto = redirects.filter( x => x.region == userPlace['region']).map(x => x.url);

            window.location = 'http://' + urlto;
        }
    }
});