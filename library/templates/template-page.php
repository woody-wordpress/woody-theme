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

class WoodyTheme_Template_Page extends WoodyTheme_TemplateAbstract
{
    protected $twig_tpl = '';
    protected $tools;
    protected $process;

    public function __construct()
    {
        $this->tools = new WoodyTheme_WoodyProcessTools;
        $this->process = new WoodyTheme_WoodyProcess;
        parent::__construct();
    }

    protected function registerHooks()
    {
        add_filter('woody_seo_edit_metas_array', [$this, 'woodySeoCanonical'], 10, 1);
    }

    protected function getHeaders()
    {
        if ($this->context['page_type'] === 'playlist_tourism') {
            return $this->playlistHeaders();
        }
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
        } else {
            if (post_password_required($this->context['post'])) {
                echo get_the_password_form($this->context['post']);
            } else {
                $this->commonContext();
                $this->pageContext();
            }
        }
    }

    protected function page404Context()
    {
        global $wp;
        $segments = explode('/', $wp->request);
        $last_segment = end($segments);
        $query = str_replace('-', ' ', $last_segment);

        $suggestions = get_transient('woody_404_suggestions_' . md5($query));

        if (empty($suggestions)) {
            $suggestions = [];
            $response = apply_filters('woody_pages_search', ['query' => $query, 'size' => 4]);
            if (!empty($response['posts'])) {
                foreach ($response['posts'] as $post_id) {
                    $post_id = explode('_', $post_id);
                    $post_id = end($post_id);
                    $post = get_post($post_id);
                    if ($post->post_type == 'touristic_sheet') {
                        $suggestions[] = getTouristicSheetPreview(['display_elements' => ['sheet_town', 'sheet_type', 'description', 'bookable'], 'display_img' => true], $post);
                    } else {
                        $suggestions[] = getPagePreview(['display_elements' => ['description'], 'display_button' => true, 'display_img' => true], $post);
                    }
                }
            }

            if (!empty($suggestions)) {
                set_transient('woody_404_suggestions_' . md5($query), $suggestions, 1209600); // Keep 2 weeks
            }
        }


        $vars = [
            'title' =>  __("Oups !", 'woody-theme'),
            'subtitle' =>  '404 - ' . __("Page non trouvée", 'woody-theme'),
            'text' => __("La page que vous recherchez a peut-être été supprimée ou est temporairement indisponible.", 'woody-theme'),
            'suggestions' => $suggestions,
            'search' => get_permalink(get_field('es_search_page_url', 'options'))
        ];

        $custom = apply_filters('woody_404_custom', $vars);
        $this->context['title'] = __("Erreur 404 : Page non trouvée", 'woody-theme') . ' | ' . get_bloginfo('name');
        $this->context['content'] = $custom;
    }

    protected function pageContext()
    {
        $this->context['is_frontpage'] = false;

        $social_shares = getActiveShares();
        $this->context['social_shares'] = \Timber::compile($this->context['woody_components']['blocks-shares-tpl_01'], $social_shares);

        /******************************************************************************
         * Compilation du Diaporama pour les pages de type "accueil" (!= frontpage)
         ******************************************************************************/
        $page_type = wp_get_post_terms($this->context['post_id'], 'page_type');
        if (!empty($page_type[0]) && $page_type[0]->slug == 'front_page') {
            $this->context['is_frontpage'] = true;
            $home_slider = getAcfGroupFields('group_5bb325e8b6b43', $this->context['post']);
            if (!empty($home_slider['landswpr_slides'])) {
                $this->context['home_slider'] = \Timber::compile($this->context['woody_components'][$home_slider['landswpr_woody_tpl']], $home_slider);
            }

            $this->context['after_landswpr'] = !empty($this->context['page_parts']['after_landswpr']) ? $this->context['page_parts']['after_landswpr'] : '';
        }

        /*********************************************
         * Compilation du bloc prix
         *********************************************/

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
                $groupQuotation = new GroupQuotation;
                // On vérifie si le prix est calculé sur un ensemble de composant et on le définit le cas échéant
                if (!empty($trip_infos['the_price']['activate_quotation'])) {
                    $trip_infos['the_price'] = $groupQuotation->calculTripPrice($trip_infos['the_price']);
                    $quotation_id = get_option("options_quotation_page_url");
                    $quotation_id = pll_get_post($quotation_id) !== false ? pll_get_post($quotation_id) : $quotation_id;
                    $trip_infos['quotation_link']['link_label'] = get_permalink($quotation_id) . "?sejour=" . $this->context['post_id'];
                }
                if (!empty($trip_infos['the_duration']['duration_unit']) && $trip_infos['the_duration']['duration_unit'] == 'component_based') {
                    $trip_infos['the_duration'] = $groupQuotation->calculTripDuration($trip_infos['the_duration']);
                }
            }
            // If price equals 0, replace elements to display Free
            if (isset($trip_infos['the_price']['price']) && $trip_infos['the_price']['price'] == 0) {
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
                $minutes_num = intval($trip_infos['the_duration']['count_minutes']);
                if ($minutes_num >= 60) {
                    $trip_infos['the_duration']['duration_unit'] = 'hours';
                    $convertedTime = minuteConvert($minutes_num);
                    $trip_infos['the_duration']['count_hours'] = (!empty($convertedTime['hours'])) ? strval($convertedTime['hours']) : '';
                    $trip_infos['the_duration']['count_minutes'] = (!empty($convertedTime['minutes'])) ? strval($convertedTime['minutes']) : '';
                }
            } elseif ($trip_infos['the_duration']['duration_unit'] === 'hours') {
                $trip_infos['the_duration']['count_minutes'] = '';
            }

            if (!empty($trip_infos['the_duration']['count_days']) || !empty($trip_infos['the_length']['length']) || !empty($trip_infos['the_price']['price'])) {
                //TODO: Gérer le fichier gps pour affichage s/ carte
                $trip_infos['the_duration']['count_days'] = ($trip_infos['the_duration']['count_days']) ? humanDays($trip_infos['the_duration']['count_days']) : '';
                $trip_infos['the_price']['price'] = (!empty($trip_infos['the_price']['price'])) ? str_replace('.', ',', $trip_infos['the_price']['price']) : '';
                $this->context['trip_infos'] = \Timber::compile($this->context['woody_components'][$trip_infos['tripinfos_woody_tpl']], $trip_infos);
            } else {
                $trip_infos = [];
            }
        }

        if (!empty($page_type[0]) && $page_type[0]->slug != 'front_page') {
            /*********************************************
             * Compilation de l'en tête de page pour les pages qui ne sont pas de type "accueil"
             *********************************************/
            $page_teaser = [];
            $page_teaser = getAcfGroupFields('group_5b2bbb46507bf', $this->context['post']);
            if ($page_type[0]->slug != 'front_page' and !empty($page_teaser)) {
                $page_teaser['page_teaser_title'] = (!empty($page_teaser['page_teaser_display_title'])) ? str_replace('-', '&#8209', $this->context['post_title']) : '';
                $page_teaser['the_classes'] = [];
                $page_teaser['the_classes'][] = (!empty($page_teaser['background_img_opacity'])) ? $page_teaser['background_img_opacity'] : '';
                $page_teaser['the_classes'][] = (!empty($page_teaser['background_color'])) ? $page_teaser['background_color'] : '';
                $page_teaser['the_classes'][] = (!empty($page_teaser['border_color'])) ? $page_teaser['border_color'] : '';
                $page_teaser['the_classes'][] = (!empty($page_teaser['teaser_margin_bottom'])) ? $page_teaser['teaser_margin_bottom'] : '';
                $page_teaser['the_classes'][] = (!empty($page_teaser['background_img'])) ? 'isRel' : '';
                $page_teaser['classes'] = (!empty($page_teaser['the_classes'])) ? implode(' ', $page_teaser['the_classes']) : '';
                $page_teaser['breadcrumb'] = $this->createBreadcrumb();
                $page_teaser['trip_infos'] = (!empty($this->context['trip_infos'])) ? $this->context['trip_infos'] : '';
                $page_teaser['social_shares'] = (!empty($this->context['social_shares'])) ? $this->context['social_shares'] : '';
                if (!empty($page_teaser['page_teaser_media_type']) && $page_teaser['page_teaser_media_type'] == 'map') {
                    $page_teaser['post_coordinates'] = (!empty(getAcfGroupFields('group_5b3635da6529e', $this->context['post']))) ? getAcfGroupFields('group_5b3635da6529e', $this->context['post']) : '';
                }

                if (!empty($page_teaser['page_teaser_display_created'])) {
                    $page_teaser['created'] = get_the_date();
                }

                // Unset breadcrumb if checked in hide page zones options
                if (!empty($this->context['hide_page_zones']) && in_array('breadcrumb', $this->context['hide_page_zones'])) {
                    unset($page_teaser['breadcrumb']);
                }

                $page_teaser = apply_filters('woody_custom_page_teaser', $page_teaser, $this->context);

                $this->context['page_teaser'] = \Timber::compile($this->context['woody_components'][$page_teaser['page_teaser_woody_tpl']], $page_teaser);
            }

            /*********************************************
             * Compilation du visuel et accroche pour les pages qui ne sont pas de type "accueil"
             *********************************************/

            $page_hero = [];
            $page_hero = getAcfGroupFields('group_5b052bbee40a4', $this->context['post']);
            if (!empty($page_hero['page_heading_media_type']) && ($page_hero['page_heading_media_type'] == 'movie' && !empty($page_hero['page_heading_movie']) || ($page_hero['page_heading_media_type'] == 'img' && !empty($page_hero['page_heading_img'])))) {
                if (empty($page_teaser['page_teaser_display_title'])) {
                    $page_hero['title_as_h1'] = true;
                }

                if (!empty($page_hero['page_heading_img'])) {
                    $page_hero['page_heading_img']['attachment_more_data'] = (!empty($page_hero['page_heading_img']['ID'])) ? $this->tools->getAttachmentMoreData($page_hero['page_heading_img']['ID']) : [];
                }

                if (!empty($page_hero['page_heading_add_social_movie']) && !empty($page_hero['page_heading_social_movie'])) {
                    preg_match_all('@src="([^"]+)"@', $page_hero['page_heading_social_movie'], $result);
                    if (!empty($result[1]) && !empty($result[1][0])) {
                        $iframe_url = $result[1][0];

                        if (strpos($iframe_url, 'youtube') != false) {
                            $yt_params_url = $iframe_url . '?&autoplay=0&rel=0';
                            $page_hero['page_heading_social_movie'] = str_replace($iframe_url, $yt_params_url, $page_hero['page_heading_social_movie']);
                        }
                    }
                }

                $page_hero['title'] = (!empty($page_hero['title'])) ? str_replace('-', '&#8209', $page_hero['title']) : '';
                $this->context['page_hero'] = \Timber::compile($this->context['woody_components'][$page_hero['heading_woody_tpl']], $page_hero);
            }

            $this->context = apply_filters('woody_page_context', $this->context);
        }
    }

    protected function createBreadcrumb()
    {
        $data = [];
        $breadcrumb = '';
        $current_post_id = $this->context['post']->ID;



        // On ajoute la page courante
        $data['items'][] = [
            'title' => get_the_title($current_post_id),
            'url' => apply_filters('woody_get_permalink', $current_post_id)
        ];

        // On ajoute toutes les pages parentes
        $ancestors_ids = get_post_ancestors($current_post_id);
        if (!empty($ancestors_ids) && is_array($ancestors_ids)) {
            foreach ($ancestors_ids as $ancestor_id) {
                $data['items'][] = [
                    'title' => get_the_title($ancestor_id),
                    'url' => apply_filters('woody_get_permalink', $ancestor_id)
                ];
            }
        }

        // On ajoute la page d'accueil
        $front_id = get_option('page_on_front');
        if (!empty($front_id)) {
            $data['items'][] = [
                'title' => get_the_title($front_id),
                'url' => apply_filters('woody_get_permalink', $front_id)
            ];
        }

        $tpl = apply_filters('breadcrumb_tpl', null);
        $template = $tpl['template'] ? $this->context['woody_components'][$tpl['template']] : $this->context['woody_components']['woody_widgets-breadcrumb-tpl_01'];

        $breadcrumb = Timber::compile($template, $data);

        return $breadcrumb;
    }

    protected function commonContext()
    {
        $this->context['page_terms'] = implode(' ', getPageTerms($this->context['post_id']));
        $this->context['default_marker'] = file_get_contents($this->context['dist_dir'] . '/img/default-marker.svg');
        $this->context['hide_page_zones'] = get_field('hide_page_zones');

        if (is_array($this->context['hide_page_zones'])) {
            if (in_array('header', $this->context['hide_page_zones'])) {
                $this->context['body_class'] = $this->context['body_class'] . ' no-page-header';
            }
            if (in_array('footer', $this->context['hide_page_zones'])) {
                $this->context['body_class'] = $this->context['body_class'] . ' no-page-footer';
            }
        }

        /*********************************************
         * Check type de publication
         *********************************************/
        if ($this->context['page_type'] === 'playlist_tourism') {
            $this->playlistContext();
        }

        /*********************************************
         * Compilation du bloc de réservation
         *********************************************/
        $bookblock = [];
        $bookblock = getAcfGroupFields('group_5c0e4121ee3ed', $this->context['post']);

        if (!empty($bookblock['bookblock_playlists'][0]['pl_post_id'])) {
            $bookblock['the_classes'] = [];
            $bookblock['the_classes'][] = (!empty($bookblock['bookblock_bg_params']['background_img_opacity'])) ? $bookblock['bookblock_bg_params']['background_img_opacity'] : '';
            $bookblock['the_classes'][] = (!empty($bookblock['bookblock_bg_params']['background_color'])) ? $bookblock['bookblock_bg_params']['background_color'] : '';
            $bookblock['the_classes'][] = (!empty($bookblock['bookblock_bg_params']['border_color'])) ? $bookblock['bookblock_bg_params']['border_color'] : '';
            $bookblock['the_classes'][] = (!empty($bookblock['bookblock_bg_params']['background_img'])) ? 'isRel' : '';
            if (!empty($bookblock['bookblock_bg_params']['background_img_opacity']) || !empty($bookblock['bookblock_bg_params']['background_color']) || !empty($bookblock['bookblock_bg_params']['border_color'])) {
                $bookblock['the_classes'][] = 'padd-all-md';
            }
            $bookblock['classes'] = (!empty($bookblock['the_classes'])) ? implode(' ', $bookblock['the_classes']) : '';
            if (!empty($bookblock['bookblock_playlists'])) {
                foreach ($bookblock['bookblock_playlists'] as $pl_key => $pl) {
                    $bookblock['bookblock_playlists'][$pl_key]['permalink'] = get_permalink($pl['pl_post_id']);
                    $pl_confId = get_field('field_5b338ff331b17', $pl['pl_post_id']);
                    $bookblock['bookblock_playlists'][$pl_key]['pl_conf_id'] = $pl_confId;
                    if (!empty($pl_confId)) {
                        $pl_lang = pll_get_post_language($pl['pl_post_id']);
                        $pl_params = apply_filters('woody_hawwwai_playlist_render', $pl_confId, $pl_lang, array(), 'json');
                        $facets = (!empty($pl_params['filters'])) ? $pl_params['filters'] : '';
                        if (!empty($facets)) {
                            foreach ($facets as $facet) {
                                if ($facet['type'] === 'daterangeWithAvailabilities') {
                                    $bookblock['bookblock_playlists'][$pl_key]['filters']['id'] = $facet['id'];
                                    $bookblock['bookblock_playlists'][$pl_key]['filters']['translations'] = (!empty($facet['TR'])) ? $facet['TR'] : '';
                                    $bookblock['bookblock_playlists'][$pl_key]['filters']['display_options'] = (!empty($facet['display_options'])) ? $facet['display_options'] : '';
                                    if (!empty($facet['display_options']['booking_range']['values'])) {
                                        $range_values = $facet['display_options']['booking_range']['values'];
                                        if ($range_values[0]['mode'] == 3 && !empty($range_values[0]['customValue'])) {
                                            $bookblock['bookblock_playlists'][$pl_key]['filters']['singledate'] = true;
                                            $bookblock['bookblock_playlists'][$pl_key]['filters']['periods'] = $range_values[0]['customValue'];
                                        } else {
                                            $bookblock['bookblock_playlists'][$pl_key]['filters']['daterange'] = true;
                                        }
                                    }
                                    if (!empty($facet['display_options']['persons']['values'])) {
                                        foreach ($facet['display_options']['persons']['values'] as $person) {
                                            if (!empty($person['field'])) {
                                                $bookblock['bookblock_playlists'][$pl_key]['filters'][$person['field']] = $person['display'];
                                            }
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            $this->context['bookblock'] = \Timber::compile($this->context['woody_components'][$bookblock['bookblock_woody_tpl']], $bookblock);
        }


        /*********************************************
         * Compilation des sections
         *********************************************/
        $this->context['sections'] = [];
        if (!empty($this->context['post'])) {
            $sections = get_field('section', $this->context['post']->ID);
            $this->context['the_sections'] = $this->process->processWoodySections($sections, $this->context);
        }
    }

    protected function playlistContext()
    {
        $this->context['body_class'] .= ' apirender apirender-playlist apirender-wordpress';

        // No Index if autoselect_id
        $autoselect_id = filter_input(INPUT_GET, 'autoselect_id', FILTER_VALIDATE_INT);
        if (!empty($autoselect_id)) {
            $this->context['metas'][] = '<meta name="robots" content="noindex, follow" />';
        }

        /** ************************
         * Vérification pré-cochage
         ************************ **/
        $playlist_type = get_field('field_5c7e59967f790', $this->context['post_id']);
        $autoselect_id = '';
        $existing_playlist = get_field('field_5c7e8bf42b9af', $this->context['post_id']);


        if ($playlist_type == 'autoselect' && !empty($existing_playlist['existing_playlist_autoselect_url']) && !empty($existing_playlist['playlist_autoselect_id'])) {
            $post_id = url_to_postid($existing_playlist['existing_playlist_autoselect_url']);
            $playlistConfId = get_field('field_5b338ff331b17', $post_id);
            $autoselect_id = $existing_playlist['playlist_autoselect_id'];
        } else {
            $autoselect_field = get_field('field_5c7e5bd174a2f', $this->context['post_id']);
            if (!empty($autoselect_field['new_playlist_autoselect_id'])) {
                $autoselect_id = $autoselect_field['new_playlist_autoselect_id'];
            }
            $playlistConfId = get_field('field_5b338ff331b17', $this->context['post_id']);
        }

        // allowed parameters for Wordpress playlists need to be added here
        $checkMethod = !empty($_POST) ? INPUT_POST : INPUT_GET;
        $checkQueryVars = [
            // page number (12 items by page)
            'listpage'   => [
                'filter' => FILTER_VALIDATE_INT,
                'flags'  => [FILTER_REQUIRE_SCALAR, FILTER_NULL_ON_FAILURE],
                'options'   => ['min_range' => 1]
            ],
        ];
        $checkAutoSelect = [
            // id of created facet autoselection returning filtered playlist
            'autoselect_id'   => [
                'filter' => FILTER_VALIDATE_INT,
                'flags'  => [FILTER_REQUIRE_SCALAR, FILTER_NULL_ON_FAILURE],
            ],
        ];

        // build query in validated array
        $query = filter_input_array($checkMethod, $checkAutoSelect, $add_non_existing = false);
        $query_GQV = filter_input_array(INPUT_GET, $checkQueryVars, $add_non_existing = false);

        $query = array_merge((array) $query, (array) $query_GQV);
        foreach ($query as $key => $param) {
            if (!$param) {
                unset($query[$key]);
            }
        }

        // Si un identifiant de précochage est présent, on le passe à l'apirender
        if (!empty($autoselect_id)) {
            $query['autoselect_id'] = $autoselect_id;
        }

        // Get from Apirender
        $this->context['playlist_tourism'] = apply_filters('woody_hawwwai_playlist_render', $playlistConfId, pll_current_language(), $query);

        // save confId
        if (!empty($playlistConfId) && is_array($this->context['playlist_tourism'])) {
            $this->context['playlist_tourism']['confId'] = $playlistConfId;
        }

        // Return template
        if (empty($this->context['playlist_tourism']['content'])) {
            $this->context['playlist_tourism']['content'] = '<center style="margin: 80px 0">Playlist non configurée</center>';
            status_header('410');
        }

        // handle api error
        if (isset($this->context['playlist_tourism']['status'])) {
            $code = intval($this->context['playlist_tourism']['status']);
            status_header($code);
        }
    }

    /***************************
     * Configuration des HTTP headers
     *****************************/
    public function playlistHeaders()
    {
        $headers = [];
        $headers['xkey'] = [];
        if (!empty($this->context['playlist_tourism']['modified'])) {
            $headers['Last-Modified'] = gmdate('D, d M Y H:i:s', strtotime($this->context['playlist_tourism']['modified'])) . ' GMT';
        }
        if (!empty($this->context['playlist_tourism']['playlistId'])) {
            $headers['xkey'][] = 'ts-idplaylist-' . $this->context['playlist_tourism']['playlistId'];
        }
        if (!empty($this->context['playlist_tourism']['confId'])) {
            $headers['xkey'][] = 'hawwwai-idconf-' . $this->context['playlist_tourism']['confId'];
        }
        if (!empty($this->context['playlist_tourism']['apirender_uri'])) {
            $headers['x-apirender-url'] = $this->context['playlist_tourism']['apirender_uri'];
        }
        return $headers;
    }

    /***************************
     * Overide Canonical
     *****************************/
    public function woodySeoCanonical($metas)
    {
        $listpage = filter_input(INPUT_GET, 'listpage', FILTER_VALIDATE_INT);
        $post_type = get_the_terms(get_the_ID(), 'page_type');
        if (!empty($post_type) && $post_type[0]->slug === 'playlist_tourism' && !empty($listpage) && is_numeric($listpage) && !empty($metas['canonical']) && !empty($metas['canonical']['#attributes']) && !empty($metas['canonical']['#attributes']['href'])) {
            $metas['canonical']['#attributes']['href'] = $metas['canonical']['#attributes']['href'] . '?listpage=' . $listpage;
        }
        return $metas;
    }
}
