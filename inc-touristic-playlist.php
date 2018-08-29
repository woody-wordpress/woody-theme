<?php

/**
 * The playlist process file (need to be included in page.php)
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 *
 *  Timber $context variable available
 */


$context['body_class'] .= ' apirender apirender-playlist apirender-wordpress';

/** ************************
 * Appel apirender pour récupérer le DOM de la playlist
 ************************ **/
$playlist_conf_id = get_field('field_5b338ff331b17');
$partialPlaylist = apply_filters('wp_woody_hawwwai_playlist_render', $playlist_conf_id, 'fr');

if (!$partialPlaylist) {
    print_r('error fetching playlist');
    exit;
}

/***************************
 * Configuration des HTTP headers
 *****************************/
function add_sheet_headers()
{
    // global $playlist_id;
    global $partialPlaylist;

    header('Vary : Cookie, Accept-Encoding');
    header('Cache-Control: no-cache, no-store, must-revalidate, max-age = 0');
    if (!is_admin()) {
        header('Cache-Control: public, max-age=604800, must-revalidate');
    }
    header('Last-Modified: ' .gmdate('D, d M Y H:i:s', strtotime($partialPlaylist['modified'])).' GMT', false);
    // header('x-ts-idplaylist: ' .$playlist_id);
};

// add_action('send_headers', 'add_sheet_headers', 10, 1);
// do_action('send_headers', 'add_sheet_headers');

/************** TEST *************************************/
if (PL_CONF_TESTING !== null) {
    $context['PL_CONF_TESTING'] = json_decode(PL_CONF_TESTING, true);
}
/***********************************************/


/**** *********************** ****
 ****   Print full template
 **** ************************** **/
$context['playlist_template'] = $partialPlaylist['content'];
if (!empty($context['playlist_template'])) {
    // On rend le $context dans le touristic_playlist.twig
    Timber::render('touristic_playlist.twig', $context);
} else {
    print_r('error fetching playlist');
    exit;
}

exit;
