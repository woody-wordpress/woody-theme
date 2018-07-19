<?php
/**
 * Front Theme Cleanup
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Cleanup_Front
{
    public function __construct()
    {
        $this->register_hooks();
    }

    protected function register_hooks()
    {
        add_action('after_setup_theme', array($this, 'start_cleanup'));
    }

    public function start_cleanup()
    {
        // Launching operation cleanup.
        add_action('init', array($this, 'cleanup_head'));
        // Remove WP version from RSS.
        add_filter('the_generator', array($this, 'remove_rss_version'));
        // Remove pesky injected css for recent comments widget.
        add_filter('wp_head', array($this, 'remove_wp_widget_recent_comments_style'), 1);
        // Clean up comment styles in the head.
        add_action('wp_head', array($this, 'remove_recent_comments_style'), 1);
        // Remove inline width attribute from figure tag
        add_filter('img_caption_shortcode', array($this, 'remove_figure_inline_style'), 10, 3);
    }

    public function cleanup_head()
    {
        // EditURI link.
        remove_action('wp_head', 'rsd_link');
        // Category feed links.
        remove_action('wp_head', 'feed_links_extra', 3);
        // Post and comment feed links.
        remove_action('wp_head', 'feed_links', 2);
        // Windows Live Writer.
        remove_action('wp_head', 'wlwmanifest_link');
        // Index link.
        remove_action('wp_head', 'index_rel_link');
        // Previous link.
        remove_action('wp_head', 'parent_post_rel_link', 10, 0);
        // Start link.
        remove_action('wp_head', 'start_post_rel_link', 10, 0);
        // Canonical.
        remove_action('wp_head', 'rel_canonical', 10, 0);
        // Shortlink.
        remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
        // Links for adjacent posts.
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
        // WP version.
        remove_action('wp_head', 'wp_generator');
        // Emoji detection script.
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        // Emoji styles.
        remove_action('wp_print_styles', 'print_emoji_styles');
    }

    public function remove_rss_version()
    {
        return '';
    }

    public function remove_wp_widget_recent_comments_style()
    {
        if (has_filter('wp_head', 'wp_widget_recent_comments_style')) {
            remove_filter('wp_head', 'wp_widget_recent_comments_style');
        }
    }

    public function remove_recent_comments_style()
    {
        global $wp_widget_factory;
        if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
            remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
        }
    }

    public function remove_figure_inline_style($output, $attr, $content)
    {
        $atts = shortcode_atts(array(
            'id'      => '',
            'align'   => 'alignnone',
            'width'   => '',
            'caption' => '',
            'class'   => '',
        ), $attr, 'caption');

        $atts['width'] = (int) $atts['width'];
        if ($atts['width'] < 1 || empty($atts['caption'])) {
            return $content;
        }

        if (! empty($atts['id'])) {
            $atts['id'] = 'id="' . esc_attr($atts['id']) . '" ';
        }

        $class = trim('wp-caption ' . $atts['align'] . ' ' . $atts['class']);

        if (current_theme_supports('html5', 'caption')) {
            return '<figure ' . $atts['id'] . ' class="' . esc_attr($class) . '">'
            . do_shortcode($content) . '<figcaption class="wp-caption-text">' . $atts['caption'] . '</figcaption></figure>';
        }
    }
}

// Execute Class
new WoodyTheme_Cleanup_Front();
