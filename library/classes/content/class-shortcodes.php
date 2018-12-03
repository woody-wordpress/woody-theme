<?php
/**
 * Shortscodes
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

 class WoodyTheme_Shortcodes{

    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_shortcode('ma_meteo', [$this,'theWeather']);
    }

    private function getWeather($atts){
        $return = [];

        // $atts diuspoanibles : fond, images, localite

        $today = date('l d M',strtotime('now'));
        for ($i=0; $i < 4 ; $i++) {
            $return['days'][] = [
                'date' => date('l d M',strtotime('+ ' . $i . ' days'))
            ];
        }

        if(!empty($atts['images'])){
            $imgs = [
                'storm' => WP_DIST_URL . '/img/plugins/weather/stormy.jpg',
                'sun' => WP_DIST_URL . '/img/plugins/weather/sunny.jpg',
                'rain' => WP_DIST_URL . '/img/plugins/weather/rainy.jpg',
                'cloud' => WP_DIST_URL . '/img/plugins/weather/cloudy.jpg',
            ];
        }

        // Test icones
        $icons = ['climacon-storm', 'climacon-sun', 'climacon-cloud-rain', 'climacon-cloud'];

        foreach ($return['days'] as $day_key => $day) {
            $return['days'][$day_key]['summary'] = [
                'sky' => 'nuageux',
                'icon' => $icons[$day_key],
                'img' => (!empty($imgs)) ? $imgs['cloud'] : '',
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

    public function theWeather($atts){

        $return = '';
        $vars = $this->getWeather($atts);

        if(!empty($atts['fond'])){
            $vars['bg_color'] = $atts['fond'];
        }

        $return = Timber::compile('plugins/weather.twig', $vars);
        return $return;
    }

 }
