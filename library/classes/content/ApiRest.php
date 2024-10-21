<?php

/**
 * API Rest
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.25.2
 */

namespace Woody\WoodyTheme\library\classes\content;

use WoodyProcess\Compilers\WoodyTheme_WoodyCompilers;

class ApiRest
{
    public $compilers;

    public function __construct()
    {
        $this->compilers = new WoodyTheme_WoodyCompilers();

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
            register_rest_route('woody', 'compile/focus', array(
                'methods' => 'GET',
                'callback' => [$this, 'compileFocusApiRest'],
            ));
        });

        add_action('rest_api_init', function () {
            register_rest_route('woody', 'svg/symbol', array(
                'methods' => 'GET',
                'callback' => [$this, 'getSvgSymbol'],
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
            $wrapper = ['display_img' => true, 'display_button' => false];
        } elseif (!empty($display_decoded) && empty($ratio)) {
            $display_decoded['display_img'] = false;
            $wrapper = $display_decoded;
        } else {
            $display_decoded['display_img'] = true;
            $wrapper = $display_decoded;
        }

        $post_preview = [];

        // Cas d'une mise en avant contenu existant
        if(!empty($post_id)) {
            header('xkey: ' . WP_SITE_KEY . '_' . $post_id, false);
            $post = get_post($post_id);

            if ($post->post_status == 'publish') {
                $post_preview = getAnyPostPreview($wrapper, $post);
            }

            // Cas d'une mise en avant contenu libre
        } elseif(!empty($field) && !empty($current_id)) {
            // On récupère le contenu de l'item par rapport à son index de section, son index de bloc et son post id
            $item = get_field($field, $current_id);

            $post_preview = empty($item) ? [] : getCustomPreview($item, $wrapper);
        } else {
            return 'getPagePreviewApiRest : erreur dans les paramètres';
        }

        if ($html_format && !empty($tpl_twig)) {
            return \Timber::compile('cards/' . $tpl_twig . '/tpl.twig', [
                'item' => $post_preview,
                'image_style' => $ratio,
                'display_button' => empty($wrapper['display_button']) ? false : $wrapper['display_button']
            ]);
        } else {
            return $post_preview;
        }
    }

    public function compileFocusApiRest()
    {
        // Mise en avant manuelle (basée sur une liste de post ids)
        # /wp-json/woody/compile/focus?current_id=${current_id}&layout=manual_focus&tpl_twig=${tpl_twig}&post_ids=${post_ids}&display=${display}
        // Mise en avant de fiches SIT (basée sur un conf_id)
        # /wp-json/woody/compile/focus?current_id=${current_id}&layout=auto_focus_sheets&tpl_twig=${tpl_twig}&conf_id=${conf_id}&display=${display}
        $current_id = filter_input(INPUT_GET, 'current_id', FILTER_VALIDATE_INT);
        $layout = filter_input(INPUT_GET, 'layout');
        $tpl_twig = filter_input(INPUT_GET, 'tpl_twig');
        $post_ids = filter_input(INPUT_GET, 'post_ids');
        $conf_id = filter_input(INPUT_GET, 'conf_id');
        $display = filter_input(INPUT_GET, 'display');

        $display_decoded = json_decode(base64_decode($display), true);

        $display_options = [];
        if (empty($display_decoded)) {
            $display_options = ['display_img' => true, 'display_button' => false];
        } else {
            $display_decoded['display_img'] = true;
            $display_options = $display_decoded;
        }

        $return = '';
        $wrapper = [];

        if (!empty($layout)) {
            $wrapper = [
                'acf_fc_layout' => $layout,
                'woody_tpl' => $tpl_twig
            ];
            if ($layout == 'manual_focus') {
                if (!empty($post_ids)) {
                    $post_ids = explode(',', $post_ids);
                    $wrapper['content_selection'] = [];
                    foreach ($post_ids as $post_id) {
                        $wrapper['content_selection'][] = [
                            'content_selection_type' => 'existing_content',
                            'existing_content' => [
                                'content_selection' => $post_id
                            ]
                        ];
                    }
                }
            } elseif ($layout == 'auto_focus_sheets') {
                $wrapper['playlist_conf_id'] = $conf_id;
            }

            $wrapper = array_merge($wrapper, $display_options);

            if(!empty($wrapper)) {
                $current_post = get_post($current_id);

                $twig_paths = getWoodyTwigPaths();

                $return = $this->compilers->formatFocusesData($wrapper, $current_post, $twig_paths);
            }
        }

        return $return;
    }

    public function getSvgSymbol(\WP_REST_Request $req)
    {
        # /wp-json/woody/svg/symbol?text=J1
        # /wp-json/woody/svg/symbol?icon=shoes
        $text = filter_input(INPUT_GET, 'text');
        $icon = filter_input(INPUT_GET, 'icon');

        $res = "";
        if (!empty($icon)) {
            // récupération du symbole
            $symbol = self::get_svg_symbol_by_id(WP_DIST_DIR . '/addons/woody-library/static/symbols.svg', "monument");
            if (is_wp_error($symbol)) {
                return $symbol;
            }
            $res = '<svg xmlns="http://www.w3.org/2000/svg">'  .$symbol . '</svg>';

            // insertion du texte le cas échéant
            if (!empty($text)) {
                // $res='<svg>text : '.$text.'</svg>';
            }

            // header('xkey: ' . WP_SITE_KEY);
            header('content-type: image/svg+xml');
            header('cache-control: no-cache');
            header('content-length: ' . strlen($res));

            echo $res;
            exit;

        }

        return new \WP_Error('400', esc_html__('Missed params', 'woody'), array('status' => 400));
    }

    public static function get_svg_symbol_by_id($file_path, $symbol_id) {
        if (!file_exists($file_path)) {
            return new \WP_Error('400', esc_html__('SVG file not found', 'woody'), array('status' => 404));
        }
        $svg_content = file_get_contents($file_path);
        $svg_content = str_replace('xmlns="http://www.w3.org/2000/svg"', '', $svg_content);
        $svg_xml = new \SimpleXMLElement($svg_content);
        $result = $svg_xml->xpath("//symbol[@id='{$symbol_id}']");
        if (empty($result)) {
            return new \WP_Error('400', esc_html__('SVG symbol not found : ' . woody_addon_asset_path('woody-library', "static/symbols.svg"), 'woody'), array('status' => 404));
        }
        return $result[0]->asXML();
    }
}
