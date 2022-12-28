<?php

/**
 * ACF Short Link
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_ACF_ShortLink
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('wp_ajax_get_woody_shortlink', [$this, 'getShortLinkData']);
        add_action('template_redirect', array($this, 'redirectShortLink'));
    }

    public function getShortLinkData()
    {
        $shortLinkData = [];
        $post_id = filter_input(INPUT_GET, 'post_id', FILTER_SANITIZE_STRING);
        if (empty($post_id)) {
            status_header(404);
            exit();
        }

        $shortLinkData['post_id'] = $post_id;

        $page_type_object = get_the_terms($post_id, 'page_type');
        $shortLinkData['page_type'] = !empty($page_type_object[0]) ? $page_type_object[0]->slug : null;

        // Return JSON
        wp_send_json(apply_filters('woody_shortlink_data', $shortLinkData));
        exit;
    }

    public function redirectShortLink()
    {
        $post_id = get_the_ID();
        $post_type = get_post_type($post_id);

        if ($post_type !== 'short_link') {
            return;
        }

        $short_link = get_field('short_link_page_url', $post_id);
        if (is_numeric($short_link)) {
            $linked_id = $short_link;
            $linked_url = woody_get_permalink($linked_id);
        } else {
            $linked_id = url_to_postid($short_link);
            $linked_url = $short_link;
        }

        $linked_url = apply_filters('woody_shortlink_redirect', $linked_url, $linked_id, $post_id);
        wp_redirect($linked_url, 301, 'Woody ShortLink');
        exit;
    }
}
