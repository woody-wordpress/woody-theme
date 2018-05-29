<?php

class Basetheme_ACF {

    const ACF = "acf-pro/acf.php";

    public function execute() {
        $this->register_hooks();
    }

    protected function register_hooks() {
        add_filter('plugin_action_links', array($this, 'disallow_acf_deactivation'), 10, 4);
        add_filter('acf/load_field/name=woody_tpl', array($this, 'woody_tpl_acf_load_field'));
        // add_filter('acf/load_field/name=section_layout', array($this, 'section_layout_acf_load_field'));

    }

    function disallow_acf_deactivation($actions, $plugin_file, $plugin_data, $context) {
        if (array_key_exists('deactivate', $actions) and $plugin_file == self::ACF) {
            unset( $actions['deactivate'] );
        }
        return $actions;
    }

    function woody_tpl_acf_load_field($field){
        $field['choices'] = [];
        $woody = new Woody();
        switch ($field['key']) {
            case 'field_5afd2c9616ecd':
                $components = $woody->getTemplatesByAcfGroup($field['key']);
            break;
            default: $components = $woody->getTemplatesByAcfGroup($field['parent']);
        }
        if(!empty($components)){
            foreach ($components as $key => $component) {
                $field['choices'][$key] = '<img class="img-responsive" src="/app/themes/site-theme/dist/img/woody/' . $component['thumbnails']['small'] . '" alt="' . $key . '" width="150" height="150"/> ';
            }
        }

        return $field;
    }

    function section_layout_acf_load_field($field){
        $field['choices'] = [];
        $woody = new Woody();
        print '<pre>';
        print_r($field);
        exit;
        if(!empty($components)){
            foreach ($components as $key => $component) {
                $field['choices'][$key] = '<img class="img-responsive" src="' . $component['thumbnails']['small'] . '" alt="' . $key . '"/> ';
            }
        }
        return $field;
    }

}
