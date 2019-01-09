<?php
/**
 * Polylang
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */
use Woody\Utils\Output;

class WoodyTheme_Polylang
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('after_setup_theme', [$this, 'loadThemeTextdomain']);

        add_filter('pll_is_cache_active', [$this, 'isCacheActive']);
        add_filter('pll_copy_taxonomies', [$this, 'copyAttachmentTypes'], 10, 2);

        add_filter('woody_pll_days', [$this, 'woodyPllDays'], 10);
        add_filter('woody_pll_months', [$this, 'woodyPllMonths'], 10);
        add_filter('woody_pll_get_posts', [$this, 'woodyPllGetPosts'], 10, 1);
        add_filter('pll_check_canonical_url', [$this, 'pllCheckCanonicalUrl'], 10, 2);
    }

    public function isCacheActive()
    {
        return true;
    }

    public function pllCheckCanonicalUrl($redirect_url, $language)
    {
        $polylang_options = get_option('polylang');
        if (!$polylang_options['redirect_lang']) {
            Output::log('no redirect ' . $redirect_url);
            return false;
        } else {
            Output::log('continue redirect ' . $redirect_url);
            return $redirect_url;
        }
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

    public function loadThemeTextdomain()
    {
        load_theme_textdomain('woody-theme', get_template_directory() . '/languages');
    }

    public function woodyPllGetPosts($post_id)
    {
        $return = get_transient('woody_pll_post_translations_' . $post_id);
        if (empty($return)) {
            $default_language = pll_default_language();
            $languages = pll_languages_list();

            // Set default language
            $return[$default_language] = $post_id;

            // Set Link languages
            foreach ($languages as $lang) {
                if ($lang != $default_language) {
                    $return[$lang] = pll_get_post($post_id, $lang);
                }
            }
        }
        return $return;
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
