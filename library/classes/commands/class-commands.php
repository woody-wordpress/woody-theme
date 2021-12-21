<?php

/**
 * Commands
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class WoodyTheme_Commands
{
    public function __construct()
    {
        $this->registerHooks();
        $this->registerCommands();
    }

    protected function registerHooks()
    {
    }

    protected function registerCommands()
    {
        \WP_CLI::add_command('woody_flush', [$this, 'flush']);
        \WP_CLI::add_command('woody_flush_core', [$this, 'flush_core']);
        \WP_CLI::add_command('woody_flush_site', [$this, 'flush_site']);
        \WP_CLI::add_command('woody_flush_twig', [$this, 'flush_twig']);
        \WP_CLI::add_command('woody_flush_varnish', [$this, 'flush_varnish']);
        \WP_CLI::add_command('woody_flush_cloudflare', [$this, 'flush_cloudflare']);
        \WP_CLI::add_command('woody_cache_warm', [$this, 'cache_warm']);
        \WP_CLI::add_command('woody_maintenance', [$this, 'maintenance']);
        \WP_CLI::add_command('woody_maintenance_core', [$this, 'maintenance_core']);
    }

    public function flush()
    {
        $this->flush_site();
        $this->flush_core();
        $this->cache_warm();
        $this->flush_twig();
        $this->flush_varnish();
        $this->flush_cloudflare();
    }

    public function flush_site()
    {
        do_action('woody_subtheme_update');
        output_success('woody_subtheme_update');
    }

    public function flush_core()
    {
        $theme_version = wp_get_theme(get_template())->get('Version');
        update_option('woody_theme_version', $theme_version, true);
        output_success('woody_theme_version ' . $theme_version);

        do_action('woody_theme_update');
        output_success('woody_theme_update');

        global $wpdb;
        $results = $wpdb->get_results("SELECT option_name FROM {$wpdb->prefix}options WHERE autoload='no'");
        if (!empty($results)) {
            foreach ($results as $val) {
                wp_cache_delete($val->option_name, 'options');
            }
        }
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('notoptions', 'options');
        wp_cache_delete('1:notoptions', 'site-options');
        output_success('wp_cache_delete alloptions');

        wp_cache_delete('plugins', 'plugins');
        output_success('wp_cache_delete plugins');
    }

    public function cache_warm()
    {
        do_action('woody_cache_warm');
        output_success('woody_cache_warm');
    }

    public function maintenance($args, $assoc_args)
    {
        $status = (current($args) == 'true') ? true : false;

        $fs = new Filesystem();
        if (!$fs->exists(WP_MAINTENANCE_DIR)) {
            $fs->mkdir(WP_MAINTENANCE_DIR, 0775);
        }

        if ($status) {
            $fs->dumpFile(WP_MAINTENANCE_DIR . '/' . WP_SITE_KEY, date('Y-m-d H:i:s'));
            output_success('woody_maintenance ON');
        } else {
            $fs->remove(WP_MAINTENANCE_DIR . '/' . WP_SITE_KEY);
            output_success('woody_maintenance OFF');
        }
    }

    public function maintenance_core($args, $assoc_args)
    {
        $status = (current($args) == 'true') ? true : false;

        $fs = new Filesystem();
        if (!$fs->exists(WP_MAINTENANCE_DIR)) {
            $fs->mkdir(WP_MAINTENANCE_DIR, 0775);
        }

        if ($status) {
            $fs->dumpFile(WP_MAINTENANCE_DIR . '/all', date('Y-m-d H:i:s'));
            output_success('woody_maintenance_core ON');
        } else {
            $fs->remove(WP_MAINTENANCE_DIR . '/all');
            output_success('woody_maintenance_core OFF');
        }
    }

    public function flush_twig()
    {
        if (WP_ENV != 'dev' && !WOODY_TWIG_CACHE_DISABLE) {
            try {
                $fs = new Filesystem();
                if (!$fs->exists(WP_TIMBER_DIR)) {
                    $fs->mkdir(WP_TIMBER_DIR, 0775);
                }

                // Clear Twig Cache
                $cmd = sprintf("rm -rf %s", WP_TIMBER_DIR . '/*');
                exec($cmd);
                output_log($cmd);
                output_success("woody_flush_twig");
            } catch (IOExceptionInterface $exception) {
                output_warning("Une erreur est survenue au moment de la création de " . $exception->getPath());
            }
        } else {
            output_warning("Twig cache désactivé");
        }
    }

    public function flush_varnish()
    {
        do_action('woody_flush_varnish');
    }

    public function flush_cloudflare()
    {
        if (WP_ENV != 'prod' || empty(WOODY_CLOUDFLARE_URL) || empty(WOODY_CLOUDFLARE_ZONE) || empty(WOODY_CLOUDFLARE_TOKEN)) {
            output_warning('Plugin CDN CloudFlare non activé');
            return;
        }

        $response = wp_remote_post('https://api.cloudflare.com/client/v4/zones/' . WOODY_CLOUDFLARE_ZONE . '/purge_cache', [
            'headers' => ['Authorization' => 'Bearer ' . WOODY_CLOUDFLARE_TOKEN],
            'body' => '{"purge_everything":true}'
        ]);

        if (is_wp_error($response)) {
            output_warning(['woody_flush_cloudflare' => $response->get_error_message()]);
        } else {
            output_success(sprintf('woody_flush_cloudflare : %s', WOODY_CLOUDFLARE_URL));
        }
    }
}
