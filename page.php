<?php

$context = Timber::get_context();

// Creating Timber object to access twig keys
$context['post'] = new TimberPost();

// Get the TimberPost availables methods
$post_methods = get_class_methods('TimberPost');

// Creating object Timber Image for hero_image and post thumbnail
// $context['post']->img = new TimberImage($post->hero_img);
// $context['post']->thumbnail = new TimberImage($post->_thumbnail_id);

$woody = new Woody();
$context['woody_components'] = $woody->getTwigsPaths();

// Get the sections of the page
$sections = $context['post']->get_field('section');

$context['sections'] = [];

foreach ($sections as $key => $section) {
    // Foreach section put the post data into vars to pass them to related twig tpl

    // print '<pre>';
    // print_r($section);
    // exit();

    // Section_heading data
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


    // Section_footer data
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


    // Vars to display the section properly
    $display = [];

    // Section_content components
    $components = [];

    if(!empty($section['section_layout'])){
        $the_layout = Timber::compile($context['woody_components'][$section['section_layout']], $components);
    }

    $the_section = [
        'header' => $the_header,
        'footer' => $the_footer,
        'layout' => $the_layout,
        'display' => $display
    ];

    $context['the_sections'][] = Timber::compile($context['woody_components']['section-section_full-tpl_1'], $the_section);

    // print '<pre>';
    // print_r($the_section);
    // exit();
}

// print '<pre>';
// // print_r($context['woody_components']['section-section_full-tpl_1']);
// // print_r($context['the_sections']);
// // print_r($context['woody_components']);
// // print_r($context['post']);
// exit();

Timber::render('page.twig', $context);
