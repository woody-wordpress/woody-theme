<?php
/**
 * Permalink
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Permalink
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
        add_action('woody_theme_update', [$this,'cleanTransient']);
        add_action('woody_subtheme_update', [$this,'cleanTransient']);

        add_action('template_redirect', [$this,'redirect404'], 0);
    }

    public function woodyGetPermalink($post_id)
    {
        $current_lang = pll_current_language();
        $posts = get_transient('woody_get_permalink', []);

        if (empty($posts[$post_id]) && empty($posts[$post_id][$current_lang])) {
            $posts[$post_id][$current_lang] = get_permalink($post_id);
            set_transient('woody_get_permalink', $posts);
        }

        return $posts[$post_id][$current_lang];
    }

    public function redirect404()
    {
        global $wp_query, $wp;
        if ($wp_query->is_404) {
            $segments = explode('/', $wp->request);
            $last_segment = end($segments);
            $query_result = new \WP_Query([
                'lang' => pll_current_language(),
                'posts_per_page' => 1,
                'post_status' => ['publish', 'draft', 'trash'],
                'orderby' => 'ID',
                'order' => 'ASC',
                'name' => $last_segment,
                'post_type' => 'page'
            ]);

            if (!empty($query_result->posts)) {
                $post = current($query_result->posts);
                wp_redirect(get_permalink($post->ID));
                exit();
            }
        }
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

    public function cleanTransient()
    {
        delete_transient('woody_get_permalink');
    }
}
