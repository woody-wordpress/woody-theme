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

    // ** DEBUG **//
    // print '<pre>';
    // print_r($section);
    // exit();
    // ** DEBUG **//

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


    // Data for display options
    $display = [
        'display_fullwidth' => (!empty($section['display_fullwidth'])) ? $section['display_fullwidth'] : '',
        'background' => [
            'color' => (!empty($section['background_color'])) ? $section['background_color'] : '',
            'img' => (!empty($section['background_img'])) ? $section['background_img'] : '',
            'img_opacity' => (!empty($section['background_img_opacity'])) ? $section['background_img_opacity'] : '',
        ],
        'paddings' => [
            'top' => (!empty($section['section_paddings']['section_padding_top'])) ? $section['section_paddings']['section_padding_top'] : '',
            'bottom' => (!empty($section['section_paddings']['section_padding_bottom'])) ? $section['section_paddings']['section_padding_bottom'] : '',
        ],
        'margins' => [
            'top' => (!empty($section['section_margins']['section_margin_top'])) ? $section['section_margins']['section_margin_top'] : '',
            'bottom' => (!empty($section['section_margins']['section_margin_bottom'])) ? $section['section_margins']['section_margin_bottom'] : '',
        ]
    ];

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
