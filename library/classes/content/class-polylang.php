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
        add_filter('pll_is_cache_active', [$this, 'isCacheActive']);
        add_action('after_setup_theme', [$this, 'loadThemeTextdomain']);
    }

    public function isCacheActive()
    {
        return true;
    }

    public function loadThemeTextdomain()
    {
        load_theme_textdomain('woody-theme', get_template_directory() . '/languages');
    }

    private function twigExtractPot()
    {
        // Commande pour créer automatiquement woody-theme.pot
        // A ouvrir ensuite avec PoEdit.app sous Mac
        // cd ~/www/wordpress/current/web/app/themes/woody-theme
        // wp i18n make-pot . languages/woody-theme.pot

        // Yoast
        __("Page non trouvée %%sep%% %%sitename%%", 'woody-theme');
        __("Erreur 404 : Page non trouvée", 'woody-theme');

        // Woody blocs
        __("M'y rendre", 'woody-theme');
        __("Ajouter à mes favoris", 'woody-theme');
        __("Voir l'itinéraire", 'woody-theme');
        __("Voir la vidéo", 'woody-theme');
        __("Affiner ma recherche", 'woody-theme');
        __("Voir les résultats sur la carte", 'woody-theme');
        __("Voir la carte", 'woody-theme');
        __("Partager sur Facebook", 'woody-theme');
        __("Partager sur Twitter", 'woody-theme');
        __("Partager sur Google+", 'woody-theme');
        __("Partager sur Instagram", 'woody-theme');
        __("Partager sur Pinterest", 'woody-theme');
        __("Partager par email", 'woody-theme');
        __("Accès au menu principal", 'woody-theme');
    }
}
