<?php
/**
 * ACF sync field
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package HawwwaiTheme
 * @since HawwwaiTheme 1.0.0
 */

class HawwwaiTheme_ACF {

    const ACF = "acf-pro/acf.php";

    public function __construct() {
        $this->register_hooks();
    }

    protected function register_hooks() {
        acf_update_setting('save_json', get_template_directory() . '/acf-json');
        acf_append_setting('load_json', get_template_directory() . '/acf-json');

        add_filter('plugin_action_links', array($this, 'disallow_acf_deactivation'), 10, 4);
        add_filter('acf/load_field/name=woody_tpl', array($this, 'woody_tpl_acf_load_field'));
    }

    /**
     * Benoit Bouchaud
     * On bloque l'accès à la désactivation du plugin ACF
     */
    public function disallow_acf_deactivation($actions, $plugin_file, $plugin_data, $context) {
        if (array_key_exists('deactivate', $actions) and $plugin_file == self::ACF) {
            unset( $actions['deactivate'] );
        }
        return $actions;
    }

    /**
     * Benoit Bouchaud
     * On ajoute les templates Woody disponibles dans les option du champ radio woody_tpl
     */
    public function woody_tpl_acf_load_field($field){

        $woody = new Woody();

        switch ($field['key']) {
            case 'field_5afd2c9616ecd':
                $components = $woody->getTemplatesByAcfGroup($field['key']);
            break;
            default:
                $components = $woody->getTemplatesByAcfGroup($field['parent']);
        }

        $field['choices'] = [];
        if(!empty($components)){
            foreach ($components as $key => $component) {
                $field['choices'][$key] = '<img class="img-responsive" src="/app/themes/site-theme/dist/img/woody/' . $component['thumbnails']['small'] . '" alt="' . $key . '" width="150" height="150" />';
            }
        }

        return $field;
    }
}

// Execute Class
$HawwwaiTheme_ACF = new HawwwaiTheme_ACF();
