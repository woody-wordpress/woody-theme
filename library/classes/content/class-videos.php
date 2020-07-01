<?php

/**
 * Images
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.25.3
 *
 */

class WoodyTheme_Videos
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('timber_render', [$this, 'timberRender'], 10);
    }

    public function timberRender($render)
    {
        return preg_replace('/<iframe ([^>]*) src="https:\/\/(www.youtube.com|youtube.com|youtu.be|vimeo.com|www.vimeo.com|www.dailymotion.com|dailymotion.com)/', '<iframe class="lazyload" $1 data-src="https://$2', $render);
    }
}
