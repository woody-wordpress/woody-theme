<?php

/**
 * ACF sync field
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

//TODO: Executer les fonction back en is_admin uniquement + screen_id post
class WoodyTheme_ACF
{
    /**
     * @var string
     */
    public const ACF = "acf-pro/acf.php";

    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('woody_theme_update', [$this, 'woodyThemeUpdate']);
        add_action('woody_cache_warm', [$this, 'generateLayoutsTransients'], 100);

        add_action('create_term', [$this, 'cleanTermsChoicesCache']);
        add_action('edit_term', [$this, 'cleanTermsChoicesCache']);
        add_action('delete_term', [$this, 'cleanTermsChoicesCache']);
        // Suppression du cache des blocs pour mettre à jour les termes des taxonomies
        add_action('saved_term', [$this, 'deleteTermCache'], 10, 4);

        add_action('acf/init', [$this, 'registerHooksAfterAcfInit']);

        add_action('acf/save_post', [$this, 'clearVarnishCache'], 20);

        add_filter('acf/settings/load_json', [$this, 'acfJsonLoad']);
        add_filter('woody_acf_save_paths', [$this, 'acfJsonSave']);
        add_filter('acf/load_field/type=select', [$this, 'woodyIconLoadField']);

        add_filter('acf/load_field/name=focused_taxonomy_terms', [$this, 'focusedTaxonomyTermsLoadField']);
        add_filter('acf/load_value/name=focused_taxonomy_terms', [$this, 'termsLoadValue'], 10, 3);

        add_filter('acf/load_field/name=list_el_terms', [$this, 'focusedTaxonomyTermsLoadField']);
        add_filter('acf/load_value/name=list_el_terms', [$this, 'termsLoadValue'], 10, 3);

        add_filter('acf/load_field/name=list_filter_custom_terms', [$this, 'focusedTaxonomyTermsLoadField']);
        add_filter('acf/load_value/name=list_filter_custom_terms', [$this, 'termsLoadValue'], 10, 3);

        add_filter('acf/load_field/name=list_filter_taxonomy', [$this, 'pageTaxonomiesLoadField']);
        add_filter('acf/load_value/name=list_filter_taxonomy', [$this, 'termsLoadValue'], 10, 3);

        add_filter('acf/load_field/name=gallery_tags', [$this, 'focusedTaxonomyTermsLoadField']);
        add_filter('acf/load_value/name=gallery_tags', [$this, 'termsLoadValue'], 10, 3);

        add_filter('acf/load_field/name=display_elements', [$this, 'displayElementLoadField'], 10, 3);

        add_filter('acf/fields/google_map/api', [$this, 'acfGoogleMapKey']);
        add_filter('acf/location/rule_types', [$this, 'woodyAcfAddPageTypeLocationRule']);
        add_filter('acf/location/rule_values/page_type_and_children', [$this, 'woodyAcfAddPageTypeChoices']);
        add_filter('acf/location/rule_match/page_type_and_children', [$this, 'woodyAcfPageTypeMatch'], 10, 3);

        add_filter('acf/fields/post_object/result', [$this, 'postObjectAcfResults'], 10, 4);
        add_filter('acf/fields/post_object/query', [$this, 'getPostObjectDefaultTranslation'], 10, 3);
        add_filter('acf/fields/page_link/result', [$this, 'postObjectAcfResults'], 10, 4);

        add_filter('acf/load_value/type=gallery', [$this, 'pllGalleryLoadField'], 10, 3);

        add_filter('acf/load_field/name=section_content', [$this, 'sectionContentLoadField']);
        add_filter('acf/load_field/name=section_animations', [$this, 'sectionAnimationsForAdmin']);

        add_filter('acf/load_field/name=page_heading_tags', [$this, 'listAllPageTerms'], 10, 3);

        add_filter('acf/load_field/name=tags_primary', [$this, 'addPrimaryTagsFields'], 10, 3);
        add_filter('acf/load_field/name=active_shares', [$this, 'addSocialChoices'], 10, 3);
        add_filter('acf/load_fields', [$this, 'addSeasonChoices'], 10, 2);
        add_filter('acf/update_value', [$this, 'updateCurrentSeason'], 10, 3);

        add_filter('acf/fields/taxonomy/query', [$this, 'hideProfileChildTaxonomies'], 10, 3);

        // Custom Filter
        add_filter('woody_get_field_option', [$this, 'woodyGetFieldOption'], 10, 3);
        add_filter('woody_get_field_object', [$this, 'woodyGetFieldObject'], 10, 3);
        add_filter('woody_get_fields_by_group', [$this, 'woodyGetFieldsByGroup'], 10, 3);

        add_action('wp_ajax_woody_tpls', [$this, 'woodyGetAllTemplates']);

        // Ajax Call
        add_action('wp_ajax_generate_layout_acf_clone', [$this, 'getRenderedLayout']);

        add_filter('acf/load_value/name=edit_mode', [$this, 'editModeLoadField'], 10, 3);
    }

    public function registerHooksAfterAcfInit()
    {
        if (get_field('display_default_lang_title', 'options')) {
            add_filter('acf/fields/post_object/result', [$this, 'translatePostTitleResult'], 10, 4);
            add_filter('acf/fields/page_link/result', [$this, 'translatePostTitleResult'], 10, 4);
        }

        if (get_field('display_sheet_aspect', 'options')) {
            add_filter('acf/fields/post_object/result', [$this, 'getAspectPostTitleResult'], 10, 4);
            add_filter('acf/fields/page_link/result', [$this, 'getAspectPostTitleResult'], 10, 4);
        }
    }

    public function woodyGetFieldOption($field_name = null)
    {
        return (empty($field_name)) ? null : get_field($field_name, 'options');
    }

    // Identique à woodyGetFieldsOption mais avec la fonction get_field_object
    public function woodyGetFieldsObject($field_name = null)
    {
        return (empty($field_name)) ? null : get_field_object($field_name);
    }

    // Retourne un tableau des champs du groupe donné
    public function woodyGetFieldsByGroup($group_name = null)
    {
        return (empty($group_name)) ? null : acf_get_fields($group_name);
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

    public function clearVarnishCache()
    {
        $screen = get_current_screen();
        if (!empty($screen->id) && strpos($screen->id, 'acf-options') !== false) {
            // Purge all varnish cache on save menu
            do_action('woody_flush_varnish');
        }
    }

    public function addPrimaryTagsFields($field)
    {
        // Empty subfields in case of acf-json commit with overrides
        $field['sub_fields'] = [];

        // Get all site taxonomies
        $taxonomies = getPageTaxonomies();

        // Foreach taxonomy, create a subfield
        if (is_array($taxonomies) && !empty($taxonomies)) {
            foreach ($taxonomies as $taxonomy) {
                $field['sub_fields'][] = [
                    'ID' => 0,
                    'append' => '',
                    'class' => '',
                    'conditional_logic' => 0,
                    'default_value' => '',
                    'id' => '',
                    'instructions' => '',
                    'key' => $taxonomy->name . '_field_tag_primary',
                    'label' => $taxonomy->label,
                    'maxlength' => '',
                    'menu_order' => 0,
                    'name' => $taxonomy->name . '_primary',
                    'parent' => "field_5d7bada38eedf",
                    'placeholder' => 'primary_' . $taxonomy->name,
                    'prefix' => 'acf',
                    'prepend' => '',
                    'required' => 0,
                    'type' => 'text',
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => ''
                    ],
                    '_name' => 'primary_' . $taxonomy->name,
                    '_prepare' => 0,
                    '_valid' => 1
                ];
            }
        }

        return $field;
    }

    public function pllGalleryLoadField($value, $post_id, $field)
    {
        if (!empty($value) && is_array($value)) {
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
        $keys = (empty(WOODY_ACF_GOOGLE_MAPS_KEY)) ? WOODY_GOOGLE_MAPS_API_KEY : WOODY_ACF_GOOGLE_MAPS_KEY;
        if (!empty($keys) && is_array($keys)) {
            $rand_keys = array_rand($keys, 1);
            $api['key'] = $keys[$rand_keys];
            return $api;
        }
    }

    /**
     * Benoit Bouchaud
     * On ajoute tous les termes de taxonomie du site dans le sélecteur de termes de la mise en avant automatique
     */
    public function focusedTaxonomyTermsLoadField($field)
    {
        // Reset field's choices + create $terms for future choices
        $choices = [];

        $lang = $this->getCurrentLang();
        $choices = wp_cache_get('woody_terms_choices', 'woody');
        if (empty($choices[$lang])) {
            // Remove useless taxonomies
            $unset_taxonomies = [
                'page_type',
                'post_translations', // Polylang
                'language', // Polylang
            ];

            // Get all site taxonomies and exclude those we don't want to use
            if ($field['name'] === "gallery_tags") {
                $taxonomies = get_object_taxonomies('attachment', 'objects');

                // $unset_taxonomies[] = 'attachment_types';
            } else {
                $taxonomies = get_object_taxonomies('page', 'objects');
            }

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

                    $display_parent_tag_name = get_field('display_parent_tag_name', 'options');
                    $parent_name='';
                    if ($display_parent_tag_name) {
                        //Get the root ancestor of a term
                        $ancestors = get_ancestors($term->term_id, $taxonomy->name);
                        $root_parent_term_id = end($ancestors);
                        if (!empty($root_parent_term_id)) {
                            $root_parent_term = get_term($root_parent_term_id);
                            //Add root parent name
                            if (!empty($root_parent_term)) {
                                $parent_name = '<small style="color:#cfcfcf; font-style:italic"> - ( Enfant de ' . $root_parent_term->name . ' )</small>' ;
                            }
                        }
                    }

                    $choices[$lang][$term->term_id] = $taxonomy->label . ' - ' . $term->name . $parent_name;
                }
            }

            // Sort by values
            if (!empty($choices[$lang]) && is_array($choices[$lang])) {
                asort($choices[$lang]);
            }

            wp_cache_set('woody_terms_choices', $choices, 'woody');
        }

        $field['choices'] = (empty($choices[$lang])) ? [] : $choices[$lang];
        return $field;
    }

    public function pageTaxonomiesLoadField($field)
    {
        $lang = $this->getCurrentLang();
        $choices = wp_cache_get('woody_page_taxonomies_choices', 'woody');
        if (empty($choices[$lang])) {
            $taxonomies = get_object_taxonomies('page', 'objects');

            foreach ($taxonomies as $taxonomy) {
                $choices[$lang][$taxonomy->name] = $taxonomy->label;
            }

            wp_cache_set('woody_page_taxonomies_choices', $choices, 'woody');
        }

        $field['choices'] = (empty($choices[$lang])) ? [] : $choices[$lang];
        return $field;
    }

    /**
     * Léo POIROUX
     * On traduit les termes lors que la synchronisation d'une page dans les blocs Focus ou Liste de contenus
     */
    public function termsLoadValue($value, $post_id, $field)
    {
        $lang = $this->getCurrentLang();
        if (!empty($value) && is_array($value) && function_exists('pll_get_term')) {
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
                $field['choices'][$key] = '<div class="wicon-select"><span class="wicon wicon-woody-icons ' . $key . '"></span><span>' . $icon . '</span></div>';
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
        foreach ($page_types as $type) {
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

        $taxonomies = getPageTaxonomies();
        foreach ($taxonomies as $taxonomy) {
            $field['choices']['_' . $taxonomy->name] = (empty($taxonomy->labels->singular_name)) ? $taxonomy->label . ' <small>Tag principal</small>' : $taxonomy->labels->singular_name . ' principal(e)</small>';
        }

        return $field;
    }

    public function sectionContentLoadField($field)
    {
        //TODO: Move to woody-plugin
        if (!in_array('topics', WOODY_OPTIONS)) {
            // On retire le bloc de mise en avant de topic si le plugin n'est pas activé
            unset($field['layouts']['layout_5d7912723303c']);
        }

        if (!in_array('groups', WOODY_OPTIONS)) {
            // On retire le bloc de mise en avant de composant de séjour si le plugin n'est pas activé
            unset($field['layouts']['5d148175d0510']);
        }

        if (!in_array('weather', WOODY_OPTIONS)) {
            // On retire l'option bloc météo si le plugin n'est pas activé
            unset($field['layouts']['layout_weather']);
        }

        if (!in_array('tides', WOODY_OPTIONS)) {
            // On retire l'option bloc tides si le plugin n'est pas activé
            unset($field['layouts']['layout_addon_tides']);
        }

        if (!in_array('disqus', WOODY_OPTIONS)) {
            // On retire l'option bloc commentaires si le plugin n'est pas activé
            unset($field['layouts']['layout_addon_disqus']);
        }

        if (!in_array('ski_resort', WOODY_OPTIONS)) {
            // On retire l'option bloc infolive si le plugin n'est pas activé (par sécurité)
            unset($field['layouts']['layout_infolive']);
        }

        return $field;
    }

    /**
     * Affichage du champs d'animations de sections seulement si l'utilisateur a le rôle administrateur
     */
    public function sectionAnimationsForAdmin($field)
    {
        $user = wp_get_current_user();
        $is_administrator = in_array('administrator', $user->roles);

        if ($is_administrator) {
            $field['wrapper']['class'] = '';
        }

        return $field;
    }

    public function getPageTypeTerms()
    {
        $page_types = wp_cache_get('woody_terms_page_type', 'woody');
        if (false === $page_types) {
            $page_types = get_terms(array('taxonomy' => 'page_type', 'hide_empty' => false, 'hierarchical' => true));
            wp_cache_set('woody_terms_page_type', $page_types, 'woody');
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
            if (($taxonomy == 'places' || $taxonomy == 'seasons' || $taxonomy == 'themes') && is_array(get_the_terms(get_the_id(), $taxonomy))) {
                $terms = array_merge($terms, get_the_terms(get_the_id(), $taxonomy));
                if ($displayIcon) {
                    $terms = apply_filters('woody_taxonomies_with_icons', $terms);
                }
            }
        }

        if (!empty($terms)) {
            foreach ($terms as $term) {
                $hasIcon = empty($term->term_icon) ? '' : '<span class="wicon ' . $term->term_icon . '"></span>';
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

    public function woodyThemeUpdate()
    {
        // Clean Cache
        wp_cache_delete('woody_tpls_order', 'woody');
        wp_cache_delete('woody_tpls_components', 'woody');
        wp_cache_delete('woody_terms_page_type', 'woody');
        wp_cache_delete('woody_website_pages_taxonomies', 'woody');
        wp_cache_delete('woody_page_taxonomies_choices', 'woody');
        wp_cache_delete('woody_terms_choices', 'woody');
        wp_cache_delete('woody_twig_paths', 'woody');
        wp_cache_delete('woody_components', 'woody');
        wp_cache_delete('woody_icons_folder', 'woody');

        // Warm Cache
        getWoodyTwigPaths();
    }

    public function cleanTermsChoicesCache()
    {
        wp_cache_delete('woody_page_taxonomies_choices', 'woody');
        wp_cache_delete('woody_terms_choices', 'woody');
    }

    public function postObjectAcfResults($title, $post, $field, $post_id)
    {
        $parent_id = getPostRootAncestor($post->ID);

        if (!empty($parent_id)) {
            $parent = get_post($parent_id);
            $title = $title . '<small style="color:#cfcfcf; font-style:italic"> - ( Enfant de ' . $parent->post_title . ' )</small>';
        }

        return $title;
    }

    public function translatePostTitleResult($title, $post, $field, $post_id) {
        $post_lang = apply_filters('woody_pll_get_post_language', $post->ID);
        $default_lang = apply_filters('woody_pll_default_lang_code', null);

        if ($post_lang !== $default_lang) {
            $translation = apply_filters('woody_default_lang_post_title', $post->ID);
            if (!empty($translation)) {
                $title = $title . '<small style="color:#cfcfcf; font-style:italic"> - ( ' . $default_lang . ': ' . $translation . ' )</small>';
            }
        }

        return $title;
    }

    public function getAspectPostTitleResult($title, $post, $field, $post_id) {
        if ($post->post_type == 'touristic_sheet') {
            $touristic_source_identifier = get_field('touristic_source_identifier', $post->ID);

            if (!empty($touristic_source_identifier)) {
                $get_aspect = explode('-', $touristic_source_identifier);
                $sheet_aspect = sizeof($get_aspect) > 1 ? $get_aspect[0] : null;

                if (!empty($sheet_aspect)) {
                    $title = $title . '<small style="color:#cfcfcf; font-style:italic; text-transform: uppercase"> - ' . $sheet_aspect . '</small>';
                }
            }
        }

        return $title;
    }

    public function getPostObjectDefaultTranslation($args, $field, $post_id)
    {
        $default_lang_args = [];
        // Si l'option d'affichage des traductions dans les mises en avant est activée
        // On permet de rechercher les posts dans la langue par défaut
        $display_default_lang_title = apply_filters('woody_get_field_option', 'display_default_lang_title');
        if ($display_default_lang_title) {
            $page_lang = apply_filters('woody_pll_get_post_language', $post_id);
            $default_lang = apply_filters('woody_pll_default_lang_code', null);
            // Si l'on est pas sur langue par défaut du site
            // Si l'utilisateur fait une recherche
            if ($page_lang !== $default_lang && !empty($args['s'])) {
                // On lance une WP_query identique à ACF en forçant la langue => langue par défaut
                $default_lang_args['lang'] = pll_default_language();
                $new_args = array_merge($default_lang_args, $args);
                $new_args['post_type'] = 'page';
                $default_lang_query = new WP_Query($new_args);
                // Si on obtient des résultats dans la langue par défaut,
                // On récupère l'id de la traduction de ce contenu dans la langue courante
                if (!empty($default_lang_query->posts)) {
                    foreach ($default_lang_query->posts as $post) {
                        $translations[] = pll_get_post($post->ID);
                    }
                }

                // On limite la recherche de posts aux id des traductions et on reset le paramètre de recherche
                if (!empty($translations)) {
                    $args['post__in'] = $translations;
                    $args['s'] = '';
                }
            }
        }

        return $args;
    }

    public function editModeLoadField($value, $post_id, $field)
    {
        $global_lite_mode = get_field('global_lite_edit_mode', 'options');

        if ($global_lite_mode && $value != 'lite') {
            $value = 'lite';
        }

        return $value;
    }

    private function sortWoodyTpls()
    {
        $woodyTpls = [
            'swipers' => [
                'swipers-landing_swipers-tpl_01',
                'swipers-landing_swipers-tpl_02',
                'swipers-landing_swipers-tpl_03',
                'swipers-landing_swipers-tpl_04',
                'swipers-landing_swipers-tpl_09',
                'swipers-landing_swipers-tpl_06',
                'swipers-landing_swipers-tpl_08',
                'swipers-landing_swipers-tpl_05',
                'swipers-landing_swipers-tpl_07'
            ],
            'heroes' => [
                'blocks-hero-tpl_01',
                'blocks-hero-tpl_11',
                'blocks-hero-tpl_10',
                'blocks-hero-tpl_14',
                'blocks-hero-tpl_08',
                'blocks-hero-tpl_12',
                'blocks-hero-tpl_02',
                'blocks-hero-tpl_09',
                'blocks-hero-tpl_20',
                'blocks-hero-tpl_03',
                'blocks-hero-tpl_07',
                'blocks-hero-tpl_04',
                'blocks-hero-tpl_13',
                'blocks-hero-tpl_06',
                'blocks-hero-tpl_05',
                'blocks-hero-tpl_18',
                'blocks-hero-tpl_19',
                'blocks-hero-tpl_15',
                'blocks-hero-tpl_16',
                'blocks-hero-tpl_21',
                'blocks-hero-tpl_22',
                'blocks-hero-tpl_17'
            ],
            'teasers' => [
                'blocks-page_teaser-tpl_01',
                'blocks-page_teaser-tpl_02',
                'blocks-page_teaser-tpl_03',
                'blocks-page_teaser-tpl_04',
                'blocks-page_teaser-tpl_07',
                'blocks-page_teaser-tpl_06',
                'blocks-page_teaser-tpl_05'
            ],
            'sections' => [
                'grids_basic-grid_1_cols-tpl_01',
                'grids_basic-grid_1_cols-tpl_02',
                'grids_basic-grid_2_cols-tpl_01',
                'grids_basic-grid_2_cols-tpl_06',
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
                'lists-list_grids-tpl_102',
                'blocks-focus-tpl_103',
                'blocks-focus-tpl_112',
                'blocks-focus-tpl_104',
                'blocks-focus-tpl_113',
                'blocks-focus-tpl_105',
                'blocks-focus-tpl_102',
                'blocks-focus-tpl_101',
                'blocks-focus-tpl_110',
                'blocks-focus-tpl_106',
                'blocks-focus-tpl_122',
                'blocks-focus-tpl_107',
                'blocks-focus-tpl_108',
                'blocks-focus-tpl_109',
                'blocks-focus-tpl_119',
                'blocks-focus-tpl_120',
                'blocks-focus-tpl_123',
                'blocks-focus-tpl_124',
                'blocks-focus-tpl_114',
                'blocks-focus-tpl_116',
                'blocks-focus-tpl_121',
                'blocks-focus-tpl_111',
                'blocks-focus-tpl_117',
                'blocks-focus-tpl_118',
                'blocks-focus-tpl_125',
                'blocks-focus-tpl_126',
                'blocks-focus-tpl_131',
                'blocks-focus-tpl_128',
                'blocks-focus-tpl_129',
                'blocks-focus-tpl_130',
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
                'blocks-focus-tpl_203',
                'blocks-focus-tpl_310',
                'blocks-focus-tpl_301',
                'blocks-focus-tpl_304',
                'blocks-focus-tpl_316',
                'blocks-focus-tpl_308',
                'blocks-focus-tpl_306',
                'blocks-focus-tpl_313',
                'blocks-focus-tpl_309',
                'blocks-focus-tpl_303',
                'blocks-focus-tpl_307',
                'blocks-focus-tpl_311',
                'blocks-focus-tpl_325',
                'blocks-focus-tpl_314',
                'blocks-focus-tpl_302',
                'blocks-focus-tpl_305',
                'blocks-focus-tpl_315',
                'blocks-focus-tpl_317',
                'blocks-focus-tpl_318',
                'blocks-focus-tpl_312',
                'blocks-focus-tpl_320',
                'blocks-focus-tpl_330',
                'blocks-focus-tpl_331',
                'blocks-focus-tpl_332',
                'blocks-focus-tpl_333',
                'blocks-focus-tpl_337',
                'blocks-focus-tpl_338',
                'blocks-focus-tpl_339',
                'blocks-focus-tpl_340',
                'blocks-focus-tpl_341',
                'blocks-focus-tpl_321',
                'blocks-focus-tpl_322',
                'blocks-focus-tpl_319',
                'blocks-focus-tpl_323',
                'blocks-focus-tpl_324',
                'blocks-focus-tpl_342',
                'blocks-focus-tpl_343',
                'blocks-focus-tpl_344',
                'blocks-focus-tpl_345',
                'blocks-focus-tpl_346',
                'blocks-focus-tpl_335',
                'blocks-focus-tpl_336',
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
                'blocks-focus-tpl_407',
                'blocks-focus-tpl_403',
                'blocks-focus-tpl_406',
                'blocks-focus-tpl_404',
                'blocks-focus-tpl_405',
                'blocks-focus-tpl_410',
                'blocks-focus-tpl_412',
                'blocks-focus-tpl_413',
                'blocks-focus-tpl_420',
                'blocks-focus-tpl_421',
                'lists-list_grids-tpl_401',
                'lists-list_grids-tpl_402',
                'blocks-focus-tpl_501',
                'blocks-focus-tpl_502',
                'blocks-focus-tpl_503',
                'blocks-focus-tpl_504',
                'blocks-focus-tpl_505',
                'blocks-focus-tpl_506',
                'blocks-focus-tpl_507',
                'blocks-focus-tpl_508',
                'blocks-focus-tpl_509',
                'blocks-focus-tpl_523',
                'blocks-focus-tpl_521',
                'blocks-focus-tpl_524',
                'blocks-focus-tpl_522',
                'blocks-focus-tpl_510',
                'blocks-focus-tpl_511',
                'blocks-focus-tpl_512',
                'blocks-focus-tpl_548',
                'blocks-focus-tpl_549',
                'blocks-focus-tpl_550',
                'blocks-focus-tpl_551',
                'blocks-focus-tpl_601',
                'blocks-focus-tpl_602',
                'blocks-focus-tpl_605',
                'blocks-focus-tpl_603',
                'blocks-focus-tpl_604',
                'blocks-focus-tpl_701',
                'blocks-focus-tpl_801',
                'blocks-focus-tpl_1001',
                'blocks-focus-tpl_127',
                'blocks-focus-tpl_202',
                'blocks-focus-tpl_318',
                'blocks-focus-tpl_408',
                'blocks-focus-tpl_409',
                'blocks-focus-tpl_411',
                'blocks-focus-tpl_412',
                'blocks-focus_map-tpl_01',
                'blocks-focus_map-tpl_02',
                'blocks-focus_map-tpl_06',
                'blocks-focus_map-tpl_07',
                'blocks-focus_map-tpl_04',
                'blocks-focus_map-tpl_05',
                'blocks-focus_map-tpl_03',
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
                'blocks-media_gallery-tpl_111',
                'blocks-media_gallery-tpl_103',
                'blocks-media_gallery-tpl_104',
                'blocks-media_gallery-tpl_101',
                'blocks-media_gallery-tpl_115',
                'blocks-media_gallery-tpl_105',
                'blocks-media_gallery-tpl_113',
                'blocks-media_gallery-tpl_107',
                'blocks-media_gallery-tpl_108',
                'blocks-media_gallery-tpl_106',
                'blocks-media_gallery-tpl_112',
                'blocks-media_gallery-tpl_109',
                'blocks-media_gallery-tpl_114',
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
                'blocks-media_gallery-tpl_212',
                'blocks-media_gallery-tpl_213',
                'blocks-media_gallery-tpl_214',
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
                'blocks-feature-tpl_04',
            ],
            'features_v2' => [
                'blocks-feature_v2-tpl_101',
                'blocks-feature_v2-tpl_102',
                'blocks-feature_v2-tpl_103',
                'blocks-feature_v2-tpl_104',
                'blocks-feature_v2-tpl_201',
                'blocks-feature_v2-tpl_202',
                'blocks-feature_v2-tpl_203',
                'blocks-feature_v2-tpl_204',
                'blocks-feature_v2-tpl_301',
                'blocks-feature_v2-tpl_302',
                'blocks-feature_v2-tpl_303',
                'blocks-feature_v2-tpl_304',
                'blocks-feature_v2-tpl_401',
                'blocks-feature_v2-tpl_402',
                'blocks-feature_v2-tpl_403',
                'blocks-feature_v2-tpl_404',
                'blocks-feature_v2-tpl_501',
                'blocks-feature_v2-tpl_502',
                'blocks-feature_v2-tpl_503',
                'blocks-feature_v2-tpl_504',
                'blocks-feature_v2-tpl_601',
                'blocks-feature_v2-tpl_602',
                'blocks-feature_v2-tpl_603',
                'blocks-feature_v2-tpl_604'
            ],
            'quote' => [
                'blocks-quote-tpl_02',
                'blocks-quote-tpl_01',
                'blocks-quote-tpl_03',
            ],
            'summary' => [
                'blocks-summary-tpl_01',
                'blocks-summary-tpl_03',
                'blocks-summary-tpl_02',
            ]
        ];

        foreach ($woodyTpls as $componentName => $woodyComponent) {
            $index = 1;
            foreach ($woodyComponent as $componentTpl) {
                $return[$componentName . '_' . $index] = $componentTpl;
                ++$index;
            }
        }

        return $return;
    }

    /**
     * WP_AJAX call to create popin with all templates
     */
    public function woodyGetAllTemplates()
    {
        $return = wp_cache_get('woody_tpls_components', 'woody');

        $user = wp_get_current_user();
        $is_administrator = in_array('administrator', $user->roles);

        if (empty($return)) {
            $tplComponents = [];
            $woodyComponents = getWoodyComponents();

            foreach ($woodyComponents as $key => $component) {
                $display_options = '';
                if (!empty($component['display'])) {
                    $display_options = json_encode($component['display'], JSON_THROW_ON_ERROR);
                }

                $lib_design = !empty($component['lib_design']) ? $component['lib_design'] : '';
                $is_new_tpl = (!empty($component['creation']) && isWoodyNewTpl($component['creation'])) ? "<span class='tpl-badge tpl-new'>Nouveau</span>" : '';
                $is_custom_tpl = !empty($component['custom_theme']) ? "<span class='tpl-badge tpl-custom-theme'>Personnalisé</span>" : '';
                $groups = empty($component['acf_groups']) ? '' : implode(" ", $component['acf_groups']);
                if (!empty($groups)) {
                    if (strpos($component['thumbnails']['small'], 'custom_woody_tpls') === false) {
                        $img_views_path = '/img/woody-library/views/';
                    } else {
                        $img_views_path = apply_filters('custom_woody_tpls_thumbnails_path', '/img/', $component['thumbnails']['small']);
                    }

                    if ($is_administrator || !$is_administrator && $lib_design != 'TODO' || empty($lib_design)) {
                        $tplComponents[$key] = "<div class='tpl-choice-wrapper " . $groups . "' data-value='". $key ."' data-display-options='". $display_options ."'>
                        <img class='img-responsive lazyload' src='data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' data-src='" . WP_HOME . "/app/dist/" . WP_SITE_KEY . $img_views_path . $component['thumbnails']['small'] . "?version=" . get_option("woody_theme_version") . "' alt='" . $key . "' width='150' height='150' />
                        <h5 class='tpl-title'>" . $component["name"] . "</h5><div class='tpl-badges'>" . $is_new_tpl . $is_custom_tpl . "</div>" .
                        "</div>";
                    }
                }
            }

            $woody_tpls_order = wp_cache_get('woody_tpls_order', 'woody');
            if (empty($woody_tpls_order)) {
                $woody_tpls_order = array_flip($this->sortWoodyTpls());
                wp_cache_set('woody_tpls_order', $woody_tpls_order, 'woody');
            }

            foreach ($woody_tpls_order as $order_key => $value) {
                if (!array_key_exists($order_key, $tplComponents)) {
                    unset($woody_tpls_order[$order_key]);
                }
            }

            $tplComponents = array_merge($woody_tpls_order, $tplComponents);

            foreach ($tplComponents as $value) {
                $return .= '<li>' . $value . '</li>' ;
            }

            wp_cache_set('woody_tpls_components', $return, 'woody');
        }

        wp_send_json($return);
        exit;
    }

    ////////////////////////////////////////////
    //  Generate acf clones only when needed  //
    ////////////////////////////////////////////

    /**
     * Set layouts transient on deploy
     */
    public function generateLayoutsTransients()
    {
        add_filter('user_can_richedit', [$this, 'addUserRichedit']);
        $user = wp_get_current_user();
        $user->add_cap('upload_files');

        $field = acf_get_field("field_5b043f0525968");
        $field['name'] = "#rowindex-name#";
        $field['display_layouts'] = true;

        foreach ($field['layouts'] as $layout) {
            $new_field = $field;
            $new_field['layouts'] = [$layout];

            ob_start();
            do_action('acf/render_field', $new_field);
            $html_str = ob_get_contents();
            ob_end_clean();

            $clone_pos = strpos($html_str, '<div class="clones">') + 20;
            $html_str = substr_replace($html_str, "", 0, $clone_pos);
            $valuespos = strrpos($html_str, '<div class="values">');
            $html_str = substr_replace($html_str, "", $valuespos);
            $html_str = substr($html_str, 0, -10);

            wp_cache_set('layout-' . $layout['name'], $html_str);
        }

        remove_filter('user_can_richedit', [$this, 'addUserRichedit']);
        $user->remove_cap('upload_files');
    }

    public function deleteTermCache($term_id, $tt_id, $taxonomy, $update) {
        switch($taxonomy) {
            case 'seasons':
            case 'attachment_categorie':
            case 'themes':
            case 'places':
                wp_cache_delete('layout-auto_focus');
                wp_cache_delete('layout-manual_focus');
                wp_cache_delete('layout-content_list');
                wp_cache_delete('layout-gallery');
                break;
            case 'attachment_hashtags':
                wp_cache_delete('layout-socialwall');
                break;
            default:
                break;
        }

        // $field = acf_get_field("field_5b043f0525968");

        // foreach ($field['layouts'] as $layout) {
        //     console_log($layout['name']);
        //     // wp_cache_delete('layout-' . $layout['name']);
        // }
    }

    public function addUserRichedit()
    {
        return true;
    }

    public function getRenderedLayout()
    {
        $return = '';
        $key = filter_input(INPUT_GET, 'key', FILTER_SANITIZE_STRING);
        $layout_name = filter_input(INPUT_GET, 'layout', FILTER_SANITIZE_STRING);

        $transient = wp_cache_get('layout-' . $layout_name);
        if (!empty($transient)) {
            $return = $transient;
        } else {
            add_filter('user_can_richedit', [$this, 'addUserRichedit']);
            $user = wp_get_current_user();
            $user->add_cap('upload_files');

            // field_5b043f0525968 == "section_content"
            $field = acf_get_field($key);
            $field['name'] = "#rowindex-name#";
            $field['display_layouts'] = true;

            foreach ($field['layouts'] as $key => $layout) {
                if ($layout['name'] != $layout_name) {
                    unset($field['layouts'][$key]);
                }
            }

            ob_start();
            do_action('acf/render_field', $field);
            $html_str = ob_get_contents();
            ob_end_clean();

            $clone_pos = strpos($html_str, '<div class="clones">') + 20;
            $html_str = substr_replace($html_str, "", 0, $clone_pos);
            $valuespos = strrpos($html_str, '<div class="values">');
            $html_str = substr_replace($html_str, "", $valuespos);
            // remove last tag
            $return = substr($html_str, 0, -10);
            wp_cache_set('layout-' . $layout_name, $return);

            remove_filter('user_can_richedit', [$this, 'addUserRichedit']);
            $user->remove_cap('upload_files');
        }

        wp_send_json($return);
    }

    public function addSocialChoices($field)
    {
        if (empty($field['choices'])) {
            $field['choices'] = array(
                "facebook" => "Facebook",
                "twitter" => "Twitter",
                "linkedin" => "LinkedIn",
                "whatsapp" => "WhatsApp",
                "pinterest" => "Pinterest",
                "mail" => "Mail"
            );
        };

        return $field;
    }

    public function addSeasonChoices($fields, $parent)
    {
        // Si on est dans la page Paramètres
        if ($parent['key'] == 'group_5c0fed3541b45') {
            $seasons = apply_filters('woody_pll_the_seasons', 'auto');

            // S'il y a des saisons
            if (!empty($seasons)) {
                // On récupère le group de paramètres de saison
                $season_settings_fields = acf_get_fields('group_season_settings');

                // Si les choices sont vides
                if (empty($season_settings_fields[1]['choices'])) {
                    // On traite chaque saison
                    foreach ($seasons as $season) {
                        // Saison courante
                        if ($season['current_lang'] == true) {
                            // Traitement pour toujours avoir la saison courant en premier
                            if (empty($season_settings_fields[1]['choices'])) {
                                // Si les choices sont vides, on l'ajoute directement
                                $season_settings_fields[1]['choices'][$season['slug']] = $season['name'];
                            } else {
                                // S'il y a déjà des choices, on l'ajoute avant ceux-ci
                                $current_lang_choice = [$season['slug'] => $season['name']];
                                $season_settings_fields[1]['choices'] = $current_lang_choice + $season_settings_fields[1]['choices'];
                            }

                            // Default value sur la saison courante
                            $season_settings_fields[1]['default_value'] = $season['slug'];
                            // On met à jour le champ pour être sûr que la saison courante est sélectionnée de base
                            update_field('current_season', $season['slug'], 'options');
                        } else {
                            // Saison non-courante
                            $season_settings_fields[1]['choices'][$season['slug']] = $season['name'];
                        }
                    }
                };

                // On ajoute les champs de paramètres de saison
                $fields = array_merge($fields, $season_settings_fields);
            }
        }

        return $fields;
    }

    public function updateCurrentSeason($value, $post_id, $field)
    {
        // Si on est dans la page paramètres
        // S'il s'agit du champ de saison courante
        if (!empty($post_id) && $post_id == 'options' && (!empty($field['name']) && $field['name'] == 'current_season')) {
            $current_season_field = $value;
            $polylang_option = get_option('polylang');

            // Si la valeur du champ est différente de l'option active
            if (!empty($current_season_field) && !empty($polylang_option) && $current_season_field !== $polylang_option['default_lang']) {
                // On change la default_lang dans le tableau d'options polylang
                $polylang_option['default_lang'] = $current_season_field;

                // On met à jour les options polylang avec le précédent tableau
                update_option('polylang', $polylang_option);

                // On met à jour la "saison prioritaire" pour le calcul des canoniques
                update_option('woody_season_priority', $current_season_field);

                // On flush le varnish
                do_action('woody_flush_varnish');

                // On lance un rsdu
                do_action('woody_async_add', 'woody_hawwwai_update_all_canonicals');

                // On redirige vers la page d'option dans la langue qui vient d'être enregistrée
                wp_redirect(admin_url() . 'admin.php?page=woody-settings&lang=' . $current_season_field);
                exit;
            }
        }

        return $value;
    }

    public function hideProfileChildTaxonomies($args, $field, $post_id)
    {
        if($field['_name'] == 'profiles_parent_category') {
            $args['parent'] = 0;
        }

        return $args;
    }
}