<?php
/**
 * ACF sync field
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */
use Symfony\Component\Finder\Finder;

class WoodyTheme_ACF
{
    const ACF = "acf-pro/acf.php";

    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('woody_theme_update', array($this,'cleanTransient'));
        add_action('woody_subtheme_update', array($this,'cleanTransient'));
        add_filter('acf/settings/save_json', array($this,'acfJsonSave'));
        add_filter('acf/settings/load_json', array($this,'acfJsonLoad'));
        add_filter('acf/load_field/type=radio', array($this, 'woodyTplAcfLoadField'));
        add_filter('acf/load_field/type=select', array($this, 'woodyIconLoadField'));
        add_filter('acf/load_field/name=focused_taxonomy_terms', array($this, 'focusedTaxonomyTermsLoadField'));
        add_filter('acf/location/rule_types', array($this, 'woodyAcfAddPageTypeLocationRule'));
        add_filter('acf/location/rule_values/page_type_and_children', array($this, 'woodyAcfAddPageTypeChoices'));
        add_filter('acf/location/rule_match/page_type_and_children', array($this, 'woodyAcfPageTypeMatch'), 10, 3);
    }

    public function acfJsonSave($path)
    {
        // Save ACF Json on Dev
        if (WP_ENV == 'dev') {
            $path = get_template_directory() . '/acf-json';
        }
        return $path;
    }

    public function acfJsonLoad($paths)
    {
        // remove original path (optional)
        unset($paths[0]);
        $paths[] = get_template_directory() . '/acf-json';
        return $paths;
    }

    /**
     * Benoit Bouchaud
     * On ajoute les templates Woody disponibles dans les option du champ radio woody_tpl
     */
    public function woodyTplAcfLoadField($field)
    {
        if (strpos($field['name'], 'woody_tpl') !== false) {
            $field['choices'] = [];

            $woodyComponents = wp_cache_get('woody_components');
            if (empty($woodyComponents)) {
                $woodyComponents = Woody::getComponents();
                wp_cache_set('woody_components', $woodyComponents);
            }

            switch ($field['key']) {
                case 'field_5afd2c9616ecd': // Cas des sections
                    $components = Woody::getTemplatesByAcfGroup($woodyComponents, $field['key']);
                break;
                default:
                if (is_numeric($field['parent'])) {
                    // From 08/31/18, return of $field['parent'] is the acf post id instead of the key
                    $parent_field_as_post = get_post($field['parent']);
                    $components = Woody::getTemplatesByAcfGroup($woodyComponents, $parent_field_as_post->post_name);
                } else {
                    $components = Woody::getTemplatesByAcfGroup($woodyComponents, $field['parent']);
                }

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
                    <img class="img-responsive lazyload" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-src="' . WP_HOME . '/app/dist/' . WP_SITE_KEY . '/img/woody-library/views/' . $component['thumbnails']['small'] . '?version=' . get_option('woody_theme_version') . '" alt="' . $key . '" width="150" height="150" />
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
        $page_types = $this->getPageTypeTerms();
        foreach ($page_types as $key => $type) {
            $choices[$type->slug] = $type->name;
        }
        return $choices;
    }

    public function woodyAcfPageTypeMatch($match, $rule, $options)
    {
        if (empty($options['post_type']) || $options['post_type'] != 'page') {
            return $match;
        }

        $page_types = $this->getPageTypeTerms();
        foreach ($page_types as $term) {
            if ($term->slug == $rule['value']) {
                $current_term = $term;
                break;
            }
        }

        $children_terms_ids = [];
        if (!empty($current_term)) {
            foreach ($page_types as $term) {
                if ($term->parent == $current_term->term_id) {
                    $children_terms_ids[] = $term->term_id;
                }
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
            if (in_array($term_id, $children_terms_ids) || (!empty($current_term) && $term_id == $current_term->term_id)) {
                $match = true;
            }
        }

        if ($rule['operator'] == "!=") {
            $match = !$match;
        }

        return $match;
    }

    public function getPageTypeTerms()
    {
        $page_types = get_transient('woody_terms_page_type');
        if (false === $page_types) {
            $page_types = get_terms(array('taxonomy' => 'page_type', 'hide_empty' => false, 'hierarchical' => true));
            set_transient('woody_terms_page_type', $page_types);
        }

        return $page_types;
    }

    public function cleanTransient()
    {
        delete_transient('woody_terms_page_type');
    }
}
