<?php

/**
 * Images
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 *
 */

// class WoodyTheme_Videos
// {
//     public function __construct()
//     {
//         $this->registerHooks();
//     }

//     protected function registerHooks()
//     {
//         add_filter('embed_oembed_html', [$this, 'responsiveVideoOembedHtml'], 10, 4);
//     }

//     // Remove default image sizes here.
//     public function responsiveVideoOembedHtml($html, $url, $attr, $post_id)
//     {

//         // Whitelist of oEmbed compatible sites that **ONLY** support video.
//         // Cannot determine if embed is a video or not from sites that
//         // support multiple embed types such as Facebook.
//         // Official list can be found here https://codex.wordpress.org/Embeds
//         $video_sites = array(
//             'youtube', // first for performance
//             'youtu.be',
//             'dailymotion',
//             'vimeo',
//         );

//         $is_video = false;

//         // Determine if embed is a video
//         foreach ($video_sites as $site) {
//             // Match on `$html` instead of `$url` because of
//             // shortened URLs like `youtu.be` will be missed
//             if (strpos($html, $site)) {
//                 $is_video = true;
//                 break;
//             }
//         }

//         // Process video embed
//         if (true == $is_video) {

//             // Find the `<iframe>`
//             $doc = new DOMDocument();
//             $doc->loadHTML($html);
//             $tags = $doc->getElementsByTagName('iframe');

//             // Get width and height attributes
//             foreach ($tags as $tag) {
//                 $width  = $tag->getAttribute('width');
//                 $height = $tag->getAttribute('height');
//                 break; // should only be one
//             }

//             $class = 'responsive-embed'; // Foundation class

//             // Determine if aspect ratio is 16:9 or wider
//             if (is_numeric($width) && is_numeric($height) && ($width / $height >= 1.7)) {
//                 $class .= ' widescreen'; // space needed
//             }

//             // Wrap oEmbed markup in Foundation responsive embed
//             return '<div class="' . $class . '">' . $html . '</div>';
//         } else { // not a supported embed
//             return $html;
//         }
//     }
// }
