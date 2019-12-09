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
        $this->mode = get_query_var('sitemap');
        $this->context = Timber::get_context();

        // Get polylang config
        $sitemap = [];
        $polylang = get_option('polylang');
        if ($polylang['force_lang'] == 3 && !empty($polylang['domains'])) {
            $sitemap_lang = get_option('woody_sitemap_' . pll_current_language());
            if (!empty($sitemap_lang)) {
                $sitemap = $sitemap_lang;
            }
        } else {
            $languages = pll_languages_list();
            foreach ($languages as $lang) {
                $sitemap_lang = get_option('woody_sitemap_' . $lang);
                if (!empty($sitemap_lang)) {
                    $sitemap = array_merge($sitemap, $sitemap_lang);
                }
            }
        }

        $nb_pages = count($sitemap);
        switch ($this->mode) {
            case 'index':
                $this->twig_tpl = 'sitemap/sitemapindex.xml.twig';
                for ($i = 1; $i <= $nb_pages; $i++) {
                    $this->context['sitemaps'][] = [
                        'loc' => 'sitemap-' . $i . '.xml',
                        'lastmod' => date('c', time()),
                    ];
                }

                if (empty($this->context['sitemaps'])) {
                    status_header(404);
                    exit();
                }
                break;
            case 'list':
                $paged = (get_query_var('page')) ? get_query_var('page') - 1 : 0;
                $this->twig_tpl = 'sitemap/sitemap.xml.twig';
                if (!empty($sitemap[$paged])) {
                    $this->context['urls'] = $sitemap[$paged];
                } else {
                    status_header(404);
                    exit();
                }
                break;
            case 'xsl':
                $this->twig_tpl = 'sitemap/sitemap.xsl.twig';
                break;
        }
    }
}
