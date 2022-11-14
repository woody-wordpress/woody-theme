<?php

/**
 * PWA
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.23.0
 */

class WoodyTheme_PWA
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('init', [$this, 'rewriteRules']);
        add_filter('query_vars', [$this, 'queryVars']);
        add_filter('template_include', [$this, 'manifestTemplate']);
    }

    public function queryVars($qvars)
    {
        $qvars[] = 'manifest_pwa';
        return $qvars;
    }

    public function rewriteRules()
    {
        add_rewrite_rule('pwa-manifest.json', 'index.php?manifest_pwa=true', 'top');
    }

    public function manifestTemplate($template)
    {
        $manifest = get_query_var('manifest_pwa');
        if (!empty($manifest)) {
            return get_template_directory() . '/pwa-manifest.php';
        }

        return $template;
    }
}
