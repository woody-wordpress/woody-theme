!(function ($) {
    $('#post').each(function () {

        var $this = $(this);
        var $taxonomiesBoxes = $this.find('#side-sortables .postbox .inside > .categorydiv:not(#taxonomy-page_type) div[id$="-all"] ');
        var $primaryTagsFields = $this.find('.acf-field-group.acf-field-5d7bada38eedf');

        // Au clic sur chacun des tags
        // On affiche/masque les boutons d'action
        var displayTermPrimaryButton = function ($el, $box) {
            var checked = $box.find('input:checkbox:checked').length;
            if ($el.prop('checked') && checked > 1) {
                $el.parent('.selectit').siblings('.set-primary-term').removeClass('hide');
            } else if ($el.prop('checked') && checked == 1) {
                $el.parent('.selectit').addClass('is-primary-term');
                $el.parent('.selectit').siblings('.unset-primary-term').removeClass('hide');
            } else {
                $el.parent('.selectit').removeClass('is-primary-term').siblings('.primary-toggle').addClass('hide');
            }
        };

        // Au clic sur les boutons "Rendre principal" ou "Retirer le tag principal"
        // On affiche/masque les boutons d'action
        // On remplit le champ caché correspondant à la taxonomie concernée
        var primaryTermButtonsActions = function () {

            $('.unset-primary-term').click(function () {
                $(this).siblings('.selectit').removeClass('is-primary-term');
                $(this).addClass('hide');
                $(this).siblings('.set-primary-term').removeClass('hide');

                // On supprime la valeur du champ "taxonomie principale" concerné
                var targetTaxonomy = $(this).parents('.categorydiv').attr('id').replace('taxonomy-', 'primary_');
                var targetField = $primaryTagsFields.find('.acf-field[data-name="' + targetTaxonomy + '"] input[type="text"]');
                targetField.val('');
            });

            $('.set-primary-term').click(function () {

                var $currentPrimary = $(this).closest('.categorychecklist').find('.is-primary-term');
                var $currentPrimaryButton = $currentPrimary.siblings('.unset-primary-term');

                $(this).addClass('hide');
                $(this).siblings('.unset-primary-term').removeClass('hide');

                $currentPrimaryButton.addClass('hide');
                $currentPrimaryButton.siblings('.set-primary-term').removeClass('hide');
                $currentPrimary.removeClass('is-primary-term');

                $(this).siblings('.selectit').addClass('is-primary-term');

                // On met à jour la valeur du champ "taxonomie principale" concerné
                var targetTaxonomy = $(this).parents('.categorydiv').attr('id').replace('taxonomy-', 'primary_');
                var targetField = $primaryTagsFields.find('.acf-field[data-name="' + targetTaxonomy + '"] input[type="text"]');
                targetField.val($(this).data('term-id'));
            });
        }

        var addSetUnsetPrimaryButtons = function ($el) {
            $el.parent('.selectit').parent('li').append('<span class="primary-toggle set-primary-term hide" data-term-id="' + $el.val() + '"><small>Rendre principal<small></span>');
            $el.parent('.selectit').parent('li').append('<span class="primary-toggle unset-primary-term hide" data-term-id="' + $el.val() + '"><small>Principal<small></span>');
        }


        var unsetPrimaryTerm = function (termCheckbox) {
            let $term = termCheckbox.closest('li');
            if ($term.find('.is-primary-term').length > 0) {
                $term.find('.unset-primary-term').trigger('click');
                $term.find('.set-primary-term').addClass('hide');
            }
        }

        // Pour chacune des metaboxes de taxonomie, on créé les boutons d'action
        $taxonomiesBoxes.each(function () {
            var $taxonomyBox = $(this);
            var $termsCheckboxes = $taxonomyBox.find('input:checkbox');

            $termsCheckboxes.each(function () {
                var $termCheckbox = $(this);
                addSetUnsetPrimaryButtons($termCheckbox);
                displayTermPrimaryButton($termCheckbox, $taxonomyBox);

                $termCheckbox.click(function () {
                    displayTermPrimaryButton($termCheckbox, $taxonomyBox);
                    unsetPrimaryTerm($termCheckbox);
                });
            });

        });

        primaryTermButtonsActions();

        // Si un tag primaire est sélectionné, on l'affiche en tant que tel dans la metabox
        var setPrimaryTerms = function (field) {
            var $el = field.$el,
                taxName = $el.data('name').replace('primary_', ''),
                $postbox = $('#side-sortables').find('#' + taxName + 'div');

            $postbox.find('.categorychecklist li').each(function () {
                if ($(this).attr('id').replace(taxName + '-', '') == $el.find('.acf-input-wrap input').val()) {
                    $(this).find('.selectit').addClass('is-primary-term');
                    $(this).find('.set-primary-term').addClass('hide');
                    $(this).find('.unset-primary-term').removeClass('hide');
                }
            });
        }

        // Pour chaque champ de tag primaire, on récupère la valeur et on applique l'activation du terme principal
        var $primaryTaxFields = $('.acf-field-5d7bada38eedf').find('.acf-field[data-name^="primary_"]');
        $primaryTaxFields.each(function () {
            acf.addAction('load_field/name=' + $(this).data('name'), setPrimaryTerms);
        });
    });
})(jQuery);
