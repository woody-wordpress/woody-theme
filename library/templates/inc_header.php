<?php

$context = [
    'styles' => [
        'header_resa' => 'background-color: #fff; height: 50px; padding: 15px; position:relative;',
        'header_resa_container' => 'max-width:1400px; margin:0 auto',
        'logo' => 'display: block; margin: 0 auto; height:100%',
        'backhome' => 'color:rgba(0,0,0,.4); font-size: 14px; padding:10px; border:1px solid; position:absolute; left:0; top:50%; transform:translateY(-50%)'
    ],
    'logo_url' => (!empty(get_stylesheet_directory_uri() . '/logo.svg')) ? get_stylesheet_directory_uri() . '/logo.svg' : '',
    'home_url' => home_url()
];
$context = apply_filters('inc_header_override', $context);
Timber::render('inclusions/inc_header.twig', $context);
