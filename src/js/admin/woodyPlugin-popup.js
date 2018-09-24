import $ from 'jquery';

// Create an observer instance linked to the callback function
var targetNode = document.getElementById('post'); // Select the node that will be observed for mutations
if (targetNode != null) {

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
        currentConfig.confID_field.attr('value', e.data.confId);
    }, false);

    // Callback function to execute when mutations are observed
    var callback = function(mutationsList) {
        for (var mutation of mutationsList) {
            if (mutation.type == 'childList') {
                $('.acf-field-5b33902f31b18').each(function() {
                    var $this = $(this);

                    var context = 'playlist_page';
                    if (typeof $this.parents('.acf-flexible-content').length != undefined && $this.parents('.acf-flexible-content').length != 0) {
                        context = 'playlist_block';
                    }
                    // if ($this.parents('.acf-flexible-content').length != 0) {
                    //     context = 'playlist_block';
                    // }
                    $this.unbind().click(function() {
                        var pageTitle = $('input[name="post_title"]').val(),
                            post_ID = $('#post_ID').val();

                        if (context == 'playlist_block') {
                            var sectionTitle = $this.parents('.acf-row').find('.acf-field-5b0d1dc8907e7 input').val();
                            if (sectionTitle == '') {
                                sectionTitle = 'section sans titre';
                            }
                            var playlistName = 'WP - Sélection de fiches - post_' + post_ID + ' - ' + pageTitle + ' - ' + sectionTitle;
                        } else {
                            var playlistName = 'WP - Playlist - post_' + post_ID + ' - ' + pageTitle;
                        }

                        $('.acf-field-5b338f0d31b16 input[type="text"]').attr('value', playlistName);

                        // Set Context
                        currentConfig.context = context;
                        currentConfig.playlistName = playlistName;

                        // On récupère la valeur du champ confID pour la passer à l'éditeur si elle est définie
                        currentConfig.confID_field = $this.siblings('.acf-field-5b338ff331b17').find('input[type="text"]');
                        currentConfig.confID = currentConfig.confID_field.attr('value');

                        //
                        if (window.siteConfig) {
                            console.log(siteConfig);
                            if (typeof currentConfig != undefined && currentConfig.confID.length != 0) {
                                currentConfig.iframeUrl = 'https://api.tourism-system.rc-preprod.com/render/facetconfs/cles-config/' + currentConfig.confID + '/' + siteConfig.site_key + '/fr?login=' + siteConfig.login + '&pwd=' + siteConfig.password;
                            } else {
                                currentConfig.iframeUrl = 'https://api.tourism-system.rc-preprod.com/render/facetconfs/choix-playlist/' + siteConfig.site_key + '/fr?context=' + currentConfig.context + '&name=' + currentConfig.playlistName + '&login=' + siteConfig.login + '&pwd=' + siteConfig.password;
                            }

                            $iframe.find('iframe').attr('src', currentConfig.iframeUrl).end().removeClass('closed').addClass('opened');
                        } else {
                            console.error('No siteConfig set (required)');
                        }

                    });
                });
            }
        }
    };

    var observer = new MutationObserver(callback);
    var config = { attributes: false, childList: true, subtree: true }; // Options for the observer (which mutations to observe)
    observer.observe(targetNode, config); // Start observing the target node for configured mutations
    // observer.disconnect(); // Later, you can stop observing
}
