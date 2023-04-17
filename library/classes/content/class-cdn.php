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
        if ($relation_type == 'dns-prefetch' || $relation_type == 'preconnect') {
            $hints[] = '//' . WOODY_CLOUDFLARE_URL;
        }

        return $hints;
    }

    public function timberRender($render)
    {
        // Not apply on sitemap
        if (strpos($_SERVER['REQUEST_URI'], 'sitemap') !== false || strpos($_SERVER['REQUEST_URI'], 'wp-json') !== false) {
            return $render;
        }

        preg_match_all('#("|\')\/app\/(dist|themes|uploads|plugins)\/([^"\' ]*)#', $render, $matches);
        $render = $this->replaceCDN($matches, $render);

        preg_match_all('#http(s?):\/\/([a-zA-Z0-9-_.]*)\/app\/(dist|themes|uploads|plugins)\/([^"\' ]*)#', $render, $matches);
        $render = $this->replaceCDN($matches, $render);

        preg_match_all('#http(s?):\/\/([a-zA-Z0-9-_.]*)\/wp\/wp-includes\/([^"\' ]*)#', $render, $matches);

        return $this->replaceCDN($matches, $render);
    }

    private function replaceCDN($matches = [], $render = '')
    {
        $matches = (!empty($matches) && !empty($matches[0])) ? $matches[0] : $matches;
        if (!empty($matches)) {
            foreach ($matches as $url) {

                // If matches is empty, it's an array
                if (is_array($url)) {
                    continue;
                }

                // First regex return quote on first letter
                $prefix = null;
                if (substr($url, 0, 1) == '"' || substr($url, 0, 1) == "'") {
                    $prefix = substr($url, 0, 1);
                    $url = substr($url, 1);
                }

                $host = parse_url($url, PHP_URL_HOST);
                $scheme = parse_url($url, PHP_URL_SCHEME);
                $path = parse_url($url, PHP_URL_PATH);
                $extension = pathinfo($path, PATHINFO_EXTENSION);

                if (!empty($path) && !in_array($extension, ['pdf','docx','doc','xls','xlsx','ppt','ppt','zip','rar','gz','html'])) {
                    if (empty($host)) {
                        $new_url = 'https://' . WOODY_CLOUDFLARE_URL . $path;
                    } else {
                        $new_url = str_replace($host, WOODY_CLOUDFLARE_URL, $url);
                        $new_url = str_replace($scheme, 'https', $new_url);
                    }

                    $render = str_replace($prefix.$url, $prefix.$new_url, $render);
                }
            }
        }

        return $render;
    }
}
