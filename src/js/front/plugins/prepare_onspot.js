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

var dest_coord = {
    latitude: 51.507351,
    longitude: -0.127758
};

// TODO: Get user location + create pop up
// var opt = {
//     timeout: 5000,
//     maximumAge: 0
// };

// function success(pos) {
//    var crd = pos.coords;

//     console.log('Position actuelle :');
//     console.log('Latitude : ' + crd.latitude);
//     console.log('Longitude : ' + crd.longitude);
//     console.log('Précision : ' + crd.accuracy + ' mètres.');

//     if (isInArea(crd)) {
//     }
// }

// function error(err) {
//     console.warn(`ERREUR (${err.code}): ${err.message}`);
// }

// navigator.geolocation.getCurrentPosition(success, error, opt);

var coord = {
    latitude: 51.619722,
    longitude: 0.079651,
    accuracy: 100
};

isInArea(coord, dest_coord);

function isInArea(coord, dest_coord) {
    var is_inside = false;

    var radius = 30;
    var distance = calculDistance(coord, dest_coord );

    if(distance <= radius){
        is_inside = true;
    }

    return is_inside;
}

// Convert Degrees into radian value
function degreesToRadian(degrees){
    return degrees * Math.PI / 180;
}

// Calcul distance between two coordinates. If distance is under radius, then it's inside the circle
function calculDistance(coord, dest_coord){
    var R = 6371; // Earth's Radius in Km

    var dLat = degreesToRadian(dest_coord.latitude - coord.latitude);
    var dLon = degreesToRadian(dest_coord.longitude - coord.longitude);

    var lat1 = degreesToRadian(coord.latitude);
    var lat2 = degreesToRadian(dest_coord.latitude);

    var a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.sin(dLon/2) * Math.sin(dLon/2) * Math.cos(lat1) * Math.cos(lat2);
    var c = 2 * Math.atan(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}
