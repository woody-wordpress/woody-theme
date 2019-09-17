<?php

/**
 * Commands
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Woody\Utils\Output;

class WoodyTheme_Commands
{
    public function __construct()
    {
        $this->registerHooks();
        $this->registerCommands();
    }

    protected function registerHooks()
    {
        add_action('woody_flush_varnish', [$this, 'flush_varnish']);
    }

    protected function registerCommands()
    {
        \WP_CLI::add_command('woody_flush', [$this, 'flush']);
        \WP_CLI::add_command('woody_flush_core', [$this, 'flush_core']);
        \WP_CLI::add_command('woody_flush_site', [$this, 'flush_site']);
        \WP_CLI::add_command('woody_flush_twig', [$this, 'flush_twig']);
        \WP_CLI::add_command('woody_flush_varnish', [$this, 'flush_varnish']);
        \WP_CLI::add_command('woody_cache_warm', [$this, 'cache_warm']);
    }

    public function flush($args)
    {
        $this->flush_site();
        $this->flush_core();
        $this->flush_cache();
        $this->cache_warm();
        $this->flush_twig();
        $this->flush_varnish();
    }

    public function flush_site()
    {
        do_action('woody_subtheme_update');
        Output::success('woody_subtheme_update');
    }

    public function flush_core()
    {
        $theme_version = wp_get_theme(get_template())->get('Version');
        update_option('woody_theme_version', $theme_version, true);
        Output::success('woody_theme_version ' . $theme_version);

        do_action('woody_theme_update');
        Output::success('woody_theme_update');

        // Clear the cache to prevent an update_option() from saving a stale db_version to the cache
        wp_cache_flush();
        Output::success('woody_flush_cache');

        // (Not all cache back ends listen to 'flush')
        wp_cache_delete('alloptions', 'options');
        Output::success('wp_cache_delete alloptions');
    }

    public function cache_warm()
    {
        do_action('woody_cache_warm');
        Output::success('woody_cache_warm');
    }

    public function flush_twig()
    {
        if (WP_ENV != 'dev') {
            try {
                $filesystem = new Filesystem();
                if (!$filesystem->exists(WP_TIMBER_DIR)) {
                    $filesystem->mkdir(WP_TIMBER_DIR, 0775);
                }

                // Clear Twig Cache
                $cleared = $this->rmdir(WP_TIMBER_DIR);

                if ($cleared) {
                    Output::success("woody_flush_twig");
                } else {
                    Output::error("woody_flush_twig");
                }
            } catch (IOExceptionInterface $exception) {
                Output::error("Une erreur est survenue au moment de la création de " . $exception->getPath());
            }
        } else {
            Output::warning("Twig cache désactivé en DEV");
        }
    }

    public function flush_varnish()
    {
        // Options
        $varnish_caching_enable = get_option('varnish_caching_enable');
        if (!$varnish_caching_enable) {
            Output::warning('Plugin Varnish non activé');
            return;
        }

        $vcaching_prefix = 'varnish_caching_';
        $vcaching_useSsl = get_option($vcaching_prefix . 'ssl');
        $vcaching_purgeKey = ($purgeKey = trim(get_option($vcaching_prefix . 'purge_key'))) ? $purgeKey : null;
        $vcaching_varnishIp = get_option($vcaching_prefix . 'ips');
        $vcaching_varnishIp = explode(',', $vcaching_varnishIp);
        $vcaching_varnishIp = apply_filters('vcaching_varnish_ips', $vcaching_varnishIp);

        // Get schema
        $schema = apply_filters('vcaching_schema', $vcaching_useSsl ? 'https://' : 'http://');

        // Get hosts
        $hosts = [];
        $polylang = get_option('polylang');
        if ($polylang['force_lang'] == 3 && !empty($polylang['domains'])) {
            foreach ($polylang['domains'] as $lang => $domain) {
                $hosts[$lang] = parse_url($domain, PHP_URL_HOST);
            }
        } else {
            $hosts['all'] = parse_url(WP_HOME, PHP_URL_HOST);
        }

        foreach ($hosts as $lang => $host) {
            foreach ($vcaching_varnishIp as $ip) {
                $purgeme = $schema . $ip . '/.*';
                $headers = array('host' => $host, 'X-VC-Purge-Method' => 'regex', 'X-VC-Purge-Host' => $host);
                if (!is_null($vcaching_purgeKey)) {
                    $headers['X-VC-Purge-Key'] = $vcaching_purgeKey;
                }
                $response = wp_remote_request($purgeme, array('method' => 'PURGE', 'headers' => $headers, "sslverify" => false));
                if ($response instanceof WP_Error) {
                    foreach ($response->errors as $error => $errors) {
                        $noticeMessage = 'Error ' . $error . ' : ';
                        foreach ($errors as $error => $description) {
                            $noticeMessage .= ' - ' . $description;
                        }
                        Output::error(['woody_flush_varnish' => $noticeMessage]);
                    }
                } else {
                    Output::success(sprintf('woody_flush_varnish : %s (%s)', WP_SCHEME . '://' . $host, $lang));
                }
            }
        }
    }

    private function rmdir($dir, $inside_only = true)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->rmdir($dir . DIRECTORY_SEPARATOR . $item, false)) {
                return false;
            }
        }

        if ($inside_only) {
            return true;
        }

        return rmdir($dir);
    }
}
