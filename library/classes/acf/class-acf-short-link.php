<?php

/**
 * ACF Short Link
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_ACF_ShorLink
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('rest_api_init', function () {
            register_rest_route('woody', 'short-link', array(
                'methods' => 'POST',
                'callback' => [$this, 'getShortLinkData'],
            ));
        });
        add_action('template_redirect', array($this, 'redirectShortLink'));
    }

    public function getShortLinkData(\WP_REST_Request $request)
    {
        $post_id = $request->get_body();
        $cache_key = 'woody_shortLink_' . $post_id;
        if (false === ($shortLinkData = wp_cache_get($cache_key, 'woody'))) {
            $page_type_object = get_the_terms($post_id, 'page_type');
            $shortLinkData['page_type'] = !empty($page_type_object[0]) ? $page_type_object[0]->slug : '';
            $shortLinkData['conf_id'] = get_field('field_5b338ff331b17', $post_id);
            wp_cache_set($cache_key, $shortLinkData, 'woody', 2*60);
        }

        return $shortLinkData;
    }

    public function redirectShortLink()
    {
        $post_id = get_the_ID();
        $post_type = get_post_type($post_id);

        if ($post_type !== 'short_link') {
            return;
        }

        $autoselect_id = get_field('playlist_autoselection_id', $post_id);
        $linked_url = get_field('short_link_page_url', $post_id);

        if (is_numeric($linked_url)) {
            $linked_id = $linked_url;
            $linked_url = apply_filters('woody_get_permalink', $linked_id);
        } else {
            $linked_id = url_to_postid($linked_url);
        }

        $linked_post_type = get_the_terms($linked_id, 'page_type');
        if ($linked_post_type[0]->slug == 'playlist_tourism' && !empty($autoselect_id)) {
            $playlist_map_display = get_field('playlist_map_display', $post_id);
            if (!empty($playlist_map_display)) {
                $short_link_final_url = $linked_url . '?autoselect_id=' . $autoselect_id . '#map';
            } else {
                $short_link_final_url = $linked_url . '?autoselect_id=' . $autoselect_id;
            }
        } else {
            $short_link_final_url = $linked_url;
        }

        wp_redirect($short_link_final_url, 301, 'Woody ShortLink');
        exit;
    }
}
