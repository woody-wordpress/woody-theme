<?php
/**
 * Helpers
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Helpers
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('woody_get_permalink', [$this, 'woodyGetPermalink'], 10);
        add_action('pll_save_post', [$this, 'savePost'], 10, 3);
        add_action('delete_post', [$this, 'deletePost'], 10);
    }

    public function woodyGetPermalink($post_id)
    {
        $return = [];
        $posts = get_transient('woody_get_permalink', []);
        if (empty($posts[$post_id])) {
            $posts[$post_id] = get_permalink($post_id);
            set_transient('woody_get_permalink', $posts);

            $return = $posts[$post_id];
        }

        return $return;
    }

    // --------------------------------
    // Clean Transient
    // --------------------------------
    public function savePost($post_id, $post, $update)
    {
        delete_transient('woody_get_permalink');
    }

    public function deletePost($post_id)
    {
        delete_transient('woody_get_permalink');
    }
}
