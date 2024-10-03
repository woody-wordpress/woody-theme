<?php

/**
 * Taxonomy
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

namespace Woody\WoodyTheme\library\classes\content;

class Unpublisher
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('add_meta_boxes', array($this, 'registerMetaBox'));
        add_action('save_post', array($this, 'saveUnpublisherParams'));

        add_action('woody_theme_update', [$this, 'scheduleUnpublishPosts']);
        add_action('woody_unpublish_posts', [$this, 'woodyUnpublishPosts']);

        add_action('woody_theme_update', [$this, 'scheduleMissedPosts']);
        add_action('woody_missed_posts', [$this, 'woodyMissedPosts']);

        \WP_CLI::add_command('woody:missed_posts', [$this, 'woodyMissedPosts']);
    }

    public function registerMetaBox()
    {
        $post_types = get_post_types(['public' => true, '_builtin' => false]);
        $post_types['page'] = 'page';

        $excluded = ['short_link', 'snowflake_config', 'testimony', 'touristic_sheet', 'woody_model', 'woody_section_model'];

        $excluded = apply_filters('woody/unpublisher/excluded_post_types', $excluded);

        foreach ($post_types as $post_type) {
            if (in_array($post_type, $excluded)) {
                unset($post_types[$post_type]);
            }
        }

        add_meta_box(
            'woody-unpublisher',
            'Planifier la dépublication',
            array($this, 'unpublisherMetaBoxTpl'),
            $post_types,
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
        echo '<input placeholder="Choisir une date" id="wUnpublisher_date" name="wUnpublisher_date" value="' . $wUnpublisher_date_value . '"/>';
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

    public function scheduleUnpublishPosts()
    {
        if (!wp_next_scheduled('woody_unpublish_posts')) {
            wp_schedule_event(time(), 'hourly', 'woody_unpublish_posts');
            output_success(sprintf('- Schedule %s', 'woody_unpublish_posts'));
        }
    }

    public function woodyUnpublishPosts()
    {
        global $wpdb;

        \Moment\Moment::setLocale('fr_FR');
        $m = new \Moment\Moment();
        $m->setTimezone(WOODY_TIMEZONE);

        $current_date = $m->format();

        // On récupère les posts publiés qui ont une date de dépublication inférieure à la date courante
        $results = $wpdb->get_results(
            "SELECT {$wpdb->prefix}posts.ID
        FROM {$wpdb->prefix}posts, {$wpdb->prefix}postmeta
        WHERE {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_ID
        AND {$wpdb->prefix}posts.post_status = 'publish'
        AND {$wpdb->prefix}postmeta.meta_key = '_wUnpublisher_date'
        AND {$wpdb->prefix}postmeta.meta_value != ''
        AND {$wpdb->prefix}postmeta.meta_value < '{$current_date}'
        "
        );

        if (!empty($results)) {
            foreach ($results as $result) {
                wp_update_post([
                    'ID' => $result->ID,
                    'post_status' => 'draft'
                ]);
                update_post_meta($result->ID, '_wUnpublisher_date', '');
                clean_post_cache($result->ID);
            }
        }
    }

    public function scheduleMissedPosts()
    {
        if (!wp_next_scheduled('woody_missed_posts')) {
            wp_schedule_event(time(), 'hourly', 'woody_missed_posts');
            output_success(sprintf('- Schedule %s', 'woody_missed_posts'));
        }
    }

    public function woodyMissedPosts()
    {
        global $wpdb;

        $m = new \Moment\Moment();
        $m->setTimezone(WOODY_TIMEZONE);

        $now = $m->format('Y-m-d H:i:00');

        $args = array(
            'public'                => true,
            'exclude_from_search'   => false,
            '_builtin'              => false
        );
        $post_types = get_post_types($args, 'names', 'and');
        $post_types_str = implode("','", $post_types);

        if (!empty($post_types_str)) {
            $sql = "SELECT ID from $wpdb->posts WHERE post_type in ('post','page','{$post_types_str}') AND post_status='future' AND post_date_gmt<'{$now}'";
        } else {
            $sql = "SELECT ID from $wpdb->posts WHERE post_type in ('post','page') AND post_status='future' AND post_date_gmt<'{$now}'";
        }

        $results = $wpdb->get_results($sql);
        $to_publish = [];
        if ($results) {
            foreach ($results as $result) {
                output_log('Publishing post ' . $result->ID);
                $to_publish[] = $result->ID;
                wp_update_post(['ID' => $result->ID, 'post_status' => 'publish']);
            }

            output_success(sprintf('Future posts published : %s', implode(',', $to_publish)));
        }
    }
}
