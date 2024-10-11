<?php
/**
 * Force reorder plugins
 *
 * @link https://codex.wordpress.org/Function_Reference/activate_plugin
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

namespace Woody\WoodyTheme\library\classes\plugins;

class Order
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('woody_theme_update', [$this, 'woodyThemeUpdate'], 1);
    }

    public function woodyThemeUpdate()
    {
        // Liste des plugins forcer par poids
        // Tous les plugins dans cette liste ne doivent pas forcément être activés.
        // Mais si il le sont, ils le seront dans le bon ordre
        $plugins_weight = [
            'woody-plugin/woody.php' => 100,
        ];

        $order_active_plugins = [];
        $active_plugins = get_option('active_plugins');
        foreach ($active_plugins as $weight => $plugin) {
            if (array_key_exists($plugin, $plugins_weight)) {
                $order_active_plugins[$plugins_weight[$plugin] + 1000] = $plugin;
                continue;
            }

            $order_active_plugins[$weight] = $plugin;
        }

        ksort($order_active_plugins);
        update_option('active_plugins', $order_active_plugins, true);
    }
}
