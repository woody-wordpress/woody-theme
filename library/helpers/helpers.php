<?php

/**
 * Woody
 * @author Léo POIROUX
 * @copyright Raccourci Agency 2022
 */

function woody_get_permalink($post = null)
{
    if (is_int($post)) {
        $post_id = $post;
    } elseif ($post instanceof WP_Post) {
        $post_id = $post->ID;
    } else {
        $post_id = null;
    }

    return apply_filters('woody_get_permalink', $post_id);
}
