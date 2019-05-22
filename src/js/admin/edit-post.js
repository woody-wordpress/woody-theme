import $ from 'jquery';

$( '#post' ).each( function ()
{

    // Added button "Retirer le tag principal"
    $( '#taxonomy-themes, #taxonomy-places, #taxonomy-seasons' ).each( function ()
    {
        var $this = $( this );
        var name = $this.attr( 'id' ).replace( 'taxonomy-', '' );

        $this.append( '<input class="button" id="primary-term-' + name + '-enable" style="text-align:center;" value="Retirer le tag principal">' );

        var $input = $( '#yoast-wpseo-primary-' + name );
        var $button = $this.find( '#primary-term-' + name + '-enable' );

        var bindMakePrimaryTerm = function ()
        {
            $this.find( '.wpseo-make-primary-term' ).click( function ()
            {
                $button.show();
            } );
        }

        var removePrimaryTerm = function ()
        {
            $this.find( '.wpseo-primary-term' ).removeClass( 'wpseo-primary-term' ).addClass( 'wpseo-non-primary-term' );
            $input.attr( 'value', '' );
        }

        if ( $input.val() == '' ) {
            $button.hide();
        } else {
            $button.show();
        }

        $input.change( function ()
        {
            if ( $( this ).val() == '' ) {
                $button.hide();
            } else {
                $button.show();
            }
        } );

        $button.click( function ()
        {
            $( this ).hide();
            removePrimaryTerm();
        } );

        // On attend que Yoast soit chargé
        setTimeout( () => { bindMakePrimaryTerm(); }, 1500 );
    } );

    // Alert change langue
    $( '#select-post-language' ).each( function ()
    {
        var $this = $( this );
        var $select = $this.find( '#post_lang_choice' );

        // Added notAllowed on select
        $select.addClass( 'notAllowed' );

        // Added lock button
        $this.append( '<div class="button button-lock button-primary"><span class="dashicons dashicons-lock"></span></div>' );
        var $lock = $this.find( '.button-lock' );
        var $lock_icon = $lock.find( '.dashicons' );

        // Popin confirm change lang
        $lock.click( function ()
        {
            if ( $lock.hasClass( 'button-primary' ) ) {
                var confirm = window.confirm( "Êtes-vous sûr de vouloir changer la langue de cette page ?" );
                if ( confirm == true ) {
                    $select.removeClass( 'notAllowed' );
                    $lock.removeClass( 'button-primary' );
                    $lock_icon.addClass( 'dashicons-unlock' ).removeClass( 'dashicons-lock' );
                }
            } else {
                $select.addClass( 'notAllowed' );
                $lock.addClass( 'button-primary' );
                $lock_icon.removeClass( 'dashicons-unlock' ).addClass( 'dashicons-lock' );
            }
        } );
    } );

    // Show on scroll
    var $preview_button = $( '#minor-publishing-actions .preview.button' );
    var $save_button = $( '#publishing-action' );
    $( window ).scroll( function ()
    {
        if ( $( window ).scrollTop() < 800 ) {
            $preview_button.removeClass( 'sticky' );
            $save_button.removeClass( 'sticky' );
        } else {
            $preview_button.addClass( 'sticky' );
            $save_button.addClass( 'sticky' );
        }
    } );

    // On ferme toutes les metaboxes ACF
    // $('.acf-postbox').addClass('closed');

    // On referme les metaboxes par défaut sur l'édition d'un post
    // $('#pageparentdiv, #revisionsdiv, #wpseo_meta, #members-cp').addClass('closed');

    // Action sur les focus
    var toggleChoiceAction = function ( $bigparent )
    {
        $bigparent.find( '.tpl-choice-wrapper' ).each( function ()
        {
            var $this = $( this );

            // On toggle la description de chaque template dans les champs woody_tpl
            $this.find( '.toggle-desc' ).click( function ( e )
            {
                e.stopPropagation();
                $this.find( '.tpl-desc' ).toggleClass( 'hidden' );
                $this.find( '.desc-backdrop' ).toggleClass( 'hidden' );
            } );

            $this.find( '.close-desc' ).click( function ()
            {
                $this.find( '.tpl-desc' ).addClass( 'hidden' );
                $this.find( '.desc-backdrop' ).addClass( 'hidden' );
            } );

            $this.find( '.desc-backdrop' ).click( function ()
            {
                $this.find( '.tpl-desc' ).addClass( 'hidden' );
                $( this ).addClass( 'hidden' );
            } );
        } );
    }

    // Action sur les focus
    var fitChoiceAction = function ( $bigparent, count )
    {

        $bigparent.find( '.tpl-choice-wrapper' ).each( function ()
        {
            var $this = $( this );

            var fittedfor = $this.data( 'fittedfor' );
            var acceptsmax = $this.data( 'acceptsmax' );
            if ( fittedfor == 'all' ) fittedfor = 0;

            // On affiche un état en fonction du nombre d'élément
            if ( count >= fittedfor && count <= acceptsmax ) {
                $this.removeClass( 'notfit' );
                $this.addClass( 'fit' );
            } else {
                $this.removeClass( 'fit' );
                $this.addClass( 'notfit' );
            }
        } );
    }

    var countElements = function ( field )
    {
        var $parent = field.parent().$el;
        var $bigparent = field.parent().parent().$el;

        // add class to this field
        $parent.each( function ()
        {
            toggleChoiceAction( $bigparent );

            setTimeout( () =>
            {
                var count = $( this ).find( '.acf-table .acf-row' ).length - 1;
                fitChoiceAction( $bigparent, count );
            }, 2000 );
        } );
    };

    acf.addAction( 'ready_field/key=field_5b22415792db0', countElements );
    acf.addAction( 'append_field/key=field_5b22415792db0', countElements );
    acf.addAction( 'remove_field/key=field_5b22415792db0', countElements );

    // **
    // Update tpl-choice-wrapper classes for autofocus layout
    // **
    var getAutoFocusData_AJAX = {};
    var getAutoFocusData = function ( $parent )
    {
        var query_params = {};

        var block_id = $parent.attr( 'id' );
        if ( typeof block_id == 'undefined' ) {
            block_id = "autofocusID_" + Math.random().toString( 16 ).slice( 2 );
            $parent.attr( 'id', block_id );
        }

        // Append Message
        var $message_wrapper = $parent.find( '.acf-tab-wrap' );
        if ( $message_wrapper.find( '.woody-count-message' ).length == 0 ) {
            var $message = $( '<div>' ).append( '<div class="woody-count-message"> \
                <span class="loading"><small>Chargement du nombre d\'éléments mis en avant ...</small></span> \
                <span class="success" style="display:none;"><small>Nombre d\'éléments mis en avant :</small><span class="count"></span></span> \
                <span class="alert" style="display:none;"><small>Aucune mise en avant ne correspond à votre sélection. Merci de modifier vos paramètres</small></span> \
                </div>').children();
            $message_wrapper.append( $message );
        } else {
            var $message = $message_wrapper.find( '.woody-count-message' );
        }

        $message
            .find( '.loading' ).show().end()
            .find( '.success' ).hide().end()
            .find( '.alert' ).hide().end();

        // Create query
        query_params[ 'current_post' ] = $( '#post_ID' ).val();
        $parent.find( 'input:checked, input[type="number"]' ).each( function ()
        {
            var $this = $( this );
            var name = $this.parents( '.acf-field' ).data( 'name' );
            if ( !query_params[ name ] ) query_params[ name ] = [];
            query_params[ name ].push( $this.val() );
        } );
        $parent.find( 'select' ).each( function ()
        {
            var $this = $( this );
            var name = $this.parents( '.acf-field' ).data( 'name' );
            query_params[ name ] = $this.val();
        } );

        // Ajax
        if ( typeof getAutoFocusData_AJAX[ block_id ] !== 'undefined' ) {
            getAutoFocusData_AJAX[ block_id ].abort();
        }

        getAutoFocusData_AJAX[ block_id ] = $.ajax( {
            type: 'POST',
            dataType: 'json',
            url: ajaxurl,
            data: {
                action: 'woody_autofocus_count',
                params: query_params
            },
            success: function ( data )
            {
                delete getAutoFocusData_AJAX[ block_id ];

                fitChoiceAction( $parent, data );

                if ( data === 0 ) {
                    $message
                        .find( '.loading' ).hide().end()
                        .find( '.success' ).hide().end()
                        .find( '.alert' ).show().end();
                } else {
                    $message
                        .find( '.loading' ).hide().end()
                        .find( '.success' ).show().find( '.count' ).html( data ).end().end()
                        .find( '.alert' ).hide().end();
                }
            },
            error: function () { },
        } );

    }

    var getAutoFocusQuery = function ( field )
    {
        var $parent = field.$el.parent();
        var $bigparent = field.parent().$el;

        $parent.each( function ()
        {
            var $this = $( this );
            toggleChoiceAction( $bigparent );

            getAutoFocusData( $this );

            $this.find( 'input[type="checkbox"], input[type="radio"], select' ).on( 'change', function ()
            {
                getAutoFocusData( $this );
            } );

            $this.find( 'input[type="number"]' ).keyup( function ()
            {
                getAutoFocusData( $this );
            } );
        } );

    }

    acf.addAction( 'ready_field/key=field_5b27890c84ed3', getAutoFocusQuery );
    acf.addAction( 'append_field/key=field_5b27890c84ed3', getAutoFocusQuery );

    // Collapse all section or layouts
    $( '#acf-group_5afd260eeb4ab .acf-field.collapsing-rows' ).each( function ()
    {
        var $this = $( this );
        if ( $this.hasClass( 'acf-field-5afd2c6916ecb' ) ) {
            var rowsType = 'les sections';
            var theRows = '> .acf-field-5afd2c6916ecb > .acf-input > .acf-repeater > .acf-table > .ui-sortable .acf-row';
        } else if ( $this.hasClass( 'acf-field-5b043f0525968' ) ) {
            var rowsType = 'les blocs';
            var theRows = '> .acf-flexible-content > .values .layout';
        }

        $this.prepend( '<span class="woodyRowsCollapse"><span class="text">Fermer ' + rowsType + '</span><span class="dashicons dashicons-arrow-up' + '"></span></span>' );

        $( '.woodyRowsCollapse' ).click( function ()
        {
            $( this ).siblings( '.acf-input' ).find( theRows ).addClass( '-collapsed' );
        } )
    } );
} );
