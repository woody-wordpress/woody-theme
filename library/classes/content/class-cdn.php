<?php

/**
 * CDN
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.25.0
 */

class WoodyTheme_CDN
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('wp_resource_hints', [$this, 'wpResourceHints']);
        add_filter('timber_render', [$this, 'timberRender'], 10);
    }

    public function wpResourceHints($hints)
    {
        $hints[] = 'woody.cloudly.space';
        return $hints;
    }

    public function timberRender($render)
    {
        $render = preg_replace('/("|\')\/app\/(dist|themes|uploads|plugins)\/([^"\' ]*)/', '$1https://woody.cloudly.space/app/$2/$3', $render);
        $render = preg_replace('/http(s?):\/\/([a-zA-Z0-9-_.]*)\/app\/(dist|themes|uploads|plugins)\/([^"\' ]*)/', 'https://woody.cloudly.space/app/$3/$4', $render);
        $render = preg_replace('/http(s?):\/\/([a-zA-Z0-9-_.]*)\/wp\/wp-includes\/([^"\' ]*)/', 'https://woody.cloudly.space/wp/wp-includes/$3', $render);
        return $render;
    }
}
