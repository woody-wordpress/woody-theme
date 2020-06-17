<?php

function getActiveShares()
{
    $return['current_url'] = add_query_arg([$_GET], get_permalink());
    $return['current_media'] = !empty(get_field('field_5b0e5ddfd4b1b')) ? get_field('field_5b0e5ddfd4b1b')['url'] : "";
    $active_shares = get_option('options_active_shares');
    $path_icons = '/src/icons/shares/';

    $shares = [ 'facebook' => ['sharing_message' => __('Partager sur Facebook', 'woody-theme'),
                               'sharing_link' => 'https://facebook.com/sharer/sharer.php?u',
                               'sharing_icon' => file_get_contents(get_template_directory() . $path_icons . 'facebook.svg')
                            ],
                'twitter' => ['sharing_message' => __('Partager sur Twitter', 'woody-theme'),
                              'sharing_link' => 'https://twitter.com/intent/tweet/?url',
                              'sharing_icon' => file_get_contents(get_template_directory() . $path_icons . 'twitter.svg')
                            ],
                'pinterest' => ['sharing_message' => __('Partager sur Pinterest', 'woody-theme'),
                                'sharing_link' => 'https://pinterest.com/pin/create/button/?url',
                                'sharing_icon' => file_get_contents(get_template_directory() . $path_icons . 'pinterest.svg')
                            ],
                'mail' => [  'sharing_message' => __('Partager par e-mail', 'woody-theme'),
                             'sharing_link' => 'mailto:?body',
                             'sharing_icon' => file_get_contents(get_template_directory() . $path_icons . 'mail.svg')
                        ],
                'whatsapp' => [ 'sharing_message' => __('Partager sur WhatsApp', 'woody-theme'),
                                'sharing_link'=> 'whatsapp://send?text',
                                'sharing_icon' => file_get_contents(get_template_directory() . $path_icons . 'whatsapp.svg')
                            ]
            ];

    foreach ($shares as $name => $share) {
        if (in_array($name, $active_shares)) {
            $return['active_shares'][$name] = $share;
        }
    }

    return $return;
}
