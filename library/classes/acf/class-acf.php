<?php
/**
 * ACF sync field
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_ACF
{
    const ACF = "acf-pro/acf.php";

    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        acf_update_setting('save_json', get_template_directory() . '/acf-json');
        acf_append_setting('load_json', get_template_directory() . '/acf-json');

        add_filter('plugin_action_links', array($this, 'disallowAcfDeactivation'), 10, 4);
        add_filter('acf/load_field/type=radio', array($this, 'woodyTplAcfLoadField'));
        add_filter('acf/load_field/type=select', array($this, 'woodyIconLoadField'));
        add_filter('acf/load_field/name=focused_taxonomy_terms', array($this, 'focusedTaxonomyTermsLoadField'));
        add_filter('acf/location/rule_types', array($this, 'woodyAcfAddPageTypeLocationRule'));
        add_filter('acf/location/rule_values/page_type_and_children', array($this, 'woodyAcfAddPageTypeChoices'));
        add_filter('acf/location/rule_match/page_type_and_children', array($this, 'woodyAcfPageTypeMatch'), 10, 3);
        // add_filter('acf/load_field/name=playlist_name', array($this, 'playlistNameLoadField'));
    }

    /**
     * Benoit Bouchaud
     * On bloque l'accès à la désactivation du plugin ACF
     */
    public function disallowAcfDeactivation($actions, $plugin_file, $plugin_data, $context)
    {
        if (array_key_exists('deactivate', $actions) and $plugin_file == self::ACF) {
            unset($actions['deactivate']);
        }
        return $actions;
    }

    /**
     * Benoit Bouchaud
     * On ajoute les templates Woody disponibles dans les option du champ radio woody_tpl
     */
    public function woodyTplAcfLoadField($field)
    {
        if (strpos($field['name'], 'woody_tpl') !== false) {
            $field['choices'] = [];

            switch ($field['key']) {
                case 'field_5afd2c9616ecd':
                    $components = Woody::getTemplatesByAcfGroup($field['key']);
                break;
                default:
                    $components = Woody::getTemplatesByAcfGroup($field['parent']);
            }

            if (!empty($components)) {
                foreach ($components as $key => $component) {
                    $tpl_name = (!empty($component['name'])) ? $component['name'] : '{Noname :/}';
                    $tpl_desc = (!empty($component['description'])) ? $component['description'] : '{Nodesc :/}';

                    $fitted_for = (!empty($component['items_count'][0]['fitted_for'])) ? $component['items_count'][0]['fitted_for'] : '';
                    $accepts_max = (!empty($component['items_count'][0]['accepts_max'])) ? $component['items_count'][0]['accepts_max'] : '';
                    $count_data = [];

                    if (!empty($fitted_for)) {
                        $count_data[] = 'data-fittedfor="' . $fitted_for . '"';
                    }

                    if (!empty($accepts_max)) {
                        $count_data[] = 'data-acceptsmax="' . $accepts_max . '"';
                    }

                    $count_data = implode(' ', $count_data);

                    $field['choices'][$key] = '<div class="tpl-choice-wrapper" ' . $count_data . '>
                    <img class="img-responsive" src="' . get_stylesheet_directory_uri() . '/dist/img/woody-library/views/' . $component['thumbnails']['small'] . '" alt="' . $key . '" width="150" height="150" />
                    <h5 class="tpl-title">' . $tpl_name . '</h5>
                    <div class="dashicons dashicons-info toggle-desc"></div>
                    <div class="tpl-desc hidden"><h4 class="tpl-title">' . $tpl_name . '</h4>' . $tpl_desc . '<span class="dashicons dashicons-no close-desc"></span></div>
                    <div class="desc-backdrop hidden"></div>
                    </div>';
                    if ($field['name'] == 'section_woody_tpl' || $field['name'] == 'tab_woody_tpl' || $field['name'] == 'slide_woody_tpl') {
                        foreach ($field['choices'] as $name => $value) {
                            if (strpos($name, 'basic-grid_1_cols-tpl_01') !== false) {
                                $field['default_value'] = $name;
                            }
                        }
                    }
                }
            }
        }

        return $field;
    }

    /**
     * Benoit Bouchaud
     * On ajoute tous les termes de taxonomie du site dans le sélecteur de termes de la mise en avant automatique
     */
    public function focusedTaxonomyTermsLoadField($field)
    {
        // Reset field's choices + create $terms for future choices
        $field['choices'] = [];
        $terms = [];

        // Get all site taxonomies and exclude those we don't want to use
        $taxonomies = get_taxonomies();
        $excluded_taxonomies = array('page_type', 'post_tag', 'nav_menu', 'link_category', 'post_format');

        foreach ($taxonomies as $key => $taxonomy) {
            // Don't loop through useless taxonomies
            if (in_array($taxonomy, $excluded_taxonomies)) {
                continue;
            }

            // Get terms for each taxonomy and push them in $terms
            $tax_terms = get_terms(array(
                    'taxonomy' => $taxonomy,
                    'hide_empty' => false,
                ));
            foreach ($tax_terms as $key => $term) {
                if ($term->name == 'Uncategorized') {
                    continue;
                }
                $terms[] = $term;
            }
        }

        // Forach term, get its + its taxonomy human name and push it into $field['choices']
        foreach ($terms as $key => $term) {
            $tax_machine_name = $term->taxonomy;
            $tax = get_taxonomy($tax_machine_name);
            $tax_name = $tax->label;
            $field['choices'][$term->term_taxonomy_id] = $term->name . ' - ' . $tax_name;
        }

        return $field;
    }

    /**
    * Benoit Bouchaud
    * On remplit le select "icones" avec les woody-icons disponibles
    */
    public function woodyIconLoadField($field)
    {
        if (strpos($field['name'], 'woody_icon') !== false) {
            $icons = getWoodyIcons();
            foreach ($icons as $key => $icon) {
                $field['choices'][$key] = '<div class="wicon-select"><span class="wicon-woody-icons ' . $key . '"></span><span>' . $icon . '</span></div>';
            }
        }

        return $field;
    }


    public function woodyAcfAddPageTypeLocationRule($choices)
    {
        $choices['Woody']['page_type_and_children'] = 'Type de publication (et ses enfants)';
        return $choices;
    }

    public function woodyAcfAddPageTypeChoices($choices)
    {
        $page_types = get_terms(array('taxonomy' => 'page_type', 'hide_empty' => false, 'hierarchical' => true));
        foreach ($page_types as $key => $type) {
            $choices[$type->term_id] = $type->name;
        }
        return $choices;
    }

    public function woodyAcfPageTypeMatch($match, $rule, $options)
    {
        $children_terms_ids = [];
        $children_terms = get_terms(array('taxonomy' => 'page_type', 'hide_empty' => false, 'parent' => $rule['value']));
        if (!empty($children_terms)) {
            foreach ($children_terms as $term) {
                $children_terms_ids[] = $term->term_id;
            }
        }

        $selected_term_ids = [];
        if ($options['ajax'] && !empty($options['post_terms']) && !empty($options['post_terms']['page_type'])) {
            $selected_term_ids = $options['post_terms']['page_type'];
        } else {
            $current_page_type = wp_get_post_terms($options['post_id'], 'page_type');
            if (!empty($current_page_type[0]) && !empty($current_page_type[0]->term_id)) {
                $selected_term_ids[] = $current_page_type[0]->term_id;
            }
        }

        // Toujours vide à la création de page
        if (empty($selected_term_ids)) {
            return false;
        }

        foreach ($selected_term_ids as $term_id) {
            if (in_array($term_id, $children_terms_ids)) {
                $match = true;
            }
        }

        if ($rule['operator'] == "!=") {
            $match = !$match;
        }

        return $match;
    }

    // public function playlistNameLoadField($field)
    // {
    //     global $post;
    //     if (!empty($post)) {
    //         $confId = get_field('playlist_conf_id', $post->ID);
    //         $post_title = $post->post_title;
    //         $type_term = get_the_terms($post->ID, 'page_type');
    //         if (!empty($type_term)) {
    //             $type = $type_term[0]->slug;
    //             if ($type == 'playlist_tourism') {
    //                 $field['value'] = 'WP - Playlist ' . $post->post_title;
    //             }
    //         }
    //         $response = apply_filters('wp_hawwwai_sit_conf_editor_rename', $confId, $field['value']);
    //     }

    //     return $field;
    // }
}
