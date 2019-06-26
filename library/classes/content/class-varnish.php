<?php
/**
 * Varnish
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Varnish
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('vcaching_purge_urls', [$this, 'vcachingPurgeUrls'], 10, 1);
        // if (WP_ENV != 'dev') {
            add_action('wp_enqueue_scripts', [$this, 'overrideTTL'], 1000);
        // }
    }

    public function vcachingPurgeUrls($purge_urls = [])
    {
        foreach ($purge_urls as $key => $url) {
            if (strpos($url, '/author/') !== false || strpos($url, '/feed/') !== false) {
                unset($purge_urls[$key]);
            }
        }

        return array_unique($purge_urls);
    }

    // Met le max-age des pages protégées à 0.
    public function overrideTTL()
    {
        $post = get_post();
        if (!empty($post) && post_password_required($post)) {
            Header('X-VC-TTL: 0', true);
        } else if(!empty($post)) {
            $focus_content_exists = false;
            $weather_content_exists = false;
            $field = get_field('section', $post->ID)[0];

            // Essaie de trouver des solutions via de simple get field
            // Ou alors une requête sur la meta avec une query spécifique (need LIKE statement)

            foreach($field['section_content'] as $sub_section) {
                switch ($sub_section['acf_fc_layout']) {
                    case 'manual_focus' :
                    case 'auto_focus'   :
                    case 'auto_focus_sheet':
                        $focus_content_exists = true;
                        break;
                    case 'weather'      :
                        $weather_content_exists = true;
                        break;
                }
            }

            // for($i = 0 ; ($i < sizeof($field['section_content'])) && !($focus_content_exists && $weather_content_exists); $i++){
            //     switch ($field['section_content'][$i]['acf_fc_layout']){
            //         case 'manual_focus' :
            //         case 'auto_focus'   :
            //         case 'auto_focus_sheet':
            //             $focus_content_exists = true;
            //             break;
            //         case 'weather'      :
            //             $weather_content_exists = true;
            //             break;
            //     }
            // }

            if ( $focus_content_exists ) {
                Header('X-VC-TTL : 7200', true);
            } else if ( $weather_content_exists ) {
                Header('X-VC-TTL : 14400', true);
            } else {
                Header('X-VC-TTL : 31622400');
            }
        }
    }
}
