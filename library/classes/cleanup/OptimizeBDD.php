<?php
/**
 * Admin Theme Cleanup
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

namespace Woody\WoodyTheme\library\classes\cleanup;

class OptimizeBDD
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('woody_theme_update', [$this, 'scheduleOptimizeBDD']);
        // add_action('woody_cleanup_optimize_bdd', [$this, 'cleanupOptimizeBDD']);
        // \WP_CLI::add_command('woody:optimize_bdd', [$this, 'cleanupOptimizeBDD']);
    }

    // public function cleanupOptimizeBDD()
    // {
    //     global $wpdb;
    //     $tables = $wpdb->get_results("SHOW TABLES");
    //     if (!empty($tables)) {
    //         foreach ($tables as $table) {
    //             foreach ($table as $t) {
    //                 $wpdb->query(sprintf("ALTER TABLE %s ENGINE=INNODB;", $t));
    //                 if (empty($wpdb->last_error)) {
    //                     output_success(sprintf('Optimize Table %s', $t));
    //                 } else {
    //                     output_error(sprintf('Optimize Table %s', $t));
    //                 }
    //             }
    //         }
    //     }
    // }

    public function scheduleOptimizeBDD()
    {
        // if (!wp_next_scheduled('woody_cleanup_optimize_bdd')) {
        //     wp_schedule_event(time(), 'weekly', 'woody_cleanup_optimize_bdd');
        //     output_success(sprintf('+ Schedule %s', 'woody_cleanup_optimize_bdd'));
        // }

        if (wp_next_scheduled('woody_cleanup_optimize_bdd')) {
            wp_clear_scheduled_hook('woody_cleanup_optimize_bdd');
            output_success(sprintf('- Schedule %s', 'woody_cleanup_optimize_bdd'));
        }
    }
}
