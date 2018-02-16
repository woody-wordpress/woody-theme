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
        add_filter('acf/load_field/name=woody_tpl', array($this, 'woodytpl_acf_load_field'));
        add_filter('acf/load_field/name=img_size', array($this, 'image_size_acf_load_field'));
        add_filter('acf/fields/flexible_content/layout_title/name=content_element', array($this, 'custom_flexible_content_layout_title'), 10, 4);
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



    function woodytpl_acf_load_field($field){

        // Reset existing choices
         $field['choices'] = array(
             'tempate_1' => 'Template 1'
         );

        // Define path to Woody library
        $woody_tpls = site_url('/vendor/rc/woody/views');

        if(strpos($field['parent'], 'field') !== FALSE){
            $the_parent = $field['parent'];
            // Lorsque le champ parent est chargé, il charge tous ses enfants.
            // Il repasse donc dans le filtre que nous sommes en train d'écrire et provoque une boucle infinie
            // $parent_field = get_field_object($the_parent);
            // error_log('bla', 3, dirname(__FILE__) . '/debug.log');
            d($field);
            // d($parent_field);
            global $wpdb;
            $posts = $wpdb->get_results("SELECT post_excerpt FROM wp_posts WHERE post_name = '$the_parent'");
            d($posts);

        }

        return $field;
    }


    // Rewrite title of content_element rows to display a more readable title chosed by the user himself
    function custom_flexible_content_layout_title( $title, $field, $layout, $i ){

        $admin_title = get_sub_field('admin_group_name');
        if(!empty($admin_title)){
            $title = $admin_title;
        }
        return $title;
    }

}
