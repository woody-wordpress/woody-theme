import $ from 'jquery';

$(document).ready(function() {

    /**
     * Remove switcher if there is no opposite page
     * @param param (boolean) prepare || onspot
     */
    var removeSwitcher = function() {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: frontendajax.ajaxurl,
            data: {
                action: 'get_opposite',
                post_id: globals.post_id
            },
            success: function(response) {
                if (response !== false) {
                    $('.prepare_onspot_wrapper').css('display', 'flex');
                }
            },
            error: function(err){
                console.error(err);
            }
        });
    }
    removeSwitcher();

    if ($('#wpadminbar').length < 1) {
        /**
         * Redirect function
         * @param param (boolean) prepare || onspot
         */
        var redirectPrepareOnspot = function(param) {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: frontendajax.ajaxurl,
                data: {
                    action: 'redirect_prepare_onspot',
                    params: param,
                    post_id: globals.post_id
                },
                success: function(url) {
                    Cookies.set('prepare_onspot', param);
                    // If pages exists, redirect, otherwise don't
                    if (url) {
                        window.location.replace(url);
                    }
                }
            });
        }

        /////////////////////////////////////////////////////////////////////////
        // Set cookie to stay on prepare or spot
        /////////////////////////////////////////////////////////////////////////

        if (!Cookies.get('prepare_onspot')) {
            Cookies.set('prepare_onspot', $('.tools .prepare_onspot_switcher input').prop('checked'));
        } else {
            var cookie = Cookies.get('prepare_onspot');
            var switcher = $('.tools .prepare_onspot_switcher input').prop('checked');

            if (cookie != switcher) {
                redirectPrepareOnspot(cookie);
            }
        }

        ////////////////////////////////////////////////////////////////////////
        // Click on switcher
        ////////////////////////////////////////////////////////////////////////

        $('.tools .prepare_onspot_switcher input').on('click', function() {
            var switcher = $(this).prop('checked');
            redirectPrepareOnspot(switcher);
        });

        ////////////////////////////////////////////////////////////////////////
        // First visit on website event
        ////////////////////////////////////////////////////////////////////////

        if (!Cookies.get('firstvisit')) {

            Cookies.set('firstvisit', true, { expires: 5 });
            var dest_coord = {
                latitude: 0,
                longitude: 0,
            };

            // Check if we are on "prepare page", if not, useless to continue
            var switcher = $('.tools .prepare_onspot_switcher input').prop('checked');
            if (switcher != false) {

                // Check if opposite page exists
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: frontendajax.ajaxurl,
                    data: {
                        action: 'get_opposite',
                        post_id: globals.post_id
                    },
                    success: function(opposite_exists) {
                        if (opposite_exists) {

                            // Get OT coordinates
                            $.ajax({
                                type: 'POST',
                                dataType: 'json',
                                url: frontendajax.ajaxurl,
                                data: {
                                    action: 'get_destination_coord',
                                },
                                success: function(response) {
                                    if (response.lat.length > 0 && response.lon.length > 0) {
                                        dest_coord.latitude = parseFloat(response.lat);
                                        dest_coord.longitude = parseFloat(response.lon);

                                        // TODO: check if get current pos works on preprod
                                        // Get current user position
                                        var opt = {
                                            timeout: 5000,
                                            maximumAge: 0
                                        };

                                        function success(pos) {
                                            var coord = {
                                                latitude: pos.coords.latitude,
                                                longitude: pos.coords.longitude,
                                                accuracy: pos.coords.accuracy
                                            };

                                            // Check if user is near OT's position
                                            // If true, give he opportunity to go on spot pages
                                            if (isInArea(coord, dest_coord)) {
                                                $('.tools .prepare_onspot_switcher input').trigger('click');
                                            }
                                        }

                                        function error(err) {
                                            console.warn(`ERREUR (${err.code}): ${err.message}`);
                                        }

                                        navigator.geolocation.getCurrentPosition(success, error, opt);
                                    } else {
                                        console.warn('Empty latitude and longitude vars');
                                    }
                                },
                                error: function(error) {
                                    console.error('get_destination_coord AJAX : ' + error);
                                }
                            });
                        }
                    },
                    error: function(error) {
                        console.error('get_opposite AJAX : ' + error);
                    }
                });
            }

            function isInArea(coord, dest_coord) {
                var is_inside = false;

                var radius = 25;
                var distance = calculDistance(coord, dest_coord);

                if (distance <= radius) {
                    is_inside = true;
                }

                return is_inside;
            }

            // Convert Degrees into radian value
            function degreesToRadian(degrees) {
                return degrees * Math.PI / 180;
            }

            // Calcul distance between two coordinates. If distance is under radius, then it's inside the circle
            function calculDistance(coord, dest_coord) {
                var R = 6371; // Earth's Radius in Km

                var dLat = degreesToRadian(dest_coord.latitude - coord.latitude);
                var dLon = degreesToRadian(dest_coord.longitude - coord.longitude);

                var lat1 = degreesToRadian(coord.latitude);
                var lat2 = degreesToRadian(dest_coord.latitude);

                var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.sin(dLon / 2) * Math.sin(dLon / 2) * Math.cos(lat1) * Math.cos(lat2);
                var c = 2 * Math.atan(Math.sqrt(a), Math.sqrt(1 - a));
                return R * c;
            }
        }
    }
});
