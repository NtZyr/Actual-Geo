jQuery( document ).ready( function( $ ) {
    $.getJSON('https://ipinfo.io', function(data){
        $.each( data, function( index, value ) {
            $( '<li>' + index + ': ' + value + '</li>' ).appendTo( '.current_position_data' );
        } ); 
    })

    $(".chosen-select").chosen();

    $( 'button[name="add_geo-redirect"]' ).click( function() {
        redirectRow = $( '<tr>' );
        redirectRow__country = $( '<td>' );
        redirectRow__countryRegion = $( '<td>' );
        redirectRow__URL = $( '<td>' );

        redirectRow__country_select = $( '<select>').attr( {
            class: 'chosen-select',
            name: 'geo_redirects[' + redirect_num + '][country]',
        });
        redirectRow__url_select = $( '<select>').attr({
            name: 'geo_redirects[' + redirect_num + '][url]'
        });

        redirectRow__country.append( $('<label>').text( country_label ) );
        $.each( countries, function( index, value ) {
            redirectRow__country_select.append( $('<option>').attr( 'value', index ).text( value ) );
        } );
        redirectRow__country.append( redirectRow__country_select );
        redirectRow.append( redirectRow__country );
        
        redirectRow__countryRegion.append( $('<label>').text( countryRegion_label ) );
        redirectRow__countryRegion.append( $('<input>').attr({
            type: 'text',
            name: 'geo_redirects[' + redirect_num + '][region]'
        }) );
        redirectRow.append( redirectRow__countryRegion );
        
        redirectRow__URL.append( $('<label>').text( url_label ) );
        $.each( sites, function( index, value ) {
            redirectRow__url_select.append( $('<option>').attr( 'value', index ).text( value ) );
        } );
        redirectRow__URL.append( redirectRow__url_select );
        redirectRow.append( redirectRow__URL );
        
        redirectRow.appendTo( '.redirects-table tbody' );

        $(".chosen-select").chosen();

        redirect_num += 1;
    } );
} );