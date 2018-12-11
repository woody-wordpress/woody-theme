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
    protected $mode = '';

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
            switch ($this->mode) {
                case 'index':
                case 'list':
                    header('Content-Type: text/xml; charset=UTF-8');
                    break;
                case 'xsl':
                    header('Content-Type: text/xsl; charset=UTF-8');
                    break;
            }
            Timber::render($this->twig_tpl, $this->context);
        }
    }

    private function initContext()
    {
        $this->context = Timber::get_context();
        $this->mode = get_query_var('sitemap');
        switch ($this->mode) {
            case 'index':
                $query = $this->getPosts();
                if (!empty($query)) {
                    $this->twig_tpl = 'sitemap/sitemapindex.xml.twig';
                    for ($i=1; $i <= $query->max_num_pages; $i++) {
                        $this->context['sitemaps'][] = [
                            'loc' => 'sitemap-' . $i . '.xml',
                            'lastmod' => date('c', time()),
                        ];
                    }
                }
                break;
            case 'list':
                $query = $this->getPosts();
                if (!empty($query)) {
                    $this->twig_tpl = 'sitemap/sitemap.xml.twig';
                    while ($query->have_posts()) {
                        $post = $query->the_post();
                        $this->context['urls'][] = [
                            'loc' => get_permalink($post),
                            'lastmod' => get_the_modified_date('c', $post),
                        ];
                    }
                }
                break;
            case 'xsl':
                $this->twig_tpl = 'sitemap/sitemap.xsl.twig';
                break;
        }

        /* Restore original Post Data */
        wp_reset_postdata();
    }

    private function getPosts()
    {
        $polylang = get_option('polylang');
        if ($polylang['force_lang'] == 1) {
            // Si le site est alias, on fusionne toutes les pages dans le même sitemap
            $languages = pll_languages_list();
        } else {
            // Si le site est en multi domaines, on cree un sitemap par langue
            $languages = pll_current_language();
        }

        $query = new WP_Query([
            'post_type' => ['page', 'touristic_sheet'],
            'orderby' => 'menu_order',
            'order'   => 'DESC',
            'lang' => $languages,
            'posts_per_page' => 200,
            'paged' => (get_query_var('page')) ? get_query_var('page') : 1
        ]);

        if ($query->have_posts()) {
            return $query;
        }
    }
}
