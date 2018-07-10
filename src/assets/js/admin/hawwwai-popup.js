import $ from 'jquery';

// Create Iframe
var pageTitle = '',
    currentConfig = {},
    $iframe = $('<div/>').append('<div id="hawwwai-playlist-editor" class="closed" data-role="popup"><iframe src=""><p>Your browser does not support iframes.</p></iframe><span class="close-playlist-editor dashicons dashicons-no-alt"></span></div>').children();

// Append iFrame
$iframe.each(function() {
    var $this = $(this);

    $this
        .appendTo('body')
        .find('.close-playlist-editor').click(function(e) {
            e.stopPropagation();
            $this.removeClass('opened').addClass('closed');
        })
});

//eventListener => On récupère le confID que nous envoie l 'éditeur
window.addEventListener('message', function(e) {
    currentConfig.confID_field.attr('value', e.data.conf_id);
}, false);

// Callback function to execute when mutations are observed
var callback = function(mutationsList) {
    for (var mutation of mutationsList) {
        if (mutation.type == 'childList') {
            $('.acf-field-5b33902f31b18').each(function() {
                var $this = $(this);

                var context = 'playlist_page';
                if ($this.parents('.acf-flexible-content') != 0) {
                    context = 'playlist_block';
                }

                $this.unbind().click(function() {
                    pageTitle = $('input[name="post_title"]').val();

                    if (context == 'playlist_block') {
                        var sectionTitle = $this.parents('.acf-row').find('.acf-field-5b0d1dc8907e7 input').val();
                        var playlistName = 'WP - Bloc Playlist ' + pageTitle + ' - ' + sectionTitle;
                    } else {
                        var playlistName = 'WP - Playlist ' + pageTitle;
                    }

                    // Set Context
                    currentConfig.context = context;
                    currentConfig.playlistName = playlistName;

                    // On récupère la valeur du champ confID pour la passer à l'éditeur si elle est définie
                    currentConfig.confID_field = $this.siblings('.acf-field-5b338ff331b17').find('input[type="text"]');
                    currentConfig.confID = currentConfig.confID_field.attr('value');

                    if (currentConfig.confID.length != 0) {
                        currentConfig.iframeUrl = 'https://api.tourism-system.rc-preprod.com/render/facetconfs/cles-config/' + currentConfig.confID + '/crt-reunion/fr?login=reunion_website&pwd=9f4f5a30';
                    } else {
                        currentConfig.iframeUrl = 'https://api.tourism-system.rc-preprod.com/render/facetconfs/choix-playlist/crt-reunion/fr?context=' + currentConfig.context + '&name=' + currentConfig.playlistName + '&login=reunion_website&pwd=9f4f5a30';
                    }

                    $iframe.find('iframe').attr('src', currentConfig.iframeUrl).end().removeClass('closed').addClass('opened');
                });
            });
        }
    }
};

// Create an observer instance linked to the callback function
var observer = new MutationObserver(callback);
var targetNode = document.getElementById('post'); // Select the node that will be observed for mutations
var config = { attributes: false, childList: true, subtree: true }; // Options for the observer (which mutations to observe)
observer.observe(targetNode, config); // Start observing the target node for configured mutations
// observer.disconnect(); // Later, you can stop observing
