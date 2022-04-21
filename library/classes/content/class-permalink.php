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
        \WP_CLI::add_command('woody:flush_permalinks', [$this, 'flush_permalinks']);

        require_once(__DIR__ . '/../../helpers/helpers.php');
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

        $permalink = (!$force) ? wp_cache_get(sprintf('woody_get_permalink_%s', $post_id), 'woody') : null;
        if (empty($permalink)) {
            $permalink = get_permalink($post_id);
            wp_cache_set(sprintf('woody_get_permalink_%s', $post_id), $permalink, 'woody');
        }

        return $permalink;
    }

    public function templateRedirect()
    {
        global $post;
        if (!empty($post)) {
            $permalink = woody_get_permalink($post->ID);
            if (!empty($permalink) && !empty($_SERVER['REQUEST_URI'])) {
                $permalink_path = parse_url($permalink, PHP_URL_PATH);
                $permalink_path = (substr($permalink_path, -1) == '/') ? substr($permalink_path, 0, -1) : $permalink_path;

                $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $request_path = (substr($request_path, -1) == '/') ? substr($request_path, 0, -1) : $request_path;

                //console_log(['permalink_path' => $permalink_path, 'request_path' => $request_path]);
                if ($permalink_path != $request_path) {
                    wp_redirect($permalink, 301, 'Woody Permalink');
                    exit;
                }
            }
        }
    }

    public function redirect404()
    {
        global $wp_query;
        $pll_current_language = pll_current_language();
        if ($wp_query->is_404 && empty($wp_query->queried_object_id) && !empty($_SERVER['REQUEST_URI']) && !empty($pll_current_language)) {
            $permalink = null;

            // Get request path without / on the end
            $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $request_path = (substr($request_path, -1) == '/') ? substr($request_path, 0, -1) : $request_path;

            // Magic get post_id from $request_path
            $post_id = url_to_postid($request_path);

            if (!empty($post_id)) {
                $permalink = woody_get_permalink($post_id);
            } elseif (!empty($request_path)) {
                $segments = explode('/', $request_path);
                $last_segment = end($segments);

                if (!empty($last_segment)) {
                    // Test if is sheet
                    $sheet_lang = null;
                    $sheet_id = null;
                    preg_match('/-([a-z_]{2,})-([0-9]{5,})$/', $last_segment, $sheet_match);
                    if (!empty($sheet_match) && !empty($sheet_match[1]) && !empty($sheet_match[2])) {
                        $sheet_lang = $sheet_match[1];
                        $sheet_id = $sheet_match[2];
                        $query_result = new \WP_Query([
                        'lang' => $pll_current_language,
                        'post_status' => ['publish'],
                        'posts_per_page' => 1,
                        'orderby' => 'ID',
                        'order' => 'ASC',
                        'post_type'   => 'touristic_sheet',
                        'meta_query'  => [
                            'relation' => 'AND',
                            [
                                'key'     => 'touristic_sheet_id',
                                'value'   => $sheet_id,
                                'compare' => 'IN',
                                ]
                            ],
                        ]);
                    } else {
                        $query_result = new \WP_Query([
                        'lang' => $pll_current_language,
                        'posts_per_page' => 1,
                        'post_status' => ['publish'],
                        'orderby' => 'ID',
                        'order' => 'ASC',
                        'name' => $last_segment,
                        'post_type' => 'page'
                    ]);
                    }
                }

                if (!empty($query_result) && !empty($query_result->posts)) {
                    $post = current($query_result->posts);
                    $permalink = woody_get_permalink($post->ID);

                    if (!empty($permalink)) {
                        $parse_permalink = parse_url($permalink, PHP_URL_PATH);
                        $parse_permalink = (substr($parse_permalink, -1) == '/') ? substr($parse_permalink, 0, -1) : $parse_permalink;

                        if (!empty($parse_permalink)) {

                            // On teste si l'url trouvée est bien dans la même langue que celle d'origine (pour une fiche)
                            if (!empty($sheet_lang)) {
                                preg_match('/-([a-z_]{2,})-([0-9]{5,})$/', $parse_permalink, $sheet_match);
                                if (!empty($sheet_match) && !empty($sheet_match[1]) && !empty($sheet_match[2]) && $sheet_match[1] != $sheet_lang) {
                                    $permalink = null;
                                    output_error('redirect404 ' . json_encode([
                                        'request_path' => $request_path,
                                        'parse_permalink' => $parse_permalink,
                                        'pll_current_languag' => $pll_current_language
                                    ]));
                                }
                            }

                            // On définit l'url d'origine
                            $url = $request_path . (substr(WOODY_PERMALINK_STRUCTURE, -1) == '/' ? '/' : '');
                            $match_url = $request_path;

                            if (!empty($permalink) && $url != $parse_permalink && $match_url != $parse_permalink) {
                                $params = [
                                    'url' => $url,
                                    'match_url' => $match_url,
                                    'match_data' => [
                                        'source' => [
                                            'flag_query' => 'ignore'
                                        ]
                                    ],
                                    'group_id' => (int) get_option('woody_auto_redirect'),
                                    'action_type' => 'url',
                                    'action_code' => 301,
                                    'action_data' => [
                                        'url' => $parse_permalink . (substr(WOODY_PERMALINK_STRUCTURE, -1) == '/' ? '/' : '')
                                    ],
                                    'match_type'  => 'url',
                                    'regex'  => 0,
                                ];

                                include WP_PLUGINS_DIR . '/redirection/models/group.php';
                                Red_Item::create($params);
                            }
                        }
                    }
                }
            }

            // Redirect if $permalink exist
            $parse_permalink = parse_url($permalink, PHP_URL_PATH);
            $parse_permalink = (substr($parse_permalink, -1) == '/') ? substr($parse_permalink, 0, -1) : $parse_permalink;
            if (!empty($permalink) && !empty($parse_permalink) && $parse_permalink != $request_path) {
                wp_redirect($permalink, 301, 'Woody Soft 404');
                exit;
            }
        } elseif (is_singular()) {
            global $post, $page;
            $num_pages = substr_count($post->post_content, '<!--nextpage-->') + 1;
            if ($page > $num_pages) {
                wp_redirect(woody_get_permalink($post->ID), 301, 'Woody NexPage');
                exit;
            }
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
                    $this->cacheDeleteChildrenPosts($children_page->ID, $pre_post_update);
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
        return ($count != 0) ? true : false;
    }

    // --------------------------------
    // Clean Cache
    // --------------------------------
    public function flush_permalinks()
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
            $count_updated = $wpdb->query($wpdb->prepare("UPDATE wp_redirection_items SET status = 'disabled' WHERE action_data LIKE '%$slug' AND status = 'enabled'"));
            $result = (!empty($count_updated)) ? 'before_delete_post : Disabling ' . $count_updated . ' redirect(s) to urls ending with ' . $slug : 'Found 0 redirection to this page';
            output_log($result);
        }
    }
}
