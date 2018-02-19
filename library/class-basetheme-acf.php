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
        add_filter('acf/load_field/name=content_element', array($this, 'content_element_acf_load_field'));
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

    function content_element_acf_load_field($field){
        // d($field);

        // Define path to Woody library
        $path = dirname(get_home_path());
        $woody_tpls = $path . '/vendor/rc/woody/views';

        foreach ($field['layouts'] as $key => $layout) {
            // We get woody's folder corresponding to our parent_layout name and scan the folder
            $tpl_folder = $woody_tpls . '/blocks/' . $layout['name'];
            if(is_dir($tpl_folder)){
                $files = scandir($tpl_folder);
                if(!empty($files)){

                    $data = [];

                    foreach ($files as $key => $file) {
                        $avoid = array('.','twig', $layout['name']);
                        $name = str_replace($avoid, '', $file);
                        if(!empty($name)){
                            $readable_name = str_replace('tpl_', 'Template ', $name);
                            $data[] =  $readable_name;
                        }
                    }

                    $field_register = [
                            'key' => 'field_woodytpl_' . $layout['name'] . '_' . $key,
                            'label' => 'Template',
                            'name' => 'woody_tpl',
                            'type' => 'radio',
                            'parent' => $field['key'],
                            'menu_order' => 10,
                            'wrapper' => [
                                'width' => '100',
                                'class' => '',
                                'id' => ''
                            ],
                            'choices' => $data,
                            '_name' => 'woody_tpl',
                            '_prepare' => 0,
                            '_valid' => 1,
                            'required' => 0,
                            'instructions' => '',
                            'allow_null' => 1,
                            'other_choice' => 0,
                            'layout' => 0,
                            'class' => '',
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => '',
                            'append' => '',
                            'maxlength' => '',
                            'parent_layout' => $layout['key']
                    ];

                    $field['layouts'][$layout['key']]['sub_fields'][] = $field_register;

                }
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

}
