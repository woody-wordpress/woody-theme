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
        // add_action('save_post', array($this, 'saveUnpublisherParams'));

        add_action('woody_theme_update', [$this, 'scheduleUnpublishPosts']);
        // add_action('woody_unpublish_posts', [$this, 'woodyUnpublishPosts']);
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

        echo '<label for="wUnpublisher_date">Date de dépublication : </label>';
        echo '<div class="input-wrapper">';
        echo '<input placeholder="Choisir une date" id="wUnpublisher_date" name="wUnpublisher_date" value="' . $wUnpublisher_date_value . '"/>';
        echo '<small class="unpublisher-reset-date">x</small>';
        echo '</div>';
        echo '<div><small><i>À compter de la date choisie (+/- 1h), le contenu passe en brouillon</div></small></i>';
    }

    // public function saveUnpublisherParams($post_id)
    // {
    //     if (!isset($_POST['saveUnpublisherParams_nonce'])) {
    //         return $post_id;
    //     }

    //     if (!wp_verify_nonce($_POST['saveUnpublisherParams_nonce'], 'saveUnpublisherParams')) {
    //         return $post_id;
    //     }

    //     update_post_meta($post_id, '_wUnpublisher_date', $_POST['wUnpublisher_date']);
    // }

    public function scheduleUnpublishPosts()
    {
        // if (!wp_next_scheduled('woody_unpublish_posts')) {
        //     wp_schedule_event(time(), 'hourly', 'woody_unpublish_posts');
        //     output_success(sprintf('+ Schedule %s', 'woody_unpublish_posts'));
        // }

        if (wp_next_scheduled('woody_unpublish_posts')) {
            wp_clear_scheduled_hook('woody_unpublish_posts');
            output_success(sprintf('- Schedule %s', 'woody_unpublish_posts'));
        }
    }

    // public function woodyUnpublishPosts()
    // {
    //     global $wpdb;

    //     $today = date(DATE_ATOM);
    //     $results = $wpdb->get_results(
    //         "SELECT `wp_posts`.`ID`
    //         FROM `wp_posts`, `wp_postmeta`
    //         WHERE `wp_posts`.`ID` = `wp_postmeta`.`post_id`
    //         AND `wp_postmeta`.`meta_key` = '_wUnpublisher_date'
    //         AND `wp_postmeta`.`meta_value` < {$today}
    //         AND `wp_postmeta`.`meta_value` != ''"
    //     );

    //     if (!empty($results)) {
    //         foreach ($results as $result) {
    //             wp_update_post([
    //                 'ID' => $result->ID,
    //                 'post_status' => 'draft'
    //             ]);
    //             update_post_meta($result->ID, '_wUnpublisher_date', '');
    //         }
    //     }
    // }
}