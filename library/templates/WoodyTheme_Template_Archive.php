<?php
/**
 * Template
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

namespace Woody\WoodyTheme\library\templates;

class WoodyTheme_Template_Archive
{
    public function __construct()
    {
        global $wp_query;
        $url = WP_HOME;

        $disabled_contexts = [
            'is_single',
            'is_preview',
            'is_archive',
            'is_date',
            'is_year',
            'is_month',
            'is_day',
            'is_time',
            'is_author',
            'is_category',
            'is_tag',
            'is_tax',
            // 'is_feed',
            // 'is_comment_feed',
            // 'is_trackback',
        ];

        foreach ($disabled_contexts as $context) {
            if ($wp_query->$context == true) {
                wp_redirect($url, 301, 'Woody Archive');
                exit;
            }
        }
    }
}
