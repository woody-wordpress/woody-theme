<?php

$context = Timber::get_context();

// Creating Timber object to access twig keys
$context['post'] = new TimberPost();

// Creating object Timber Image for post thumbnail
// $context['post']->thumbnail = new TimberImage($post->_thumbnail_id);

// Declare Woody and get the list of woody components
$woody = new Woody();
$context['woody_components'] = $woody->getTwigsPaths();

// Create a empty array to fill with rendered twig components and get the sections of the page
$context['sections'] = [];
$sections = $context['post']->get_field('section');

foreach ($sections as $key => $section) {

    // Foreach section, fill vars to display in the woody's components

    // Send $section to section's header tpl
    $the_header = Timber::compile($context['woody_components']['section-section_header-tpl_1'], $section);

    // Send $section to section's footer tpl
    $the_footer = Timber::compile($context['woody_components']['section-section_footer-tpl_1'], $section);

    // Creating data for display options
    $display = [
        'background_img' => (!empty($section['background_img'])) ? $section['background_img'] : '',
    ];

    $classes_array = [];

    // Set the container class
    if(empty($section['display_fullwidth'])){
        $classes_array[] = 'grid-container';
    }

    // Implode classes
    $display['classes'] = implode(' ', $classes_array);

    // Render the section layout with rendered woody's components
    $components = [];
    if(!empty($section['section_layout'])){
        $the_layout = Timber::compile($context['woody_components'][$section['section_layout']], $components);
    }

    // Fill $the_section var with rendered woody's components to fill $context['the_sections']
    $the_section = [
        'header' => $the_header,
        'footer' => $the_footer,
        'layout' => $the_layout,
        'display' => $display
    ];
    $context['the_sections'][] = Timber::compile($context['woody_components']['section-section_full-tpl_1'], $the_section);

    // ** DEBUG **//
    // print '<pre>';
    // print_r($display);
    // exit();
    // ** DEBUG **//
}

// ** DEBUG **//
// Get the TimberPost availables methods
//$post_methods = get_class_methods('TimberPost');
// print '<pre>';
// print_r($post_methods);
// // print_r($context['woody_components']['section-section_full-tpl_1']);
// // print_r($context['the_sections']);
// // print_r($context['woody_components']);
// // print_r($context['post']);
// exit();
// ** DEBUG **//

// Render the $context in page.twig
Timber::render('page.twig', $context);
