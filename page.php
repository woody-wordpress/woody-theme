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
// PC::debug(get_class_methods(TimberPost), 'TwigMethods');

$context['site_config'] = [];
$context['site_config']['site_key'] = WP_SITE_KEY;
$credentials = get_option('woody_credentials');
if (!empty($credentials['public_login']) && !empty($credentials['public_password'])) {
    $context['site_config']['login'] = $credentials['public_login'];
    $context['site_config']['password'] = $credentials['public_password'];
    $context['site_config'] = json_encode($context['site_config']);
}

include get_template_directory() . '/header.php';

$context['current_url'] = get_permalink();
$context['active_social_shares'] = getActiveShares();

/** ****************************
 * Compilation de l'en tête de page
 **************************** **/
$page_teaser = [];
$page_teaser = getAcfGroupFields('group_5b2bbb46507bf');
if (!empty($page_teaser)) {
    if (!empty($page_teaser['page_teaser_display_title'])) {
        $page_teaser['page_teaser_title'] = $context['post']->post_title;
    }
    $page_teaser['the_classes'] = [];
    if (!empty($page_teaser['background_img_opacity'])) {
        $page_teaser['the_classes'][] = $page_teaser['background_img_opacity'];
    }

    if (!empty($page_teaser['background_color'])) {
        $page_teaser['the_classes'][] = $page_teaser['background_color'];
    }

    if (!empty($page_teaser['teaser_margin_bottom'])) {
        $page_teaser['the_classes'][] = $page_teaser['teaser_margin_bottom'];
    }

    if (!empty($page_teaser['background_img'])) {
        $page_teaser['the_classes'][] = 'isRel';
    }

    $page_teaser['classes'] = implode(' ', $page_teaser['the_classes']);

    $page_teaser['breadcrumb'] = yoast_breadcrumb('<div class="breadcrumb-wrapper padd-top-sm padd-bottom-sm">', '</div>', false);

    $context['page_teaser'] = Timber::compile($context['woody_components'][$page_teaser['page_teaser_woody_tpl']], $page_teaser);
}

$context['page_type'] = getTermsSlugs($context['post']->ID, 'page_type', true);

/** ****************************
 * Compilation du visuel et accroche
 **************************** **/
$page_hero = [];
$page_hero = getAcfGroupFields('group_5b052bbee40a4');
if (($page_hero['page_heading_media_type'] == 'movie' && !empty($page_hero['page_heading_movie']) || ($page_hero['page_heading_media_type'] == 'img' && !empty($page_hero['page_heading_img'])))) {
    if (empty($page_teaser['page_teaser_display_title'])) {
        $page_hero['title_as_h1'] = true;
    }
    if ($page_hero['page_heading_img']) {
        $page_hero['page_heading_img']['attachment_more_data'] = getAttachmentMoreData($page_hero['page_heading_img']['ID']);
    }
    $context['page_hero'] = Timber::compile($context['woody_components'][$page_hero['heading_woody_tpl']], $page_hero);
}

/** ****************************
 * Compilation du bloc prix
 **************************** **/
$trip_infos = [];
if (!empty(getAcfGroupFields('group_5b6c5e6ff381d'))) {
    $trip_infos = getAcfGroupFields('group_5b6c5e6ff381d');
    //TODO: Gérer le fichier gps pour affichage s/ carte
    if ($trip_infos['the_duration']['count_days']) {
        $trip_infos['the_duration']['count_days'] = humanDays($trip_infos['the_duration']['count_days']);
    }
    $context['trip_infos'] = Timber::compile($context['woody_components'][$trip_infos['tripinfos_woody_tpl']], $trip_infos);
}

 /** ************************
  * Check type de publication
  ************************ **/

if ($context['page_type'] === 'playlist_tourism') {
    include 'inc-touristic-playlist.php';
} else {
    /** ************************
    * Compilation des sections
    ************************ **/
    $context['sections'] = [];
    $sections = $context['post']->get_field('section');

    if (!empty($sections)) {
        foreach ($sections as $key => $section) {
            $the_header = '';
            $the_footer = '';

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

            if (!empty($section['section_content'])) {
                foreach ($section['section_content'] as $key => $layout) {
                    switch ($layout['acf_fc_layout']) {
                        case 'manual_focus':
                        case 'auto_focus':
                            if ($layout['acf_fc_layout'] == 'manual_focus') {
                                $the_items = getManualFocus_data($layout);
                            } else {
                                $the_items = getAutoFocus_data($context['post'], $layout);
                            }
                            $the_items['focus_no_padding'] = $layout['focus_no_padding'];
                            $the_items['block_titles'] = getFocusBlockTitles($layout);
                            $the_items['display_button'] = $layout['display_button'];

                            $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $the_items);
                        break;
                        case 'playlist_bloc':
                            $playlist_conf_id = $layout['playlist_conf_id'];
                            $components['items'][] = apply_filters('wp_hawwwai_sit_playlist_render', $playlist_conf_id, 'fr');
                        break;
                        case 'call_to_action':
                            // On créé un id unique pour la modal si l'option pop-in est sélectionnée
                            if (!empty($layout['cta_button_group']['add_modal'])) {
                                $layout['modal_id'] = 'cta-' . uniqid();
                            }
                            $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                        break;
                        case 'tabs_group':
                            $layout['tabs'] = nestedGridsComponents($layout['tabs'], 'tab_woody_tpl', 'tabs');
                            $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                        break;
                        case 'slides_group':
                            $layout['slides'] = nestedGridsComponents($layout['slides'], 'slide_woody_tpl');
                            $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                        break;
                         case 'gallery':
                            foreach ($layout['gallery_items'] as $key => $media_item) {
                                // On ajoute une entrée "gallery_items" pour être compatible avec le tpl woody
                                $layout['gallery_items'][$key]['attachment_more_data'] = getAttachmentMoreData($media_item['ID']);
                            }
                            $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                        break;
                        case 'socialwall':
                            $layout['gallery_items'] = [];
                            if ($layout['socialwall_type'] == 'manual') {
                                foreach ($layout['socialwall_manual'] as $key => $media_item) {
                                    // On ajoute une entrée "gallery_items" pour être compatible avec le tpl woody
                                    $layout['gallery_items'][] = $media_item;
                                    $layout['gallery_items'][$key]['attachment_more_data'] = getAttachmentMoreData($media_item['ID']);
                                }
                            } elseif ($layout['socialwall_type'] == 'auto') {
                                // On récupère les termes sélectionnés
                                $queried_terms = [];
                                foreach ($layout['socialwall_auto'] as $key => $term) {
                                    $queried_terms[] =  $term;
                                }
                                // On récupère les images en fonction des termes sélectionnés
                                $layout['gallery_items'] = getAttachmentsByTerms('media_category', $queried_terms);

                                foreach ($layout['gallery_items'] as $key => $media_item) {
                                    $layout['gallery_items'][$key]['attachment_more_data'] = getAttachmentMoreData($media_item['ID']);
                                }
                            }
                            $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                        break;
                        default:
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
