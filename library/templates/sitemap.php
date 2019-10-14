<?php

/**
 * Template
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Template_Sitemap
{
    protected $twig_tpl = '';
    protected $context = [];
    protected $mode = '';

    public function __construct()
    {
        $this->initContext();
    }

    protected function registerHooks()
    { }

    public function render()
    {
        if (!empty($this->twig_tpl) && !empty($this->context)) {
            switch ($this->mode) {
                case 'index':
                case 'list':
                    header('Content-Type: text/xml; charset=UTF-8');
                    break;
                case 'xsl':
                    header('Content-Type: text/xsl; charset=UTF-8');
                    break;
            }
            Timber::render($this->twig_tpl, $this->context);
        }
    }

    private function initContext()
    {
        $sitemap = get_transient('woody_sitemap');
        $nb_pages = count($sitemap);

        $this->mode = get_query_var('sitemap');
        switch ($this->mode) {
            case 'index':
                $this->twig_tpl = 'sitemap/sitemapindex.xml.twig';
                for ($i = 1; $i <= $nb_pages; $i++) {
                    $this->context['sitemaps'][] = [
                        'loc' => 'sitemap-' . $i . '.xml',
                        'lastmod' => date('c', time()),
                    ];
                }
                break;
            case 'list':
                $paged = (get_query_var('page')) ? get_query_var('page') : 1;
                $this->twig_tpl = 'sitemap/sitemap.xml.twig';
                if (!empty($sitemap[$paged])) {
                    $this->context = $sitemap[$paged];
                }

                break;
            case 'xsl':
                $this->twig_tpl = 'sitemap/sitemap.xsl.twig';
                break;
        }
    }
}
