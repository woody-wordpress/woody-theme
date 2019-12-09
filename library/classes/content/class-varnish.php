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
            // Force "no format" because otherwise generates a cache or shortcodes are not yet generated
            $sections = get_field('section', $post->ID, false);
            if (is_array($sections)) {
                foreach ($sections as $section) {
                    // field_5b043f0525968 == section_content
                    if (!empty($section['field_5b043f0525968']) && is_array($section['field_5b043f0525968'])) {
                        foreach ($section['field_5b043f0525968'] as $section_content) {
                            if ($section_content['acf_fc_layout'] == 'tabs_group') {
                                // field_5b4722e2c1c13_field_5b471f474efee == tabs
                                if (!empty($section_content['field_5b4722e2c1c13_field_5b471f474efee']) && is_array($section_content['field_5b4722e2c1c13_field_5b471f474efee'])) {
                                    foreach ($section_content['field_5b4722e2c1c13_field_5b471f474efee'] as $tab) {
                                        // field_5b4728182f9b0_field_5b4727a878098_field_5b91294459c24 == light_section_content
                                        if (!empty($tab['field_5b4728182f9b0_field_5b4727a878098_field_5b91294459c24']) && is_array($tab['field_5b4728182f9b0_field_5b4727a878098_field_5b91294459c24'])) {
                                            foreach ($tab['field_5b4728182f9b0_field_5b4727a878098_field_5b91294459c24'] as $light_section_content) {
                                                $light_section_content['focused_sort'] = $light_section_content['field_5b912bde59c2b_field_5b27a67203e48'];
                                                $ttl = $this->getTLLbyField($light_section_content);
                                                if (!empty($ttl)) {
                                                    $woody_varnish_caching_ttl = $ttl;
                                                    break 4;
                                                }
                                            }
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
        } elseif ($section_content['acf_fc_layout'] == 'weather' || $section_content['acf_fc_layout'] == 'infolive') {
            return WOODY_VARNISH_CACHING_WEATHERPAGE_TTL;
        }
    }
}
