<?php
// /**
//  * Permalink Cleanup
//  *
//  * @package WoodyTheme
//  * @since WoodyTheme 1.0.0
//  */

// class WoodyTheme_Cleanup_Permalink
// {
//     public function __construct()
//     {
//         $this->registerHooks();
//     }

//     public function registerHooks()
//     {
//         // Launching operation cleanup.
//         add_action('woody_theme_update', [$this, 'cleanupPermalink'], 1);
//     }

//     public function cleanupPermalink()
//     {
//         $permalinks = get_option('permalink-manager-uris', []);

//         $cleanup_permalinks = [];
//         foreach ($permalinks as $post_id => $permalink) {
//             if (is_numeric($post_id)) {
//                 if (false !== get_post_status($post_id)) {
//                     $cleanup_permalinks[$post_id] = $permalink;
//                 }
//             } else {
//                 $cleanup_permalinks[$post_id] = $permalink;
//             }
//         }

//         update_option('permalink-manager-uris', $cleanup_permalinks);
//     }
// }
