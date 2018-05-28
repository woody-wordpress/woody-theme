<?php
/**
 * Configure options
 *
 * @package WordPress
 */

update_option( 'timezone_string', '', '', 'yes' ); // Mettre vide si le serveur est déjà configuré sur la bonne timezone Europe/Paris
update_option( 'date_format', 'j F Y', '', 'yes' );
update_option( 'time_format', 'G\hi', '', 'yes' );
update_option( 'acf_pro_license', 'b3JkZXJfaWQ9MTIyNTQwfHR5cGU9ZGV2ZWxvcGVyfGRhdGU9MjAxOC0wMS0xNSAwOTozMToyMw==', '', 'yes' );
update_option( 'wp_php_console', array('password' => 'root', 'register' => true, 'short' => true, 'stack' => true), '', 'yes' );
update_option( 'rocket_lazyload_options', array('images' => true, 'iframes' => true, 'youtube' => true), '', 'yes' );
