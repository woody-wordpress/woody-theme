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

        add_filter('duplicate_post_taxonomies_excludelist_filter', [$this, 'excludePostTranslations']);
        add_filter('republish_post_taxonomies_use_filters', '__return_true');
        add_filter('duplicate_post_taxonomies_use_filters', '__return_true');
    }

    public function excludePostTranslations($tax)
    {
        if (!in_array('post_translations', $tax)) {
            $tax[] = 'post_translations';
        }

        return $tax;
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
     * * @noRector
     * Commande pour créer automatiquement woody-theme.pot
     * A ouvrir ensuite avec PoEdit.app sous Mac
     * cd ~/www/wordpress/current/web/app/themes/woody-theme
     * wp i18n make-pot . languages/woody-theme.pot
     */
    private function twigExtractPot()
    {
        // Woody SEO
        __("Page non trouvée", 'woody-theme');
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

        // Trip infos
        __('À partir de', 'woody-theme');
        __('à partir de', 'woody-theme');
        __('heure', 'woody-theme');
        __('heures', 'woody-theme');
        __('minute', 'woody-theme');
        __('minutes', 'woody-theme');
        __('min', 'woody-theme');
        __('max', 'woody-theme');
        __('pers.', 'woody-theme');
        __('Demander un devis', 'woody-theme');
        __('personne', 'woody-theme');
        __('personnes', 'woody-theme');
        __('Départ', 'woody-theme');
        __('Arrivée', 'woody-theme');
        __('Etape', 'woody-theme');
        __('Réserver', 'woody-theme');

        // Circuits
        __('J', 'woody-theme');

        // Availabilities calendar
        __('Attention, vous n\'avez pas saisi d\'identifiant de playlist en paramètre du shortcode.
            Pour rappel, le shortcode s\'utilise par exemple de cette manière', 'woody-theme');
        __('Année', 'woody-theme');
        __('Établissement', 'woody-theme');
        __('Établissement disponible', 'woody-theme');
        __('Établissement indisponible', 'woody-theme');
        __('Aucune information concernant cet établissement pour l\'année en cours', 'woody-theme');

        // Woody blocs
        __("M'y rendre", 'woody-theme');
        __("Voir l'itinéraire", 'woody-theme');
        __("Voir la vidéo", 'woody-theme');
        __("Affiner ma recherche", 'woody-theme');
        __("Voir les résultats sur la carte", 'woody-theme');
        __('résultat', 'woody-theme');
        __("résultats", 'woody-theme');
        __('Aucun résultat', 'woody-theme');
        __("Voir la carte", 'woody-theme');
        __("Afficher la carte", 'woody-theme');
        __("Masquer la carte", 'woody-theme');
        __("Partager sur Facebook", 'woody-theme');
        __("Partager sur Twitter", 'woody-theme');
        __("Partager sur LinkedIn", 'woody-theme');
        __("Partager sur WhatsApp", 'woody-theme');
        __("Partager sur Google+", 'woody-theme');
        __("Partager sur Instagram", 'woody-theme');
        __("Partager sur Pinterest", 'woody-theme');
        __("Partager par email", 'woody-theme');
        __("Partager", 'woody-theme');
        __("Voir sur Instagram", 'woody-theme');
        __("Accès au menu principal", 'woody-theme');
        __("Titre de la vidéo manquant", 'woody-theme');
        __("Légende de la vidéo manquante", 'woody-theme');
        __("Que recherchez-vous ?", "woody-theme");
        __('Vous cherchez quelque chose ?', 'woody-theme');
        __("Rechercher", 'woody-theme');
        __("Réinitialiser", 'woody-theme');
        __("Choisissez vos dates", 'woody-theme');
        __('Date d\'arrivée', 'woody-theme');
        __('Durée du séjour', 'woody-theme');
        __("adulte(s)", 'woody-theme');
        __("enfant(s)", 'woody-theme');
        __("jour", 'woody-theme');
        __("jours", 'woody-theme');
        __("semaine", 'woody-theme');
        __("semaines", 'woody-theme');
        __("mois", 'woody-theme');
        __("Sélection", 'woody-theme');
        __("Page", 'woody-theme');
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
        __('Changer de saison', 'woody-theme');
        __('Réservable', 'woody-theme');
        __('Télécharger', 'woody-theme');
        __("Aller à la page d'accueil", "woody-theme");
        __("Retour à la page d'accueil", 'woody-theme');
        __("Tout", 'woody-theme');
        __('Afficher tous les tarifs', 'woody-theme');
        __('Afficher plus', 'woody-theme');
        __('Afficher moins', 'woody-theme');
        __('Date de naissance', 'woody-theme');
        __('Nationalité', 'woody-theme');
        __('Sommaire', 'woody-theme');

        //Actions
        __('Ajouter', 'woody-theme');
        __('Supprimer', 'woody-theme');
        __('Cette liste est vide actuellement. Naviguez et ajoutez des pages !', 'woody-theme');

        // Favorites
        __('Ajouter à mes favoris', 'woody-theme');
        __('Imprimer/Exporter mes favoris', 'woody-theme');

        // Group quotation
        __('Pour faire une demande de devis, veuillez choisir un séjour.', 'woody-theme');
        __("Il semblerait qu'il n'y ait pas ou plus de séjours proposant une demande de devis disponibles sur le site", "woody-theme");
        __("Séjours et activités", "woody-theme");
        __('Retour à l\'offre', 'woody-theme');
        __('Récapitulatif', 'woody-theme');
        __('30 secondes', 'woody-theme');
        __('Informations', 'woody-theme');
        __('Coordonnées', 'woody-theme');
        __("Mon devis", "woody-theme");
        __("Votre séjour/journée", "woody-theme");
        __('Comprenant', 'woody-theme');
        __('Gratuit', 'woody-theme');
        __('Nombre de personnes', 'woody-theme');
        __("Language de la/des visite(s)");
        __("* Champs obligatoires", "woody-theme");
        __("Nom/Prénom", "woody-theme");
        __("Raison sociale", "woody-theme");
        __("Email", "woody-theme");
        __("Téléphone", "woody-theme");
        __("Votre commentaire", "woody-theme");
        __("Valider ma demande de devis", "woody-theme");
        __("Mon panier", "woody-theme");
        __("TOTAL ", "woody-theme");
        __("Base de", "woody-theme");
        __("personne", "woody-theme");

        // Divers
        __('Publié le', 'woody-theme');
        __('Mis à jour le', 'woody-theme');
        __('Désolé, aucun contenu ne correspond à votre recherche', 'woody-theme');
        __('Lire aussi', 'woody-theme');

        // Infolive
        //TODO: Remove after infolive plugin deletion
        __('Remontées mécaniques', 'woody-theme');
        __('Pistes', 'woody-theme');
        __('Domaine Alpin', 'woody-theme');
        __('Domaine Nordique', 'woody-theme');
        __('Détails', 'woody-theme');
        __('Météo', 'woody-theme');
        __('Voir le bulletin météo', 'woody-theme');
        __('Bulletin complet', 'woody-theme');
        __('Webcams', 'woody-theme');
        __('Voir les webcams', 'woody-theme');
        __('Flux erroné !', 'woody-theme');
        __('Module Infoneige non configuré !', 'woody-theme');
        __('Enneigement', 'woody-theme');
        __('Hauteur', 'woody-theme');
        __('Pistes ouvertes', 'woody-theme');
        __('Risque d\'avalanche', 'woody-theme');
        __('Limité', 'woody-theme');
        __('Moyen', 'woody-theme');
        __('Élevé', 'woody-theme');
        __('Non spécifié', 'woody-theme');
        __('Qualité de la neige', 'woody-theme');
        __('En live', 'woody-theme');
        __('Toute l\'info live', 'woody-theme');
        __('Altitude', 'woody-theme');
        __('Fraîche', 'woody-theme');
        __('Douce', 'woody-theme');
        __('Dure', 'woody-theme');
        __('Humide', 'woody-theme');
        __('De printemps', 'woody-theme');
        __('Matin', 'woody-theme');
        __('Après-midi', 'woody-theme');
        __('Ouverte', 'woody-theme');
        __('Fermée', 'woody-theme');
        __('Prévision d\'ouverture', 'woody-theme');
        __('Liaison', 'woody-theme');
    }
}
