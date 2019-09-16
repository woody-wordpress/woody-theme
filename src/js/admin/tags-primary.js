import $ from 'jquery';

$('#post').each(function() {
    var $this = $(this);
    var $taxonomiesBoxes = $this.find('#side-sortables .postbox .inside > .categorydiv:not(#taxonomy-page_type) div[id$="-all"] ');

    // Au clic sur chacun des tags
    // On affiche/masque les boutons d'action
    var displayTermPrimaryButton = function($el, $box) {
        var checked = $box.find('input:checkbox:checked').length;
        if ($el.attr('checked') == 'checked' && checked > 1) {
            $el.parent('.selectit').siblings('.set-primary-term').removeClass('hide');
        } else if ($el.attr('checked') == 'checked' && checked == 1) {
            $el.parent('.selectit').addClass('is-primary-term');
            $el.parent('.selectit').siblings('.unset-primary-term').removeClass('hide');
        } else {
            $el.parent('.selectit').removeClass('is-primary-term').siblings('.primary-toggle').addClass('hide');
        }
    };

    // Au clic sur les boutons "Rendre principal" ou "Retirer le tag principal"
    // On affiche/masque les boutons d'action
    // On remplit le champ caché correspondant à la taxonomie concernée
    var primaryTermButtonsActions = function() {

        $('.unset-primary-term').click(function() {
            $(this).siblings('.selectit').removeClass('is-primary-term');

            $(this).addClass('hide');
            $(this).siblings('.set-primary-term').removeClass('hide');
        });

        $('.set-primary-term').click(function() {

            var $currentPrimary = $(this).closest('.categorychecklist').find('.is-primary-term');
            var $currentPrimaryButton = $currentPrimary.siblings('.unset-primary-term');

            $(this).addClass('hide');
            $(this).siblings('.unset-primary-term').removeClass('hide');

            $currentPrimaryButton.addClass('hide');
            $currentPrimaryButton.siblings('.set-primary-term').removeClass('hide');
            $currentPrimary.removeClass('is-primary-term');

            $(this).siblings('.selectit').addClass('is-primary-term');
        });
    }

    var addSetUnsetPrimaryButtons = function($el) {
        $el.parent('.selectit').parent('li').append('<span class="primary-toggle set-primary-term hide" data-term-id="' + $el.val() + '"><small>Rendre principal<small></span>');
        $el.parent('.selectit').parent('li').append('<span class="primary-toggle unset-primary-term hide" data-term-id="' + $el.val() + '"><small>Retirer le tag principal<small></span>');
    }

    // Pour chacune des metaboxes de taxonomie, on créé les boutons d'action
    $taxonomiesBoxes.each(function() {
        var $taxonomyBox = $(this);
        var $termsCheckboxes = $taxonomyBox.find('input:checkbox');

        $termsCheckboxes.each(function() {
            var $termCheckbox = $(this);
            addSetUnsetPrimaryButtons($termCheckbox);
            displayTermPrimaryButton($termCheckbox, $taxonomyBox);

            $termCheckbox.click(function() {
                displayTermPrimaryButton($termCheckbox, $taxonomyBox);
            });
        });

    });

    primaryTermButtonsActions();

});
