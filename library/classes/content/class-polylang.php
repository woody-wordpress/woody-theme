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

        add_filter('woody_pll_days', [$this, 'woodyPllDays'], 10);
        add_filter('woody_pll_months', [$this, 'woodyPllMonths'], 10);
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
        __('résultat', 'woody-theme');
        __("résultats", 'woody-theme');
        __('Aucun résultat', 'woody-theme');
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
        __('Menu', 'woody-theme');
        __('Merci de choisir une date', 'woody-theme');
        __('Comment venir ?', 'woody-theme');
        __('La page que vous recherchez est peut-être ici ?', 'woody-theme');
        __('Lire la suite', 'woody-theme');
        __('Que voulez-vous réserver ?', 'woody-theme');
        __('Choisissez vos dates de réservation', 'woody-theme');

        // Weather
        __('Bas', 'woody-theme');
        __('Haut', 'woody-theme');
        __('Vent', 'woody-theme');
        __('Température de la mer', 'woody-theme');
        __('Humidité', 'woody-theme');
        __('ISO 0°', 'woody-theme');
        __('Limite Pluie/Neige', 'woody-theme');
        __('Ensoleillé', 'woody-theme');
        __('Pluie', 'woody-theme');
        __('Neige', 'woody-theme');
        __('Grésil', 'woody-theme');
        __('Venteux', 'woody-theme');
        __('Brouillard', 'woody-theme');
        __('Nuageux', 'woody-theme');
        __('Éclaircies', 'woody-theme');
        __('Grêle', 'woody-theme');
        __('Tempête', 'woody-theme');
        __('Verglas', 'woody-theme');

        //Azimut (weather)
        __('Nord Nord-Est', 'woody-theme');
        __('Nord-Est','woody-theme');
        __('Est Nord-Est','woody-theme');
        __('Est','woody-theme');
        __('Est Sud-Est','woody-theme');
        __('Sud-Est','woody-theme');
        __('Sud Sud-Est','woody-theme');
        __('Sud','woody-theme');
        __('Sud Sud-Ouest','woody-theme');
        __('Sud-Ouest','woody-theme');
        __('Ouest Sud-Ouest','woody-theme');
        __('Ouest','woody-theme');
        __('Ouest Nord-Ouest','woody-theme');
        __('Nord-Ouest','woody-theme');
        __('Nord Nord-Ouest','woody-theme');
        __('Nord','woody-theme');

        //Part of the day (weather)
        __('Matin', 'woody-theme');
        __('Après-midi', 'woody-theme');
        __('Soirée', 'woody-theme');
        __('Nuit', 'woody-theme');
    }
}
