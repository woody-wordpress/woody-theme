<?php
/**
 * HawwwaiTheme functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @package HawwwaiTheme
 * @since HawwwaiTheme 1.0.0
 */

use Symfony\Component\Finder\Finder;

$finder = new Finder();
$finder->files()->in(__DIR__ . '/library')->name('*.php');

foreach ($finder as $file) {
    require_once(__DIR__ . '/library/' . $file->getRelativePathname());
}

// Change Timber locations
Timber::$locations = array('views', Woody::getTemplatesDirname());

// Test insert post sheet
// function test_insert_post()
// {
//     $my_post = array(
//         'post_title'    => 'Fiche SIT - Test Insert',
//         'post_status'   => 'publish',
//         'post_type' => 'touristic_sheet'
//     );

//     // Insert the post into the database
//     $result = wp_insert_post($my_post);

//     if ($result && ! is_wp_error($result)) {
//         $post_id = $result;
//         update_field('touristic_sheet_id', '22081985', $post_id);
//         update_field('focus_pretitle', 'Bordereau de la fiche', $post_id);
//         update_field('focus_title', 'Titre de la fiche', $post_id);
//         update_field('focus_subtitle', 'Commune de la fiche', $post_id);
//         update_field('focus_description', 'Description commerciale de la fiche', $post_id);
//     }
// }

// $the_post_we_search_for = get_posts(array(
//     'numberposts'	=> -1,
//     'post_type'		=> 'touristic_sheet',
//     'meta_query'	=> array(
//         'relation'		=> 'AND',
//         array(
//             'key'	 	=> 'touristic_sheet_id',
//             'value'	  	=> '22081985',
//             'compare' 	=> 'IN',
//         ),
//     ),
// ));

// if (empty($the_post_we_search_for[0])) {
//     add_action('init', 'test_insert_post');
// }
