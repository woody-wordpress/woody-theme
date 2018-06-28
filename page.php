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
 * Compilation de l'en-tête de page
 **************************** **/
$page_heading = [];
$page_heading['content'] = get_field('field_5b052bbab3867');
$page_heading['media_type'] = get_field('field_5b0e5cc3d4b1a');

if ($page_heading['media_type'] == 'img') {
    $page_heading['media'] = get_field('field_5b0e5ddfd4b1b');
} else {
    $page_heading['media'] = get_field('field_5b0e5df0d4b1c');
}

$page_heading['title_as_h1'] = get_field('field_5b0e54ebfa657');
$page_heading['classes'] = get_field('field_5b0e5ef78f6be');

$page_heading_tpl = get_field('field_5b052d70ea19b');

$context['page_heading'] = Timber::compile($context['woody_components'][$page_heading_tpl], $page_heading);

/** ************************
 * Compilation des sections
 ************************ **/
rcd($context['post']->get_field('section'), true);
$context['sections'] = [];
$sections = $context['post']->get_field('section');

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
                // TODO : Renvoyer vers la fonction d'appel à l'API render
                print 'Ceci est une playlist d\'objets touristiques';
            } else {
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

// Render the $context in page.twig
Timber::render('page.twig', $context);
