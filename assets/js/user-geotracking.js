var userGeoTrack = ( function() {
    var userGeoTrack = null;
    jQuery.ajax({
        'async': false,
        'global': false,
        'url': 'https://ipinfo.io',
        'dataType': "json",
        'success': function (data) {
            userGeoTrack = data;
        }
    });
    return userGeoTrack;
})();
