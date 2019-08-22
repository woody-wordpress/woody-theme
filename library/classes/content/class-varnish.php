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
        $woody_varnish_caching_ttl = WOODY_VARNISH_CACHING_TTL;

        $post = get_post();
        if (!empty($post) && post_password_required($post)) {
            $woody_varnish_caching_ttl = 0;
        } elseif (!empty($post)) {
            $sections = get_field('section', $post->ID);
            foreach ($sections as $section) {
                foreach ($section['section_content'] as $section_content) {
                    if ($section_content['acf_fc_layout'] == 'tabs_group') {
                        foreach ($section_content['tabs'] as $tab) {
                            foreach ($tab['light_section_content'] as $light_section_content) {
                                $ttl = $this->getTLLbyField($light_section_content);
                                if (!empty($ttl)) {
                                    $woody_varnish_caching_ttl = $ttl;
                                    break 4;
                                }
                            }
                        }
                    } else {
                        $ttl = $this->getTLLbyField($section_content);
                        if (!empty($ttl)) {
                            $woody_varnish_caching_ttl = $ttl;
                            break 2;
                        }
                    }
                }
            }
        }

        header('X-VC-TTL: ' . $woody_varnish_caching_ttl);
    }

    private function getTLLbyField($section_content)
    {
        if ($section_content['acf_fc_layout'] == 'auto_focus_sheets') {
            return WOODY_VARNISH_CACHING_FOCUSSHEET_TTL;
        } elseif ($section_content['acf_fc_layout'] == 'auto_focus' && $section_content['focused_sort'] == 'random') {
            return WOODY_VARNISH_CACHING_FOCUSRANDOM_TTL;
        } elseif ($section_content['acf_fc_layout'] == 'weather') {
            return WOODY_VARNISH_CACHING_WEATHERPAGE_TTL;
        }
    }
}
