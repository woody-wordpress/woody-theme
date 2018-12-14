<?php

function getActiveShares()
{
    $return['current_url'] = get_permalink();
    $return['active_shares'] = [
        'facebook' => true,
        'twitter' => true,
        'google' => true,
        'email' => true,
        'pinterest' => true
    ];

    wd($return, 'return');

    return $return;
}
