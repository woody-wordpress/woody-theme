import $ from 'jquery';

$('#post').each(function() {
    // On ferme toutes les metaboxes ACF
    $('.acf-postbox').addClass('closed');

    // On masque les metaboxes de taxonomies dans l'edition des posts (on les rajoutera ensuite dans des champs ACF)
    $('[id^="tagsdiv-"').hide();

    // On referme les metaboxes par défaut sur l'édition d'un post
    $('#pageparentdiv, #revisionsdiv, #wpseo_meta, #members-cp').addClass('closed');

    // Action sur les focus
    var toggleChoiceAction = function($bigparent) {
        //console.log('toggleChoiceAction', $bigparent);
        $bigparent.find('.tpl-choice-wrapper').each(function() {
            var $this = $(this);

            // On toggle la description de chaque template dans les champs woody_tpl
            $this.find('.toggle-desc').click(function(e) {
                e.stopPropagation();
                $this.find('.tpl-desc').toggleClass('hidden');
                $this.find('.desc-backdrop').toggleClass('hidden');
            });

            $this.find('.close-desc').click(function() {
                $this.find('.tpl-desc').addClass('hidden');
                $this.find('.desc-backdrop').addClass('hidden');
            });

            $this.find('.desc-backdrop').click(function() {
                $this.find('.tpl-desc').addClass('hidden');
                $(this).addClass('hidden');
            });
        });
    }

    // Action sur les focus
    var fitChoiceAction = function($bigparent, count) {
        //console.log('fitChoiceAction', $bigparent);
        //console.log(count);

        $bigparent.find('.tpl-choice-wrapper').each(function() {
            var $this = $(this);

            var fittedfor = $this.data('fittedfor');
            var acceptsmax = $this.data('acceptsmax');
            if (fittedfor == 'all') fittedfor = 0;

            // On affiche un état en fonction du nombre d'élément
            if (count >= fittedfor && count <= acceptsmax) {
                $this.removeClass('notfit');
                $this.addClass('fit');
            } else {
                $this.removeClass('fit');
                $this.addClass('notfit');
            }
        });
    }

    var countElements = function(field) {
        var $parent = field.parent().$el;
        var $bigparent = field.parent().parent().$el;

        // add class to this field
        $parent.each(function() {
            toggleChoiceAction($bigparent);

            setTimeout(() => {
                var count = $(this).find('.acf-table .acf-row').length - 1;
                fitChoiceAction($bigparent, count);
            }, 1000);
        });
    };

    acf.addAction('ready_field/key=field_5b22415792db0', countElements);
    acf.addAction('append_field/key=field_5b22415792db0', countElements);
    acf.addAction('remove_field/key=field_5b22415792db0', countElements);

})
