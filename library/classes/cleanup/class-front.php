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
        $this->registerHooks();
    }

    public function registerHooks()
    {
        // Launching operation cleanup.
        add_action('init', array($this, 'cleanupHead'), 1);
        // Remove WP version from RSS.
        add_filter('the_generator', array($this, 'removeRssVersion'), 1);
        // Remove pesky injected css for recent comments widget.
        add_filter('wp_head', array($this, 'removeWpWidgetRecentCommentsStyle'), 1);
        // Clean up comment styles in the head.
        add_action('wp_head', array($this, 'removeRecentCommentsStyle'), 1);
        // Remove inline width attribute from figure tag
        add_filter('img_caption_shortcode', array($this, 'removeFigureInlineStyle'), 10, 3);
        // Disable XMLRPC
        add_filter('xmlrpc_enabled', '__return_false');
        // Add Body Class
        add_filter('body_class', [$this, 'bodyClass']);
        // Remove private/protected prefixes
        add_filter('private_title_format', [$this, 'removePrivatePrefix']);
        add_filter('protected_title_format', [$this, 'removePrivatePrefix']);
        // Cleanup Rewrite Rules
        add_filter('rewrite_rules_array', [$this, 'rewriteRulesArray'], 1);

        add_filter('wp_robots', '__return_empty_array');
    }

    public function removeWpRobots($robots)
    {
        console_log($robots);
        return [];
    }

    public function cleanupHead()
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

        remove_action('wp_head', 'wp_robots', 12);
    }

    public function removeRssVersion()
    {
        return '';
    }

    public function removeWpWidgetRecentCommentsStyle()
    {
        if (has_filter('wp_head', 'wp_widget_recent_comments_style')) {
            remove_filter('wp_head', 'wp_widget_recent_comments_style');
        }
    }

    public function removeRecentCommentsStyle()
    {
        global $wp_widget_factory;
        if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
            remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
        }
    }

    public function removeFigureInlineStyle($output, $attr, $content)
    {
        $atts = shortcode_atts(array(
            'id'      => '',
            'align'   => 'alignnone',
            'width'   => '',
            'caption' => '',
            'class'   => '',
        ), $attr, 'caption');

        $atts['width'] = (int)$atts['width'];
        if ($atts['width'] < 1 || empty($atts['caption'])) {
            return $content;
        }

        if (!empty($atts['id'])) {
            $atts['id'] = 'id="' . esc_attr($atts['id']) . '" ';
        }

        $class = trim('wp-caption ' . $atts['align'] . ' ' . $atts['class']);

        if (current_theme_supports('html5', 'caption')) {
            return '<figure ' . $atts['id'] . ' class="' . esc_attr($class) . '">'
                . do_shortcode($content) . '<figcaption class="wp-caption-text">' . $atts['caption'] . '</figcaption></figure>';
        }
    }

    public function bodyClass($body_classes)
    {
        // Added ENV to body classes
        return array_merge($body_classes, [WP_ENV]);
    }

    public function removePrivatePrefix()
    {
        return '%s';
    }

    public function rewriteRulesArray($rules)
    {
        foreach ($rules as $rule => $rewrite) {
            if (strpos($rule, '/feed/') !== false ||
            strpos($rule, '/embed/') !== false ||
            strpos($rule, '/trackback/') !== false ||
            strpos($rule, '/page/') !== false ||
            strpos($rule, '/category/') !== false ||
            strpos($rule, '/themes/') !== false ||
            strpos($rule, '/attachment/') !== false ||
            strpos($rule, 'feed|rdf|rss|rss2|atom') !== false ||
            strpos($rewrite, 'attachment=') !== false ||
            strpos($rule, 'comment-page') !== false) {
                unset($rules[$rule]);
            }
        }
        return $rules;
    }
}
