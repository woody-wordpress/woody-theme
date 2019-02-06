<?php
/**
 * SiteMap
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_SiteMap
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('init', [$this, 'customRewriteRule']);
        add_action('after_setup_theme', [$this, 'reduceQueryLoad'], 99);
        add_action('template_redirect', [$this, 'getSitemap'], 1);
        add_filter('query_vars', [$this, 'queryVars']);
    }

    public function queryVars($qvars)
    {
        $qvars[] = 'sitemap';
        return $qvars;
    }

    public function customRewriteRule()
    {
        add_rewrite_rule('sitemap\.xml$', 'index.php?sitemap=index', 'top');
        add_rewrite_rule('sitemap-([0-9]+)?\.xml$', 'index.php?sitemap=list&page=$matches[1]', 'top');
    }

    /**
     * Check the current request URI, if we can determine it's probably an XML sitemap, kill loading the widgets
     */
    public function reduceQueryLoad()
    {
        if (! isset($_SERVER['REQUEST_URI'])) {
            return;
        }

        $request_uri = $_SERVER['REQUEST_URI'];
        $extension   = substr($request_uri, -4);

        if (false !== stripos($request_uri, 'sitemap') && in_array($extension, array( '.xml', '.xsl' ), true)) {
            remove_all_actions('widgets_init');
        }
    }

    /**
     * Hijack requests for potential sitemaps files.
     * @param \WP_Query $query Main query instance.
     */
    public function getSitemap($query)
    {
        $sitemap = get_query_var('sitemap');
        if (!empty($sitemap)) {
            add_filter('template_include', function () {
                return get_template_directory() . '/sitemap.php';
            });
        }
    }
}
