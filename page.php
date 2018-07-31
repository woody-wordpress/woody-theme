<?php
/**
 * The page template file
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

$context = Timber::get_context();

// Creating Timber object to access twig keys
$context['post'] = new TimberPost();

$context['woody_components'] = Woody::getTwigsPaths();

// rcd(get_class_methods(TimberPost), true);


/** ****************************
 * Compilation de l'en tête de page
 **************************** **/
$page_teaser = [];
$page_teaser = getAcfGroupFields('group_5b2bbb46507bf');
if (!empty($page_teaser['page_teaser_display_title'])) {
    $page_teaser['page_teaser_title'] = $context['post']->post_title;
}
$page_teaser['classes'] = $page_teaser['background_img_opacity'] . ' ' . $page_teaser['background_color'];
if (!empty($page_teaser['background_img'])) {
    $page_teaser['classes'] = $page_teaser['classes'] . ' isRel';
}
$page_teaser['breadcrumb'] = yoast_breadcrumb('<div class="breadcrumb-wrapper padd-top-sm padd-bottom-sm">', '</div>', false);

// rcd($page_teaser, true);

if (!empty($page_teaser)) {
    $context['page_teaser'] = Timber::compile($context['woody_components'][$page_teaser['page_teaser_woody_tpl']], $page_teaser);
}

/** ****************************
 * Compilation du visuel et accroche
 **************************** **/
$page_hero = [];
$page_hero = getAcfGroupFields('group_5b052bbee40a4');
// rcd($page_hero, true);
if (empty($page_teaser['page_teaser_display_title'])) {
    $page_hero['title_as_h1'] = true;
}
if (!empty($page_hero)) {
    $context['page_hero'] = Timber::compile($context['woody_components'][$page_hero['heading_woody_tpl']], $page_hero);
}

 /** ************************
  * Check type de publication
  ************************ **/
$page_type_term = wp_get_post_terms($context['post']->ID, 'page_type');
$page_type = $page_type_term[0]->slug;

if ($page_type === 'playlist_tourism') {
    /** ************************
    * Appel apirender pour récupérer le DOM de la playlist
    ************************ **/
    $playlist_conf_id = get_field('field_5b338ff331b17');
    $context['playlist_template'] = apply_filters('wp_hawwwai_sit_playlist_render', $playlist_conf_id, 'fr');
} else {
    /** ************************
    * Compilation des sections
    ************************ **/
    $context['sections'] = [];
    $sections = $context['post']->get_field('section');

    if (!empty($sections)) {
        // Foreach section, fill vars to display in the woody's components
        foreach ($sections as $key => $section) {
            // On compile les données du header de section
            $the_header = Timber::compile($context['woody_components']['section-section_header-tpl_01'], $section);

            // On compile les données du footer de section
            $the_footer = Timber::compile($context['woody_components']['section-section_footer-tpl_01'], $section);

            // Pour chaque bloc d'une section, on compile les données dans un template Woody
            // Puis on les compile dans le template de grille Woody selectionné
            $components = [];
            $components['no_padding'] = $section['scope_no_padding'];

            if (!empty($section['section_content'])) {
                foreach ($section['section_content'] as $key => $layout) {

                    switch ($layout['acf_fc_layout']) {
                        case 'manual_focus':
                            $the_items = getManualFocus_data($layout['content_selection']);
                            $the_items['focus_no_padding'] = $layout['focus_no_padding'];
                            $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $the_items);
                        break;
                        case 'auto_focus':
                            $the_items = getAutoFocus_data($context['post'], $layout);
                            $the_items['focus_no_padding'] = $layout['focus_no_padding'];
                            $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $the_items);
                        break;
                        case 'playlist_bloc':
                            $playlist_conf_id = $layout['playlist_conf_id'];
                            $components['items'][] = apply_filters('wp_hawwwai_sit_playlist_render', $playlist_conf_id, 'fr');
                        break;
                        case 'call_to_action' :
                            // On créé un id unique pour la modal si l'option pop-in est sélectionnée
                            if(!empty($layout['button']['add_modal'])){
                                $layout['modal_id'] = 'cta-' . uniqid();
                            }
                            $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                        break;
                        case 'tabs_group' :
                            $layout['tabs'] = nestedGridsComponents($layout['tabs'], 'tab_woody_tpl', 'tabs');
                            $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                        break;
                        case 'slides_group' :
                            $layout['slides'] = nestedGridsComponents($layout['slides'], 'slide_woody_tpl');
                            $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                        break;
                        case 'socialwall' :
                            $layout['gallery_items'] = [];
                            if($layout['socialwall_type'] == 'manual'){
                                foreach ($layout['socialwall_manual'] as $key => $media_item) {
                                    // On ajoute une entrée "gallery_items" pour être compatible avec le tpl woody
                                    $layout['gallery_items'][] = $media_item;
                                }
                            } elseif($layout['socialwall_type'] == 'auto'){
                                // On récupère les termes sélectionnés
                                $queried_terms = [];
                                foreach ($layout['socialwall_auto'] as $key => $term) {
                                    $queried_terms[] =  $term;
                                }
                                // On récupère les images en fonction des termes sélectionnés
                                $attachments = getAttachmentsByTerms('media_category', $queried_terms);
                                // On transforme chacune des images en objet image ACF pour être compatible avec le tpl Woody
                                foreach ($attachments->posts as $key => $attachment) {
                                    $attachment = acf_get_attachment($attachment);
                                    $layout['gallery_items'][] = $attachment;
                                }
                            }
                            $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                        break;
                        default:
                            $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                    }
                }

                if (!empty($section['woody_tpl'])) {
                    $the_layout = Timber::compile($context['woody_components'][$section['woody_tpl']], $components);
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

// On rend le $context dans le page.twig
Timber::render('page.twig', $context);
