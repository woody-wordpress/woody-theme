<?php
/**
 * The page template file
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

$context = Timber::get_context();
$context['post'] = new TimberPost();
$context['woody_components'] = Woody::getTwigsPaths();
// rcd(get_class_methods(TimberPost), true);

/** ****************************
 * Compilation de l'en tête de page
 **************************** **/
// $page_teaser = [];
// $page_teaser = getAcfGroupFields('group_5b2bbb46507bf');
// if (!empty($page_teaser)) {
//     if (!empty($page_teaser['page_teaser_display_title'])) {
//         $page_teaser['page_teaser_title'] = $context['post']->post_title;
//     }
//     $page_teaser['classes'] = $page_teaser['background_img_opacity'] . ' ' . $page_teaser['background_color'];
//     if (!empty($page_teaser['background_img'])) {
//         $page_teaser['classes'] = $page_teaser['classes'] . ' isRel';
//     }
//     $page_teaser['breadcrumb'] = yoast_breadcrumb('<div class="breadcrumb-wrapper padd-top-sm padd-bottom-sm">', '</div>', false);

//     $context['page_teaser'] = Timber::compile($context['woody_components'][$page_teaser['page_teaser_woody_tpl']], $page_teaser);
// }

/** ****************************
 * Compilation du visuel et accroche
 **************************** **/
// $page_hero = [];
// $page_hero = getAcfGroupFields('group_5b052bbee40a4');
// if (!empty($page_hero)) {
//     if (empty($page_teaser['page_teaser_display_title'])) {
//         $page_hero['title_as_h1'] = true;
//     }
//     $context['page_hero'] = Timber::compile($context['woody_components'][$page_hero['heading_woody_tpl']], $page_hero);
// }


$params = [];

// override Body Classes
$context['body_class'] .= ' apirender apirender-wordpress';

$sheet_id = $context['post']->touristic_sheet_id;
$sheet_lang = $context['post']->touristic_sheet_lang;
$season = null;
$sheet_lang = rc_clean_season($sheet_lang);

// TODO
// $seasons_languages = variable_get('seasons_languages', array());
// if(!empty($seasons_languages['languages_winter'][$sheet_lang])) $season = 'winter';
// elseif(!empty($seasons_languages['languages_summer'][$sheet_lang])) $season = 'summer';

// Set season param if required
if (!is_null($season)) {
    $params['season'] = $season;
}

$context['lang'] = $sheet_lang;
$context['fetcherType'] = 'website_'.WP_ENV;
$context['destinationName'] = null;
$context['playlistId'] = null;


// Get API auth data
$credentials = get_option('woody_credentials');
if (!empty($credentials)) {
    $context['apiLogin'] = $credentials['public_login'];
    $context['apiPassword'] = $credentials['public_password'];
} else {
    print_r('No API wp_woody_hawwwai_sheet_render set');
    exit;
}

/** ************************
 * Appel apirender pour récupérer le DOM de la fiche
 ************************ **/
$partialSheet = apply_filters('wp_woody_hawwwai_sheet_render', $sheet_id, $sheet_lang, $params);
if (empty($partialSheet)) {
    print_r('Error while fetching API Render content for Sheet #' .$sheet_id);
    exit;
}

/***************************
 * Configuration des HTTP headers
 *****************************/
function add_sheet_headers()
{
    global $sheet_id;
    global $partialSheet;

    header('Vary : Cookie, Accept-Encoding');
    header('Cache-Control: no-cache, no-store, must-revalidate, max-age = 0');
    if (!is_admin()) {
        header('Cache-Control: public, max-age=604800, must-revalidate');
    }
    header('Last-Modified: ' .gmdate('D, d M Y H:i:s', strtotime($partialSheet['modified'])).' GMT', false);
    header('x-ts-idfiche: ' .$sheet_id);
};

add_action('send_headers', 'add_sheet_headers', 10, 1);
do_action('send_headers', 'add_sheet_headers');

// Set METAS
// TODO (Doubled set metas (apirender & wordpress))
$context['metas'] = [];
foreach ($partialSheet['metas'] as $key_meta => $meta) {
    $tag = '<'.$meta['#tag'];
    foreach ($meta['#attributes'] as $key_attr => $attribute) {
        $tag .= ' '.$key_attr.'="'.$attribute.'"';
    }
    $tag .= ' />';
    $context['metas'][] = $tag;
}

// $KEYS = getMapKeys($javascript=true);

/**** *********************** ****
 ****   Print full template
 **** ************************** **/
$context['sheet_template'] = $partialSheet['content'];
if (!empty($context['sheet_template'])) {
    // On rend le $context dans le touristic_sheet.twig
    Timber::render('touristic_sheet.twig', $context);
} else {
    // \PC::Debug($context['post']);
    print_r('error fetching sheet');
    exit;
}
