<?php
/**
 * Yoast
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Yoast
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        // Add action
        //add_action('wpseo_register_extra_replacements', [$this, 'registerCustomYoastVariables']);
        add_action('wpseo_add_opengraph_additional_images', [$this, 'wpseoAddOpengraphAdditionalImages'], 10, 1);
    }

    public function wpseoAddOpengraphAdditionalImages($object)
    {
        global $post;
        $image = get_field('field_5b0e5ddfd4b1b', $post->ID);
        $object->add_image($image);
    }

    // // define the action for register yoast_variable replacments
    // public function registerCustomYoastVariables()
    // {
    //     wpseo_register_var_replacement('%%Description%%', [$this, 'getPostDescription'], 'advanced', 'Récupère la description de l\'entête');
    // }

    // // define the custom replacement callback
    // public function getPostDescription()
    // {
    //     global $post;
    //     $desc = '';

    //     if (!empty($post->ID)) {
    //         $desc = trim(strip_tags(get_field('page_teaser_desc', $post->ID)));
    //         if (empty($desc)) {
    //             $desc = trim(strip_tags(get_field('focus_description', $post->ID)));
    //         }
    //     }

    //     return $desc; // change 320 to whatever character length you would like
    // }
}
