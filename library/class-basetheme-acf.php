<?php

use Symfony\Component\Finder\Finder;

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
        add_filter('acf/load_field/name=woody_card_tpl', array($this, 'woody_card_tpl_acf_load_field'));
        add_filter('acf/load_field/name=content_element', array($this, 'content_element_acf_load_field'));
        add_filter('acf/fields/flexible_content/layout_title/name=content_element', array($this, 'custom_flexible_content_layout_title'), 10, 4);
        // add_action('acf/save_post', array($this, 'cleanUpAcfJson'), 20);
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

    function woody_card_tpl_acf_load_field($field){
        $field['choices'] = [];
        $woody = new Woody('Cards', 'card');
        $woody_templates = $woody->getTemplates();
        if(!empty($templates)){
            foreach ($woody_templates as $key => $template) {
                $choices[$template['version']] =
                '<img width="90" height="90" src="' . $template['thumbnails']['small'] . '" alt="' . $template['name'] . '" />
                <div><small>' . $template['name'] . '</small></div>';
            }
            $field['choices'] = $choices;
        }

        return $field;
    }

    /**
    * Creating a new radio field 'woody_tpl' based on availables templates in the woody plugin
    **/
    function content_element_acf_load_field($field){
        // Get every layouts from the 'content_element_field'
        foreach ($field['layouts'] as $key => $layout) {
            // Create woody object => all we need to work
            $woody = new Woody($layout['name']);
            $woody_templates = $woody->getTemplates();
            if(!empty($woody_templates)){
                // If there's templates in the woody object,
                // we fill an array 'choices' with woody's values
                $choices = [];
                foreach ($woody_templates as $key => $template) {
                    $choices[$template['version']] =
                    '<img width="90" height="90" src="' . $template['thumbnails']['small'] . '" alt="' . $template['name'] . '" />
                    <div><small>' . $template['name'] . '</small></div>';
                }

                //Register field woody_tpl
                $field_register = [
                    'ID' => 'field_woodytpl_' . $layout['name'] . '_' . $key,
                    'key' => 'field_woodytpl_' . $layout['name'] . '_' . $key,
                    'label' => 'Modèles',
                    'name' => 'woody_tpl',
                    'type' => 'radio',
                    'parent' => $field['key'],
                    'menu_order' => 10,
                    'wrapper' => [
                        'width' => '100',
                        'class' => '',
                        'id' => ''
                    ],
                    'choices' => $choices,
                    '_name' => 'woody_tpl',
                    '_prepare' => 0,
                    '_valid' => 1,
                    'required' => 0,
                    'instructions' => '',
                    'allow_null' => 1,
                    'other_choice' => 0,
                    'save_other_choice' => 0,
                    'layout' => 0,
                    'class' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                    'parent_layout' => $layout['key'],
                    'return_format' => 0
                ];

                $field['layouts'][$layout['key']]['sub_fields'][] = $field_register;
            }
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

    // Cleanup acf-json
    function cleanUpAcfJson($post_id){
        die;
        // $finder = new Finder();
        // $finder->files()->in($path);
        // foreach ($finder as $file) {
        //     $name = $file->getfileName();
        //     $path = $file->getPathName();
        //     if($name != 'group_5a60c2bb2ea78.json') continue;
        //     $json = file_get_contents($path);
        //     $field = json_decode($json, true);
        // }
        // bail early if no ACF data
        // if( empty($_POST['acf']) ) {
        //
        //     return;
        //
        // }


        // array of field values
        $fields = $_POST['acf'];

        foreach ($fields as $k_field => $value) {
            $layouts = $value['layouts'];
            foreach ($layouts as $k_layout => $layout) {
                $subfields = $layout['sub_fields'];
                    foreach ($subfields as $k_subfield => $subfield) {
                        if(strpos($subfield['key'], 'field_woodytpl_') === 0){
                            unset($_POST['acf'][$k_field]['layouts'][$k_layout]['sub_fields'][$k_subfield]);
                        }
                    }
                }
            };

            print_r($_POST['acf']); exit;

        return $post_id;
    }
}
