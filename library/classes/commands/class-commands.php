<?php
/**
 * Commands
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */
use Timber\Integrations\Command;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Woody\Utils\Output;

class WoodyTheme_Commands
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        \WP_CLI::add_command('woody_flush', [$this, 'flush']);
        \WP_CLI::add_command('woody_flush_cache', [$this, 'flush_cache']);
        \WP_CLI::add_command('woody_flush_timber', [$this, 'flush_timber']);
        \WP_CLI::add_command('woody_flush_varnish', [$this, 'flush_varnish']);
    }

    public function flush($args)
    {
        $this->flush_cache();
        $this->flush_timber();
        $this->flush_varnish();
    }

    public function flush_cache()
    {
        do_action('woody_subtheme_update');
        Output::success('woody_subtheme_update');

        do_action('woody_theme_update');
        Output::success('woody_theme_update');

        // Clear the cache to prevent an update_option() from saving a stale db_version to the cache
        wp_cache_flush();
        Output::success('wp_cache_flush');

        // (Not all cache back ends listen to 'flush')
        wp_cache_delete('alloptions', 'options');
        Output::success('wp_cache_delete alloptions');
    }

    public function flush_timber()
    {
        if (WP_ENV != 'dev') {
            try {
                $filesystem = new Filesystem();
                if (!$filesystem->exists(WP_TIMBER_DIR)) {
                    $filesystem->mkdir(WP_TIMBER_DIR, 0775);
                }

                // Clear Twig Cache
                $cleared = Command::clear_cache('twig');
                if ($cleared) {
                    Output::success("twig_clear_cache");
                } else {
                    Output::error("twig_clear_cache");
                }
            } catch (IOExceptionInterface $exception) {
                Output::error("Une erreur est survenue au moment de la création de " . $exception->getPath());
            }
        }
    }

    public function flush_varnish()
    {
        // Options
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
                        Output::error(['wp_varnish_purge' => $noticeMessage]);
                    }
                } else {
                    //Output::success(sprintf('wp_varnish_purge %s => %s (%s)', $purgeme, $host, $lang));
                    Output::success(sprintf('wp_varnish_purge : %s (%s)', WP_SCHEME . '://' . $host, $lang));
                }
            }
        }
    }
}
