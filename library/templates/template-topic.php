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

        $data['globals']['post']        = $post;
        $data['globals']['post_title']  = $post->post_title;
        $data['globals']['post_id']     = $post->ID;
        $data['globals']['page_type']   = $post->post_type;
        $data['globals']['sheet_id']    = 0;
        $data['globals']['woody_options_pages'] = [
            'favorites_url'         => get_field('favorites_page_url', 'options'),
            'search_url'            => get_field('es_search_page_url', 'options'),
            'weather_url'           => get_field('weather_page_url', 'options'),
            'disqus_instance_url'   => get_field('disqus_instance_url', 'options')
        ];

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

        // TODO: get topic Preview and create focus block to display it
        // $this->context['the_sections'] = $this->process->processWoodySections(get_field('section', $this->context['post_id']), $this->context);
    }
}
