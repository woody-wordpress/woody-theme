<?php

/**
 * Template
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

use Woody\Modules\GroupQuotation\GroupQuotation;
use WoodyProcess\Tools\WoodyTheme_WoodyProcessTools;
use WoodyProcess\Process\WoodyTheme_WoodyProcess;
use WoodyProcess\Compilers\WoodyTheme_WoodyCompilers;

class WoodyTheme_Template_Page extends WoodyTheme_TemplateAbstract
{
    /**
     * @var \WoodyProcess\Compilers\WoodyTheme_WoodyCompilers|mixed
     */
    public $compilers;

    protected $twig_tpl = '';

    protected $tools;

    protected $process;

    public function __construct()
    {
        $this->tools = new WoodyTheme_WoodyProcessTools();
        $this->process = new WoodyTheme_WoodyProcess();
        $this->compilers = new WoodyTheme_WoodyCompilers();
        parent::__construct();
    }

    protected function registerHooks()
    {
        add_filter('woody_seo_edit_metas_array', [$this, 'woodySeoCanonical'], 10, 1);
    }

    protected function getHeaders()
    {
        return apply_filters('woody_page_headers', null, $this->context);
    }

    protected function setTwigTpl()
    {
        if (!empty(is_front_page())) {
            $this->twig_tpl = 'front.twig';
        } elseif (is_404()) {
            $this->twig_tpl = 'page404.twig';
        } else {
            $this->twig_tpl = 'page.twig';
        }
    }

    protected function extendContext()
    {
        if (is_404()) {
            $this->page404Context();
        } elseif (post_password_required($this->context['post'])) {
            echo get_the_password_form($this->context['post']);
        } else {
            $this->commonContext();
            if ($this->context['page_type'] == 'front_page') {
                $this->frontPageContext();
            } else {
                $this->pageContext();
            }
        }
    }

    protected function page404Context()
    {
        global $wp;

        $vars = [
            'title' =>  __("Oups !", 'woody-theme'),
            'subtitle' =>  '404 - ' . __("Page non trouvée", 'woody-theme'),
            'text' => __("La page que vous recherchez a peut-être été supprimée ou est temporairement indisponible.", 'woody-theme'),
            'custom_html' => apply_filters('woody_404_custom_html', null)
        ];

        $custom = apply_filters('woody_404_custom', $vars);
        $this->context['title'] = __("Erreur 404 : Page non trouvée", 'woody-theme') . ' | ' . get_bloginfo('name');
        $this->context['content'] = $custom;
    }

    protected function frontPageContext()
    {
        $this->context['is_frontpage'] = true;

        //  Compilation du Diaporama et du bloc de réservation pour les pages de type "accueil" (!= frontpage)
        $this->context['home_slider'] = $this->compilers->formatHomeSlider($this->context['post'], $this->context['woody_components']);
        $this->context['after_landswpr'] = empty($this->context['page_parts']['after_landswpr']) ? '' : $this->context['page_parts']['after_landswpr'];
        $this->context['bookblock'] = $this->compilers->formatBookBlock($this->context['post'], $this->context['woody_components'], $layout = []);
    }

    protected function pageContext()
    {
        $social_shares = [];
        $this->context['is_frontpage'] = false;
        $social_shares['active_shares'] = getActiveShares();
        $this->context['social_shares'] = \Timber::compile($this->context['woody_components']['blocks-shares-tpl_01'], $social_shares);

        /*********************************************
         * Compilation du bloc prix
         *********************************************/
        // TODO : Move this code
        $trip_types = [];
        $trip_term = get_term_by('slug', 'trip', 'page_type');
        if (!empty($trip_term)) {
            $trip_types[] = $trip_term->slug;
        }

        $trip_children = get_terms('page_type', ['child_of' => $trip_term->term_id, 'hide_empty' => false, 'hierarchical' => true]);

        if (!is_wp_error($trip_children) && !empty($trip_children)) {
            foreach ($trip_children as $child) {
                $trip_types[] = $child->slug;
            }
        } else {
            //TODO: passer par le filtre Woody_trip_types dans le plugin groupes pour rajouter ces types de séjour
            $trip_types = [
                'trip',
                'activity_component',
                'visit_component',
                'accommodation_component',
                'restoration_component',
            ];
        }

        $trip_types = apply_filters('woody_trip_types', $trip_types);

        if (in_array($this->context['page_type'], $trip_types)) {
            $trip_infos = getAcfGroupFields('group_5b6c5e6ff381d', $this->context['post']);

            // Si le module groupe est activé
            if (in_array('groups', $this->context['enabled_woody_options'])) {
                // Instancier GroupQuotation peut importe les conditions, à partir du moment ou le module groups est activé
                $groupQuotation = new GroupQuotation();

                if ($trip_infos['the_price']['price_type'] == 'component_based') {
                    $trip_infos['the_price'] = $groupQuotation->calculTripPrice($trip_infos['the_price']);
                } elseif ($trip_infos['the_price']['price_type'] == 'no_tariff') {
                    $trip_infos['the_price']['price'] = "Sans tarif";
                    $trip_infos['the_price']['prefix_price'] = "";
                    $trip_infos['the_price']['suffix_price'] = "";
                    $trip_infos['the_price']['currency'] = "none";
                }

                // On vérifie si le prix est calculé sur un ensemble de composant et on le définit le cas échéant
                if (!empty($trip_infos['the_price']['activate_quotation'])) {
                    $quotation_id = get_option("options_quotation_page_url");
                    $quotation_id = pll_get_post($quotation_id) !== false ? pll_get_post($quotation_id) : $quotation_id;
                    $trip_infos['quotation_link']['link_label'] = woody_get_permalink($quotation_id) . "?sejour=" . $this->context['post_id'];
                }

                if (!empty($trip_infos['the_duration']['duration_unit']) && $trip_infos['the_duration']['duration_unit'] == 'component_based') {
                    $trip_infos['the_duration'] = $groupQuotation->calculTripDuration($trip_infos['the_duration']);
                }
            }

            // If price equals 0, replace elements to display Free
            if (isset($trip_infos['the_price']['price']) && $trip_infos['the_price']['price'] === 0) {
                $trip_infos['the_price']['price'] = __("Gratuit", "woody-theme");
                $trip_infos['the_price']['prefix_price'] = "";
                $trip_infos['the_price']['suffix_price'] = "";
                $trip_infos['the_price']['currency'] = "none";
            }

            // If empty people min and people max, unset people
            if (empty($trip_infos['the_peoples']['peoples_min']) && empty($trip_infos['the_peoples']['peoples_max'])) {
                unset($trip_infos['the_peoples']);
            }

            // Convert minutes to hours if > 60
            if ($trip_infos['the_duration']['duration_unit'] === 'minutes') {
                $minutes_num = (int) $trip_infos['the_duration']['count_minutes'];
                if ($minutes_num >= 60) {
                    $trip_infos['the_duration']['duration_unit'] = 'hours';
                    $convertedTime = minuteConvert($minutes_num);
                    $trip_infos['the_duration']['count_hours'] = (empty($convertedTime['hours'])) ? '' : strval($convertedTime['hours']);
                    $trip_infos['the_duration']['count_minutes'] = (empty($convertedTime['minutes'])) ? '' : strval($convertedTime['minutes']);
                }
            } elseif ($trip_infos['the_duration']['duration_unit'] === 'hours') {
                $trip_infos['the_duration']['count_minutes'] = '';
            }

            if (!empty($trip_infos['the_duration']['count_days']) || !empty($trip_infos['the_length']['length']) || !empty($trip_infos['the_price']['price'])) {
                //TODO: Gérer le fichier gps pour affichage s/ carte
                $trip_infos['the_duration']['count_days'] = ($trip_infos['the_duration']['count_days']) ? humanDays($trip_infos['the_duration']['count_days']) : '';
                $trip_infos['the_price']['price'] = (empty($trip_infos['the_price']['price'])) ? '' : str_replace('.', ',', $trip_infos['the_price']['price']);
                $this->context['trip_infos'] = \Timber::compile($this->context['woody_components'][$trip_infos['tripinfos_woody_tpl']], $trip_infos);
            } else {
                $trip_infos = [];
            }
        }

        // Compilation de l'en tête de page et du visuel et accroche
        $this->context['page_teaser'] = $this->compilers->formatPageTeaser($this->context);
        $page_hero = $this->compilers->formatPageHero($this->context);
        if (!empty($page_hero)) {
            $this->context['page_hero'] = $page_hero['view'];
            $this->context['body_class'] .= ' has-hero'; // Add Class has-hero
            $this->context['body_class'] = $this->context['body_class'] . ' has-' . $page_hero['data']['heading_woody_tpl']; // Add Class has-hero-block-tpl
        }

        $this->context = apply_filters('woody_page_context', $this->context);
    }

    protected function commonContext()
    {
        $this->context['page_terms'] = implode(' ', getPageTerms($this->context['post_id']));
        // TODO : Trouver toutes les utilisation du default marker et modifier/déplacer cette ligne
        $this->context['default_marker'] = file_get_contents($this->context['dist_dir'] . '/img/default-marker.svg');

        $this->context['hide_page_zones'] = get_field('hide_page_zones');
        if (is_array($this->context['hide_page_zones'])) {
            if (in_array('header', $this->context['hide_page_zones'])) {
                $this->context['body_class'] .= ' no-page-header';
            }

            if (in_array('footer', $this->context['hide_page_zones'])) {
                $this->context['body_class'] .= ' no-page-footer';
            }
        }

        $this->getParamsToNoIndex();

        /*********************************************
         * Compilation des sections
         *********************************************/
        $this->context['sections'] = [];
        if (!empty($this->context['post'])) {
            $sections = get_field('section', $this->context['post']->ID);
            $sections = apply_filters('woody_custom_sections', $sections);
            $this->context['the_sections'] = $this->process->processWoodySections($sections, $this->context);
        }
    }

    protected function getParamsToNoIndex()
    {
        $get = $_GET;
        $noindex = false;
        if (!empty($get)) {
            foreach (array_keys($get) as $key) {
                if (strpos($key, 'section_') !== false || $key == 'autoselect_id' || $key == 'fav') {
                    $noindex = true;
                }
            }
        }

        if ($noindex == true) {
            $robots_content = $this->context['metas']['robots']['#attributes']['content'];
            if (strpos($robots_content, 'noindex') == false) {
                $this->context['metas']['robots']['#attributes']['content'] = $robots_content . ', noindex';
            }
        }
    }

    /***************************
     * Overide Canonical
     *****************************/
    public function woodySeoCanonical($metas)
    {
        $listpage = filter_input(INPUT_GET, 'listpage', FILTER_VALIDATE_INT);
        $post_type = get_the_terms(get_the_ID(), 'page_type');
        if (!empty($post_type) && $post_type[0]->slug === 'playlist_tourism' && !empty($listpage) && is_numeric($listpage) && $listpage != '1' && !empty($metas['canonical']) && !empty($metas['canonical']['#attributes']) && !empty($metas['canonical']['#attributes']['href'])) {
            $metas['canonical']['#attributes']['href'] = $metas['canonical']['#attributes']['href'] . '?listpage=' . $listpage;
        }

        return $metas;
    }
}
