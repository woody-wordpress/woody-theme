<?php
/**
 * Links
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

namespace Woody\WoodyTheme\library\classes\content;

class Links
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('wp_link_query_args', [$this, 'customLinksSearch']);
        add_filter('wp_link_query', [$this, 'customLinksResults'], 10, 2);
        add_filter('page_attributes_dropdown_pages_args', [$this, 'pageAttributesDropdownPagesArgs'], 1, 1);
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
            } elseif (strpos($query['s'], '#topic') !== false) {
                $query['s'] = str_replace('#topic', '', $query['s']);
                $query['post_type'] = array('woody_topic');
            } elseif (strpos($query['s'], '#youbook') !== false) {
                $query['s'] = str_replace('#youbook', '', $query['s']);
                $query['post_type'] = array('youbook_product');
            }
        }

        return $query;
    }

    public function customLinksResults($results, $query)
    {
        foreach ($results as $result_key => $result) {
            $parent_id = getPostRootAncestor($result['ID']);
            if (!empty($parent_id)) {
                $parent = get_post($parent_id);
                $sufix = '<small style="color:#cfcfcf; font-style:italic">( Enfant de ' . apply_filters('the_title', $parent->post_title) . ')</small>';
                $results[$result_key]['title'] = $results[$result_key]['title'] . ' - ' . $sufix;
            }
        }

        return $results;
    }

    public function pageAttributesDropdownPagesArgs($dropdown_args)
    {
        // Filtre permettant d'enregistrer une page ayant pour parent une page "brouillon"
        $dropdown_args['post_status'] = array('publish', 'draft');
        return $dropdown_args;
    }
}
