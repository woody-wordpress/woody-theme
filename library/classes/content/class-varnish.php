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
        add_action('save_post', array($this, 'save_protected_post'), 1, 2);
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
    public function save_protected_post($post_id, $post)
    {
        if (post_password_required($post->context['post'])) {
            update_post_meta($post_id, 'varnish_caching_ttl', 0);
        }
    }
}
