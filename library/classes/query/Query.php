<?php

namespace Woody\WoodyTheme\library\classes\query;

class Query
{
    public function __construct()
    {
        add_filter('posts_where', [$this, 'wpQueryPostLike'], 10, 2);
    }

    public function wpQueryPostLike($where = '', $wp_query)
    {
        global $wpdb;
        if (!$post_like = $wp_query->get('post_like')) {
            return $where;
        }

        if (is_string($post_like)) {
            return $where .= ' AND ' . $wpdb->posts . ".post_title LIKE '%" . esc_sql($wpdb->esc_like($post_like)) . "%'";
        }

        if (!is_array($post_like)) {
            return $where;
        }

        if (!isset($post_like[0]) || (!is_string($post_like[0]) && !is_array($post_like[0]))) {
            return $where;
        }

        $post_like = wp_parse_args($post_like, array(
            'relation'  => 'OR',
            'column'    => 'post_title',
        ));

        $post_title_like_array = array();
        $post_title_like_array[] = $post_like[0];

        if (is_array($post_like[0])) {
            $post_title_like_array = $post_like[0];
        }

        $where_addon = array();
        foreach ($post_title_like_array as $keyword) {
            $where_addon[] = $wpdb->posts . '.' . $post_like['column'] . " LIKE '%" . esc_sql($wpdb->esc_like($keyword)) . "%'";
        }

        return $where . (' AND ( ' . implode(' ' . $post_like['relation'] . ' ', $where_addon) . ' )');
    }

    /*
    // Usage:
    // Post_title like 'Contact' OR 'Informations':
    $query = new WP_Query(array(
        'post_type'         => 'page',
        'posts_per_page'    => -1,
        'post_like'         => array(
            'relation'  => 'OR',            // OR | AND – default: OR
            'column'    => 'post_title',    // post_title | post_content etc… – default: post_title
            array(
                'Contact',
                'Informations',
            )
        )
    ));
    // Post_title like 'Contact' AND 'Informations':
    $query = new WP_Query(array(
        'post_type'         => 'page',
        'posts_per_page'    => -1,
        'post_like'         => array(
            'relation'  => 'AND',
            array(
                'Contact',
                'Informations',
            )
        )
    ));
    // Post_title like 'Contact':
    $query = new WP_Query(array(
        'post_type'         => 'page',
        'posts_per_page'    => -1,
        'post_like'         => 'Contact'
    ));
    // Post_content like 'Contact':
    $query = new WP_Query(array(
        'post_type'         => 'page',
        'posts_per_page'    => -1,
        'post_like'         => array(
            'column'    => 'post_content',
            array(
                'Contact'
            )
        )
    ));
    */
}
