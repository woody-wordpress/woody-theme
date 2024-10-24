!(function ($) {
    $('body.themes-php').each(function () {
        $('.theme-actions').remove();
    });

    // Bugfix  Enhanced media buttons
    if (window.wp !== undefined && window.wp.media !== undefined && window.wp.media.view !== undefined && window.wp.media.view.l10n !== undefined) {
        window.wp.media.view.l10n.cancelSelection = window.wp.media.view.l10n.cancel;
        window.wp.media.view.l10n.trashSelected = window.wp.media.view.l10n.trash;
        window.wp.media.view.l10n.deleteSelected = window.wp.media.view.l10n.deletePermanently;
    }
})(jQuery);
