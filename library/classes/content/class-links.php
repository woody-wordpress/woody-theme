<?php
/**
 * Links
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Links
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('wp_link_query_args', [$this, 'customLinksSearch']);
    }

    public function customLinksSearch($query)
    {
        if (!empty($query['s'])) {
            if (strpos($query['s'], '#page') !== false) {
                $query['s'] = str_replace('#page', '', $query['s']);
                $query['post_type'] = array('page');
            } elseif (strpos($query['s'], '#sit') !== false) {
                $query['s'] = str_replace('#sit', '', $query['s']);
                $query['post_type'] = array('touristic_sheet');
            } elseif (strpos($query['s'], '#lien') !== false) {
                $query['s'] = str_replace('#lien', '', $query['s']);
                $query['post_type'] = array('short_link');
            }
        }

        return $query;
    }
}
