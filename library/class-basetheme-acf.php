<?php

class Basetheme_ACF {

    const ACF = "acf-pro/acf.php";

    public function execute() {
        $this->register_hooks();
    }

    protected function register_hooks() {
        add_filter('plugin_action_links', array($this, 'disallow_acf_deactivation'), 10, 4);
        // Overriding field img_size
        // See https://www.advancedcustomfields.com/resources/acf-load_field/ for more informations
        add_filter('acf/load_field/name=img_size', array($this, 'image_size_acf_load_field'));
    }

    function disallow_acf_deactivation($actions, $plugin_file, $plugin_data, $context) {
        if (array_key_exists('deactivate', $actions) and $plugin_file == self::ACF) {
            unset( $actions['deactivate'] );
        }
        return $actions;
    }

    // Get all aditional images sizes created in /library/responsive-images.php
    // so user can chose wich size to use for each gallery
    function image_size_acf_load_field($field){

        // Reset existing choices
        $field['choices'] = array();
        // Load every images sizes (could change if we don't want to expose everything)
        // Then populate field choices with machine name
        global $_wp_additional_image_sizes;
        foreach ($_wp_additional_image_sizes as $key => $value) {
            $field['choices'][$key] = $key;
        }

        // Using exisiting function to display a human readable name for images sizes
        // (could replace global var if we don't want to expose everything)
        $sizes = basetheme_custom_sizes($field['choices']);
        $field['choices'] = $sizes;

        return $field;
    }

    // function grid_size_acf_load_field($field){
    //     // Reset existing choices
    //     $field['choices'] = array();
    // }

}
