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
        add_action('woody_theme_update', [$this,'cleanTransient']);
        add_action('woody_subtheme_update', [$this,'cleanTransient']);
        if (WP_ENV == 'dev') {
            add_filter('woody_acf_save_paths', [$this,'acfJsonSave']);
        }
        add_action('create_term', [$this,'cleanTermsChoicesTransient']);
        add_action('edit_term', [$this,'cleanTermsChoicesTransient']);
        add_action('delete_term', [$this,'cleanTermsChoicesTransient']);
        add_filter('acf/settings/load_json', [$this,'acfJsonLoad']);
        add_filter('acf/load_field/type=radio', [$this, 'woodyTplAcfLoadField']);
        add_filter('acf/load_field/type=select', [$this, 'woodyIconLoadField']);
        add_filter('acf/load_field/name=focused_taxonomy_terms', [$this, 'focusedTaxonomyTermsLoadField']);
        add_filter('acf/load_field/name=list_el_terms', [$this, 'focusedTaxonomyTermsLoadField']);
        add_filter('acf/load_field/name=list_filter_custom_terms', [$this, 'focusedTaxonomyTermsLoadField']);
        add_filter('acf/load_field/name=list_filter_taxonomy', [$this, 'pageTaxonomiesLoadField']);
        add_filter('acf/fields/google_map/api', [$this, 'acfGoogleMapKey']);
        add_filter('acf/location/rule_types', [$this, 'woodyAcfAddPageTypeLocationRule']);
        add_filter('acf/location/rule_values/page_type_and_children', [$this, 'woodyAcfAddPageTypeChoices']);
        add_filter('acf/location/rule_match/page_type_and_children', [$this, 'woodyAcfPageTypeMatch'], 10, 3);
    }

    /**
     * Register ACF Json Save directory
     */
    public function acfJsonSave($groups)
    {
        $groups['default'] = get_template_directory() . '/acf-json';
        return $groups;
    }

    /**
     * Register ACF Json load directory
     */
    public function acfJsonLoad($paths)
    {
        $paths[] = get_template_directory() . '/acf-json';
        return $paths;
    }

    /**
     * Register Raccourci GoogleMapKey
     */
    public function acfGoogleMapKey($api)
    {
        $keys = [
            'AIzaSyAIWyOS5ifngsd2S35IKbgEXXgiSAnEjsw',
            'AIzaSyBMx446Q--mQj9mzuZhb7BGVDxac6NfFYc',
            'AIzaSyB8Fozhi1FKU8oWYJROw8_FgOCbn3wdrhs',
        ];
        $rand_keys = array_rand($keys, 1);
        $api['key'] = $keys[$rand_keys];
        return $api;
    }

    /**
     * Benoit Bouchaud
     * On ajoute les templates Woody disponibles dans les option du champ radio woody_tpl
     */
    public function woodyTplAcfLoadField($field)
    {
        if (strpos($field['name'], 'woody_tpl') !== false) {
            $field['choices'] = [];

            $woodyComponents = get_transient('woody_components');
            if (empty($woodyComponents)) {
                $woodyComponents = Woody::getComponents();
                set_transient('woody_components', $woodyComponents);
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
        $choices = [];
        $terms = [];

        $choices = get_transient('woody_terms_choices');
        if (empty($choices)) {

            // Get all site taxonomies and exclude those we don't want to use
            $taxonomies = get_object_taxonomies('page');

            // Remove useless taxonomies
            unset($taxonomies['page_type']);

            foreach ($taxonomies as $key => $taxonomy) {
                // Get terms for each taxonomy and push them in $terms
                $tax_terms = get_terms(array(
                    'taxonomy' => $taxonomy,
                    'hide_empty' => false,
                ));

                $tax = get_taxonomy($taxonomy);
                foreach ($tax_terms as $key => $term) {
                    if ($term->name == 'Uncategorized') {
                        continue;
                    }
                    $choices[$term->term_taxonomy_id] = $tax->label . ' - ' . $term->name;
                }
            }

            set_transient('woody_terms_choices', $choices);
        }

        $field['choices'] = $choices;
        return $field;
    }


    public function pageTaxonomiesLoadField($field)
    {
        $choices = get_transient('woody_page_taxonomies_choices');
        if (empty($choices)) {
            $taxonomies = get_object_taxonomies('page', 'objects');

            foreach ($taxonomies as $key => $taxonomy) {
                $choices[$taxonomy->name] = $taxonomy->label;
            }

            set_transient('woody_page_taxonomies_choices', $choices);
        }

        $field['choices'] = $choices;
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
        } elseif (!empty($options['post_id'])) {
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
        delete_transient('woody_components');
        delete_transient('woody_icons_folder');
    }

    public function cleanTermsChoicesTransient()
    {
        delete_transient('woody_terms_choices');
    }
}
