<?php
/**
 * Links
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.43.7
 */

namespace Woody\WoodyTheme\library\classes\content;

class Page
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('woody_meta_lang_usages_post_types', [$this, 'metaLangUsagesPostTypes']);
    }

    public function metaLangUsagesPostTypes($addons)
    {
        $addons['page'] = [
            'posts_types' => [
                'page'
            ],
            'default_lang' => function_exists('pll_default_language') ? pll_default_language() : PLL_DEFAULT_LANGUAGE
        ];
        return $addons;
    }
}
