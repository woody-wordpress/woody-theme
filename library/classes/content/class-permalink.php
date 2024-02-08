<?php

/**
 * Permalink
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

//use Woody\Services\Providers\Wp;

class WoodyTheme_Permalink
{
    public function __construct()
    {
        $this->registerHooks();
        //$this->wpProvider = new Wp(); // Don't use because wp-plugin isn't opensource yet !
    }

    protected function registerHooks()
    {
        add_filter('woody_get_permalink', [$this, 'woodyGetPermalink'], 10, 2);

        add_action('pll_save_post', [$this, 'savePost'], 10, 3);
        add_action('delete_post', [$this, 'deletePost'], 10);
        add_action('template_redirect', [$this, 'templateRedirect'], 10);
        add_action('template_redirect', [$this, 'redirect404'], 999);

        add_action('before_delete_post', [$this, 'cleanRedirects']);

        // WP_SITE_KEY=site_key wp woody:flush_permalinks
        \WP_CLI::add_command('woody:flush_permalinks', [$this, 'flushPermalinks']);
        \WP_CLI::add_command('woody:export_permalinks', [$this, 'exportPermalinks']);

        require_once(__DIR__ . '/../../helpers/permalinks.php');
    }

    public function woodyGetPermalink($post_id = null, $force = false)
    {
        if (empty($post_id)) {
            global $post;
            if (!is_object($post)) {
                return;
            }

            $post_id = $post->ID;
        }

        $permalink = ($force) ? null : wp_cache_get(sprintf('woody_get_permalink_%s', $post_id), 'woody');
        if (empty($permalink)) {
            $permalink = get_permalink($post_id);
            wp_cache_set(sprintf('woody_get_permalink_%s', $post_id), $permalink, 'woody');
        }

        return $permalink;
    }

    public function templateRedirect()
    {
        /**
         * Cette fonction permet de toujours rediriger un post vers son permalink.
         * Utile dans le cas des fiches SIT où l'on change le parent
         */
        global $post;
        if (!empty($post)) {
            $permalink = woody_get_permalink($post->ID);
            if (!empty($permalink) && !empty($_SERVER['REQUEST_URI'])) {
                $permalink_path = parse_url($permalink, PHP_URL_PATH);
                $permalink_path = (substr($permalink_path, -1) == '/') ? substr($permalink_path, 0, -1) : $permalink_path;

                $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $request_path = (substr($request_path, -1) == '/') ? substr($request_path, 0, -1) : $request_path;

                if ($permalink_path !== $request_path) {
                    wp_redirect($permalink, 301, 'Woody Permalink');
                    exit;
                }
            }
        }
    }

    public function redirect404()
    {
        global $wp_query;
        if ($wp_query->is_404 && empty($wp_query->queried_object_id)) {
            $request_path = $this->getPath($_SERVER['REQUEST_URI']);

            if (!empty($request_path)) {
                // Magic get post_id from $request_path
                $post_id = url_to_postid($request_path);
                $permalink = (empty($post_id)) ? null : get_permalink($post_id); // Ne pas remplacer par woody_get_permalink
                if (!empty($permalink)) {
                    $this->saveAndRedirect($permalink, $request_path, 'Magic');
                    exit();
                }

                // Test if is a sheet
                $permalink = $this->getSheetPermalink($request_path);
                if (!empty($permalink)) {
                    $this->saveAndRedirect($permalink, $request_path, 'Sheet');
                    exit();
                }

                // Test if is a post
                $permalink = $this->getPostPermalink($request_path);
                if (!empty($permalink)) {
                    $this->saveAndRedirect($permalink, $request_path, 'Post');
                    exit();
                }
            }
        } elseif (is_singular()) {
            global $post, $page;
            $num_pages = substr_count($post->post_content, '<!--nextpage-->') + 1;
            if ($page > $num_pages) {
                wp_redirect(get_permalink($post->ID), 301, 'Woody NexPage');
                exit;
            }
        }
    }

    private function getSheetPermalink($request_path)
    {
        $pll_current_language = pll_current_language();
        if (!empty($request_path) && !empty($pll_current_language)) {
            $request_path = explode('/', $request_path);
            $last_segment = end($request_path);

            if (!empty($last_segment)) {
                preg_match('#-([a-z_]{2,})-(\d{5,})$#', $last_segment, $sheet_match);
                $sheet_lang = (empty($sheet_match[1])) ? null : $this->isAvailableLang($sheet_match[1]);
                $sheet_id = (empty($sheet_match[2])) ? null : $sheet_match[2];

                // Si $sheet_lang est vide car non-definie ou non-available, on prend la pll_current_language
                $sheet_lang = (empty($sheet_lang)) ? $pll_current_language : $sheet_lang;

                if (!empty($sheet_lang) && !empty($sheet_id)) {
                    $query_result = new \WP_Query([
                        'lang' => $sheet_lang,
                        'post_status' => ['publish'],
                        'posts_per_page' => 1,
                        'orderby' => 'ID',
                        'order' => 'ASC',
                        'post_type'   => 'touristic_sheet',
                        'meta_query'  => [
                            'relation' => 'AND', [
                                'key'     => 'touristic_sheet_id',
                                'value'   => $sheet_id,
                            ]
                        ]
                    ]);

                    if (!empty($query_result) && !empty($query_result->posts)) {
                        $post = current($query_result->posts);
                        return get_permalink($post->ID); // Ne pas remplacer par woody_get_permalink
                    }
                }
            }
        }
    }

    private function getPostPermalink($request_path)
    {
        $pll_current_language = pll_current_language();
        if (!empty($request_path) && !empty($pll_current_language)) {
            $request_path = explode('/', $request_path);
            $last_segment = end($request_path);

            if (!empty($last_segment)) {
                $query_result = new \WP_Query([
                    'lang' => $pll_current_language,
                    'posts_per_page' => 1,
                    'post_status' => ['publish'],
                    'orderby' => 'ID',
                    'order' => 'ASC',
                    'name' => $last_segment,
                    'post_type' => 'page'
                ]);

                if (!empty($query_result) && !empty($query_result->posts)) {
                    $post = current($query_result->posts);
                    return get_permalink($post->ID); // Ne pas remplacer par woody_get_permalink
                }
            }
        }
    }

    private function isAvailableLang($lang)
    {
        if (function_exists('pll_languages_list')) {
            $languages = pll_languages_list(['fields' => '']);
            foreach ($languages as $language) {
                if ($language->slug == $lang) {
                    return $lang;
                }
            }
        }
    }

    private function getPath($url)
    {
        if (!empty($url)) {
            $url_path = parse_url($url, PHP_URL_PATH);
            $url_path = (substr($url_path, -1) == '/') ? substr($url_path, 0, -1) : $url_path;
            return ($url_path == '/') ? null : $url_path;
        }
    }

    private function saveAndRedirect($permalink, $request_path = null, $type)
    {
        $permalink_path = $this->getPath($permalink);
        $request_path = (empty($request_path)) ? $this->getPath($_SERVER['REQUEST_URI']) : $request_path;

        if (!empty($permalink_path) && !empty($request_path) && $permalink_path != $request_path) {

            // NOTE: On désactive la sauvegarde des redirections Soft 404 car cela génère des problèmes par moment.
            // $params = [
            //     'url' => $request_path . (substr(WOODY_PERMALINK_STRUCTURE, -1) == '/' ? '/' : ''),
            //     'match_url' => $request_path,
            //     'match_data' => [
            //         'source' => [
            //             'flag_query' => 'ignore'
            //         ]
            //     ],
            //     'group_id' => (int) get_option('woody_auto_redirect'),
            //     'action_type' => 'url',
            //     'action_code' => 301,
            //     'action_data' => [
            //         'url' => $permalink_path . (substr(WOODY_PERMALINK_STRUCTURE, -1) == '/' ? '/' : '')
            //     ],
            //     'match_type'  => 'url',
            //     'regex'  => 0,
            // ];

            // include WP_PLUGINS_DIR . '/redirection/models/group.php';
            // Red_Item::create($params);

            wp_redirect($permalink, 301, 'Woody Soft 404 (' . $type . ')');
            exit;
        }
    }

    // --------------------------------
    // Save Post
    // --------------------------------
    public function savePost($post_id, $post, $update)
    {
        $this->deletePost($post_id);
        $this->cacheDeleteChildrenPosts($post_id);
    }

    public function cacheDeleteChildrenPosts($post_id)
    {
        $has_children = $this->hasChildren($post_id);
        if ($has_children) {
            $children_pages = $this->getPages($post_id);
            if (!empty($children_pages)) {
                foreach ($children_pages as $children_page) {
                    $this->deletePost($children_page->ID);
                    // Recursively
                    $this->cacheDeleteChildrenPosts($children_page->ID);
                }
            }
        }
    }

    public function deletePost($post_id)
    {
        wp_cache_delete(sprintf('woody_get_permalink_%s', $post_id), 'woody');
    }

    // TODO: use wpProvider
    private function getPages($parent_id = 0, $lang = null)
    {
        global $wpdb;
        $pages = [];

        if (function_exists('pll_current_language') && !is_null($lang)) {
            // If Polylang Pro installed + pages de 1er niveau
            $term_taxonomy_lang_id = $this->getLangTermId($lang);

            if (!empty($term_taxonomy_lang_id)) {
                $sql = "SELECT * FROM wp_posts p JOIN wp_term_relationships t ON p.ID = t.object_id WHERE (p.post_type = 'page' AND p.post_status IN ('publish', 'private', 'draft')) AND p.post_parent = %d AND t.term_taxonomy_id = %s ORDER BY p.menu_order ASC";
                $pages = $wpdb->get_results($wpdb->prepare($sql, [$parent_id, $term_taxonomy_lang_id]));
            }
        } else {
            // if no WPML
            $sql = "SELECT * FROM wp_posts p WHERE (post_type = 'page' AND post_status IN ('publish', 'private', 'draft')) AND post_parent = %d ORDER BY menu_order ASC";
            $pages = $wpdb->get_results($wpdb->prepare($sql, [$parent_id]));
        }

        return $pages;
    }

    // TODO: use wpProvider
    public function hasChildren($id)
    {
        global $wpdb;
        $sql = "SELECT count(*) FROM wp_posts WHERE (post_type = 'page' AND post_status IN ('publish', 'private', 'draft')) AND post_parent = %d";
        $count = $wpdb->get_var($wpdb->prepare($sql, [$id]));
        return $count != 0;
    }

    // --------------------------------
    // Clean Cache
    // --------------------------------
    public function flushPermalinks()
    {
        output_h1('Flush and Warm Permalinks');

        // Si le site est alias, on fusionne toutes les pages dans le même sitemap
        $languages = pll_languages_list();

        // Get Posts
        $index_lang = 1;
        $nb_lang = is_countable($languages) ? count($languages) : 0;
        foreach ($languages as $lang) {
            $query_max = $this->getPosts($lang, 1, 1);
            if (!empty($query_max) && !empty($query_max->found_posts)) {
                $max_num_pages = ceil($query_max->found_posts / 10);
                for ($page = 1; $page <= $max_num_pages; ++$page) {
                    output_h2(sprintf('UPDATE %s/%s (lang %s - %s/%s)', $page, $max_num_pages, strtoupper($lang), $index_lang, $nb_lang));
                    $query = $this->getPosts($lang, $page, 10);
                    if (!empty($query->posts)) {
                        foreach ($query->posts as $post_id) {
                            $this->deletePost($post_id);
                            $permalink = woody_get_permalink($post_id);
                            output_success(sprintf('%s [%s]', $permalink, $post_id));
                        }
                    }
                }
            }

            ++$index_lang;
        }
    }

    private function getPosts($lang = PLL_DEFAULT_LANG, $paged = 1, $posts_per_page = 10)
    {
        $args = [
            'lang' => $lang,
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'fields' => 'ids'
        ];

        $query = new \WP_Query($args);
        if ($query->have_posts()) {
            return $query;
        }
    }

    public function cleanRedirects($post_id)
    {
        $slug = get_post_meta($post_id, '_wp_desired_post_slug', true);
        if (!empty($slug)) {
            global $wpdb;
            $count_updated = $wpdb->query($wpdb->prepare("UPDATE wp_redirection_items SET status = 'disabled' WHERE action_data LIKE '%{$slug}' AND status = 'enabled'"));
            $result = (empty($count_updated)) ? 'Found 0 redirection to this page' : 'before_delete_post : Disabling ' . $count_updated . ' redirect(s) to urls ending with ' . $slug;
            output_log($result);
        }
    }

    // --------------------------------
    // List Permalinks
    // --------------------------------
    public function exportPermalinks($args, $assoc_args)
    {
        // WP_SITE_KEY=superot wp woody:export_permalinks --source=en --target=fr

        output_h1('Exports Permalinks');

        $old_filepath = dropzone_get('woody_export_permalinks');
        if(!empty($old_filepath)) {
            unlink($old_filepath);
            output_log('Suppression de ' . $old_filepath);
        }

        if(empty($assoc_args['source'])) {
            output_error('Argument --source nécessaire (sous la forme d\'un code langue (ex: en)');
            exit();
        } else {
            $source_lang = $assoc_args['source'];
        }

        if(empty($assoc_args['target'])) {
            output_error('Argument --target nécessaire (sous la forme d\'un code langue (ex: fr)');
            exit();
        } else {
            $target_lang = $assoc_args['target'];
        }

        // Upload file
        $filename = 'permalinks_' . $source_lang . '-' . $target_lang . '_' . date('Y-m-d_H-i-s') .'.csv';
        $filepath = sprintf('%s/%s', WP_UPLOAD_DIR, $filename);
        $fileurl = sprintf('%s/%s', WP_UPLOAD_URL, $filename);

        // Export CSV
        file_put_contents($filepath, $source_lang . ',' . $target_lang . "\n");

        // Get Posts
        $query_max = $this->getPosts($source_lang, 1, 1);
        if (!empty($query_max) && !empty($query_max->found_posts)) {
            $max_num_pages = ceil($query_max->found_posts / 10);
            for ($page = 1; $page <= $max_num_pages; ++$page) {
                output_h2(sprintf('EXPORT %s/%s (lang %s > %s)', $page, $max_num_pages, strtoupper($source_lang), strtoupper($target_lang)));
                $query = $this->getPosts($source_lang, $page, 10);
                if (!empty($query->posts)) {
                    foreach ($query->posts as $post_id) {

                        $source_post = get_post($post_id);
                        if($source_post->post_status != 'publish') {
                            output_log(sprintf('SKIP source unpublish %s [%s]', $source_post->post_title, $source_post->ID));
                            continue;
                        }

                        $tr_ids = pll_get_post_translations($post_id);
                        if(!empty($tr_ids[$target_lang])) {
                            $target_post_id = $tr_ids[$target_lang];
                            $target_post = get_post($target_post_id);

                            if($target_post->post_status == 'publish') {
                                $line = woody_get_permalink($post_id) . ',' . woody_get_permalink($target_post_id);
                                file_put_contents($filepath, $line . "\n", FILE_APPEND);
                                output_log(sprintf('ADD %s', $line));
                            } else {
                                output_log(sprintf('SKIP target unpublish %s [%s]', $source_post->post_title, $source_post->ID));
                            }
                        } else {
                            output_log(sprintf('SKIP no translation %s [%s]', $source_post->post_title, $source_post->ID));
                        }
                    }
                }
            }
        }

        output_h2('Finalisation');
        output_success('Télécharger l\'export des permalinks : ' . $fileurl);
        dropzone_set('woody_export_permalinks', $filepath);
    }
}
