<?php

/**
 * API Rest
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.25.2
 */

class WoodyTheme_Api_Rest
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('rest_api_init', function () {
            register_rest_route('woody', 'page/preview', array(
                'methods' => 'GET',
                'callback' => [$this, 'getPagePreviewApiRest']
            ));
        });

        add_action('rest_api_init', function () {
            register_rest_route('woody', 'components/templates', array(
                'methods' => 'GET',
                'callback' => [$this, 'getWoodyTemplatesConfig']
            ));
        });
    }

    public function getPagePreviewApiRest() {

        # /wp-json/woody/page/preview?post=${id}
        $post_id = filter_input(INPUT_GET, 'post', FILTER_VALIDATE_INT);

        if(!empty($post_id)) {
            header('xkey: ' . WP_SITE_KEY . '_' . $post_id, false);
            $post = get_post($post_id);

            $wrapper = [
                'display_img' => true,
                'display_elements' => [
                    'icon',
                    'pretitle',
                    'subtitle',
                    'description'
                ],
                'display_button' => true
            ];

            return getPagePreview($wrapper, $post);
        }
    }

    public function getWoodyTemplatesConfig()
    {
        $return = [];

        $woodyComponents = getWoodyComponents();

        foreach ($woodyComponents as $key => $component) {
            if(empty($component['thumbnails']['small'])) {
                $thumbnail = '';
            } else {
                if (strpos($component['thumbnails']['small'], 'custom_woody_tpls') === false) {
                    $img_views_path = '/img/woody-library/views/';
                } else {
                    $img_views_path = apply_filters('custom_woody_tpls_thumbnails_path', '/img/', $component['thumbnails']['small']);
                }

                $thumbnail = WP_HOME . '/app/dist/' . WP_SITE_KEY . $img_views_path . $component['thumbnails']['small'] . '?version=' . get_option('woody_theme_version');
            }

            $return[$key] = [
                'name' => empty($component['name']) ? '' : $component['name'],
                'description' => empty($component['description']) ? '' : $component['description'],
                'thumbnail' => $thumbnail,
                'display_options' => empty($component['display']) ? '' : $component['display'],
                'is_new_tpl' => empty($component['creation']) ? false : isWoodyNewTpl($component['creation']),
                'acf_groups' => empty($component['acf_groups']) ? '' : $component['acf_groups']
            ];
        }

        return $return;
    }
}