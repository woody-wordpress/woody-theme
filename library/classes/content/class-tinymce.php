<?php
class WoodyTheme_Tinymce
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('mce_buttons_2', array($this, 'tinymceAddButtons'));
        add_filter('mce_external_plugins', array($this, 'woodyAnchorButtonLoadJs' ));
        add_filter('tiny_mce_before_init', array($this, 'tinymceRegisterStyleSelect'));
        add_filter( 'mce_buttons',  array($this,'remove_button_from_tinymce'));
        // add_action('init', array($this, 'tinymceAddStylesheet'));
    }

    // Callback function to insert 'styleselect' into the $buttons array
    public function tinymceAddButtons($buttons)
    {
        array_unshift($buttons, 'woody_anchor');
        array_unshift($buttons, 'styleselect');
        return $buttons;
    }

    public function tinymceRegisterStyleSelect($init_array)
    {
        $style_formats = array(
        array(
            'title' => 'Bouton principal',
            'selector' => 'a',
            'classes' => 'button primary',
            'exact' => true
        ),
        array(
            'title' => 'Bouton secondaire',
            'selector' => 'a',
            'classes' => 'button secondary',
            'exact' => true
        ),
        array(
            'title' => 'Liste "On aime"',
            'selector' => 'ul',
            'classes' => 'list-unstyled list-wicon love-icon'
        ),
        array(
            'title' => 'Liste "Les plus"',
            'selector' => 'ul',
            'classes' => 'list-unstyled list-wicon plus-icon'
        ),
        array(
            'title' => 'Mega titre',
            'selector' => 'h2',
            'classes' => 'mega-title'
        )
    );
        $init_array['style_formats'] = json_encode($style_formats);
        return $init_array;
    }

    public function woodyAnchorButtonLoadJs($plugins)
    {
        $plugins['woody_anchor'] = get_template_directory_uri() . '/src/js/admin/plugins/woody_anchor.js';
        return $plugins;
    }

    public function remove_button_from_tinymce( $buttons ) {
        $remove_buttons = array(
            'wp_more', // read more link
        );
        foreach ( $buttons as $button_key => $button_value ) {
            if ( in_array( $button_value, $remove_buttons ) ) {
                 unset( $buttons[$button_key] );
            }
        }
        return $buttons;
    }

    // public function tinymceAddStylesheet()
    // {
    //     add_editor_style('custom-editor-style.css');
    // }
}
