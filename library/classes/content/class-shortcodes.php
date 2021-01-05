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
        add_shortcode('woody_meteo', [$this, 'weatherShortCode']);
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

        // Explode tags
        $tags = $tags ? explode(':', $tags) : '';

        // Search inside pages
        $pages_response = apply_filters('woody_pages_search', ['query' => $query, 'size' => 30, 'tags_or' => $tags]);

        $result = [];
        $result['query'] = $query;
        $result['tags'] = $tags;
        $result['display_button'] = true;

        $result['posts']['pages'] = [];
        if (!empty($pages_response['posts'])) {
            foreach ($pages_response['posts'] as $post_id) {
                $post_id = explode('_', $post_id);
                $post_id = end($post_id);
                $post = get_post($post_id);

                if (is_object($post) && $post->ID != null && $post->post_status === 'publish') {
                    switch ($post->post_type) {
                        case 'touristic_sheet':
                            $result['posts']['pages'][] = getTouristicSheetPreview(['display_elements' => ['sheet_town', 'sheet_type', 'description', 'bookable'], 'display_img' => true], $post);
                            break;
                        default:
                            $result['posts']['pages'][] = getPagePreview(['display_elements' => ['description'], 'display_img' => true], $post);
                            break;
                    }
                    // $result['posts']['pages'][] = getPagePreview(['display_elements' => ['description'], 'display_button' => true, 'display_img' => true], $post);
                }
            }
        }

        $result['total']['pages'] = 0;
        if (!empty($pages_response['total'])) {
            $result['total']['pages'] = $pages_response['total'];
        }

        // Set a default template
        $tplSearch = apply_filters('es_search_tpl', null);
        $result['tags'] = !empty($tplSearch['tags']) ?: $result['tags'];
        $template = !empty($tplSearch['template']) ?: $this->twigPaths['woody_widgets-es_search-tpl_01'];

        return \Timber::compile($template, $result);
    }

    /**
     * Function that format data to use it in getTouristicSheetPreview function
     * @param   sheet       sheet data and metadata
     * @return  sheet_data  array containing necessary data
     */
    private function formatSheetData($sheet)
    {
        $env = WP_ENV;
        $lang = pll_current_language();

        $image_url = '';
        if (!empty($sheet['data']['multimedia']) && !empty($sheet['data']['multimedia'][0]) && !empty($sheet['data']['multimedia'][0]['URL'])) {
            $image_url = $sheet['data']['multimedia'][0]['URL'];
        }

        $img = [
            'url' => ['manual' => rc_getImageResizedFromApi('%width%', '%height%', $image_url)],
            'alt' => '',
            'title' => ''
        ];

        $link = '';
        if (!empty($sheet['metadata']['canonicals_v2'])) {
            foreach ($sheet['metadata']['canonicals_v2'] as $canonical) {
                if (!empty($canonical["website_" . WP_ENV])) {
                    $link = $canonical["website_" . WP_ENV][$lang];
                }
            }
        }

        $sheet_data = [
            'items' => [
                [
                    'title' => $sheet['metadata']['name'],
                    'link'  => $link,
                    'targetBlank' => true,
                    'img' => $img,
                    'type' => '',
                    'desc' => $sheet['data']['dublinCore']['description'][$lang],
                    'town' => $sheet['data']['contacts'][0]['addresses'][0]['commune'],
                    'bordereau' => $sheet['data']['bordereau'],
                    'ratings' => null,
                ]
            ]
        ];

        return $sheet_data;
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
