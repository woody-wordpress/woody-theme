<?php
/**
 * ACF sync field
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package HawwwaiTheme
 * @since HawwwaiTheme 1.0.0
 */

class HawwwaiTheme_ACF
{
    const ACF = "acf-pro/acf.php";

    public function __construct()
    {
        $this->register_hooks();
    }

    protected function register_hooks()
    {
        acf_update_setting('save_json', get_template_directory() . '/acf-json');
        acf_append_setting('load_json', get_template_directory() . '/acf-json');

        add_filter('plugin_action_links', array($this, 'disallow_acf_deactivation'), 10, 4);
        add_filter('acf/load_field/name=woody_tpl', array($this, 'woody_tpl_acf_load_field'));
        add_filter('acf/load_field/name=focused_taxonomy_terms', array($this, 'focused_taxonomy_terms_load_field'));
        add_filter('acf/load_field/name=playlist_name', array($this, 'playlist_name_load_field'));
    }

    /**
     * Benoit Bouchaud
     * On bloque l'accès à la désactivation du plugin ACF
     */
    public function disallow_acf_deactivation($actions, $plugin_file, $plugin_data, $context)
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
    public function woody_tpl_acf_load_field($field)
    {
        switch ($field['key']) {
            case 'field_5afd2c9616ecd':
                $components = Woody::getTemplatesByAcfGroup($field['key']);
            break;
            default:
                $components = Woody::getTemplatesByAcfGroup($field['parent']);
        }

        $field['choices'] = [];
        if (!empty($components)) {
            foreach ($components as $key => $component) {
                $field['choices'][$key] = '<img class="img-responsive" src="' . get_stylesheet_directory_uri() . '/dist/img/woody/' . $component['thumbnails']['small'] . '" alt="' . $key . '" width="150" height="150" />';
            }
        }

        return $field;
    }

    /**
     * Benoit Bouchaud
     * On ajoute tous les termes de taxonomie du site dans le sélecteur de termes de la mise en avant automatique
     */
    public function focused_taxonomy_terms_load_field($field)
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

    public function playlist_name_load_field($field)
    {
        global $post;
        $confId = get_field('playlist_conf_id', $post->ID);

        $post_title = $post->post_title;
        $type_term = get_the_terms($post->ID, 'page_type');
        if (!empty($type_term)) {
            $type = $type_term[0]->slug;
            if ($type == 'playlist_tourism') {
                $field['value'] = 'WP - Playlist ' . $post->post_title;
            } else {
                $sections = get_field('field_5afd2c6916ecb', $post->ID);
                foreach ($sections[0]['section_content'] as $key => $layouts) {
                    // rcd($layouts);
                    // if ($layout['acf_fc_layout'] == 'playlist_bloc') {
                    //     rcd($layout);
                    // }
                }
            }
        }

        // Rename confname in api
//        if (!empty($field['value'] && is_plugin_active('hawwwai'))) {
//            $name = $field['value'];
//            $hawwwaiPlaylistModule = $plugin_hawwwai_kernel->getModule('playlist');
//            if (!empty($hawwwaiPlaylistModule)) {
//                $response = $hawwwaiPlaylistModule->getConfEditorManager()->renameConf($confId, $name);
//            }
//        }
        $response = apply_filters('wp_hawwwai_sit_conf_editor_rename', $confId, $field['value']);

        rcd($response);

        return $field;
    }

    public function playlist_name_load_value()
    {
    }
}

// Execute Class
new HawwwaiTheme_ACF();
