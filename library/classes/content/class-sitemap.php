<?php

/**
 * SiteMap
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

use Woody\Utils\Output;

class WoodyTheme_SiteMap
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('init', [$this, 'customRewriteRule']);
        add_action('after_setup_theme', [$this, 'reduceQueryLoad'], 99);
        add_action('template_redirect', [$this, 'getSitemap'], 1);
        add_filter('query_vars', [$this, 'queryVars']);

        add_action('woody_sitemap', [$this, 'woodySitemap']);
        add_action('wp', [$this, 'scheduleSitemap']);
        \WP_CLI::add_command('woody:sitemap', [$this, 'woodySitemap']);
    }

    public function queryVars($qvars)
    {
        $qvars[] = 'sitemap';
        return $qvars;
    }

    public function customRewriteRule()
    {
        add_rewrite_rule('sitemap\.xml$', 'index.php?sitemap=index', 'top');
        add_rewrite_rule('sitemap-([0-9]+)?\.xml$', 'index.php?sitemap=list&page=$matches[1]', 'top');
    }

    /**
     * Check the current request URI, if we can determine it's probably an XML sitemap, kill loading the widgets
     */
    public function reduceQueryLoad()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return;
        }

        $request_uri = $_SERVER['REQUEST_URI'];
        $extension   = substr($request_uri, -4);

        if (false !== stripos($request_uri, 'sitemap') && in_array($extension, array('.xml', '.xsl'), true)) {
            remove_all_actions('widgets_init');
        }
    }

    /**
     * Hijack requests for potential sitemaps files.
     * @param \WP_Query $query Main query instance.
     */
    public function getSitemap($query)
    {
        $sitemap = get_query_var('sitemap');
        if (!empty($sitemap)) {
            add_filter('template_include', function () {
                return get_template_directory() . '/sitemap.php';
            });
        }
    }

    /**
     * Schedule options table cleanup daily.
     */
    public function scheduleSitemap()
    {
        if (!wp_next_scheduled('woody_sitemap')) {
            wp_schedule_event(time(), 'daily', 'woody_sitemap');
        }
    }

    /**
     * generateSitemap with WP CLI or Cron
     */
    public function woodySitemap()
    {
        // Si le site est alias, on fusionne toutes les pages dans le même sitemap
        $languages = pll_languages_list();

        // Si la langue n'est pas active on n'ajoute pas les pages au sitemap
        $woody_lang_enable = get_option('woody_lang_enable', []);
        foreach ($languages as $key => $slug) {
            if (!in_array($slug, $woody_lang_enable)) {
                unset($languages[$key]);
            }
        }

        foreach ($languages as $lang) {
            $sitemap = [];
            $query_max = $this->getPosts($lang);
            if (!empty($query_max)) {
                Output::log(sprintf('Sitemap generate %s (%s pages)', strtoupper($lang), $query_max->max_num_pages));
                for ($i = 1; $i <= $query_max->max_num_pages; $i++) {
                    $query = $this->getPosts($lang, $i);
                    if (!empty($query->posts)) {
                        foreach ($query->posts as $post) {
                            $sitemap[] = [
                                'loc' => get_permalink($post),
                                'lastmod' => get_the_modified_date('c', $post),
                                'images' => $this->getImagesFromPost($post)
                            ];
                        }
                    }
                }
            }

            // Chunk sitemap
            $nb_urls_per_page = 1000;
            if (count($sitemap) <= $nb_urls_per_page) {
                $sitemap = [$sitemap];
            } else {
                $sitemap = array_chunk($sitemap, $nb_urls_per_page);
            }

            add_option('woody_sitemap_' . $lang, $sitemap);

            /* Restore original Post Data */
            wp_reset_postdata();
        }
    }

    private function getPosts($lang = PLL_DEFAULT_LANG, $paged = 1, $posts_per_page = 30)
    {
        $query = new \WP_Query([
            'post_type' => ['page', 'touristic_sheet'],
            'orderby' => 'menu_order',
            'order'   => 'DESC',
            'lang' => $lang,
            'posts_per_page' => $posts_per_page,
            'paged' => $paged
        ]);

        if ($query->have_posts()) {
            return $query;
        }
    }

    private function getImagesFromPost($post)
    {
        $images = [];

        if ($post->post_type == 'page') {

            $fields = [
                'field_5b0e5ddfd4b1b', // Visuel et Accroche
                'field_5b44ba5c74495', // En-tête
                'field_5b169fab55db0', // Personnalise : mise en avant
                'field_5ba8ef5bce474', // Personnalise : menu
            ];

            foreach ($fields as $field) {
                $img = get_field($field, $post->ID);
                $this->extractImg($images, $img);
            }

            $sections = get_field('field_5afd2c6916ecb', $post->ID);
            if (!empty($sections)) {
                foreach ($sections as $section) {
                    if (!empty($section['background_img'])) {
                        $this->extractImg($images, $section['background_img']);
                    }

                    if (!empty($section['icon_img'])) {
                        $this->extractImg($images, $section['icon_img']);
                    }

                    if (!empty($section['section_content'])) {
                        foreach ($section['section_content'] as $section_content) {
                            if (!empty($section_content['background_img'])) {
                                $this->extractImg($images, $section_content['background_img']);
                            }

                            if (!empty($section_content['icon_img'])) {
                                $this->extractImg($images, $section_content['icon_img']);
                            }

                            switch ($section_content['acf_fc_layout']) {
                                case 'gallery':
                                    if (!empty($section_content['gallery_items'])) {
                                        foreach ($section_content['gallery_items'] as $gallery_item) {
                                            $this->extractImg($images, $gallery_item);
                                        }
                                    }
                                    break;

                                case 'interactive_gallery':
                                    if (!empty($section_content['interactive_gallery'])) {
                                        foreach ($section_content['interactive_gallery_items'] as $gallery_item) {
                                            $this->extractImg($images, $gallery_item);
                                        }
                                    }
                                    break;

                                case 'manual_focus':
                                    if (!empty($section_content['content_selection'])) {
                                        foreach ($section_content['content_selection'] as $content_selection) {
                                            if (!empty($content_selection['custom_content']) && !empty($content_selection['custom_content']['img'])) {
                                                $this->extractImg($images, $content_selection['custom_content']['img']);
                                            }
                                        }
                                    }
                                    break;

                                case 'socialwall':
                                    if (!empty($section_content['socialwall_manual'])) {
                                        foreach ($section_content['socialwall_manual'] as $socialwall_manual) {
                                            $this->extractImg($images, $socialwall_manual);
                                        }
                                    }
                                    break;

                                default:
                                    if (!empty($section_content['img'])) {
                                        $this->extractImg($images, $section_content['img']);
                                    }
                                    break;
                            }
                        }
                    }
                }
            }
        }

        return $images;
    }

    private function extractImg(&$images, $img)
    {
        if (!empty($img) && !empty($img['url'])) {
            $images[$img['url']] = [
                'loc' => trim($img['url']),
                'title' => $this->cleanXMLWords($img['title']),
                'caption' => $this->cleanXMLWords($img['alt'] . ' ' . $img['caption'] . ' ' . $img['description'])
            ];
        }
    }

    private function cleanXMLWords($str)
    {
        $str = html_entity_decode($str);
        $str = str_replace(['<', '>', '&', '"', "'"], ' ', $str);
        $str = trim($str);
        $str = explode(' ', $str);
        $str = array_unique($str);
        $str = implode(' ', $str);

        return $str;
    }
}
