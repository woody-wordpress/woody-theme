<?php

class WoodyTheme_Inclusions
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('init', [$this, 'incRewriteRule'], 1);
        add_action('template_redirect', [$this, 'getIncs'], 1);
        add_filter('query_vars', [$this, 'queryVars']);
    }

    public function queryVars($qvars)
    {
        $qvars[] = 'inc';
        return $qvars;
    }

    public function incRewriteRule()
    {
        add_rewrite_rule('^inclusions/(.*)/?', 'index.php?inc=$matches[1]', 'top');
    }

    public function getIncs($query)
    {
        $inc = get_query_var('inc');
        if (!empty($inc)) {
            if ($inc == 'inc_header') {
                add_filter('template_include', fn () => get_template_directory() . '/library/templates/inc_header.php');
            } elseif ($inc == 'inc_footer') {
                add_filter('template_include', fn () => get_template_directory() . '/library/templates/inc_footer.php');
            }
        }
    }
}
