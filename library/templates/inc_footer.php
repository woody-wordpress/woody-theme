<?php

$context = [
    'styles' => [
        'footer_resa' => 'font-family:Helvetica,Arial, sans-serif; background-color: #eaeaea; height: 85px; padding: 15px; position:relative; border-top:1px solid rgba(0,0,0,.1);',
        'footer_resa_container' => 'max-width:1400px; margin:0 auto',
        'logo' => 'display: block; margin: 0 auto; height:100%',
    ],
    'logo_url' => (empty(get_stylesheet_directory_uri() . '/logo.svg')) ? '' : get_stylesheet_directory_uri() . '/logo.svg',
    'home_url' => home_url()
];
$context = apply_filters('inc_footer_override', $context);
\Timber::render('inclusions/inc_footer.twig', $context);
