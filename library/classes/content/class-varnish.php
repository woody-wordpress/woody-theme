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
        add_action('wp_enqueue_scripts', [$this, 'override_ttl'], 1000);
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

    public function override_ttl()
    {
        $post = get_post();
        $page_type = get_the_terms($post->ID, 'page_type');

        if (post_password_required($post)) {
            // Met le max-age des pages protégées à 0.
            Header('X-VC-TTL: 0', true);
        } else {
            switch ($page_type) {
                case 'playlist_tourism_system':
                    Header('X-VC-TTL: 10800', true);
                    break;
                case 'touristic_sheet':
                    Header('X-VC-TTL: 2592000', true);
                    break;
            }
        }
    }
}
