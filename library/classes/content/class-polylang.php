<?php
/**
 * Polylang
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Polylang
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('pll_is_cache_active', [$this, 'isCacheActive']);
    }

    protected function isCacheActive()
    {
        return true;
    }
}
