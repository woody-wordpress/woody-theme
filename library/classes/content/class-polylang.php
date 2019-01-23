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
        add_action('after_setup_theme', [$this, 'loadThemeTextdomain']);
        add_action('wp_loaded', [$this, 'wpLoaded'], 30);

        add_filter('pll_is_cache_active', [$this, 'isCacheActive']);
        add_filter('pll_copy_taxonomies', [$this, 'copyAttachmentTypes'], 10, 2);
        add_filter('wpssoc_user_redirect_url', [$this, 'wpssocUserRedirectUrl'], 10, 1);

        add_filter('woody_pll_days', [$this, 'woodyPllDays'], 10);
        add_filter('woody_pll_months', [$this, 'woodyPllMonths'], 10);
        add_filter('woody_pll_create_media_translation', [$this, 'woodyPllCreateMediaTranslation'], 10, 2);
        add_filter('woody_theme_siteconfig', [$this, 'siteConfigAddLangs'], 12, 1);
    }

    public function isCacheActive()
    {
        return true;
    }

    /**
     * Fonction qui corrige un conflit de Polylang avec Enhanced Media Library lorsque l'on a plus de 2 langues
     * Sans ce fix, les traductions des images ne sont plus liées entre elles
     */
    public function wpLoaded()
    {
        global $wp_taxonomies;
        $wp_taxonomies['language']->update_count_callback = '';
        $wp_taxonomies['post_translations']->update_count_callback = '_update_generic_term_count';
    }

    public function wpssocUserRedirectUrl($user_redirect_set)
    {
        if (function_exists('pll_current_language')) {
            if (strpos($user_redirect_set, 'wp-admin') !== false) {
                if (strpos($user_redirect_set, '?') !== false) {
                    $user_redirect_set .= '&lang=' . pll_current_language();
                } else {
                    $user_redirect_set .= '?lang=' . pll_current_language();
                }
            }
        }

        return $user_redirect_set;
    }

    public function siteConfigAddLangs($siteConfig)
    {
        if (function_exists('pll_languages_list')) {
            $siteConfig['languages'] = pll_languages_list();
        }
        return $siteConfig;
    }

    // define the pll_copy_taxonomies callback
    public function copyAttachmentTypes($taxonomies, $sync)
    {
        $custom_taxs = [
            'attachment_types' => 'attachment_types',
            'attachment_hashtags' => 'attachment_hashtags',
            'attachment_categories' => 'attachment_categories',
        ];

        $taxonomies = array_merge($custom_taxs, $taxonomies);
        return $taxonomies;
    }

    // --------------------------------
    // Copy of native Polylang function
    // PLL()->posts->create_media_translation($attachment_id, $lang);
    // --------------------------------
    public function woodyPllCreateMediaTranslation($post_id, $lang)
    {
        if (empty($post_id)) {
            return $post_id;
        }

        $post = get_post($post_id);

        if (empty($post)) {
            return $post;
        }

        // Create a new attachment ( translate attachment parent if exists )
        add_filter('pll_enable_duplicate_media', '__return_false', 99); // Avoid a conflict with automatic duplicate at upload
        $post->ID = null; // Will force the creation
        $post->post_parent = ($post->post_parent && $tr_parent = pll_get_post($post->post_parent, $lang)) ? $tr_parent : 0;
        $post->tax_input = array( 'language' => array( $lang ) ); // Assigns the language
        $tr_id = wp_insert_attachment($post);
        remove_filter('pll_enable_duplicate_media', '__return_false', 99); // Restore automatic duplicate at upload

        // Copy metadata, attached file and alternative text
        foreach (array( '_wp_attachment_metadata', '_wp_attached_file', '_wp_attachment_image_alt' ) as $key) {
            if ($meta = get_post_meta($post_id, $key, true)) {
                add_post_meta($tr_id, $key, $meta);
            }
        }

        pll_set_post_language($tr_id, $lang);

        $translations = pll_get_post_translations($post_id);
        if (! $translations && $src_lang = pll_get_post($post_id)) {
            $translations[ $src_lang->slug ] = $post_id;
        }

        $translations[ $lang ] = $tr_id;
        pll_save_post_translations($translations);

        /**
         * Fires after a media translation is created
         *
         * @since 1.6.4
         *
         * @param int    $post_id post id of the source media
         * @param int    $tr_id   post id of the new media translation
         * @param string $slug    language code of the new translation
         */
        do_action('pll_translate_media', $post_id, $tr_id, $lang);
        return $tr_id;
    }

    public function loadThemeTextdomain()
    {
        load_theme_textdomain('woody-theme', get_template_directory() . '/languages');
    }

    public function woodyPllDays()
    {
        return [
            1 => __('Lundi', 'woody-theme'),
            2 => __('Mardi', 'woody-theme'),
            3 => __('Mercredi', 'woody-theme'),
            4 => __('Jeudi', 'woody-theme'),
            5 => __('Vendredi', 'woody-theme'),
            6 => __('Samedi', 'woody-theme'),
            7 => __('Dimanche', 'woody-theme'),
        ];
    }

    public function woodyPllMonths()
    {
        return [
            1 => __("Janvier", 'woody-theme'),
            2 => __("Février", 'woody-theme'),
            3 => __("Mars", 'woody-theme'),
            4 => __("Avril", 'woody-theme'),
            5 => __("Mai", 'woody-theme'),
            6 => __("Juin", 'woody-theme'),
            7 => __("Juillet", 'woody-theme'),
            8 => __("Août", 'woody-theme'),
            9 => __("Septembre", 'woody-theme'),
            10 => __("Octobre", 'woody-theme'),
            11 => __("Novembre", 'woody-theme'),
            12 => __("Décembre", 'woody-theme'),
        ];
    }

    /**
     * Commande pour créer automatiquement woody-theme.pot
     * A ouvrir ensuite avec PoEdit.app sous Mac
     * cd ~/www/wordpress/current/web/app/themes/woody-theme
     * wp i18n make-pot . languages/woody-theme.pot
     */
    private function twigExtractPot()
    {
        // Yoast
        __("Page non trouvée %%sep%% %%sitename%%", 'woody-theme');
        __("Erreur 404 : Page non trouvée", 'woody-theme');

        // Date
        __("Lundi", 'woody-theme');
        __("Mardi", 'woody-theme');
        __("Mercredi", 'woody-theme');
        __("Jeudi", 'woody-theme');
        __("Vendredi", 'woody-theme');
        __("Samedi", 'woody-theme');
        __("Dimanche", 'woody-theme');
        __("Janvier", 'woody-theme');
        __("Février", 'woody-theme');
        __("Mars", 'woody-theme');
        __("Avril", 'woody-theme');
        __("Mai", 'woody-theme');
        __("Juin", 'woody-theme');
        __("Juillet", 'woody-theme');
        __("Août", 'woody-theme');
        __("Septembre", 'woody-theme');
        __("Octobre", 'woody-theme');
        __("Novembre", 'woody-theme');
        __("Décembre", 'woody-theme');

        // Woody blocs
        __("M'y rendre", 'woody-theme');
        __("Ajouter à mes favoris", 'woody-theme');
        __("Voir l'itinéraire", 'woody-theme');
        __("Voir la vidéo", 'woody-theme');
        __("Affiner ma recherche", 'woody-theme');
        __("Voir les résultats sur la carte", 'woody-theme');
        __("résultats", 'woody-theme');
        __("Voir la carte", 'woody-theme');
        __("Partager sur Facebook", 'woody-theme');
        __("Partager sur Twitter", 'woody-theme');
        __("Partager sur Google+", 'woody-theme');
        __("Partager sur Instagram", 'woody-theme');
        __("Partager sur Pinterest", 'woody-theme');
        __("Partager par email", 'woody-theme');
        __("Accès au menu principal", 'woody-theme');
        __("Que recherchez-vous ?", "woody-theme");
        __("Rechercher", 'woody-theme');
        __("Réinitialiser", 'woody-theme');
        __("Choisissez vos dates", 'woody-theme');
        __("adulte(s)", 'woody-theme');
        __("enfant(s)", 'woody-theme');
        __("jours", 'woody-theme');
        __("Pages", 'woody-theme');
        __("Offre touristique", 'woody-theme');
        __("Désolé, aucun contenu touristique ne correspond à votre recherche", 'woody-theme');
        __("Désolé, aucune page ne correspond à votre recherche", 'woody-theme');
        __('Fermer', 'woody-theme');
        __('résultat', 'woody-theme');
        __('Aucun résultat', 'woody-theme');
        __('Menu', 'woody-theme');
        __('Merci de choisir une date', 'woody-theme');
        __('France', 'woody-theme');
        __('Nouvelle Aquitaine', 'woody-theme');
        __('Dordogne', 'woody-theme');
        __('Comment venir ?', 'woody-theme');
        __('La page que vous recherchez est peut-être ici ?', 'woody-theme');
        __('Lire la suite', 'woody-theme');
    }
}
