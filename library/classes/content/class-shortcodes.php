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
        add_shortcode('ma_meteo', [$this,'theWeather']);
        add_shortcode('recherche-globale', [$this, 'esSearchPage']);
    }

    private function getWeather($atts)
    {
        $return = [];

        // $atts diuspoanibles : fond, images, localite

        $today = date('l d M', strtotime('now'));
        for ($i=0; $i < 4 ; $i++) {
            $return['days'][] = [
                'date' => date('l d M', strtotime('+ ' . $i . ' days'))
            ];
        }

        if (!empty($atts['images'])) {
            $imgs = [
            WP_DIST_URL . '/img/plugins/weather/stormy.jpg',
            WP_DIST_URL . '/img/plugins/weather/snowy.jpg',
            WP_DIST_URL . '/img/plugins/weather/rainy.jpg',
            WP_DIST_URL . '/img/plugins/weather/cloudy.jpg'
        ];
        }

        // Test icones
        $icons = ['climacon-storm', 'climacon-cloud-snow', 'climacon-cloud-rain', 'climacon-cloud'];

        foreach ($return['days'] as $day_key => $day) {
            $return['days'][$day_key]['summary'] = [
            'sky' => 'nuageux',
            'icon' => $icons[$day_key],
            'img' => (!empty($imgs)) ? $imgs[$day_key] : '',
            'average_temp' => '13',
            'min_temp' => '12',
            'max_temp' => '13',
            'wind' => '18',
            'humidity' => '97'
        ];
            $return['days'][$day_key]['details'] = [
            'midnight' => [
                'sky' => 'pluie',
                'icon' => 'climacon-cloud-rain',
                'temp' => '11'
            ],
            'morning' => [
                'sky' => 'nuageux',
                'icon' => 'climacon-cloud',
                'temp' => '12'
            ],
            'afternoon' => [
                'sky' => 'nuageux',
                'icon' => 'climacon-cloud',
                'temp' => '13'
            ],
            'evening' => [
                'sky' => 'nuageux',
                'icon' => 'climacon-cloud',
                'temp' => '11'
            ]
        ];
        }

        return $return;
    }

    public function theWeather($atts)
    {
        $return = '';
        $vars = $this->getWeather($atts);

        if (!empty($atts['fond'])) {
            $vars['bg_color'] = $atts['fond'];
        }

        $return = Timber::compile($this->twigPaths['woody_widgets-weather-tpl_01'], $posts);
        return $return;
    }

    public function esSearchPage($atts)
    {
        $args['tags'] = (!empty($atts['tags'])) ? explode('|', $atts['tags']) : '';
        $args['lang'] = (!empty($atts['lang'])) ? $atts['lang'] : pll_default_language();
        $args['site_key'] = WP_SITE_KEY;
        $result = [];

        $query = filter_input(INPUT_GET, 'query', FILTER_SANITIZE_STRING);
        $result['posts']['pages'] = [];
        $posts_ids = apply_filters('wp_woody_pages_search', ['query' => $query]);

        foreach ($posts_ids as $id) {
            $post = Timber::get_post($id);
            $result['posts']['pages'][] = getPagePreview(['display_elements' => ['description'], 'display_button' => true], $post);
        }

        $result['query'] = $query;
        $result['display_button'] = true;

        $return = Timber::compile($this->twigPaths['woody_widgets-es_search-tpl_01'], $result);
        return $return;
    }
}
