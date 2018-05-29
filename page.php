<?php

$context = Timber::get_context();

// Creating Timber object to access twig keys
$context['post'] = new TimberPost();

// Creating object Timber Image for post thumbnail
// $context['post']->thumbnail = new TimberImage($post->_thumbnail_id);

// Declare Woody and get the list of woody components
$woody = new Woody();
$context['woody_components'] = $woody->getTwigsPaths();

/** ****************************
 * Displaying the page's heading
 **************************** **/


/** ************************
 * Displaying the sections
 ************************ **/
// Create a empty array to fill with rendered twig components and get the sections of the page
$context['sections'] = [];
$sections = $context['post']->get_field('section');

// Foreach section, fill vars to display in the woody's components
foreach ($sections as $key => $section) {

    // Send $section to section's header tpl
    $the_header = Timber::compile($context['woody_components']['section-section_header-tpl_1'], $section);

    // Send $section to section's footer tpl
    $the_footer = Timber::compile($context['woody_components']['section-section_footer-tpl_1'], $section);

    // Creating data for display options => set the container classes
    $classes_array = [];
    if(empty($section['display_fullwidth'])){
        $classes_array[] = 'grid-container';
    }

    if(!empty($section['background_img'])){
        $display['background_img'] = $section['background_img'];
        $classes_array[] = 'isRel';
    }

    if(!empty($section['background_color'])){
        $classes_array[] = $section['background_color'];
    }
    if(!empty($section['background_img_opacity'])){
        $classes_array[] = $section['background_img_opacity'];
    }
    if(!empty($section['section_paddings']['section_padding_top'])){
        $classes_array[] = $section['section_paddings']['section_padding_top'];
    }
    if(!empty($section['section_paddings']['section_padding_bottom'])){
        $classes_array[] = $section['section_paddings']['section_padding_bottom'];
    }
    if(!empty($section['section_margins']['section_margin_top'])){
        $classes_array[] = $section['section_margins']['section_margin_top'];
    }
    if(!empty($section['section_margins']['section_margin_bottom'])){
        $classes_array[] = $section['section_margins']['section_margin_bottom'];
    }

    // Implode classes
    $display['classes'] = implode(' ', $classes_array);

    // Render the section layout with rendered woody's components
    $components = [];

    // Get every section_content's layouts in the post
    if(!empty($section['section_content']))
    foreach ($section['section_content'] as $key => $layout) {
        $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);

    }

    if(!empty($section['woody_tpl'])){
        $the_layout = Timber::compile($context['woody_components'][$section['woody_tpl']], $components);
    }

    // Fill $the_section var with rendered woody's components to fill $context['the_sections']
    $the_section = [
        'header' => $the_header,
        'footer' => $the_footer,
        'layout' => $the_layout,
        'display' => $display,
    ];
    $context['the_sections'][] = Timber::compile($context['woody_components']['section-section_full-tpl_1'], $the_section);

        // ** DEBUG **//
        // print '<pre>';
        // print_r($display);
        // exit();
        // ** DEBUG **//

}

// Render the $context in page.twig
Timber::render('page.twig', $context);
