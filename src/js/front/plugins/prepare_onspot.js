import $ from 'jquery';

$('.tools .prepare_onspot_switcher input').on('click', function() {
    var switcher = $(this).prop('checked');

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: frontendajax.ajaxurl,
        data: {
            action: 'redirect_prepare_onspot',
            params: switcher,
            post_id: globals.post_id
        },
        success: function(url) {
            window.location.replace(url);
        }
    });
});

// TODO: Get destination location
// Get it from ERP ?

// TODO: Get user location + create pop up
// Can't work on http.
var opt = {
    timeout: 5000,
    maximumAge: 0
};

function success(pos) {
    var crd = pos.coords;

    console.log('Position actuelle :');
    console.log('Latitude : ' + crd.latitude);
    console.log('Longitude : ' + crd.longitude);
    console.log('Précision : ' + crd.accuracy + ' mètres.');

    // TODO: check if current position is within area
    if (isInArea(crd)) {
        // TODO: Show pop-up
    }
}

function error(err) {
    console.warn(`ERREUR (${err.code}): ${err.message}`);
}

navigator.geolocation.getCurrentPosition(success, error, opt);

function isInArea(coord) {
    var is_inside = false;

    return is_inside;
}
