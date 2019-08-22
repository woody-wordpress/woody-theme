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
        add_action('template_redirect', [$this, 'overrideTTL'], 1000);
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
            header('X-VC-TTL: 0');
        } elseif (!empty($post)) {
            $focus_content_exists = false;
            $weather_content_exists = false;

            $sections = get_field('section', $post->ID);
            foreach ($sections as $section) {
                foreach ($section['section_content'] as $section_content) {
                    if (in_array($section_content['acf_fc_layout'], ['auto_focus', 'auto_focus_sheet'])) {
                        $focus_content_exists = true;
                        break;
                    } elseif ($section_content['acf_fc_layout'] == 'weather') {
                        $weather_content_exists = true;
                        break;
                    }
                }

                if ($focus_content_exists || $weather_content_exists) {
                    break;
                }
            }

            if ($focus_content_exists) {
                header('X-VC-TTL: ' . WOODY_VARNISH_CACHING_FOCUSPAGE_TTL);
            } elseif ($weather_content_exists) {
                header('X-VC-TTL: ' . WOODY_VARNISH_CACHING_WEATHERPAGE_TTL);
            } else {
                header('X-VC-TTL: ' . WOODY_VARNISH_CACHING_TTL);
            }
        }
    }
}
