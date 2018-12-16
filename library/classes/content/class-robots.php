<?php
/**
 * Robots
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Robots
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('robots_txt', [$this, 'robotsTxt'], 10, 2);
    }

    public function robotsTxt($output, $public)
    {
        if ($public != '0') {
            // Add Disallow
            $output = [
                'User-agent: *',
                'Disallow: /wp/',
                'Disallow: /wp/wp-admin/',
                'Disallow: /*.php$',
                'Disallow: /*.inc$',
                'Disallow: /*?*p=',
                'Disallow: /app/plugins/',
                'Disallow: /app/mu-plugins/',
                'Disallow: /app/themes/',
            ];

            // Add Sitemap
            $output[] = 'Sitemap: ' . str_replace('/wp', '/sitemap.xml', site_url());

            // TODO: Disable index bretagne temporaly
            //if (WP_SITE_KEY == 'crt-bretagne' && pll_current_language() == 'en') {
            if (WP_SITE_KEY == 'crt-bretagne') {
                $output = [
                    'User-agent: *',
                    'Disallow: /',
                ];
            }

            // Implode
            $output = implode("\n", $output);
        }

        return $output;
    }
}
