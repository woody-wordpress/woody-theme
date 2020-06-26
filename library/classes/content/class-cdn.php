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
        add_filter('timber_render', [$this, 'timberRender']);
    }

    public function timberRender($render)
    {
        if (in_array('cdn', WOODY_OPTIONS)) {
            $render = preg_replace('/http(s?):\/\/([a-zA-Z0-9-_.]*)\/app\/(dist|themes|uploads)\/([^"\']*)/', 'https://woody.cloudly.space/app/$3/$4', $render);
        }
        return $render;
    }
}
