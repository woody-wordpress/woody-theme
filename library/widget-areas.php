<?php
/**
 * Register widget areas
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

if ( ! function_exists( 'basetheme_sidebar_widgets' ) ) :
function basetheme_sidebar_widgets() {
	register_sidebar(array(
		'id' => 'sidebar-widgets',
		'name' => __( 'Sidebar widgets', 'basetheme' ),
		'description' => __( 'Drag widgets to this sidebar container.', 'basetheme' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget' => '</section>',
		'before_title' => '<h6>',
		'after_title' => '</h6>',
	));

	register_sidebar(array(
		'id' => 'footer-widgets',
		'name' => __( 'Footer widgets', 'basetheme' ),
		'description' => __( 'Drag widgets to this footer container', 'basetheme' ),
		'before_widget' => '<section id="%1$s" class="large-4 columns widget %2$s">',
		'after_widget' => '</section>',
		'before_title' => '<h6>',
		'after_title' => '</h6>',
	));
}

add_action( 'widgets_init', 'basetheme_sidebar_widgets' );
endif;
