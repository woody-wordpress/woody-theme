<?php
/**
 * Template
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Template_Page extends WoodyTheme_TemplateAbstract
{
    protected $twig_tpl = '';

    public function __construct()
    {
        parent::__construct();
    }

    protected function registerHooks()
    {
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
        } elseif (!empty(is_404())) {
            $this->twig_tpl = 'page404.twig';
        } else {
            $this->twig_tpl = 'page.twig';
        }
    }

    protected function extendContext()
    {
        if (!empty(is_404())) {
            $this->page404Context();
        } else {
            $this->commonContext();
            $this->pageContext();
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
                    $post = Timber::get_post($post_id);
                    $suggestions[] = getPagePreview(['display_elements' => ['description'], 'display_button' => true, 'display_img' => true], $post);
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
        ];

        $custom = apply_filters('woody_404_custom', $vars);

        $this->context['content'] = $custom;
    }

    protected function pageContext()
    {
        $this->context['is_frontpage'] = false;

        $social_shares = getActiveShares();
        $this->context['social_shares'] = Timber::compile($this->context['woody_components']['blocks-shares-tpl_01'], $social_shares);

        /******************************************************************************
         * Compilation du Diaporama pour les pages de type "accueil" (!= frontpage)
         ******************************************************************************/
        $page_type = wp_get_post_terms($this->context['post_id'], 'page_type');
        if ($page_type[0]->slug == 'front_page') {
            $this->context['is_frontpage'] = true;
            $home_slider = getAcfGroupFields('group_5bb325e8b6b43', $this->context['post']);
            if (!empty($home_slider['landswpr_slides'])) {
                $this->context['home_slider'] = Timber::compile($this->context['woody_components'][$home_slider['landswpr_woody_tpl']], $home_slider);
            }
        }

        /*********************************************
         * Compilation du bloc prix
         *********************************************/
        $trip_infos = getAcfGroupFields('group_5b6c5e6ff381d', $this->context['post']);
        if (!empty($trip_infos['the_duration']['count_days']) || !empty($trip_infos['the_length']['length']) || !empty($trip_infos['the_price']['price'])) {
            //TODO: Gérer le fichier gps pour affichage s/ carte
            $trip_infos['the_duration']['count_days'] = ($trip_infos['the_duration']['count_days']) ? humanDays($trip_infos['the_duration']['count_days']) : '';
            $trip_infos['the_price']['price'] = (!empty($trip_infos['the_price']['price'])) ? str_replace('.', ',', $trip_infos['the_price']['price']) : '';
            $this->context['trip_infos'] = Timber::compile($this->context['woody_components'][$trip_infos['tripinfos_woody_tpl']], $trip_infos);
        } else {
            $trip_infos = [];
        }

        if ($page_type[0]->slug != 'front_page') {
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
                $page_teaser['the_classes'][] =  (!empty($page_teaser['teaser_margin_bottom'])) ? $page_teaser['teaser_margin_bottom'] : '';
                $page_teaser['the_classes'][] = (!empty($page_teaser['background_img'])) ? 'isRel' : '';
                $page_teaser['classes'] = (!empty($page_teaser['the_classes'])) ? implode(' ', $page_teaser['the_classes']) : '';
                $page_teaser['breadcrumb'] = yoast_breadcrumb('<div class="breadcrumb-wrapper padd-all-sm">', '</div>', false);
                $page_teaser['trip_infos'] = (!empty($this->context['trip_infos'])) ? $this->context['trip_infos'] : '';
                $page_teaser['social_shares'] = (!empty($this->context['social_shares'])) ? $this->context['social_shares'] : '';
                if (!empty($page_teaser['page_teaser_media_type']) && $page_teaser['page_teaser_media_type'] == 'map') {
                    $page_teaser['post_coordinates'] = (!empty(getAcfGroupFields('group_5b3635da6529e', $this->context['post']))) ? getAcfGroupFields('group_5b3635da6529e', $this->context['post']) : '';
                }

                $this->context['page_teaser'] = Timber::compile($this->context['woody_components'][$page_teaser['page_teaser_woody_tpl']], $page_teaser);
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

                $page_hero['page_heading_img']['attachment_more_data'] = (!empty($page_hero['page_heading_img'])) ? getAttachmentMoreData($page_hero['page_heading_img']['ID']) : '';
                if (!empty($page_hero['page_heading_add_social_movie']) && !empty($page_hero['page_heading_social_movie'])) {
                    preg_match_all('@src="([^"]+)"@', $page_hero['page_heading_social_movie'], $result);
                    $iframe_url = $result[1][0];
                    if (strpos($iframe_url, 'youtube') != false) {
                        $yt_params_url = $iframe_url . '?&autoplay=0&rel=0';
                        $page_hero['page_heading_social_movie'] = str_replace($iframe_url, $yt_params_url, $page_hero['page_heading_social_movie']);
                    }
                }

                $page_hero['title'] = (!empty($page_hero['title'])) ? str_replace('-', '&#8209', $page_hero['title']) : '';

                $this->context['page_hero'] = Timber::compile($this->context['woody_components'][$page_hero['heading_woody_tpl']], $page_hero);
            }
        }
    }

    protected function commonContext()
    {
        $this->context['page_terms'] = implode(' ', getPageTerms($this->context['post_id']));
        $this->context['default_marker'] = file_get_contents($this->context['dist_dir'] . '/img/default-marker.svg');

        /*********************************************
         * Check type de publication
         *********************************************/
        if ($this->context['page_type'] === 'playlist_tourism') {
            $this->playlistContext();

            $autoselect_id = filter_input(INPUT_GET, 'autoselect_id', FILTER_VALIDATE_INT);
            if (!empty($autoselect_id)) {
                $this->context['metas'][] = '<meta name="robots" content="noindex, follow" />';
            }
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
                    if (!empty($pl_confId)) {
                        $pl_lang = pll_get_post_language($pl['pl_post_id']);
                        $pl_params = apply_filters('woody_hawwwai_playlist_render', $pl_confId, $pl_lang, array(), 'json');
                        $facets = (!empty($pl_params['filters'])) ? $pl_params['filters'] : '';
                        if (!empty($facets)) {
                            foreach ($facets as $facet) {
                                if ($facet['type'] === 'daterangeWithAvailabilities') {
                                    $bookblock['bookblock_playlists'][$pl_key]['filters']['id'] = $facet['id'];
                                    $bookblock['bookblock_playlists'][$pl_key]['filters']['daterange'] = true;
                                    $bookblock['bookblock_playlists'][$pl_key]['filters']['translations'] = (!empty($facet['TR'])) ? $facet['TR'] : '';
                                    $bookblock['bookblock_playlists'][$pl_key]['filters']['display_options'] = (!empty($facet['display_options'])) ? $facet['display_options'] : '';
                                    if (!empty($facet['display_options']['persons']['values'])) {
                                        foreach ($facet['display_options']['persons']['values'] as $person) {
                                            $bookblock['bookblock_playlists'][$pl_key]['filters'][$person['field']] = $person['display'];
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            $this->context['bookblock'] = Timber::compile($this->context['woody_components'][$bookblock['bookblock_woody_tpl']], $bookblock);
        }


        /*********************************************
        * Compilation des sections
        *********************************************/
        $this->context['sections'] = [];
        $sections = $this->context['timberpost']->get_field('section');

        if (!empty($sections)) {
            foreach ($sections as $section_id => $section) {
                $the_header = '';
                $the_layout = '';

                if (!empty($section['icon']) || !empty($section['pretitle']) || !empty($section['title']) || !empty($section['subtitle']) || !empty($section['description'])) {
                    $the_header = Timber::compile($this->context['woody_components']['section-section_header-tpl_01'], $section);
                }

                // Pour chaque bloc d'une section, on compile les données dans un template Woody
                // Puis on les compile dans le template de grille Woody selectionné
                $components = [];
                $components['no_padding'] = $section['scope_no_padding'];
                $components['alignment'] = (!empty($section['section_alignment'])) ? $section['section_alignment'] : '';

                if (!empty($section['section_content'])) {
                    foreach ($section['section_content'] as $layout_id => $layout) {
                        $layout['post'] = [
                            'ID' => $this->context['post_id'],
                            'title' => $this->context['post_title'],
                            'page_type' => $this->context['page_type']
                        ];
                        $layout['uniqid'] = 'section_' . $section_id . '_' . 'section_content_' . $layout_id;
                        $layout['visual_effects'] = (!empty($layout['visual_effects'])) ? formatVisualEffectData($layout['visual_effects']) : '';
                        $components['items'][] = getComponentItem($layout, $this->context);
                    }

                    if (!empty($section['section_woody_tpl'])) {
                        $the_layout = Timber::compile($this->context['woody_components'][$section['section_woody_tpl']], $components);
                    }
                }

                // On récupère les données d'affichage personnalisables
                $display = getDisplayOptions($section);

                // On ajoute les 3 parties compilées d'une section + ses paramètres d'affichage
                // puis on compile le tout dans le template de section Woody
                $the_section = [
                    'header' => $the_header,
                    'layout' => $the_layout,
                    'display' => $display,
                ];

                $this->context['the_sections'][] = Timber::compile($this->context['woody_components']['section-section_full-tpl_01'], $the_section);
            }
        }
    }

    protected function playlistContext()
    {
        $this->context['body_class'] .= ' apirender apirender-playlist apirender-wordpress';

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

        $query = array_merge((array)$query, (array)$query_GQV);
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
}
