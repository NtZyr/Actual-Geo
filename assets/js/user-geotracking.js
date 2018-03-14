jQuery( document ).ready( function( $ ) {
    $.getJSON('https://ipinfo.io', function(data){
        console.log( data ); 
    })
} );