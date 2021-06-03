<?php

/**
 * Template
 *
 * @package WoodyRoadBook
 * @since WoodyTheme 1.17.7
 */

use WoodyProcess\Process\WoodyTheme_WoodyProcess;

class WoodyTheme_Template_Topic extends WoodyTheme_TemplateAbstract
{
    public function __construct()
    {
        $this->process = new WoodyTheme_WoodyProcess;
        $this->registerHooks();
        $this->setTwigTpl();
        $this->extendContext();
    }

    protected function registerHooks()
    {
        add_filter('timber_compile_data', [$this, 'timberCompileData']);
    }

    protected function setTwigTpl()
    {
        $this->twig_tpl = 'page.twig';
    }


    protected function getHeaders()
    {
        if ($this->context['page_type'] === 'playlist_tourism') {
            return $this->playlistHeaders();
        }
    }

    public function timberCompileData($data)
    {
        $post = get_post();
        $data['globals']['post_title']  = $post->post_title;
        $data['globals']['post_id']     = $post->ID;
        $data['globals']['page_type']   = $post->post_type;
        return $data;
    }

    protected function extendContext()
    {
        $this->context = Timber::get_context();
        $this->context['post'] = get_post();
        $this->context['post_title'] = get_the_title();
        $this->context['post_id'] = !empty($this->context['post']) ? $this->context['post']->ID : 0;

        // Set a global dist dir
        $this->context['dist_dir'] = WP_DIST_DIR;
        $this->context['default_marker'] = file_get_contents($this->context['dist_dir'] . '/img/default-marker.svg');

        $this->context['woody_components'] = getWoodyTwigPaths();

        $this->context['sections'] = [];
        $manual_focus = $this->create_manual_focus($this->context['post']);
        $section = $this->page_create_section(['section_content' => [$manual_focus]]);
        $this->context['the_sections'] = $this->process->processWoodySections(['sections' => $section], $this->context);
    }

    /**
     * Create WP / ACF section field (empty section content).
     *
     * @return return array containing section data
     */
    private function page_create_section($args = [])
    {
        $return = [
            'bo_section_title'          => !empty($args['bo_section_title']) ? $args['bo_section_title'] : '',
            'section_content'           => !empty($args['section_content']) ? $args['section_content'] : [],
            'title'                     => !empty($args['title']) ? $args['title'] : '',
            'pretitle'                  => !empty($args['pretitle']) ? $args['pretitle'] : '',
            'subtitle'                  => !empty($args['subtitle']) ? $args['subtitle'] : '',
            'icon_type'                 => !empty($args['icon_type']) ? $args['icon_type'] : 'picto',
            'woody_icon'                => !empty($args['woody_icon']) ? $args['woody_icon'] : '',
            'icon_img'                  => !empty($args['icon_img']) ? $args['icon_img'] : false,
            'description'               => !empty($args['description']) ? $args['description'] : '',
            'section_woody_tpl'         => !empty($args['section_woody_tpl']) ? $args['section_woody_tpl'] : 'grids_basic-grid_1_cols-tpl_01',
            'section_divider'           => !empty($args['section_divider']) ? $args['section_divider'] : false,
            'scope_no_padding'          => !empty($args['scope_no_padding']) ? $args['scope_no_padding'] : false,
            'display_fullwidth'         => !empty($args['display_fullwidth']) ? $args['display_fullwidth'] : false,
            'parallax'                  => !empty($args['parallax']) ? $args['parallax'] : false,
            'section_alignment'         => !empty($args['section_alignment']) ? $args['section_alignment'] : 'align-middle',
            'background_color'          => !empty($args['background_color']) ? $args['background_color'] : '',
            'border_color'              => !empty($args['border_color']) ? $args['border_color'] : '',
            'background_img'            => !empty($args['background_img']) ? $args['background_img'] : false,
            'background_img_opacity'    => !empty($args['background_img_opacity']) ? $args['background_img_opacity'] : '',
            'section_banner'            => !empty($args['section_banner']) ? $args['section_banner'] : [],
            'scope_paddings'            => !empty($args['scope_paddings']) ? $args['scope_paddings'] : [
                'scope_padding_top'     => 'padd-top-md',
                'scope_padding_bottom'  => 'padd-bottom-md',
            ],
            'scope_margins'             => !empty($args['scope_margins']) ? $args['scope_margins'] : [
                'scope_margins_top'     => 'marg-top-md',
                'scope_margins_bottom'  => 'marg-bottom-md',
            ],
        ];

        return $return;
    }


    /**
     * Create manual focus.
     *
     * @param content_selection
     *
     * @return return
     */
    private function create_manual_focus($post)
    {
        $return = [
            'acf_fc_layout' => 'manual_focus',
            'content_selection' => [
                [
                    'bo_selection_title' => '',
                    'content_selection_type' => 'existing_content',
                    'custom_content' => [
                        'title' => '',
                        'pretitle' => '',
                        'subtitle' => '',
                        'icon_type' => '',
                        'woody_icon' => '',
                        'icon_img' => '',
                        'description' => '',
                        'action_type' => ['link'],
                        'link' => null,
                        'file' => false,
                        'media_type' => '',
                        'img' => false,
                        'movie' => [
                            'movie_poster_file' => false,
                            'mp4_movie_file' => false,
                            'movie_webm_file' => false,
                            'movie_ogg_file' => false,
                        ],
                    ],
                    'existing_content' => [
                        'content_selection' => $post
                    ],
                ],
            ],
            'focused_sort' => 'normal',
            'display_elements' => [
               'description',
            ],
            'display_img' => true,
            'display_button' => true,
            'deal_mode' => false,
            'woody_tpl' => 'blocks-focus-tpl_103',
            'focus_map_params' => [
                'map_zoom_auto' => true,
                'map_zoom' => '',
                'map_height' => 'md',
                'tmaps_confid' => '',
            ],
            'focus_no_padding' => false,
            'visual_effects' => [
                'transform' => false,
            ],
        ];

        return $return;
    }
}
