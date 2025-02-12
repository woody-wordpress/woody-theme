<?php

/**
 * Shortscodes
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

namespace Woody\WoodyTheme\library\classes\content;

use Woody\Services\Providers;

class Shortcodes
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_shortcode('woody_anchor', [$this, 'anchorShortcode']);
    }

    public function anchorShortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'id' => 'woody_anchor',
            ),
            $atts,
            'woody_anchor'
        );
        return sprintf(
            '<span class="woody_anchor" id="%s"></span>',
            esc_attr($atts['id'])
        );
    }
}
