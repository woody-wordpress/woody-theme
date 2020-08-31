<?php

/**
 * SiteMap
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

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

        add_filter('wp_sitemaps_enabled', '__return_false');

        add_action('woody_sitemap', [$this, 'woodySitemap']);
        add_action('woody_sitemap', [$this, 'woodyHumanSitemap']);
        add_action('wp', [$this, 'scheduleSitemap']);
        \WP_CLI::add_command('woody:sitemap', [$this, 'woodySitemap']);

        // Adding a shortcode to display sitemap for humans
        add_shortcode('woody_sitemap', [$this, 'sitemapShortcode']);
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
                for ($i = 1; $i <= $query_max->max_num_pages; $i++) {
                    $query = $this->getPosts($lang, $i);
                    if (!empty($query->posts)) {
                        foreach ($query->posts as $post) {
                            // On récupère la meta woodyseo_index
                            $index = get_post_meta($post->ID, 'woodyseo_index', true);

                            // Si la meta a explicitement été définie sur 0 on n'ajoute pas le post au sitemap
                            // Les fiches SIT et pages dont la meta n'a pas été définie sont ajoutées au sitemap quand même
                            if ($index !== '0') {
                                $sitemap[] = [
                                    'loc' => get_permalink($post),
                                    'lastmod' => get_the_modified_date('c', $post),
                                    'images' => $this->getImagesFromPost($post),
                                ];
                            }
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

            update_option('woody_sitemap_' . $lang, $sitemap, 'no');

            /* Restore original Post Data */
            wp_reset_postdata();
        }
    }

    private function getPosts($lang = PLL_DEFAULT_LANG, $paged = 1, $posts_per_page = 30)
    {
        $args = [
            'post_type' => ['page', 'touristic_sheet'],
            'orderby' => 'menu_order',
            'order'   => 'DESC',
            'lang' => $lang,
            'posts_per_page' => $posts_per_page,
            'paged' => $paged
        ];

        $args = apply_filters('woody_custom_sitemap_args', $args);

        $query = new \WP_Query($args);

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

    public function sitemapShortcode($atts)
    {
        $return = '';
        $lang = pll_current_language();

        $sitemap['posts'] = get_transient('sitemap_posts_' . $lang);
        if (empty($sitemap['posts'])) {
            $sitemap['posts'] = $this->getPostsByHierarchy(0, $lang);
            set_transient('sitemap_posts_' . $lang, $sitemap['posts']);
        }
        $return = \Timber::compile('woody_widgets/sitemap/tpl_01/tpl.twig', $sitemap);

        return $return;
    }

    public function woodyHumanSitemap()
    {
        $languages = pll_languages_list();

        foreach ($languages as $lang) {
            $sitemap = $this->getPostsByHierarchy(0, $lang);
            set_transient('sitemap_posts_' . $lang, $sitemap);
        }
    }

    private function getPostsByHierarchy($post_parent_id, $lang)
    {
        $return = [];

        // On récupère tous les posts enfant de $post_parent_id
        $args = array(
            'post_status' => array(
                'publish'
            ),
            'post_parent' => $post_parent_id,
            'posts_per_page' => -1,
            'post_type' => 'page',
            'lang' => $lang,
            'order' => 'ASC',
            'orderby' => 'menu_order',
        );

        $query_result = new \WP_Query($args);

        // S'il y a des posts, on les ajoutes à $return
        if (!empty($query_result->posts)) {
            foreach ($query_result->posts as $post) {
                $return[$post->ID] = [
                    'url' => get_permalink($post->ID),
                    'title' => get_the_title($post->ID),
                    'parent' => wp_get_post_parent_id($post->ID)
                ];

                // On réitère le processus tant que les posts trouvés ont des enfants
                $return[$post->ID]['children'] = $this->getPostsByHierarchy($post->ID, $lang);
            }
        }

        return $return;
    }
}
