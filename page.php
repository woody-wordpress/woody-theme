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

    // Data for section header
    $header = [
        'display_title' => (!empty($section['display_title'])) ? $section['display_title'] : '',
        'title' => (!empty($section['title'])) ? $section['title'] : '',
        'pretitle' => (!empty($section['pretitle'])) ? $section['pretitle'] : '',
        'subtitle' => (!empty($section['subtitle'])) ? $section['subtitle'] : '',
        'icon' => (!empty($section['icon'])) ? $section['icon'] : '',
        'description' => (!empty($section['description'])) ? $section['description'] : ''
    ];
    if(!empty($header)){
        $the_header = Timber::compile($context['woody_components']['section-section_header-tpl_1'], $header);
    }


    // Data for section footer
    $footer = [];
    if(!empty($section['links'])){
        foreach ($section['links'] as $key => $link_data) {
            $footer['buttons'][] = [
                'icon' => (!empty($link_data['icon'])) ? $link_data['icon'] :'',
                'link' => [
                    'title' => (!empty($link_data['link']['title'])) ? $link_data['link']['title'] :'',
                    'url' => (!empty($link_data['link']['url'])) ? $link_data['link']['url'] :'',
                    'target' => (!empty($link_data['link']['target'])) ? $link_data['link']['target'] :'',
                ]
            ];
        }
    }
    if(!empty($footer)){
        $the_footer = Timber::compile($context['woody_components']['section-section_footer-tpl_1'], $footer);
    }


    // Creating data for display options
    $display = [
        'background_img' => (!empty($section['background_img'])) ? $section['background_img'] : '',
    ];

    $classes_array = [];

    // Set the container class
    if(empty($section['display_fullwidth'])){
        $classes_array[] = 'grid-container';
    }

    // Set the background-color class
    if(!empty($section['background_color'])){
        switch ($section['background_color']) {
            case 'color_primary':
                $classes_array[] = 'bg-primary';
            break;
            case 'color_secondary':
                $classes_array[] = 'bg-secondary';
            break;
            case 'color_black':
                $classes_array[] = 'bg-black';
            break;
            case 'color_darkgray':
                $classes_array[] = 'bg-darkgray';
            break;
            case 'color_lightgray':
                $classes_array[] = 'bg-lightgray';
            break;
        }
    }

    // Set the background-image opacity class
    if(!empty($section['background_img_opacity'])){
        switch ($section['background_img_opacity']) {
            case '75':
                $classes_array[] = 'bgimg-op75';
            break;
            case '50':
                $classes_array[] = 'bgimg-op50';
            break;
            case '25':
                $classes_array[] = 'bgimg-op25';
            break;
            case '10':
                $classes_array[] = 'bgimg-op10';
            break;
        }
    }

    // Set the padding's classes
    if(!empty($section['section_paddings']['section_padding_top'])){
        switch ($section['section_paddings']['section_padding_top']) {
            case '1':
                $classes_array[] = 'padd-top-sm';
            break;
            case '2':
                $classes_array[] = 'padd-top-md';
            break;
            case '3':
                $classes_array[] = 'padd-top-lg';
            break;
        }
    }

    if(!empty($section['section_paddings']['section_padding_bottom'])){
        switch ($section['section_paddings']['section_padding_bottom']) {
            case '1':
                $classes_array[] = 'padd-bottom-sm';
            break;
            case '2':
                $classes_array[] = 'padd-bottom-md';
            break;
            case '3':
                $classes_array[] = 'padd-bottom-lg';
            break;
        }
    }

    // Set the margin's classes
    if(!empty($section['section_margins']['section_margin_top'])){
        switch ($section['section_margins']['section_margin_top']) {
            case '1':
                $classes_array[] = 'marg-top-sm';
            break;
            case '2':
                $classes_array[] = 'marg-top-md';
            break;
            case '3':
                $classes_array[] = 'marg-top-lg';
            break;
        }
    }

    if(!empty($section['section_margins']['section_margin_bottom'])){
        switch ($section['section_margins']['section_margin_bottom']) {
            case '1':
                $classes_array[] = 'marg-bottom-sm';
            break;
            case '2':
                $classes_array[] = 'marg-bottom-md';
            break;
            case '3':
                $classes_array[] = 'marg-bottom-lg';
            break;
        }
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
