<?php

/**
 * Commands
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

namespace Woody\WoodyTheme\library\classes\commands;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class Commands
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

        $this->add_default_language();
    }

    public function cache_warm()
    {
        do_action('woody_cache_warm');
        output_success('woody_cache_warm');
    }

    private function add_default_language()
    {
        // Create language if none exists
        if (function_exists('pll_languages_list')) {
            $pll_languages_list = pll_languages_list();
            if (empty($pll_languages_list)) {
                $options = get_option('polylang');
                $model = new \PLL_Admin_Model($options);
                $return = $model->add_language([
                    'name' => 'Français',
                    'slug' => 'fr',
                    'locale' => 'fr_FR',
                    'term_group' => 0,
                    'rtl' => 0,
                    'flag' => 'fr'
                ]);

                if ($return == true) {
                    output_success('Ajout de la langue fr_FR');

                    if ($nolang = $model->get_objects_with_no_lang()) {
                        if (!empty($nolang['posts'])) {
                            $model->set_language_in_mass('post', $nolang['posts'], 'fr');
                            output_success(sprintf('Attribution de la langue par défaut (%s posts)', is_countable($nolang['posts']) ? count($nolang['posts']) : 0));
                        }

                        if (!empty($nolang['terms'])) {
                            $model->set_language_in_mass('term', $nolang['terms'], 'fr');
                            output_success(sprintf('Attribution de la langue par défaut (%s terms)', is_countable($nolang['terms']) ? count($nolang['terms']) : 0));
                        }
                    }
                } else {
                    output_error($return);
                }
            }
        }
    }

    public function maintenance($args, $assoc_args)
    {
        $status = current($args) == 'true';

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
        $status = current($args) == 'true';

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
            } catch (IOExceptionInterface $ioException) {
                output_warning("Une erreur est survenue au moment de la création de " . $ioException->getPath());
            }
        } else {
            output_warning("Twig cache désactivé");
        }
    }

    public function flush_varnish()
    {
        do_action('woody_flush_varnish');
    }
}
