<?php
/**
 * The page template file
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 * @package HawwwaiTheme
 * @since HawwwaiTheme 1.0.0
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
$page_teaser = getAcfGroupFields(725);
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
$page_heading = [];
$page_heading = getAcfGroupFields(33);
if (empty($page_teaser['page_teaser_display_title'])) {
    $page_heading['title_as_h1'] = true;
}
if (!empty($page_heading)) {
    $context['page_heading'] = Timber::compile($context['woody_components'][$page_heading['heading_woody_tpl']], $page_heading);
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
            $the_header = Timber::compile($context['woody_components']['section-section_header-tpl_1'], $section);

            // On compile les données du footer de section
            $the_footer = Timber::compile($context['woody_components']['section-section_footer-tpl_1'], $section);

            // Pour chaque bloc d'une section, on compile les données dans un template Woody
            // Puis on les compile dans le template de grille Woody selectionné
            $components = [];

            if (!empty($section['section_content'])) {
                foreach ($section['section_content'] as $key => $layout) {
                    if ($layout['acf_fc_layout'] == 'manual_focus') {
                        $the_items = getManualFocus_data($layout['content_selection']);
                        $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $the_items);
                    } elseif ($layout['acf_fc_layout'] == 'auto_focus') {
                        $the_items = getAutoFocus_data($context['post'], $layout);
                        $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $the_items);
                    } elseif ($layout['acf_fc_layout'] == 'playlist_bloc') {
                        $playlist_conf_id = $layout['playlist_conf_id'];
                        $components['items'][] = apply_filters('wp_hawwwai_sit_playlist_render', $playlist_conf_id, 'fr');
                    } else {
                        if ($layout['acf_fc_layout'] == 'call_to_action' && !empty($layout['button']['add_modal'])) {
                            // On créé un id unique pour la modal si l'option pop-in est sélectionnée
                            $layout['modal_id'] = 'cta-modal' . uniqid();
                        }
                        if ($layout['acf_fc_layout'] == 'tabs_group') {
                            // On génère un ID pour le groupe de tabs
                            $layout['tabs_id'] = 'tabs-' . uniqid();
                            foreach ($layout['tabs'] as $key => $tab) {
                                $tab_content = [];
                                $layout['tabs'][$key]['tab_id'] = 'tab-' . uniqid();
                                // On compile les tpls woody pour chaque bloc ajouté dans l'onglet
                                if (!empty($tab['section_content'])) {
                                    foreach ($tab['section_content'] as $tab_layout) {
                                        $tab_content['items'][] = Timber::compile($context['woody_components'][$tab_layout['woody_tpl']], $tab_layout);
                                    }
                                    // On compile le tpl de grille woody choisi avec le DOM de chaque bloc
                                    $layout['tabs'][$key]['section_content'] = Timber::compile($context['woody_components'][$tab['tab_woody_tpl']], $tab_content);
                                }
                            }
                        }
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

            $context['the_sections'][] = Timber::compile($context['woody_components']['section-section_full-tpl_1'], $the_section);
        }
    }
}

// On rend le $context dans le page.twig
Timber::render('page.twig', $context);
