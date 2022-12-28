<?php

use Symfony\Component\Finder\Finder;

class WoodyTheme_Tinymce
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('mce_buttons_2', array($this, 'tinymceAddButtons'));
        add_filter('mce_external_plugins', array($this, 'woodyAnchorButtonLoadJs'));
        add_filter('mce_external_plugins', array($this, 'woodyIconsButtonLoadJs'));
        add_filter('mce_external_plugins', array($this, 'addTablePluginTinyMCE'));
        add_filter('tiny_mce_before_init', array($this, 'tinymceRegisterStyleSelect'));
        add_filter('tiny_mce_before_init', array($this, 'modifyValidMarkup'));
        add_filter('mce_buttons', array($this, 'remove_button_from_tinymce'));
        add_filter('mce_buttons', array($this, 'add_button_from_tinymce'));
        add_action('init', array($this, 'tinymceAddStylesheet'));

        add_action('wp_ajax_woody_icons_list', [$this, 'woodyIconsList']);
    }

    // Callback function to insert 'styleselect' into the $buttons array
    public function tinymceAddButtons($buttons)
    {
        array_unshift($buttons, 'woody_icons');
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
                'title' => 'Liste "Suivant"',
                'selector' => 'ul',
                'classes' => 'list-unstyled list-wicon next-icon'
            ),
            array(
                'title' => 'Mega titre',
                'selector' => 'h2',
                'classes' => 'mega-title'
            ),
            array(
                'title' => 'Style 2',
                'selector' => 'p',
                'classes' => 'h2'
            ),
            array(
                'title' => 'Style 3',
                'selector' => 'p',
                'classes' => 'h3'
            ),
            array(
                'title' => 'Style 4',
                'selector' => 'p',
                'classes' => 'h4'
            ),
            array(
                'title' => 'Style 5',
                'selector' => 'p',
                'classes' => 'h5'
            )
        );

        $init_array['style_formats'] = json_encode($style_formats);

        // Allow wbesites to customize their own tinymce
        $init_array = apply_filters('customTinymce', $init_array);

        return $init_array;
    }

    public function woodyAnchorButtonLoadJs($plugins)
    {
        $plugins['woody_anchor'] = get_template_directory_uri() . '/src/js/admin/plugins/woody_anchor.min.js';
        return $plugins;
    }

    public function woodyIconsButtonLoadJs($plugins)
    {
        $plugins['woody_icons'] = get_template_directory_uri() . '/src/js/admin/plugins/woody_icons.min.js';
        return $plugins;
    }

    public function addTablePluginTinyMCE($plugins)
    {
        $plugins['table'] = get_template_directory_uri() . '/src/js/admin/plugins/table.js'; // Version 4.1.0 - http://archive.tinymce.com/download/older.php

        return $plugins;
    }

    public function woodyIconsList()
    {
        $core_icons = woodyIconsFolder(get_template_directory() . '/src/icons/icons_set_01');
        $site_icons = woodyIconsFolder(get_stylesheet_directory() . '/src/icons');
        $icons = array_merge($core_icons, $site_icons);

        wp_send_json($icons);
    }

    private function findIcons($finder)
    {
        $icons = [];
        foreach ($finder as $icon_file) {
            $icons[] = str_replace('.svg', '', $icon_file->getRelativePathname());
        }

        return $icons;
    }

    public function modifyValidMarkup($settings)
    {
        // Command separated string of extended elements
        $ext = 'span[id|name|class|style]';
        // Add to extended_valid_elements if it alreay exists
        if (isset($settings['extended_valid_elements'])) {
            $settings['extended_valid_elements'] .= ',' . $ext;
        } else {
            $settings['extended_valid_elements'] = $ext;
        }
        return $settings;
    }

    public function remove_button_from_tinymce($buttons)
    {
        $remove_buttons = array(
            'wp_more', // read more link
        );
        foreach ($buttons as $button_key => $button_value) {
            if (in_array($button_value, $remove_buttons)) {
                unset($buttons[$button_key]);
            }
        }

        return $buttons;
    }

    public function add_button_from_tinymce($buttons)
    {
        $add_buttons = array(
            'table' // table plugin tinymce
        );

        foreach ($add_buttons as $button_value) {
            $buttons[] = $button_value;
        }

        return $buttons;
    }

    public function tinymceAddStylesheet()
    {
        add_editor_style(WP_DIST_URL . '/css/admin.css');
    }
}
