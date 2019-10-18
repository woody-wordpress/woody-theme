import $ from 'jquery';

$('body.themes-php').each(function() {
    $('.theme-actions').remove();
});

// Bugfix  Enhanced media buttons
if (typeof window.wp.media != 'undefined' && typeof window.wp.media.l10 != 'undefined') {
    window.wp.media.view.l10n.cancelSelection = window.wp.media.view.l10n.cancel;
    window.wp.media.view.l10n.trashSelected = window.wp.media.view.l10n.trash;
    window.wp.media.view.l10n.deleteSelected = window.wp.media.view.l10n.deletePermanently;
}
