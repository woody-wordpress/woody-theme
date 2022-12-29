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
        //TODO: added ?dnt=1 after vimeo url
        $render = preg_replace('#<iframe ([^>]*) src="https:\/\/(www.youtube.com|youtube.com|youtu.be|vimeo.com|www.vimeo.com|player.vimeo.com|www.dailymotion.com|dailymotion.com)#', '<iframe class="lazyload" $1 data-src="https://$2', $render);
        return preg_replace('#youtube.com\/embed|youtu.be\/embed#', 'youtube-nocookie.com/embed', $render);
    }
}
