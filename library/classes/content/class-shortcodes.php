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
        add_shortcode('woody_anchor', [$this, 'anchorShortcode']);
    }

    /** ***********************
     * RECHERCHE
     *********************** */
    public function searchShortCode($atts)
    {
        $query = filter_input(INPUT_GET, 'query', FILTER_SANITIZE_STRING);
        $tags = filter_input(INPUT_GET, 'tags', FILTER_SANITIZE_STRING);

        // Search inside pages
        $pages_response = apply_filters('woody_pages_search', ['query' => $query, 'tags' => $tags]);

        $result = [];
        $result['query'] = $query;
        $result['display_button'] = true;

        $result['posts']['pages'] = [];
        if (!empty($pages_response['posts'])) {
            foreach ($pages_response['posts'] as $post_id) {
                $post_id = explode('_', $post_id);
                $post_id = end($post_id);
                $post = Timber::get_post($post_id);
                $result['posts']['pages'][] = getPagePreview(['display_elements' => ['description'], 'display_button' => true, 'display_img' => true], $post);
            }
        }

        $result['total']['pages'] = 0;
        if (!empty($pages_response['total'])) {
            $result['total']['pages'] = $pages_response['total'];
        }

        // Search inside sheets
        $sheets_response = apply_filters('woody_hawwwai_sheets_search', ['query' => $query]);

        $result['posts']['touristic_sheets'] = [];
        if (!empty($sheets_response['sheets'])) {
            foreach ($sheets_response['sheets'] as $sheet) {
                $result['posts']['touristic_sheets'][] = getTouristicSheetPreview(['display_elements' => ['description', 'sheet_town', 'sheet_type'], 'display_img' => true], $sheet['data']['idFiche']);
            }
        }

        $result['total']['touristic_sheets'] = 0;
        if (!empty($sheets_response['total'])) {
            $result['total']['touristic_sheets'] = $sheets_response['total'];
        }

        // Set a default template
        $tplSearch = apply_filters('es_search_tpl', null);
        if (empty($tplSearch)) {
            $tplSearch = 'woody_widgets-es_search-tpl_01';
        }

        $template = $this->twigPaths[$tplSearch];

        return Timber::compile($template, $result);
    }

    public function anchorShortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'id' => 'woody_anchor',
            ),
            $atts,
            'woody_anchor'
        );
        $output = sprintf(
            '<span class="woody_anchor" id="%s"></span>',
            esc_attr($atts['id'])
        );
        return $output;
    }
}
