import $ from 'jquery';

// On ferme toutes les metaboxes ACF
$('.acf-postbox').addClass('closed');

// On masque les metaboxes de taxonomies dans l'edition des posts (on les rajoutera ensuite dans des champs ACF)
$('[id^="tagsdiv-"').hide();

// On referme les metaboxes par défaut sur l'édition d'un post
$('#pageparentdiv, #revisionsdiv, #wpseo_meta, #members-cp').addClass('closed');

// On toggle la description de chaque template dans les champs woody_tpl
$('.tpl-choice-wrapper').each(function() {
    var $this = $(this);

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
