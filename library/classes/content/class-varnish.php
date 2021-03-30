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

        // Force remove varnish cookie if logout
        if (!is_user_logged_in() && !empty($_COOKIE[WOODY_VARNISH_CACHING_COOKIE])) {
            setcookie(WOODY_VARNISH_CACHING_COOKIE, null, time()-3600*24*100, COOKIEPATH, COOKIE_DOMAIN, false, true);
        }
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
        $woody_varnish_caching_ttl = apply_filters('woody_varnish_override_ttl', null);
        if (!empty($woody_varnish_caching_ttl)) {
            header('X-VC-TTL: ' . $woody_varnish_caching_ttl);
        } else {
            global $post;
            if (!empty($post)) {
                // Using $post->post_password instead of post_password_required() that return false when the password is correct
                // So protected pages where cached with default TTL
                if ($post->post_password) {
                    $woody_varnish_caching_ttl = 0;
                } else {
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
                                                        $light_section_content['focused_sort'] = (!empty($light_section_content['field_5b912bde59c2b_field_5b27a67203e48'])) ? $light_section_content['field_5b912bde59c2b_field_5b27a67203e48'] : '';
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
                                        if ($section_content['acf_fc_layout'] == 'auto_focus' && !(empty($section_content['field_5b27a7859ddeb_field_5b27a67203e48']))) {
                                            $section_content['focused_sort'] = $section_content['field_5b27a7859ddeb_field_5b27a67203e48'];
                                        }
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
            }

            if ($woody_varnish_caching_ttl !== 0 && empty($woody_varnish_caching_ttl)) {
                $woody_varnish_caching_ttl = WOODY_VARNISH_CACHING_TTL;
            }

            header('X-VC-TTL: ' . $woody_varnish_caching_ttl);
        }
    }

    private function getTLLbyField($section_content)
    {
        if ($section_content['acf_fc_layout'] == 'auto_focus_sheets' || $section_content['acf_fc_layout'] == 'manual_focus_minisheet') {
            return WOODY_VARNISH_CACHING_TTL_FOCUSSHEET;
        } elseif ($section_content['acf_fc_layout'] == 'auto_focus' || $section_content['acf_fc_layout'] == 'auto_focus_topics') {
            return WOODY_VARNISH_CACHING_TTL_FOCUSRANDOM;
        } elseif ($section_content['acf_fc_layout'] == 'weather') {
            return WOODY_VARNISH_CACHING_TTL_WEATHERPAGE;
        } elseif ($section_content['acf_fc_layout'] == 'infolive') {
            return WOODY_VARNISH_CACHING_TTL_LIVEPAGE;
        }
    }
}
