<?php

function getActiveShares()
{
    $return['current_url'] = add_query_arg([$_GET], get_permalink());
    $return['current_media'] = !empty(get_field('field_5b0e5ddfd4b1b')) ? get_field('field_5b0e5ddfd4b1b')['url'] : "";
    $return['active_shares'] = [
        'facebook' => true,
        'twitter' => true,
        'email' => true,
        'pinterest' => true
    ];

    return $return;
}
