<?php

/**
 * ACF sync field
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

use Woody\Utils\Output;

class WoodyTheme_ACF
{
    const ACF = "acf-pro/acf.php";

    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('woody_theme_update', [$this, 'cleanTransient']);
        if (WP_ENV == 'dev') {
            add_filter('woody_acf_save_paths', [$this, 'acfJsonSave']);
        }
        add_action('create_term', [$this, 'cleanTermsChoicesTransient']);
        add_action('edit_term', [$this, 'cleanTermsChoicesTransient']);
        add_action('delete_term', [$this, 'cleanTermsChoicesTransient']);

        add_action('acf/save_post', [$this, 'clearOptionsTransient'], 20);

        add_filter('acf/settings/load_json', [$this, 'acfJsonLoad']);
        add_filter('acf/load_field/type=radio', [$this, 'woodyTplAcfLoadField']);
        add_filter('acf/load_field/type=select', [$this, 'woodyIconLoadField']);

        add_filter('acf/load_field/name=focused_taxonomy_terms', [$this, 'focusedTaxonomyTermsLoadField']);
        add_filter('acf/load_value/name=focused_taxonomy_terms', [$this, 'termsLoadValue'], 10, 3);

        add_filter('acf/load_field/name=list_el_terms', [$this, 'focusedTaxonomyTermsLoadField']);
        add_filter('acf/load_value/name=list_el_terms', [$this, 'termsLoadValue'], 10, 3);

        add_filter('acf/load_field/name=list_filter_custom_terms', [$this, 'focusedTaxonomyTermsLoadField']);
        add_filter('acf/load_value/name=list_filter_custom_terms', [$this, 'termsLoadValue'], 10, 3);

        add_filter('acf/load_field/name=list_filter_taxonomy', [$this, 'pageTaxonomiesLoadField']);
        add_filter('acf/load_value/name=list_filter_taxonomy', [$this, 'termsLoadValue'], 10, 3);

        add_filter('acf/load_field/name=display_elements', [$this, 'displayElementLoadField'], 10, 3);

        add_filter('acf/fields/google_map/api', [$this, 'acfGoogleMapKey']);
        add_filter('acf/location/rule_types', [$this, 'woodyAcfAddPageTypeLocationRule']);
        add_filter('acf/location/rule_values/page_type_and_children', [$this, 'woodyAcfAddPageTypeChoices']);
        add_filter('acf/location/rule_match/page_type_and_children', [$this, 'woodyAcfPageTypeMatch'], 10, 3);

        add_filter('acf/load_field/name=weather_account', [$this, 'weatherAccountAcfLoadField'], 10, 3);

        add_filter('acf/fields/post_object/result', [$this, 'postObjectAcfResults'], 10, 4);
        add_filter('acf/fields/page_link/result', [$this, 'postObjectAcfResults'], 10, 4);

        add_filter('acf/load_value/type=gallery', [$this, 'pllGalleryLoadField'], 10, 3);

        add_filter('acf/load_field/name=section_content', [$this, 'sectionContentLoadField']);

        add_filter('acf/load_field/name=page_heading_tags', [$this, 'listAllPageTerms'], 10, 3);

        // Custom Filter
        add_filter('woody_get_field_option', [$this, 'woodyGetFieldOption'], 10, 3);
    }

    public function woodyGetFieldOption($field_name)
    {
        $woody_get_field_option = get_transient('woody_get_field_option');
        if (empty($woody_get_field_option[$field_name])) {
            $woody_get_field_option[$field_name] = get_field($field_name, 'options');
            set_transient('woody_get_field_option', $woody_get_field_option);
        }
        return $woody_get_field_option[$field_name];
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

    public function clearOptionsTransient()
    {
        $screen = get_current_screen();
        if (!empty($screen->id) && strpos($screen->id, 'acf-options') !== false) {
            // delete_transient('woody_menus_cache');
            delete_transient('woody_get_field_option');

            // Purge all varnish cache on save menu
            do_action('woody_flush_varnish');
        }
    }

    public function pllGalleryLoadField($value, $post_id, $field)
    {
        if (!empty($value)) {
            foreach ($value as $id_key => $id) {
                $value[$id_key] = pll_get_post($id);
            }
        }
        return $value;
    }

    /**
     * Register Raccourci GoogleMapKey
     */
    public function acfGoogleMapKey($api)
    {
        $keys = WOODY_GOOGLE_MAPS_API_KEY;
        if (is_array($keys) && !empty($keys)) {
            $rand_keys = array_rand($keys, 1);
            $api['key'] = $keys[$rand_keys];
            return $api;
        }
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
                $woodyComponents = WoodyLibrary::getComponents();
                set_transient('woody_components', $woodyComponents);
            }

            switch ($field['key']) {
                case 'field_5afd2c9616ecd': // Cas des sections
                    $components = WoodyLibrary::getTemplatesByAcfGroup($woodyComponents, $field['key']);
                    break;
                case 'field_5d16118093cc1': // Cas des mises en avant de composants de séjours
                    $components = WoodyLibrary::getTemplatesByAcfGroup($woodyComponents, $field['key']);
                    break;
                default:
                    if (is_numeric($field['parent'])) {
                        // From 08/31/18, return of $field['parent'] is the acf post id instead of the key
                        $parent_field_as_post = get_post($field['parent']);
                        $components = WoodyLibrary::getTemplatesByAcfGroup($woodyComponents, $parent_field_as_post->post_name);
                    } else {
                        $components = WoodyLibrary::getTemplatesByAcfGroup($woodyComponents, $field['parent']);
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

                $woody_tpls_order = get_transient('woody_tpls_order');
                if (empty($woody_tpls_order)) {
                    $woody_tpls_order = array_flip($this->sortWoodyTpls());
                    set_transient('woody_tpls_order', $woody_tpls_order);
                }

                foreach ($woody_tpls_order as $order_key => $value) {
                    if (!array_key_exists($order_key, $field['choices'])) {
                        unset($woody_tpls_order[$order_key]);
                    }
                }

                $field['choices'] = array_merge($woody_tpls_order, $field['choices']);
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

        $lang = $this->getCurrentLang();
        $choices = get_transient('woody_terms_choices');
        if (empty($choices[$lang])) {

            // Get all site taxonomies and exclude those we don't want to use
            $taxonomies = get_object_taxonomies('page', 'objects');

            // Remove useless taxonomies
            $unset_taxonomies = [
                'page_type',
                'post_translations', // Polylang
                'language', // Polylang
            ];

            foreach ($taxonomies as $taxonomy) {
                // Remove useless taxonomies
                if (in_array($taxonomy->name, $unset_taxonomies)) {
                    continue;
                }

                // Get terms for each taxonomy and push them in $terms
                $tax_terms = get_terms(array(
                    'taxonomy' => $taxonomy->name,
                    'hide_empty' => false,
                ));

                foreach ($tax_terms as $term) {
                    if ($term->name == 'Uncategorized') {
                        continue;
                    }
                    $choices[$lang][$term->term_id] = $taxonomy->label . ' - ' . $term->name;
                }
            }

            // Sort by values
            if (!empty($choices[$lang]) && is_array($choices[$lang])) {
                asort($choices[$lang]);
            }

            set_transient('woody_terms_choices', $choices);
        }

        $field['choices'] = (!empty($choices[$lang])) ? $choices[$lang] : [];
        return $field;
    }

    public function pageTaxonomiesLoadField($field)
    {
        $lang = $this->getCurrentLang();
        $choices = get_transient('woody_page_taxonomies_choices');
        if (empty($choices[$lang])) {
            $taxonomies = get_object_taxonomies('page', 'objects');

            foreach ($taxonomies as $key => $taxonomy) {
                $choices[$lang][$taxonomy->name] = $taxonomy->label;
            }

            set_transient('woody_page_taxonomies_choices', $choices);
        }

        $field['choices'] = (!empty($choices[$lang])) ? $choices[$lang] : [];
        return $field;
    }

    /**
     * Léo POIROUX
     * On traduit les termes lors que la synchronisation d'une page dans les blocs Focus ou Liste de contenus
     */
    public function termsLoadValue($value, $post_id, $field)
    {
        $lang = $this->getCurrentLang();
        if (is_array($value) && function_exists('pll_get_term')) {
            foreach ($value as $key => $term_id) {
                $value[$key] = pll_get_term($term_id, $lang);
            }
        }

        return $value;
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

    public function displayElementLoadField($field)
    {
        if ($field['key'] == 'field_5bfeaaf039785') {
            return $field;
        }
        $taxonomies = get_transient('woody_website_pages_taxonomies');
        if (empty($taxonomies)) {
            $taxonomies = get_object_taxonomies('page', 'objects');
            unset($taxonomies['language']);
            unset($taxonomies['page_type']);
            unset($taxonomies['post_translations']);

            set_transient('woody_website_pages_taxonomies', $taxonomies);
        }
        foreach ($taxonomies as $key => $taxonomy) {
            $field['choices']['_' . $taxonomy->name] = (!empty($taxonomy->labels->singular_name)) ? $taxonomy->labels->singular_name . ' principal(e)</small>' : $taxonomy->label . ' <small>Tag principal</small>';
        }
        return $field;
    }

    public function weatherAccountAcfLoadField($field)
    {
        $field['choices'] = apply_filters('woody_weather_accounts', $field['choices']);
        return $field;
    }

    public function sectionContentLoadField($field)
    {
        if (!in_array('weather', WOODY_OPTIONS)) {
            // On retire l'option bloc météo si le plugin n'est pas activé
            unset($field['layouts']['layout_5c1b579ac3a87']);
        }
        return $field;
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

    public function listAllPageTerms($field)
    {
        $terms = [];
        $hero_terms = [];
        $taxonomies = get_taxonomies();
        $displayIcon = get_field('page_heading_term_icon'); // With plugin


        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy == 'places' || $taxonomy == 'seasons' || $taxonomy == 'themes') {
                if (is_array(get_the_terms(get_the_id(), $taxonomy))) {
                    $terms = array_merge($terms, get_the_terms(get_the_id(), $taxonomy));
                    if ($displayIcon) {
                        $terms = apply_filters('woody_taxonomies_with_icons', $terms);
                    }
                }
            }
        }

        if (!empty($terms)) {
            foreach ($terms as $term) {
                $hasIcon = !empty($term->term_icon) ? '<span class="' . $term->term_icon . '"></span>' : '';
                $hero_terms[$term->term_id] = $hasIcon . '<span class="label">' . $term->name . '</span>';
            }
        }

        $field['choices'] = $hero_terms;

        return $field;
    }

    private function getCurrentLang()
    {
        $current_lang = PLL_DEFAULT_LANG;

        // Polylang
        if (function_exists('pll_current_language')) {
            $current_lang = pll_current_language();
        }

        return $current_lang;
    }

    public function cleanTransient()
    {
        // Delete Transient
        delete_transient('woody_terms_page_type');
        delete_transient('woody_tpls_order');
        delete_transient('woody_components');
        delete_transient('woody_icons_folder');
        delete_transient('woody_page_taxonomies_choices');
        delete_transient('woody_terms_choices');
        delete_transient('woody_website_pages_taxonomies');
        // delete_transient('woody_menus_cache');
        delete_transient('woody_get_field_option');

        // Warm Transient
        getWoodyTwigPaths();
    }

    public function cleanTermsChoicesTransient()
    {
        delete_transient('woody_page_taxonomies_choices');
        delete_transient('woody_terms_choices');
    }

    public function postObjectAcfResults($title, $post, $field, $post_id)
    {
        $parent_id = getPostRootAncestor($post->ID);
        if (!empty($parent_id)) {
            $parent = get_post($parent_id);
            $sufix = '<small style="color:#cfcfcf; font-style:italic">( Enfant de ' . $parent->post_title . ')</small>';
            $title = $title . ' - ' . $sufix;
        }
        return $title;
    }

    private function sortWoodyTpls()
    {
        $woodyTpls = [
            'swipers' => [
                'swipers-landing_swipers-tpl_01',
                'swipers-landing_swipers-tpl_02',
                'swipers-landing_swipers-tpl_03',
                'swipers-landing_swipers-tpl_04',
                'swipers-landing_swipers-tpl_05',
                'swipers-landing_swipers-tpl_06'
            ],
            'heroes' => [
                'blocks-hero-tpl_01',
                'blocks-hero-tpl_02',
                'blocks-hero-tpl_03',
                'blocks-hero-tpl_04'
            ],
            'teasers' => [
                'blocks-page_teaser-tpl_01',
                'blocks-page_teaser-tpl_02',
                'blocks-page_teaser-tpl_03',
                'blocks-page_teaser-tpl_04'
            ],
            'sections' => [
                'grids_basic-grid_1_cols-tpl_01',
                'grids_basic-grid_1_cols-tpl_02',
                'grids_basic-grid_2_cols-tpl_01',
                'grids_basic-grid_2_cols-tpl_02',
                'grids_basic-grid_2_cols-tpl_05',
                'grids_basic-grid_2_cols-tpl_03',
                'grids_basic-grid_2_cols-tpl_04',
                'grids_basic-grid_3_cols-tpl_01',
                'grids_basic-grid_3_cols-tpl_02',
                'grids_basic-grid_3_cols-tpl_03',
                'grids_basic-grid_3_cols-tpl_04',
                'grids_basic-grid_4_cols-tpl_01',
                'grids_basic-grid_5_cols-tpl_01',
                'grids_basic-grid_6_cols-tpl_01',
                'grids_split-grid_2_cols-tpl_06',
                'grids_split-grid_2_cols-tpl_05',
                'grids_split-grid_2_cols-tpl_04',
                'grids_split-grid_2_cols-tpl_01',
                'grids_split-grid_2_cols-tpl_03',
                'grids_split-grid_2_cols-tpl_02'
            ],
            'lists_and_focuses' => [
                'blocks-focus-tpl_103',
                'blocks-focus-tpl_112',
                'blocks-focus-tpl_104',
                'blocks-focus-tpl_113',
                'blocks-focus-tpl_105',
                'blocks-focus-tpl_102',
                'blocks-focus-tpl_101',
                'blocks-focus-tpl_110',
                'blocks-focus-tpl_106',
                'blocks-focus-tpl_107',
                'blocks-focus-tpl_108',
                'blocks-focus-tpl_109',
                'blocks-focus-tpl_119',
                'blocks-focus-tpl_120',
                'blocks-focus-tpl_114',
                'blocks-focus-tpl_116',
                'blocks-focus-tpl_121',
                'blocks-focus-tpl_111',
                'blocks-focus-tpl_117',
                'blocks-focus-tpl_118',
                'lists-list_grids-tpl_207',
                'lists-list_grids-tpl_202',
                'lists-list_grids-tpl_209',
                'lists-list_grids-tpl_206',
                'lists-list_grids-tpl_208',
                'lists-list_grids-tpl_203',
                'lists-list_grids-tpl_204',
                'lists-list_grids-tpl_201',
                'lists-list_grids-tpl_205',
                'blocks-focus-tpl_201',
                'blocks-focus-tpl_310',
                'blocks-focus-tpl_301',
                'blocks-focus-tpl_304',
                'blocks-focus-tpl_308',
                'blocks-focus-tpl_306',
                'blocks-focus-tpl_313',
                'blocks-focus-tpl_309',
                'blocks-focus-tpl_303',
                'blocks-focus-tpl_307',
                'blocks-focus-tpl_311',
                'blocks-focus-tpl_302',
                'blocks-focus-tpl_305',
                'blocks-focus-tpl_315',
                'blocks-focus-tpl_312',
                'blocks-focus-tpl_314',
                'lists-list_grids-tpl_307',
                'lists-list_grids-tpl_302',
                'lists-list_grids-tpl_309',
                'lists-list_grids-tpl_306',
                'lists-list_grids-tpl_308',
                'lists-list_grids-tpl_303',
                'lists-list_grids-tpl_304',
                'lists-list_grids-tpl_301',
                'lists-list_grids-tpl_305',
                'lists-list_grids-tpl_310',
                'blocks-focus-tpl_401',
                'blocks-focus-tpl_402',
                'blocks-focus-tpl_403',
                'blocks-focus-tpl_406',
                'blocks-focus-tpl_404',
                'blocks-focus-tpl_405',
                'blocks-focus-tpl_501',
                'blocks-focus-tpl_502',
                'blocks-focus-tpl_503',
                'blocks-focus-tpl_601',
                'blocks-focus-tpl_602',
                'blocks-focus-tpl_603',
                'blocks-focus-tpl_604',
                'blocks-focus-tpl_701',
                'blocks-focus-tpl_1001',
                'blocks-focus_map-tpl_01',
                'blocks-focus_map-tpl_02',
                'lists-list_full-tpl_101',
                'lists-list_full-tpl_102',
                'lists-list_full-tpl_105',
                'lists-list_full-tpl_103',
                'lists-list_full-tpl_104',
                'lists-list_full-tpl_201',
                'lists-list_full-tpl_301'
            ],
            'galleries' => [
                'blocks-media_gallery-tpl_102',
                'blocks-media_gallery-tpl_110',
                'blocks-media_gallery-tpl_103',
                'blocks-media_gallery-tpl_104',
                'blocks-media_gallery-tpl_101',
                'blocks-media_gallery-tpl_105',
                'blocks-media_gallery-tpl_107',
                'blocks-media_gallery-tpl_108',
                'blocks-media_gallery-tpl_106',
                'blocks-media_gallery-tpl_109',
                'blocks-media_gallery-tpl_202',
                'blocks-media_gallery-tpl_203',
                'blocks-media_gallery-tpl_204',
                'blocks-media_gallery-tpl_201',
                'blocks-media_gallery-tpl_205',
                'blocks-media_gallery-tpl_302',
                'blocks-media_gallery-tpl_303',
                'blocks-media_gallery-tpl_304',
                'blocks-media_gallery-tpl_301',
                'blocks-media_gallery-tpl_305',
                'blocks-media_gallery-tpl_403',
                'blocks-media_gallery-tpl_404',
                'blocks-media_gallery-tpl_401',
                'blocks-media_gallery-tpl_405',
                'blocks-media_gallery-tpl_503',
                'blocks-media_gallery-tpl_504',
                'blocks-media_gallery-tpl_501',
                'blocks-media_gallery-tpl_505',
                'blocks-media_gallery-tpl_603',
                'blocks-media_gallery-tpl_604',
                'blocks-media_gallery-tpl_601',
                'blocks-media_gallery-tpl_605',
                'blocks-media_gallery-tpl_206',
                'blocks-media_gallery-tpl_207',
                'blocks-media_gallery-tpl_306',
                'blocks-media_gallery-tpl_307',
                'blocks-media_gallery-tpl_208',
                'blocks-media_gallery-tpl_209',
                'blocks-media_gallery-tpl_210',
                'blocks-media_gallery-tpl_211',
            ],
            'cta' => [
                'blocks-call_to_action-tpl_01',
                'blocks-call_to_action-tpl_02',
                'blocks-call_to_action-tpl_05',
                'blocks-call_to_action-tpl_03',
                'blocks-call_to_action-tpl_04',
            ],
            'socialwalls' => [
                'blocks-socialwall-tpl_01',
                'blocks-socialwall-tpl_02',
                'blocks-novascotia-tpl_01'
            ],
            'booking' => [
                'blocks-booking-tpl_01',
                'blocks-booking-tpl_02',
            ],
            'semantic_view' => [
                'blocks-semantic_view-tpl_01',
                'blocks-semantic_view-tpl_02',
                'blocks-semantic_view-tpl_03',
                'blocks-semantic_view-tpl_04',
            ],
            'features' => [
                'blocks-feature-tpl_01',
                'blocks-feature-tpl_02',
                'blocks-feature-tpl_03',
            ]
        ];

        foreach ($woodyTpls as $componentName => $woodyComponent) {
            $index = 1;
            foreach ($woodyComponent as $componentTpl) {
                $return[$componentName . '_' . $index] = $componentTpl;
                $index++;
            }
        }

        return $return;
    }
}
