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
        if (WOODY_CLOUDFLARE_ENABLE && !empty(WOODY_CLOUDFLARE_URL) && !empty(WOODY_CLOUDFLARE_ZONE) && !empty(WOODY_CLOUDFLARE_TOKEN)) {
            $this->registerHooks();
        }
    }

    protected function registerHooks()
    {
        add_filter('wp_resource_hints', [$this, 'wpResourceHints'], 10, 2);
        add_filter('timber_render', [$this, 'timberRender'], 10);
    }

    public function wpResourceHints($hints, $relation_type)
    {
        if ($relation_type == 'dns-prefetch') {
            $hints[] = 'https://' . WOODY_CLOUDFLARE_URL;
        }

        return $hints;
    }

    public function timberRender($render)
    {
        $render = preg_replace('/("|\')\/app\/(dist|themes|uploads|plugins)\/([^"\' ]*)/', '$1https://' . WOODY_CLOUDFLARE_URL . '/app/$2/$3', $render);
        $render = preg_replace('/http(s?):\/\/([a-zA-Z0-9-_.]*)\/app\/(dist|themes|uploads|plugins)\/([^"\' ]*)/', 'https://' . WOODY_CLOUDFLARE_URL . '/app/$3/$4', $render);
        $render = preg_replace('/http(s?):\/\/([a-zA-Z0-9-_.]*)\/wp\/wp-includes\/([^"\' ]*)/', 'https://' . WOODY_CLOUDFLARE_URL . '/wp/wp-includes/$3', $render);
        return $render;
    }
}
