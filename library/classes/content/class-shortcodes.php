<?php
/**
 * Shortscodes
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

use \Woody\Services\Providers;

class WoodyTheme_Shortcodes
{
    public function __construct()
    {
        $this->registerHooks();
        $this->twigPaths = getWoodyTwigPaths();
    }

    protected function registerHooks()
    {
        add_shortcode('woody_meteo', [$this,'weatherShortCode']);
        add_shortcode('woody_recherche', [$this, 'searchShortCode']);
    }

    /** ***********************
     * RECHERCHE
     *********************** */
    public function searchShortCode($atts)
    {
        $query = filter_input(INPUT_GET, 'query', FILTER_SANITIZE_STRING);

        // Search inside pages
        $pages_response = apply_filters('wp_woody_pages_search', ['query' => $query]);

        $result = [];
        $result['query'] = $query;
        $result['display_button'] = true;

        $result['posts']['pages'] = [];
        if (!empty($pages_response['posts'])) {
            foreach ($pages_response['posts'] as $post_id) {
                $post_id = explode('_', $post_id);
                $post_id = end($post_id);
                $post = Timber::get_post($post_id);
                $result['posts']['pages'][] = getPagePreview(['display_elements' => ['description'], 'display_button' => true], $post);
            }
        }

        $result['total']['pages'] = 0;
        if (!empty($pages_response['total'])) {
            $result['total']['pages'] = $pages_response['total'];
        }

        // Search inside sheets
        $sheets_response = apply_filters('wp_woody_hawwwai_sheets_search', ['query' => $query]);

        $result['posts']['touristic_sheets'] = [];
        if (!empty($sheets_response['sheets'])) {
            foreach ($sheets_response['sheets'] as $sheet) {
                $result['posts']['touristic_sheets'][] = getTouristicSheetPreview(['display_elements' => ['description', 'sheet_town', 'sheet_type']], $sheet['data']['idFiche']);
            }
        }

        $result['total']['touristic_sheets'] = 0;
        if (!empty($sheets_response['total'])) {
            $result['total']['touristic_sheets'] = $sheets_response['total'];
        }

        return Timber::compile($this->twigPaths['woody_widgets-es_search-tpl_01'], $result);
    }
}
