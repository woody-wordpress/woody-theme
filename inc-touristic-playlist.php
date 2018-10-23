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


$context['custom_body_classes'] = 'apirender apirender-playlist apirender-wordpress';

/** ************************
 * Appel apirender pour récupérer le DOM de la playlist
 ************************ **/

$playlistConfId = get_field('field_5b338ff331b17');

$checkMethod = !empty($_POST) ? INPUT_POST : INPUT_GET;

// allowed parameters for Wordpress playlists need to be added here
$checkParams = [

    // page number (12 items by page)
    'page'   => [
        'filter' => FILTER_VALIDATE_INT,
        'flags'  => [FILTER_REQUIRE_SCALAR, FILTER_NULL_ON_FAILURE],
        'options'   => ['min_range' => 1]
    ],

    // id of created facet autoselection returning filtered playlist
    'autoselect_id'   => [
        'filter' => FILTER_VALIDATE_INT,
        'flags'  => [FILTER_REQUIRE_SCALAR, FILTER_NULL_ON_FAILURE],
    ],
];

// build query in validated array
$query = filter_input_array($checkMethod, $checkParams, $add_non_existing = false);
// \PC::debug($query);

// Get from Apirender
$partialPlaylist = apply_filters('wp_woody_hawwwai_playlist_render', $playlistConfId, 'fr', $query);
if (!$partialPlaylist) {
    print_r('error fetching playlist');
    exit;
}

$playlistId = isset($partialPlaylist['playlistId']) ? $partialPlaylist['playlistId'] : null; // returned by apirender
$apiRenderUri = isset($partialPlaylist['apirender_uri']) ? $partialPlaylist['apirender_uri'] : null; // returned by plugin woody fn


/***************************
 * Configuration des HTTP headers
 *****************************/
function add_sheet_headers()
{
    global $playlistId;
    global $apiRenderUri;
    global $partialPlaylist;

    header('Vary : Cookie, Accept-Encoding');
    header('Cache-Control: no-cache, no-store, must-revalidate, max-age = 0');
    if (!is_admin()) {
        header('Cache-Control: public, max-age=604800, must-revalidate');
    }
    header('Last-Modified: ' .gmdate('D, d M Y H:i:s', strtotime($partialPlaylist['modified'])).' GMT', false);
    if (!empty($playlistId)) {
        header('x-ts-idplaylist: ' .$playlistId);
    }
    if (!empty($apiRenderUri)) {
        header('x-apirender-url: ' .$apiRenderUri);
    }
};

add_action('send_headers', 'add_sheet_headers', 10, 1);
do_action('send_headers', 'add_sheet_headers');


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
