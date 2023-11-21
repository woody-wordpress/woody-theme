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
    }

    public function getPagePreviewApiRest()
    {
        # /wp-json/woody/page/preview?post=${id}&field=${field}&current_id=${current_id}&html_format=${html_format}&tpl_twig=${tpl_twig}&ratio=${ratio}&display=${display}
        $post_id = filter_input(INPUT_GET, 'post', FILTER_VALIDATE_INT);
        $field = filter_input(INPUT_GET, 'field');
        $current_id = filter_input(INPUT_GET, 'current_id', FILTER_VALIDATE_INT);
        $html_format = filter_input(INPUT_GET, 'html_format', FILTER_VALIDATE_BOOLEAN);
        $tpl_twig = filter_input(INPUT_GET, 'tpl_twig');
        $ratio = filter_input(INPUT_GET, 'ratio');
        $display = filter_input(INPUT_GET, 'display');

        $display_decoded = json_decode(base64_decode($display), true);

        if (empty($display_decoded)) {
            $wrapper = [
                'display_img' => true,
                'display_button' => false
            ];
        } else {
            $wrapper = $display_decoded;
        }

        // Cas d'une mise en avant contenu existant
        if(!empty($post_id)) {
            header('xkey: ' . WP_SITE_KEY . '_' . $post_id, false);
            $post = get_post($post_id);

            if ($post->post_status == 'publish') {
                $post_preview = [];

                switch ($post->post_type) {
                    case 'touristic_sheet':
                        $post_preview = getTouristicSheetPreview($wrapper, $post);
                        break;
                    default:
                        $post_preview = getPagePreview($wrapper, $post);
                        break;
                }
            }

        // Cas d'une mise en avant contenu libre
        } elseif(!empty($field) && !empty($current_id)) {
            // On récupère le contenu de l'item par rapport à son index de section, son index de bloc et son post id
            $item = get_field($field, $current_id);

            $post_preview = empty($item) ? '' : getCustomPreview($item, $wrapper);
        } else {
            return 'getPagePreviewApiRest : erreur dans les paramètres';
        }

        if ($html_format == true && !empty($tpl_twig)) {
            return Timber::compile('cards/' . $tpl_twig . '/tpl.twig', [
                'item' => $post_preview,
                'image_style' => $ratio
            ]);
        } else {
            return $post_preview;
        }
    }
}