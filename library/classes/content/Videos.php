<?php

/**
 * Images
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.25.3
 *
 */

namespace Woody\WoodyTheme\library\classes\content;

class Videos
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('timber_render', [$this, 'timberRender'], 10);
        add_filter('pre_oembed_result', [$this, 'preOembedResult'], 10, 3);
    }

    public function timberRender($render)
    {
        //TODO: added ?dnt=1 after vimeo url
        $render = preg_replace('#<iframe ([^>]*) src="https:\/\/(www.youtube.com|youtube.com|youtu.be|vimeo.com|www.vimeo.com|player.vimeo.com|www.dailymotion.com|dailymotion.com)#', '<iframe class="lazyload" $1 data-src="https://$2', $render);
        return preg_replace('#youtube.com\/embed|youtu.be\/embed#', 'youtube-nocookie.com/embed', $render);
    }

    public function preOembedResult($pre, $url, $args)
    {
        // Ce filtre est en place sinon oEmbed fait une requête à l'API Vimeo pour récupérer les informations de la vidéo
        // Cependant, l'API Vimeo a bloqué notre IP.
        if (strpos($url, 'vimeo.com') !== false) {
            $player_url = str_replace('https://vimeo.com', 'https://player.vimeo.com/video', $url);
            $player = '<iframe src="%s?dnt=1" width="640" height="360" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media"></iframe>';
            $pre = sprintf($player, $player_url);
        }
        return $pre;
    }
}
