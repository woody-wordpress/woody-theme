<?php

/**
 * Taxonomy
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Unpublisher
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('add_meta_boxes', array($this, 'registerMetaBox'));
        add_action('save_post', array($this, 'saveUnpublisherParams'));

        add_action('init', [$this, 'woody_unpublish_posts']);
        add_action('woody_unpublish_posts', [$this, 'getPostUnpublishedValue']);
    }

    public function registerMetaBox()
    {
        add_meta_box(
            'woody-unpublisher',
            'Planifier la dépublication',
            array($this, 'unpublisherMetaBoxTpl'),
            'page',
            'side',
            'high'
        );
    }

    public function unpublisherMetaBoxTpl($post)
    {
        $wUnpublisher_date_value = get_post_meta($post->ID, '_wUnpublisher_date', true);
        wp_nonce_field('saveUnpublisherParams', 'saveUnpublisherParams_nonce');

        // echo '<label for="wUnpublisher_date">Date de dépublication : </label>';
        echo '<div class="input-wrapper">';
        echo '<input placeholder="Choisir une une date" id="wUnpublisher_date" name="wUnpublisher_date" value="' . $wUnpublisher_date_value . '"/>';
        echo '<small class="unpublisher-reset-date">x</small>';
        echo '</div>';
        echo '<div><small><i>À compter de la date choisie (+/- 1h), le contenu passe en brouillon</div></small></i>';
    }

    public function saveUnpublisherParams($post_id)
    {
        if (!isset($_POST['saveUnpublisherParams_nonce'])) {
            return $post_id;
        }

        if (!wp_verify_nonce($_POST['saveUnpublisherParams_nonce'], 'saveUnpublisherParams')) {
            return $post_id;
        }


        update_post_meta($post_id, '_wUnpublisher_date', $_POST['wUnpublisher_date']);
    }

    public function woody_unpublish_posts()
    {
        if (!wp_next_scheduled('woody_unpublish_posts')) {
            wp_schedule_event(time(), 'hourly', 'woody_unpublish_posts');
        }
    }

    public function getPostUnpublishedValue()
    {
        $pages = get_pages([
            'meta_key' => '_wUnpublisher_date'
        ]);

        if (empty($pages)) {
            return;
        }

        foreach ($pages as $page) {
            if (is_object($page) && !empty(get_post_meta($page->ID, '_wUnpublisher_date', true))) {
                $unpublish_date_meta = get_post_meta($page->ID, '_wUnpublisher_date', true);

                $timezone = (!empty(get_option('timezone_string'))) ? get_option('timezone_string') : 'Europe/Paris';
                $unpublish_date = new DateTime($unpublish_date_meta, new DateTimeZone($timezone));

                $timestamp = $unpublish_date->getTimestamp();

                if ($timestamp < time()) {
                    $updated_post = [
                        'ID' => $page->ID,
                        'post_status' => 'draft'
                    ];
                    wp_update_post($updated_post);
                }
            }
        }
    }
}
