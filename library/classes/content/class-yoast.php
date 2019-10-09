<?php

/**
 * Yoast
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

use WoodyProcess\Tools\WoodyTheme_WoodyProcessTools;

class WoodyTheme_Yoast
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        //add_action('wpseo_register_extra_replacements', [$this, 'registerCustomYoastVariables']);
        add_action('wpseo_add_opengraph_additional_images', [$this, 'wpseoAddOpengraphAdditionalImages'], 10, 1);
        add_filter('wpseo_opengraph_image', [$this, 'wpseoOpengraphImage'], 10, 1);
        add_filter('wpseo_metadesc', [$this, 'wpseoMetaDesc'], 10, 1);
        add_filter('wpseo_metadesc', [$this, 'wpseoTransformPattern'], 10, 1);
        add_filter('wpseo_title', [$this, 'wpseoTransformPattern'], 10, 1);
    }

    public function wpseoAddOpengraphAdditionalImages($object)
    {
        global $post;
        if (!empty($post)) {
            $attachment = get_field('field_5b0e5ddfd4b1b', $post->ID);

            $image = [
                'url' => site_url() . '/ogimage.jpg', // fake url
                'width' => 1200,
                'height' => 675,
                'alt' => $attachment['description'],
            ];

            $object->add_image($image);
        }
    }

    public function wpseoOpengraphImage($url)
    {
        global $post;
        if (!empty($post)) {
            $attachment = get_field('field_5b0e5ddfd4b1b', $post->ID);
            return $attachment['sizes']['ratio_16_9_large'];
        }
    }

    /**
     * Remove HTML tags in string.
     * @param   string meta description as string
     * @return  string formatted
     */
    public function wpseoMetaDesc($string)
    {
        return strip_tags($string);
    }

    public function wpseoTransformPattern($string)
    {
        $tools = new WoodyTheme_WoodyProcessTools;
        $string = $tools->replacePattern($string, Timber::get_post());
        return $string;
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
