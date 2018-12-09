<?php
/**
 * Template
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Template_Sitemap
{
    protected $twig_tpl = '';
    protected $context = [];

    public function __construct()
    {
        $this->initContext();
    }

    protected function registerHooks()
    {
    }

    public function render()
    {
        if (!empty($this->twig_tpl) && !empty($this->context)) {
            Timber::render($this->twig_tpl, $this->context);
        }
    }

    private function initContext()
    {
        $query = new WP_Query([
            'post_type' => 'page',
            'orderby' => 'menu_order',
            'order'   => 'DESC',
            'posts_per_page' => 50,
            'paged' => (get_query_var('page')) ? get_query_var('page') : 1
        ]);

        wd($query);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $post = $query->the_post();
            }

            /* Restore original Post Data */
            wp_reset_postdata();
        }
    }
}
