<?php

/**
 * SiteMap
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_SiteMap
{
    private $existing_options;

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

        // Disable native sitemap /wp-sitemap-posts-page-1.xml
        add_filter('wp_sitemaps_enabled', '__return_false');

        // Adding a shortcode to display sitemap for humans
        add_shortcode('woody_sitemap', [$this, 'sitemapShortcode']);

        // Cron + CLI
        add_action('wp', [$this, 'scheduleSitemap']);
        add_action('woody_sitemap', [$this, 'woodySitemap']);
        \WP_CLI::add_command('woody:sitemap', [$this, 'woodySitemap']);

        add_action('woody_sitemap_set_shortcode_by_lang', [$this, 'setShortcodeByLang']);
        add_action('woody_sitemap_update_sitemap_form_posts', [$this, 'updateSitemapFormPosts']);
    }

    public function woodySitemap()
    {
        global $wpdb;

        // Get old sitemap chunks
        $this->existing_options = [];
        $results = $wpdb->get_results("SELECT option_name FROM {$wpdb->prefix}options WHERE option_name LIKE '%woody_sitemap%'");
        foreach ($results as $val) {
            $this->existing_options[$val->option_name] = $val->option_name;
        }

        $this->asyncShortcode();
        $this->asyncXML();

        // Cleanup
        if (!empty($this->existing_options)) {
            foreach ($this->existing_options as $option_name) {
                delete_option($option_name);
                output_success('DELETE : ' . $option_name);
            }
        }
    }

    public function queryVars($qvars)
    {
        $qvars[] = 'woody-sitemap';
        return $qvars;
    }

    public function customRewriteRule()
    {
        add_rewrite_rule('sitemap\.xml$', 'index.php?woody-sitemap=index', 'top');
        add_rewrite_rule('sitemap-([0-9]+)?\.xml$', 'index.php?woody-sitemap=list&page=$matches[1]', 'top');
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
        $sitemap = get_query_var('woody-sitemap');
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
    private function asyncXML()
    {
        global $wpdb;

        // Si le site est alias, on fusionne toutes les pages dans le même sitemap
        $languages = pll_languages_list();

        // Si la langue n'est pas active on n'ajoute pas les pages au sitemap
        $woody_lang_enable = get_option('woody_lang_enable', []);
        foreach ($languages as $key => $slug) {
            if (!in_array($slug, $woody_lang_enable)) {
                unset($languages[$key]);
            }
        }

        // On merge toutes les langues quand nous sommes sur un seul domaine
        $polylang = get_option('polylang');
        if ($polylang['force_lang'] == 3 && !empty($polylang['domains'])) {
            $merge_sitemap_lang = false;
        } else {
            $merge_sitemap_lang = true;
        }

        $sitemap = [];
        $nb_chunks = 0;
        foreach ($languages as $lang) {
            // Si on ne fusionne pas toutes les langues
            if (!$merge_sitemap_lang) {
                $sitemap = [];
                $nb_chunks = 0;
            }

            // Get Posts
            $query_max = $this->getPosts($lang);
            if (!empty($query_max)) {
                for ($page = 1; $page <= $query_max->max_num_pages; $page++) {
                    $option_name = sprintf('woody_sitemap_%s_chunk_%s', $merge_sitemap_lang ? 'all' : $lang, $nb_chunks);
                    do_action('woody_async_add', 'woody_sitemap_update_sitemap_form_posts', ['lang' => $lang, 'page' => $page, 'option_name' => $option_name], $option_name);

                    if (!empty($this->existing_options[$option_name])) {
                        unset($this->existing_options[$option_name]);
                    }

                    $nb_chunks++;
                }
            }

            // Save registry
            if (!$merge_sitemap_lang) {
                $option_name = sprintf('woody_sitemap_%s', $lang);
                update_option($option_name, $nb_chunks, 'no');
                output_success('SAVE : ' . $option_name);
                if (!empty($this->existing_options[$option_name])) {
                    unset($this->existing_options[$option_name]);
                }
            }
        }

        // Save registry
        if ($merge_sitemap_lang) {
            $option_name = sprintf('woody_sitemap_%s', 'all');
            update_option($option_name, $nb_chunks, 'no');
            output_success('SAVE : ' . $option_name);
            if (!empty($this->existing_options[$option_name])) {
                unset($this->existing_options[$option_name]);
            }
        }

        /* Restore original Post Data */
        wp_reset_postdata();
    }

    public function updateSitemapFormPosts($args = [])
    {
        global $wpdb;

        $sitemap = [];
        $query = $this->getPosts($args['lang'], $args['page']);
        if (!empty($query->posts)) {
            foreach ($query->posts as $post) {
                $woodyseo_index = $wpdb->get_row("SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id='{$post->ID}' AND meta_key='woodyseo_index'");
                if (is_null($woodyseo_index) || $woodyseo_index->meta_value == true) {
                    // Si la meta a explicitement été définie sur 0 on n'ajoute pas le post au sitemap
                    // Les fiches SIT et pages dont la meta n'a pas été définie sont ajoutées au sitemap quand même
                    $sitemap[] = [
                        'loc' => apply_filters('woody_get_permalink', $post->ID),
                        'lastmod' => get_the_modified_date('c', $post),
                        'images' => $this->getImagesFromPost($post),
                    ];
                }
                $wpdb->flush();
            }
        }

        update_option($args['option_name'], $sitemap, 'no');
        output_success('SAVE : ' . $args['option_name']);
    }

    private function getPosts($lang = PLL_DEFAULT_LANG, $paged = 1, $posts_per_page = 1000)
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

    // Shortcode

    public function sitemapShortcode($atts)
    {
        $return = '';

        $lang = pll_current_language();
        $sitemap['posts'] = get_option('woody_sitemap_shortcode_' . $lang);
        if (!empty($sitemap['posts'])) {
            $return = \Timber::compile('woody_widgets/sitemap/tpl_01/tpl.twig', $sitemap);
        }

        return $return;
    }

    private function asyncShortcode()
    {
        $languages = pll_languages_list();
        foreach ($languages as $lang) {
            $option_name = 'woody_sitemap_shortcode_' . $lang;
            do_action('woody_async_add', 'woody_sitemap_set_shortcode_by_lang', ['lang' => $lang, 'option_name' => $option_name], $option_name);

            if (!empty($this->existing_options[$option_name])) {
                unset($this->existing_options[$option_name]);
            }
        }
    }

    public function setShortcodeByLang($args = [])
    {
        $sitemap = $this->getPostsByHierarchy(0, $args['lang']);
        update_option($args['option_name'], $sitemap, 'no');
        output_success('SAVE : ' . $args['option_name']);
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
                    'url' => apply_filters('woody_get_permalink', $post->ID),
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
