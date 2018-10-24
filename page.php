<?php
/**
 * The page template file
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

$context = Timber::get_context();
$context['title'] = wp_title(null, false);
$context['post'] = new TimberPost();
$context['woody_components'] = getWoodyTwigPaths();
// PC::debug(get_class_methods(TimberPost), 'TwigMethods');

$context['site_config'] = [];
$context['site_config']['site_key'] = WP_SITE_KEY;
$credentials = get_option('woody_credentials');
if (!empty($credentials['public_login']) && !empty($credentials['public_password'])) {
    $context['site_config']['login'] = $credentials['public_login'];
    $context['site_config']['password'] = $credentials['public_password'];
    $context['site_config'] = json_encode($context['site_config']);
}

// Icons
$icons = ['favicon', '16', '32', '64', '120', '128', '152', '167', '180', '192'];
foreach ($icons as $icon) {
    $icon_ext = ($icon == 'favicon') ? $icon . '.ico' : 'favicon.' . $icon . 'w-' . $icon . 'h.png';
    if (file_exists(WP_CONTENT_DIR . '/dist/' . WP_SITE_KEY . '/favicon/' . $icon_ext)) {
        $context['icons'][$icon] = WP_HOME . '/app/dist/' . WP_SITE_KEY . '/favicon/' . $icon_ext;
    }
}

$context['current_url'] = get_permalink();
$context['active_social_shares'] = getActiveShares();
$context['page_type'] = getTermsSlugs($context['post']->ID, 'page_type', true);
if (class_exists('SubWoodyTheme_TemplateParts')) {
    $SubWoodyTheme_TemplateParts = new SubWoodyTheme_TemplateParts();
    $context['page_parts'] = $SubWoodyTheme_TemplateParts->getParts();
}

/*********************************************
 * Compilation du Diaporama en page d'accueil
 *********************************************/
$home_slider = getAcfGroupFields('group_5bb325e8b6b43');
if (!empty($home_slider['landswpr_slides'])) {
    $context['home_slider'] = Timber::compile($context['woody_components'][$home_slider['landswpr_woody_tpl']], $home_slider);
}

/*********************************************
 * Compilation du bloc prix
 *********************************************/

$trip_infos = getAcfGroupFields('group_5b6c5e6ff381d');
if (!empty($trip_infos['the_duration']['count_days']) || !empty($trip_infos['the_length']['length']) || !empty($trip_infos['the_price']['price'])) {
    //TODO: Gérer le fichier gps pour affichage s/ carte
    $trip_infos['the_duration']['count_days'] = ($trip_infos['the_duration']['count_days']) ? humanDays($trip_infos['the_duration']['count_days']) : '';
    $trip_infos['the_price']['price'] = (!empty($trip_infos['the_price']['price'])) ? str_replace('.', ',', $trip_infos['the_price']['price']) : '';
    $context['trip_infos'] = Timber::compile($context['woody_components'][$trip_infos['tripinfos_woody_tpl']], $trip_infos);
} else {
    $trip_infos = [];
}

/*********************************************
 * Compilation de l'en tête de page
 *********************************************/
$page_teaser = [];
$page_teaser = getAcfGroupFields('group_5b2bbb46507bf');
if (!empty($page_teaser)) {
    $page_teaser['page_teaser_title'] = (!empty($page_teaser['page_teaser_display_title'])) ? $context['post']->post_title : '';
    $page_teaser['the_classes'] = [];
    $page_teaser['the_classes'][] = (!empty($page_teaser['background_img_opacity'])) ? $page_teaser['background_img_opacity'] : '';
    $page_teaser['the_classes'][] = (!empty($page_teaser['background_color'])) ? $page_teaser['background_color'] : '';
    $page_teaser['the_classes'][] = (!empty($page_teaser['border_color'])) ? $page_teaser['border_color'] : '';
    $page_teaser['the_classes'][] =  (!empty($page_teaser['teaser_margin_bottom'])) ? $page_teaser['teaser_margin_bottom'] : '';
    $page_teaser['the_classes'][] = (!empty($page_teaser['background_img'])) ? 'isRel' : '';
    $page_teaser['classes'] = (!empty($page_teaser['the_classes'])) ? implode(' ', $page_teaser['the_classes']) : '';
    $page_teaser['breadcrumb'] = yoast_breadcrumb('<div class="breadcrumb-wrapper padd-top-sm padd-bottom-sm">', '</div>', false);
    $page_teaser['trip_infos'] = (!empty($context['trip_infos'])) ? $context['trip_infos'] : '';
    if (!empty($page_teaser['page_teaser_media_type']) && $page_teaser['page_teaser_media_type'] == 'map') {
        $page_teaser['post_coordinates'] = (!empty(getAcfGroupFields('group_5b3635da6529e'))) ? getAcfGroupFields('group_5b3635da6529e') : '';
    }
    $context['page_teaser'] = Timber::compile($context['woody_components'][$page_teaser['page_teaser_woody_tpl']], $page_teaser);
}

/*********************************************
 * Compilation du visuel et accroche
 *********************************************/
$page_hero = [];
$page_hero = getAcfGroupFields('group_5b052bbee40a4');
if (!empty($page_hero['page_heading_media_type']) && ($page_hero['page_heading_media_type'] == 'movie' && !empty($page_hero['page_heading_movie']) || ($page_hero['page_heading_media_type'] == 'img' && !empty($page_hero['page_heading_img'])))) {
    //TODO: A vérifier => vu une incohérence sur un site !
    if (empty($page_teaser['page_teaser_display_title'])) {
        $page_hero['title_as_h1'] = true;
    }

    $page_hero['page_heading_img']['attachment_more_data'] = (!empty($page_hero['page_heading_img'])) ? getAttachmentMoreData($page_hero['page_heading_img']['ID']) : '';
    if (!empty($page_hero['page_heading_social_movie'])) {
        preg_match_all('@src="([^"]+)"@', $page_hero['page_heading_social_movie'], $result);
        $iframe_url = $result[1][0];
        if (strpos($iframe_url, 'youtube') != false) {
            $yt_params_url = $iframe_url . '?&autoplay=0&rel=0';
            $page_hero['page_heading_social_movie'] = str_replace($iframe_url, $yt_params_url, $page_hero['page_heading_social_movie']);
        }
    }
    $context['page_hero'] = Timber::compile($context['woody_components'][$page_hero['heading_woody_tpl']], $page_hero);
}

/*********************************************
 * Check type de publication
 *********************************************/

if ($context['page_type'] === 'playlist_tourism') {
    include 'inc-touristic-playlist.php';
} else {
    /*********************************************
    * Compilation des sections
    *********************************************/
    $context['sections'] = [];
    $sections = $context['post']->get_field('section');

    if (!empty($sections)) {
        foreach ($sections as $section_id => $section) {
            $the_header = '';
            $the_footer = '';
            $the_layout = '';

            if (!empty($section['icon']) || !empty($section['pretitle']) || !empty($section['title']) || !empty($section['subtitle']) || !empty($section['description'])) {
                $the_header = Timber::compile($context['woody_components']['section-section_header-tpl_01'], $section);
            }
            if (!empty($section['links'])) {
                $the_footer = Timber::compile($context['woody_components']['section-section_footer-tpl_01'], $section);
            }

            // Pour chaque bloc d'une section, on compile les données dans un template Woody
            // Puis on les compile dans le template de grille Woody selectionné
            $components = [];
            $components['no_padding'] = $section['scope_no_padding'];
            $components['alignment'] = (!empty($section['section_alignment'])) ? $section['section_alignment'] : '';

            if (!empty($section['section_content'])) {
                foreach ($section['section_content'] as $layout_id => $layout) {
                    $layout['uniqid'] = 'section_' . $section_id . '_' . 'section_content_' . $layout_id;
                    $layout['visual_effects'] = (!empty($layout['visual_effects'])) ? formatVisualEffectData($layout['visual_effects']) : '';

                    switch ($layout['acf_fc_layout']) {

                        case 'manual_focus':
                        case 'auto_focus':
                        case 'auto_focus_sheets':
                            $components['items'][] = formatFocusesData($layout, $context['post'], $context['woody_components']);
                        break;
                        case 'geo_map':
                            $components['items'][] = formatGeomapData($layout, $context['woody_components']);
                        break;
                        case 'content_list':
                            $components['items'][] = formatFullContentList($layout, $context['post'], $context['woody_components']);
                        break;
                        default:
                            if ($layout['acf_fc_layout'] == 'playlist_bloc') {
                                $playlist_conf_id = $layout['playlist_conf_id'];
                            }
                            if ($layout['acf_fc_layout'] == 'call_to_action') {
                                if (!empty($layout['cta_button_group']['add_modal'])) {
                                    $layout['modal_id'] = 'cta-' . uniqid();
                                }
                            }
                            if ($layout['acf_fc_layout'] == 'tabs_group') {
                                $layout['tabs'] = nestedGridsComponents($layout['tabs'], 'tab_woody_tpl', 'tabs');
                            }
                             if ($layout['acf_fc_layout'] == 'slides_group') {
                                 $layout['slides'] = nestedGridsComponents($layout['slides'], 'slide_woody_tpl');
                             }
                             if ($layout['acf_fc_layout'] == 'gallery') {
                                 foreach ($layout['gallery_items'] as $key => $media_item) {
                                     $layout['gallery_items'][$key]['attachment_more_data'] = getAttachmentMoreData($media_item['ID']);
                                 }
                             }
                             if ($layout['acf_fc_layout'] == 'socialwall') {
                                 $layout['gallery_items'] = [];
                                 if ($layout['socialwall_type'] == 'manual') {
                                     foreach ($layout['socialwall_manual'] as $key => $media_item) {
                                         // On ajoute une entrée "gallery_items" pour être compatible avec le tpl woody
                                         $layout['gallery_items'][] = $media_item;
                                         $layout['gallery_items'][$key]['attachment_more_data'] = getAttachmentMoreData($media_item['ID']);
                                     }
                                 } elseif ($layout['socialwall_type'] == 'auto') {
                                     // On récupère les images en fonction des termes sélectionnés
                                     $layout['gallery_items'] = (!empty($layout['socialwall_auto'])) ? getAttachmentsByTerms('attachment_hashtags', $layout['socialwall_auto']) : '';
                                     if (!empty($layout['gallery_items'])) {
                                         foreach ($layout['gallery_items'] as $key => $media_item) {
                                             $layout['gallery_items'][$key]['attachment_more_data'] = getAttachmentMoreData($media_item['ID']);
                                         }
                                     }
                                 }
                             }

                            $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                    }
                }

                if (!empty($section['section_woody_tpl'])) {
                    $the_layout = Timber::compile($context['woody_components'][$section['section_woody_tpl']], $components);
                }
            }

            // On récupère les données d'affichage personnalisables
            $display = getDisplayOptions($section);

            // On ajoute les 3 parties compilées d'une section + ses paramètres d'affichage
            // puis on compile le tout dans le template de section Woody
            $the_section = [
                'header' => $the_header,
                'footer' => $the_footer,
                'layout' => $the_layout,
                'display' => $display,
            ];

            $context['the_sections'][] = Timber::compile($context['woody_components']['section-section_full-tpl_01'], $the_section);
        }
    }
}

if (!empty(is_front_page())) {
    $template = 'front.twig';
} else {
    $template = 'page.twig';
}

// On rend le $context dans le page.twig
Timber::render($template, $context);
