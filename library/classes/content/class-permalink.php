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
        add_action('woody_theme_update', [$this, 'cleanTransient']);

        add_action('template_redirect', [$this, 'redirect404'], 999);
    }

    public function woodyGetPermalink($post_id)
    {
        $return = '';
        $current_lang = pll_current_language();
        $posts = get_transient('woody_get_permalink');

        if (!empty($posts[$post_id]) && !empty($posts[$post_id][$current_lang])) {
            $return = $posts[$post_id][$current_lang];
        } else {
            $return = get_permalink($post_id) ?: get_permalink();
            $posts[$post_id][$current_lang] = $return;
            set_transient('woody_get_permalink', $posts);
        }

        return $return;
    }

    public function redirect404()
    {
        global $wp_query, $wp;
        if ($wp_query->is_404 && !empty($wp->request)) {
            $permalink = null;
            $post_id = url_to_postid($wp->request);
            if (!empty($post_id)) {
                $permalink = get_permalink($post_id);
            } else {
                $segments = explode('/', $wp->request);
                $last_segment = end($segments);

                // Test if is sheet
                preg_match('/-([a-z_]{2,})-([0-9]{5,})$/', $last_segment, $sheet_id);
                if (!empty($sheet_id) && !empty($sheet_id[2])) {
                    $query_result = new \WP_Query([
                        'lang' => pll_current_language(), // query all polylang languages
                        'post_status' => ['publish'],
                        'posts_per_page' => 1,
                        'orderby' => 'ID',
                        'order' => 'ASC',
                        'post_type'   => 'touristic_sheet',
                        'meta_query'  => [
                            'relation' => 'AND',
                            [
                                'key'     => 'touristic_sheet_id',
                                'value'   => $sheet_id[2],
                                'compare' => 'IN',
                            ]
                        ],
                    ]);
                } else {
                    $query_result = new \WP_Query([
                        'lang' => pll_current_language(),
                        'posts_per_page' => 1,
                        'post_status' => ['publish'],
                        'orderby' => 'ID',
                        'order' => 'ASC',
                        'name' => $last_segment,
                        'post_type' => 'page'
                    ]);
                }

                if (!empty($query_result->posts)) {
                    $post = current($query_result->posts);
                    $permalink = get_permalink($post->ID);

                    if (!empty($permalink)) {
                        $parse_permalink = parse_url($permalink, PHP_URL_PATH);
                        if (!empty($parse_permalink) && $parse_permalink != '/') {
                            if (substr($wp->request, -1) == '/') {
                                $url = '/' . $wp->request;
                                $match_url = '/' . substr($wp->request, 0, -1);
                            } else {
                                $url = '/' . $wp->request . '/';
                                $match_url = '/' . $wp->request;
                            }

                            if ($url != $parse_permalink && $match_url != $parse_permalink) {
                                $params = [
                                    'url' => $url,
                                    'match_url' => $match_url,
                                    'group_id' => (int) get_option('woody_auto_redirect'),
                                    'action_type' => 'url',
                                    'action_code' => 301,
                                    'action_data' => [
                                        'url' => $parse_permalink
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
            if (!empty($permalink)) {
                wp_redirect($permalink, 301, 'Woody');
                exit;
            }
        } elseif (is_singular()) {
            global $post, $page;
            $num_pages = substr_count($post->post_content, '<!--nextpage-->') + 1;
            if ($page > $num_pages) {
                wp_redirect(get_permalink($post->ID), 301, 'Woody');
                exit;
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
