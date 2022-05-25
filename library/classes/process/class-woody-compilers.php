<?php

namespace WoodyProcess\Compilers;

use WoodyProcess\Getters\WoodyTheme_WoodyGetters;
use WoodyProcess\Tools\WoodyTheme_WoodyProcessTools;

/**
 * Render Woody data with Timber
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.10.0
 * @author Jeremy Legendre - Benoit Bouchaud
 */


class WoodyTheme_WoodyCompilers
{
    protected $tools;
    protected $getter;

    public function __construct()
    {
        $this->tools = new WoodyTheme_WoodyProcessTools();
        $this->getter = new WoodyTheme_WoodyGetters();
        $this->registerHooks();
    }

    public function registerHooks()
    {
        add_action('save_post', [$this, 'savePost'], 10, 3);
    }

    /**
     *
     * Nom : formatFocusesData
     * Auteur : Benoit Bouchaud
     * Return : Retourne le html d'une mise en avant de contenu
     * @param    wrapper Le wrapper du champ de mise en avant
     * @param    current_post le post courant (pour les autofocus hierarchiques)
     * @param    twigPaths les chemins des templates woody
     * @return   items - Un tableau de données
     *
     */
    public function formatFocusesData($wrapper, $current_post, $twigPaths)
    {
        $return = '';
        $the_items = [];

        switch ($wrapper['acf_fc_layout']) {
            case 'manual_focus':
            case 'focus_trip_components':
                $the_items = $this->getter->getManualFocusData($wrapper);
            break;
            case 'auto_focus':
                $the_items = $this->getter->getAutoFocusData($current_post, $wrapper);
            break;
            case 'auto_focus_sheets':
                if (!empty($wrapper['playlist_conf_id'])) {
                    $the_items = $this->getter->getAutoFocusSheetData($wrapper);
                }
            break;
            case 'auto_focus_topics':
                $the_items = $this->getter->getAutoFocusTopicsData($wrapper);
            break;
            case 'profile_focus':
                $the_items = $this->getter->getProfileFocusData($wrapper);
            break;
        }
        $the_items['alert'] = apply_filters('add_admin_alert_message', '');
        if (!empty($the_items) && !empty($the_items['items']) && is_array($the_items['items'])) {
            foreach ($the_items['items'] as $item_key => $item) {
                if (!empty($item['description'])) {
                    $the_items['items'][$item_key]['description'] = str_replace(['[', ']'], '', $item['description']);
                }
            }

            if ($wrapper['acf_fc_layout'] == 'auto_focus_sheets') {
                $the_items['block_titles'] = $this->tools->getBlockTitles($wrapper, 'focus_block_title_');
            } else {
                $the_items['block_titles'] = $this->tools->getBlockTitles($wrapper);
            }

            $the_items['no_padding'] = (!empty($wrapper['focus_no_padding'])) ? $wrapper['focus_no_padding'] : '';
            $the_items['display_button'] = (!empty($wrapper['display_button'])) ? $wrapper['display_button'] : false;
            $the_items['display_img'] = (!empty($wrapper['display_img'])) ? $wrapper['display_img'] : false;
            $the_items['default_marker'] = (!empty($wrapper['default_marker'])) ? $wrapper['default_marker'] : '';
            $the_items['visual_effects'] = $wrapper['visual_effects'];
            $the_items['display_index'] = (!empty($wrapper['display_index'])) ? $wrapper['display_index'] : false;

            // Responsive stuff
            if (!empty($wrapper['mobile_behaviour'])) {
                if ($wrapper['mobile_behaviour']['mobile_grid'] == 'grid') {
                    $the_items['swResp'] = false;
                } elseif ($wrapper['mobile_behaviour']['mobile_grid'] == 'swiper') {
                    $the_items['swResp'] = true;
                }
                $the_items['mobile_behaviour'] = $wrapper['mobile_behaviour'];
            }

            if (!empty($wrapper['focus_block_title_bg_params'])) {
                $the_items['display_block_titles'] = $this->tools->getDisplayOptions($wrapper['focus_block_title_bg_params']);
            }
            if (!empty($wrapper['focus_block_bg_params'])) {
                $the_items['display'] = $this->tools->getDisplayOptions($wrapper['focus_block_bg_params']);
            }

            if (!empty($wrapper['focus_map_params'])) {
                if (!empty($wrapper['focus_map_params']['tmaps_confid'])) {
                    $the_items['map_params']['tmaps_confid'] = $wrapper['focus_map_params']['tmaps_confid'];
                } elseif (!empty(get_field('tmaps_confid', 'option'))) {
                    $the_items['map_params']['tmaps_confid'] = get_field('tmaps_confid', 'option');
                }
                if (!empty($wrapper['focus_map_params']['map_height'])) {
                    $the_items['map_params']['map_height'] = $wrapper['focus_map_params']['map_height'];
                }
                if (!empty($wrapper['focus_map_params']['map_zoom_auto'])) {
                    $the_items['map_params']['map_zoom_auto'] = $wrapper['focus_map_params']['map_zoom_auto'];
                }
                if (!empty($wrapper['focus_map_params']['map_zoom'])) {
                    if (empty($the_items['map_params']['map_zoom_auto']) || $the_items['map_params']['map_zoom_auto'] === false) {
                        $the_items['map_params']['map_zoom'] = $wrapper['focus_map_params']['map_zoom'];
                    }
                }
            }

            $the_items = apply_filters('woody_format_focuses_data', $the_items, $wrapper);

            $return = !empty($wrapper['woody_tpl']) ? \Timber::compile($twigPaths[$wrapper['woody_tpl']], $the_items) : \Timber::compile($twigPaths['blocks-focus-tpl_103'], $the_items) ;
        }

        return $return;
    }

    public function formatMinisheetData($wrapper, $twigPaths)
    {
        // Sheet item
        $data = $this->getter->getManualFocusMinisheetData($wrapper);


        // Block titles
        $data['block_titles'] = $this->tools->getBlockTitles($wrapper, 'sheets_block_title_');
        $data['block_titles']['display_options'] = $this->tools->getDisplayOptions($wrapper);

        // Display options
        $data['display_options']['no_padding'] = (!empty($wrapper['sheet_no_padding'])) ? $wrapper['sheet_no_padding'] : 0;

        $return = \Timber::compile($twigPaths[$wrapper['woody_tpl']], $data);
        return $return;
    }

    public function formatGeomapData($wrapper, $twigPaths)
    {
        $return = '';
        if (empty($wrapper['markers']) && empty($wrapper['routes'])) {
            return;
        }

        if (!empty($wrapper['routes'])) {
            foreach ($wrapper['routes'] as $key => $route) {
                if ($route['route_file']) {
                    $filename = get_attached_file($route['route_file']['ID']);
                    $filetype = wp_check_filetype($filename);

                    // Parameters :
                    $fill_color = $route['fill_color'];
                    $route_color = $route['route_color'];
                    $stroke_thickness = $route['stroke_thickness'];
                    $parameters = $route['parameters'];

                    if ($filetype['ext'] == 'json' || $filetype['ext'] == 'geojson') {
                        $json = file_get_contents($filename);
                        $route['route_file'] = $json;

                        $wrapper['routes'][$key] = json_decode($route['route_file'], true);
                        foreach ($wrapper['routes'][$key]['features'] as $f_key => $feature) {
                            $wrapper['routes'][$key]['features'][$f_key]['route'] = true;

                            if ($parameters === true) {
                                $wrapper['routes'][$key]['features'][$f_key]['properties']['fill'] = $fill_color;
                                $wrapper['routes'][$key]['features'][$f_key]['properties']['stroke'] = $route_color;
                                $wrapper['routes'][$key]['features'][$f_key]['properties']['stroke-width'] = $stroke_thickness;
                            }
                            $fill_opacity = isset($wrapper['routes'][$key]['features'][$f_key]['properties']['fill-opacity']) ? $wrapper['routes'][$key]['features'][$f_key]['properties']['fill-opacity'] : 0;
                            $wrapper['routes'][$key]['features'][$f_key]['properties']['fill-opacity'] = $fill_opacity == 0 ? 0.5 : $fill_opacity;

                            // Route Fields aren't supposed to have markers.
                            if ($feature['geometry']['type'] == "Point") {
                                unset($wrapper['routes'][$key]['features'][$f_key]);
                            }
                        }
                        $wrapper['routes'][$key]['features'] = array_values($wrapper['routes'][$key]['features']);
                        $wrapper['routes'][$key] = json_encode($wrapper['routes'][$key]);
                    }
                } else {
                    unset($wrapper['routes'][$key]);
                }
            }
        }

        if (!empty($wrapper['markers'])) {
            // Set boolean to fitBounds
            $wrapper['map_zoom_auto'] = ($wrapper['map_zoom_auto']) ? 'true' : 'false';

            // Calcul center of map
            $sum_lat = $sum_lng = 0;
            foreach ($wrapper['markers'] as $key => $marker) {
                if (!empty($marker['map_position']['lat'])) {
                    $sum_lat += $marker['map_position']['lat'];
                }
                if (!empty($marker['map_position']['lng'])) {
                    $sum_lng += $marker['map_position']['lng'];
                }
            }
            $wrapper['default_lat'] = $sum_lat / count($wrapper['markers']);
            $wrapper['default_lng'] = $sum_lng / count($wrapper['markers']);

            // Get markers
            foreach ($wrapper['markers'] as $key => $marker) {
                $the_marker = [];
                $marker['default_marker'] = $wrapper['default_marker'];
                if (empty($marker['title']) && empty($marker['description']) && empty($marker['img']) && !empty($marker['link']['url'])) {
                    $wrapper['markers'][$key]['marker_as_link'] = true;
                }
                $wrapper['markers'][$key]['compiled_marker']  = \Timber::compile('/_objects/markerObject.twig', $marker);

                if (!empty($marker['title']) || !empty($marker['description']) || !empty($marker['img'])) {
                    $the_marker['item']['title'] = (!empty($marker['title'])) ? $marker['title'] : '';
                    $the_marker['item']['description'] = (!empty($marker['description'])) ? $marker['description'] : '';
                    if (!empty($marker['img'])) {
                        $the_marker['image_style'] = 'ratio_16_9';
                        $the_marker['item']['img'] = $marker['img'];
                    }
                    $the_marker['item']['link'] = (!empty($marker['link'])) ? $marker['link'] : '';
                    $wrapper['markers'][$key]['marker_thumb_html']  = \Timber::compile($twigPaths['cards-geomap_card-tpl_01'], $the_marker);
                }
            }
        }

        if (empty($wrapper['tmaps_confid']) && !empty(get_field('tmaps_confid', 'option'))) {
            $wrapper['tmaps_confid'] = get_field('tmaps_confid', 'option');
        }

        $return = \Timber::compile($twigPaths[$wrapper['woody_tpl']], $wrapper);
        return $return;
    }

    public function formatSemanticViewData($wrapper, $twigPaths)
    {
        $return = '';
        $the_items = [];
        $post_id = get_the_ID();
        $front_id = get_option('page_on_front');

        if ($wrapper['semantic_view_type'] == 'manual' && !empty($wrapper['semantic_view_include'])) {
            $the_query = [
                'post_type' => 'page',
            ];
            foreach ($wrapper['semantic_view_include'] as $included_id) {
                $the_query['post__in'][] = $included_id;
            }
        } else {
            if ($wrapper['semantic_view_type'] == 'sisters') {
                $parent_id = wp_get_post_parent_id($post_id);
            } else {
                $parent_id = $post_id;
            }

            if (!empty($wrapper['semantic_view_page_types'])) {
                $tax_query = [
                    'relation' => 'AND',
                    'page_type' => array(
                        'taxonomy' => 'page_type',
                        'terms' => $wrapper['semantic_view_page_types'],
                        'field' => 'term_id',
                        'operator' => 'IN'
                    ),
                ];
            }

            $orderby = !empty($wrapper['semantic_view_order']) ? $wrapper['semantic_view_order'] : 'menu_order' ;

            $the_query = [
                'post_type'     => 'page',
                'post_parent'   => $parent_id,
                'post__not_in'  => [$post_id, $front_id],
                'tax_query'     => (!empty($tax_query)) ? $tax_query : '',
                'posts_per_page' => -1,
                'order'         => 'ASC',
                'orderby'       => $orderby
            ];

            // Si des pages ont été ajoutées dans le champ "Pages à exclure"
            if (!empty($wrapper['semantic_view_exclude'])) {
                foreach ($wrapper['semantic_view_exclude'] as $excluded_id) {
                    $the_query['post__not_in'][] = $excluded_id;
                }
            }
        }

        $query_result = new \WP_query($the_query);

        if (!empty($query_result->posts)) {
            foreach ($query_result->posts as $key => $post) {
                $data = [];
                $data = getPagePreview($wrapper, $post);
                if (!empty($data['description'])) {
                    preg_match_all("/\[[^\]]*\]/", $data['description'], $matches);
                    if (!empty($matches[0])) {
                        foreach ($matches[0] as $match) {
                            $str = str_replace(['[', ']'], '', $match);
                            $link = '<a href="' . woody_get_permalink(pll_get_post($post->ID)) . '">' . $str . '</a>';
                            $data['description'] = str_replace($match, $link, $data['description']);
                        }
                    }
                }
                $the_items['items'][$key] = $data;
            }
        }

        if (!empty($the_items)) {
            $return = \Timber::compile($twigPaths[$wrapper['woody_tpl']], $the_items);
        }

        return $return;
    }

    public function formatListContent($wrapper, $current_post, $twigPaths)
    {
        $return = '';

        // On définit des variables de base
        $the_list['permalink'] = woody_get_permalink($current_post->ID);
        $the_list['uniqid'] = $wrapper['uniqid'];
        $the_list['has_map'] = false;

        // On récupère la pagination et sa position pour passer un paramètre à la query
        $paginate = ($wrapper['the_list_pager']['list_pager_type'] == 'basic_pager') ? true : false;

        if ($paginate) {
            $the_list['pager_position'] = $wrapper['the_list_pager']['list_pager_position'];
        }
        // On récupère les champs du formulaire de requete du backoffice
        $list_el_wrapper = $wrapper['the_list_elements']['list_el_req_fields'];

        // On ajoute une variable à passer à la pagination (surchargée par les paramètres GET la cas échéant)
        $list_el_wrapper['seed'] = date('dmY');
        // On récupère les items par défaut et on les stocke dans un cache pour les passer aux filtres
        $cache_key = 'list_filters__post_' . $current_post->ID . '_' . $wrapper['uniqid'];
        $default_items = wp_cache_get($cache_key, 'woody');
        if (empty($default_items)) {
            $default_items = $this->getter->getAutoFocusData($current_post, $list_el_wrapper, $paginate, $wrapper['uniqid'], true);
            wp_cache_set($cache_key, $default_items, 'woody');
        }

        // On crée/update l'option qui liste les caches pour pouvoir les supprimer lors d'un save_post
        $cache_list = dropzone_get('woody_list_filters_cache');
        if (empty($cache_list)) {
            dropzone_set('woody_list_filters_cache', [$cache_key]);
        } elseif (!in_array($cache_key, $cache_list)) {
            $cache_list[] = $cache_key;
            dropzone_set('woody_list_filters_cache', $cache_list);
        }

        // On récupère les ids des posts non filtrés pour les passer au paramètre post__in de la query
        $default_items_ids = [];
        if (!empty($default_items) && !empty($default_items['items'])) {
            foreach ($default_items['items'] as $item) {
                $default_items_ids[] = $item['post_id'];
            }
        }

        // On récupère et on applique les valeurs des filtres si existantes
        $form_result = (!empty(filter_input_array(INPUT_GET))) ? filter_input_array(INPUT_GET) : [];
        if (!empty($form_result['uniqid'])) {

            // On supprimte ce qui est inutile pour les filtres car on a déjà une liste de posts correspondant à la requete du backoffice
            unset($list_el_wrapper['focused_taxonomy_terms']);

            // On surcharge le seed avec celui reçu dans les paramètres GET pour maitriser le random des listes
            $list_el_wrapper['seed'] = (!empty($form_result['seed'])) ? $form_result['seed'] : null;

            foreach ($form_result as $result_key => $input_value) {
                if (strpos($result_key, $the_list['uniqid']) !== false && strpos($result_key, 'tt') !== false) { // Taxonomy Terms
                    $input_value = (!is_array($input_value)) ? [$input_value] : $input_value;
                    foreach ($input_value as $single_value) {
                        // Si on poste la value 'all', on ne filtre pas sur cet input
                        if ($single_value !== 'all') {
                            $list_el_wrapper['filtered_taxonomy_terms'][$result_key][] = $single_value;
                        }
                    }
                } elseif (strpos($result_key, $the_list['uniqid']) !== false && strpos($result_key, 'td') !== false) { // Trip Duration
                    if (strpos($result_key, 'max') !== false) {
                        $list_el_wrapper['focused_trip_duration']['max'] = $input_value;
                    } else {
                        $list_el_wrapper['focused_trip_duration']['min'] = $input_value;
                    }
                } elseif (strpos($result_key, $the_list['uniqid']) !== false && strpos($result_key, 'tp') !== false) { // Trip Price
                    if (strpos($result_key, 'max') !== false) {
                        $list_el_wrapper['focused_trip_price']['max'] = $input_value;
                    } else {
                        $list_el_wrapper['focused_trip_price']['min'] = $input_value;
                    }
                }
            }

            $the_items = $this->getter->getAutoFocusData($current_post, $list_el_wrapper, $paginate, $wrapper['uniqid'], false, $default_items_ids, $wrapper['the_list_filters']);
        } else {
            $the_items = $this->getter->getAutoFocusData($current_post, $list_el_wrapper, $paginate, $wrapper['uniqid'], false);
        }

        // On affiche un message s'il n'y aucun résultat
        if (empty($the_items)) {
            $the_items['empty'] = __('Désolé, aucun contenu ne correspond à votre recherche', 'woody-theme');
        }

        $the_items['no_padding'] = (!empty($wrapper['list_no_padding'])) ? $wrapper['list_no_padding'] : '';

        // Show button
        $the_items['display_button'] = (!empty($list_el_wrapper['display_button'])) ? $list_el_wrapper['display_button'] : false;

        // On compile la grille des éléments
        $the_list['the_grid'] = \Timber::compile($twigPaths[$wrapper['listgrid_woody_tpl']], $the_items);

        // On récupère le nombre de résultats
        $the_list = $this->tools->countFocusResults($the_items, $the_list);

        // Récupère la donnée des filtres de base
        if (!empty($wrapper['the_list_filters'])) {
            $the_list['filters'] = $this->getter->getListFilters($wrapper['the_list_filters'], $list_el_wrapper, $default_items);
            // Si on a trouvé un filtre de carte, on remplit le tableau the_map
            if (isset($the_list['filters']['the_map'])) {
                $map_items = $this->getter->getAutoFocusData($current_post, $list_el_wrapper, $paginate, $wrapper['uniqid'], true, $default_items_ids, $wrapper['the_list_filters']);

                $the_list['filters']['the_map']['markers'] = $this->formatListMapFilter($map_items, $wrapper['default_marker'], $twigPaths);
                $the_list['has_map'] = true;
            }
        }

        // Récupère la pagination compilée
        if ($paginate && !empty($the_items['max_num_pages'])) {
            $the_list['pager'] = $this->formatListPager($the_items['max_num_pages'], $wrapper['uniqid'], $list_el_wrapper['seed']);
        }

        $return = \Timber::compile($twigPaths[$wrapper['the_list_filters']['listfilter_woody_tpl']], $the_list);
        return $return;
    }

    public function savePost($post_id, $post, $update)
    {
        if (!empty($post) && $post->post_type == 'page') {
            $cache_list = dropzone_get('woody_list_filters_cache');
            if (!empty($cache_list)) {
                foreach ($cache_list as $cache_key) {
                    wp_cache_delete($cache_key, 'woody');
                }
                dropzone_delete('woody_list_filters_cache');
            }
        }
    }

    /**
     * Create pagination if needed
     * @param   max_num_pages
     * @param   uniqid          section id of list content
     * @return  return          pagination html elements
     */
    public function formatListPager($max_num_pages, $uniqid, $seed)
    {
        $return = [];
        $page_offset = (!empty($_GET[$uniqid])) ? htmlentities(stripslashes($_GET[$uniqid])) : 1;

        $pager_args = [
            'total' => $max_num_pages,
            'format' => '?' . $uniqid . '=%#%&seed=' . $seed,
            'current' => $page_offset,
            'mid_size' => 3,
            'type' => 'list'
        ];

        $return = paginate_links($pager_args);
        return $return;
    }

    /**
     * Create map filter if needed
     * @param   max_num_pages
     * @param   uniqid          section id of list content
     * @return  return          pagination html elements
     */
    public function formatListMapFilter($items, $marker, $twigPaths)
    {
        $return = [];

        if (!empty($items)) {
            foreach ($items['items'] as $item) {
                if (!empty($item['location']['lat']) && !empty($item['location']['lng'])) {
                    $the_marker = [
                        'image_style' => 'ratio_16_9',
                        'item' => [
                            'title' => $item['title'],
                            'description' => $item['description'],
                            'img' => $item['img'],
                            'link' => $item['link']
                        ]
                    ];

                    $return[] = [
                        'map_position' => [
                            'lat' => $item['location']['lat'],
                            'lng' => $item['location']['lng']
                        ],
                        'compiled_marker' => $marker,
                        'marker_thumb_html' => \Timber::compile($twigPaths['cards-geomap_card-tpl_01'], $the_marker)
                    ];
                }
            }
        }
        return $return;
    }

    public function formatSummaryItems($post_id)
    {
        $return = [];
        $permalink = woody_get_permalink($post_id);
        $sections = get_field('section', $post_id);
        if (!empty($sections) && is_array($sections)) {
            foreach ($sections as $s_key => $section) {
                if (!empty($section['display_in_summary']) && empty($section['hide_section'])) {
                    $return['items'][] = [
                        'title' => (!empty($section['section_summary_title'])) ? $section['section_summary_title'] : 'Section ' . $s_key,
                        'anchor' => (!empty($section['section_summary_title'])) ? $permalink . '#summary-' . sanitize_title($section['section_summary_title']) : $permalink . '#pageSection-' . $s_key,
                        'id' => '#pageSection-' . $s_key,
                        'location' => [
                            'latitude' => (!empty($section['section_latitude'])) ? $section['section_latitude'] : '',
                            'longitude' => (!empty($section['section_longitude'])) ? $section['section_longitude'] : ''
                        ]
                    ];

                    foreach ($return['items'] as $item) {
                        if (!empty($item['location']) && !empty($item['location']['latitude']) && !empty($item['location']['longitude'])) {
                            $return['display_map'] = true;
                            break;
                        }
                    }
                }
            }
        }

        return $return;
    }

    public function formatPageTeaser($context, $custom_post_id = null)
    {
        if (!empty($custom_post_id) && is_numeric($custom_post_id)) {
            $context['post'] = get_post($custom_post_id);
            $context['post_id'] = $custom_post_id;
            $context['post_title'] = get_the_title($custom_post_id);
        }

        // On récupère les champs du groupe En-tête de page
        $page_teaser = getAcfGroupFields('group_5b2bbb46507bf', $context['post']);

        $page_teaser['the_classes'] = [];

        // On récupère le tpl du visuel & accroche pour ajouter du contexte à l'en-tête si besoin
        //TODO: Vérifier que le comportement est bon avec un fadingHero

        if (!empty(getAcfGroupFields('group_5b052bbee40a4', $context['post'])['heading_woody_tpl'])) {
            $page_hero_tpl = substr(getAcfGroupFields('group_5b052bbee40a4', $context['post'])['heading_woody_tpl'], -6);

            if ($page_hero_tpl == 'tpl_05' || $page_hero_tpl == 'tpl_06') {
                $page_teaser['the_classes'][] = (empty($page_teaser['background_color'])) ? 'bg-transparent' : '';
            }
        }

        $page_teaser['page_teaser_title'] = (!empty($page_teaser['page_teaser_display_title'])) ? str_replace('-', '&#8209', $context['post_title']) : '';

        $page_teaser['the_classes'][] = (!empty($page_teaser['background_img_opacity'])) ? $page_teaser['background_img_opacity'] : '';
        $page_teaser['the_classes'][] = (!empty($page_teaser['background_color_opacity'])) ? $page_teaser['background_color_opacity'] : '';
        $page_teaser['the_classes'][] = (!empty($page_teaser['background_color'])) ? $page_teaser['background_color'] : '';
        $page_teaser['the_classes'][] = (!empty($page_teaser['border_color'])) ? $page_teaser['border_color'] : '';
        $page_teaser['the_classes'][] = (!empty($page_teaser['teaser_margin_bottom'])) ? $page_teaser['teaser_margin_bottom'] : '';
        $page_teaser['the_classes'][] = (!empty($page_teaser['background_img'])) ? 'isRel' : '';
        $page_teaser['the_classes'][] = (!empty($page_teaser['page_teaser_class'])) ? $page_teaser['page_teaser_class'] : '';
        $page_teaser['classes'] = (!empty($page_teaser['the_classes'])) ? implode(' ', $page_teaser['the_classes']) : '';

        $page_teaser['breadcrumb'] = $this->createBreadcrumb($context);

        $page_teaser['trip_infos'] = (!empty($context['trip_infos'])) ? $context['trip_infos'] : '';
        $page_teaser['social_shares'] = (!empty($context['social_shares'])) ? $context['social_shares'] : '';

        if (!empty($page_teaser['page_teaser_add_media'])) {
            unset($page_teaser['profile']);
        } elseif (!empty($page_teaser['page_teaser_add_profile'])) {
            unset($page_teaser['page_teaser_img']);
        }

        if (!empty($page_teaser['page_teaser_media_type']) && $page_teaser['page_teaser_media_type'] == 'map') {
            $page_teaser['post_coordinates'] = (!empty(getAcfGroupFields('group_5b3635da6529e', $context['post']))) ? getAcfGroupFields('group_5b3635da6529e', $context['post']) : '';
        }

        if (!empty($page_teaser['page_teaser_display_created'])) {
            $page_teaser['created'] = get_the_date();
        }

        // Unset breadcrumb if checked in hide page zones options
        if (!empty($context['hide_page_zones']) && in_array('breadcrumb', $context['hide_page_zones'])) {
            unset($page_teaser['breadcrumb']);
        }

        if (!empty($page_teaser['page_teaser_img']) && is_array($page_teaser['page_teaser_img'])) {
            $page_teaser['page_teaser_img']['attachment_more_data'] = (!empty($page_teaser['page_teaser_img']['ID'])) ? $this->tools->getAttachmentMoreData($page_teaser['page_teaser_img']['ID']) : [];
        }

        $page_teaser['page_teaser_pretitle'] = (!empty($page_teaser['page_teaser_pretitle'])) ? $this->tools->replacePattern($page_teaser['page_teaser_pretitle'], $context['post_id']) : '';
        $page_teaser['page_teaser_subtitle'] = (!empty($page_teaser['page_teaser_subtitle'])) ? $this->tools->replacePattern($page_teaser['page_teaser_subtitle'], $context['post_id']) : '';
        $page_teaser['page_teaser_desc'] = (!empty($page_teaser['page_teaser_desc'])) ? $this->tools->replacePattern($page_teaser['page_teaser_desc'], $context['post_id']) : '';

        // Existing profile
        if (!empty($page_teaser['page_teaser_add_profile']) && !empty($page_teaser['profile']['use_profile']) && !empty($page_teaser['profile']['profile_post'])) {
            $profile_id = $page_teaser['profile']['profile_post'];

            //Add Profil expression category if checked
            if (!empty($page_teaser['profile']['use_profile_expression']) && !empty($page_teaser['profile']['profile_expression'])) {
                $profile_expressions=$this->getter->getProfileExpressions($page_teaser['profile']['profile_post'], $page_teaser['profile']['profile_expression']);
            }

            $page_teaser['profile'] = [
                'profile_title' => get_the_title($profile_id),
                'profile_picture' => get_field('profile_picture', $profile_id),
                'profile_description' => get_field('profile_description', $profile_id),
                'profile_expressions' => (!empty($profile_expressions)) ? $profile_expressions : '',
            ];
        }

        $page_teaser['tmaps_confid'] = get_field('tmaps_confid', 'option');

        $page_teaser = apply_filters('woody_custom_page_teaser', $page_teaser, $context);

        return \Timber::compile($context['woody_components'][$page_teaser['page_teaser_woody_tpl']], $page_teaser);
    }

    public function formatPageHero($context, $custom_post_id = null)
    {
        if (!empty($custom_post_id) && is_numeric($custom_post_id)) {
            $context['post'] = get_post($custom_post_id);
            $context['post_id'] = $custom_post_id;
        }

        $page_hero = getAcfGroupFields('group_5b052bbee40a4', $context['post']);

        if (!empty($page_hero['page_heading_media_type']) && ($page_hero['page_heading_media_type'] == 'movie' && !empty($page_hero['page_heading_movie']) || ($page_hero['page_heading_media_type'] == 'img' && !empty($page_hero['page_heading_img'])))) {
            if (empty(get_field('page_teaser_display_title', $context['post_id']))) {
                $page_hero['title_as_h1'] = true;
            }

            if (!empty($page_hero['page_heading_img']) && is_array($page_hero['page_heading_img'])) {
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
            $page_hero['isfrontpage']= !empty(get_option('page_on_front')) && get_option('page_on_front') == pll_get_post($context['post_id']) ? true : false ;
            $page_hero['title'] = (!empty($page_hero['title'])) ? $this->tools->replacePattern($page_hero['title'], $context['post_id']) : '';
            $page_hero['pretitle'] = (!empty($page_hero['pretitle'])) ? $this->tools->replacePattern($page_hero['pretitle'], $context['post_id']) : '';
            $page_hero['subtitle'] = (!empty($page_hero['subtitle'])) ? $this->tools->replacePattern($page_hero['subtitle'], $context['post_id']) : '';
            $page_hero['description'] = (!empty($page_hero['description'])) ? $this->tools->replacePattern($page_hero['description'], $context['post_id']) : '';

            $page_hero['title'] = (!empty($page_hero['title'])) ? str_replace('-', '&#8209', $page_hero['title']) : '';

            $page_hero['the_classes'] = [];
            $page_hero['the_classes'][] = (!empty($page_hero['title'])) ? 'has-title' : '';
            $page_hero['the_classes'][] = (!empty($page_hero['pretitle'])) ? 'has-pretitle' : '';
            $page_hero['the_classes'][] = (!empty($page_hero['subtitle'])) ? 'has-subtitle' : '';
            $page_hero['the_classes'][] = (!empty($page_hero['description'])) ? 'has-description' : '';
            $page_hero['classes'] = (!empty($page_hero['the_classes'])) ? implode(' ', $page_hero['the_classes']) : '';

            $page_hero = apply_filters('woody_custom_page_hero', $page_hero, $context);
            return [
                'view' => \Timber::compile($context['woody_components'][$page_hero['heading_woody_tpl']], $page_hero),
                'data' => $page_hero,
            ];
        } else {
            return '';
        }
    }

    protected function createBreadcrumb($context)
    {
        $data = [];
        $breadcrumb = '';
        $current_post_id = (!empty($context['mirror_id'])) ? $context['mirror_id'] : $context['post']->ID;

        // On ajoute la page d'accueil
        $front_id = get_option('page_on_front');
        if (!empty($front_id)) {
            $data['items'][] = [
                'title' => get_the_title($front_id),
                'url' => woody_get_permalink($front_id)
            ];
        }

        // On ajoute toutes les pages parentes
        $ancestors_ids = get_post_ancestors($current_post_id);
        if (!empty($ancestors_ids) && is_array($ancestors_ids)) {
            $ancestors_ids = array_reverse($ancestors_ids);
            foreach ($ancestors_ids as $ancestor_id) {
                $data['items'][] = [
                    'title' => get_the_title($ancestor_id),
                    'url' => woody_get_permalink($ancestor_id)
                ];
            }
        }

        // On ajoute la page courante
        $data['items'][] = [
            'title' => get_the_title($current_post_id),
            'url' => woody_get_permalink($current_post_id)
        ];

        $tpl = apply_filters('breadcrumb_tpl', null);
        $template = (!empty($tpl['template']) && !empty($context['woody_components'][$tpl['template']])) ? $context['woody_components'][$tpl['template']] : $context['woody_components']['woody_widgets-breadcrumb-tpl_01'];

        $breadcrumb = \Timber::compile($template, $data);

        return $breadcrumb;
    }

    public function formatTestimonials($layout)
    {
        if (!empty($layout['testimonials'])) {
            foreach ($layout['testimonials'] as $testimony_key => $testimony) {
                if (!empty($testimony['testimony_post_object']) && is_int($testimony['testimony_post_object'])) {
                    $layout['testimonials'][$testimony_key]['text'] = get_field('testimony_text', $testimony['testimony_post_object']);
                    $layout['testimonials'][$testimony_key]['title'] = get_the_title($testimony['testimony_post_object']);
                    $profile = get_field('testimony_linked_profile', $testimony['testimony_post_object']);
                    if (!empty($profile) && is_int($profile)) {
                        $layout['testimonials'][$testimony_key]['signature'] = get_the_title($profile);
                        $layout['testimonials'][$testimony_key]['img'] = get_field('profile_picture', $profile);
                    }
                }
            }
        }

        return $layout;
    }

    public function formatHomeSlider($post, $woody_components)
    {
        $home_slider = getAcfGroupFields('group_5bb325e8b6b43', $post);

        $plyr_options = [
            'muted' => true,
            'autoplay' => true,
            'controls' => ['volume', 'mute'],
            'loop' => ['active' => true],
            'youtube' => ['noCookie' => true]
        ];

        $home_slider['plyr_options'] = json_encode($plyr_options);

        if (!empty($home_slider) && !empty($home_slider['landswpr_slides'])) {
            foreach ($home_slider['landswpr_slides'] as $slide_key => $slide) {
                // Si on est dans le cas d'une vidéo oEmbed, on récupère la plus grande miniature possible
                // Permet d'afficher un poster le temps du chargement de Plyr
                if (!empty($slide['landswpr_slide_media']) && $slide['landswpr_slide_media']['landswpr_slide_media_type'] == 'embed' && !empty($slide['landswpr_slide_media']['landswpr_slide_embed'])) {
                    if (!empty(embedProviderThumbnail($slide['landswpr_slide_media']['landswpr_slide_embed']))) {
                        $home_slider['landswpr_slides'][$slide_key]['landswpr_slide_media']['landswpr_slide_embed_thumbnail_url'] = embedProviderThumbnail($slide['landswpr_slide_media']['landswpr_slide_embed']);
                    }
                }

                if (!empty($slide['landswpr_slide_media']) && $slide['landswpr_slide_media']['landswpr_slide_media_type'] == 'img' && !empty($slide['landswpr_slide_media']['landswpr_slide_img'])) {
                    $home_slider['landswpr_slides'][$slide_key]['landswpr_slide_media']['landswpr_slide_img']['lazy'] = apply_filters('woody_landswpr_slide_img_lazy', 'disabled');
                    $home_slider['landswpr_slides'][$slide_key]['landswpr_slide_media']['landswpr_slide_img']['attachment_more_data'] = $this->tools->getAttachmentMoreData($home_slider['landswpr_slides'][$slide_key]['landswpr_slide_media']['landswpr_slide_img']['ID']);
                }

                if (!empty($slide['landswpr_slide_add_social_movie']) && !empty($slide['landswpr_slide_social_movie'])) {
                    preg_match_all('@src="([^"]+)"@', $slide['landswpr_slide_social_movie'], $result);
                    if (!empty($result[1]) && !empty($result[1][0])) {
                        $iframe_url = $result[1][0];

                        if (strpos($iframe_url, 'youtube') != false) {
                            $yt_params_url = $iframe_url . '?&autoplay=0&rel=0';
                            $home_slider['landswpr_slides'][$slide_key]['landswpr_slide_has_social_movie'] = true;
                            $home_slider['landswpr_slides'][$slide_key]['landswpr_slide_social_movie'] = str_replace($iframe_url, $yt_params_url, $slide['landswpr_slide_social_movie']);
                        }
                    }
                }
            }

            $home_slider = apply_filters('woody_format_homeslider_data', $home_slider);

            return \Timber::compile($woody_components[$home_slider['landswpr_woody_tpl']], $home_slider);
        }
    }

    public function formatBookBlock($post, $woody_components)
    {
        $bookblock = [];
        $bookblock = getAcfGroupFields('group_5c0e4121ee3ed', $post);

        if (!empty($bookblock['bookblock_playlists'][0]['pl_post_id'])) {
            $bookblock['the_classes'] = [];
            $bookblock['the_classes'][] = (!empty($bookblock['bookblock_bg_params']['background_img_opacity'])) ? $bookblock['bookblock_bg_params']['background_img_opacity'] : '';
            $bookblock['the_classes'][] = (!empty($bookblock['bookblock_bg_params']['background_color'])) ? $bookblock['bookblock_bg_params']['background_color'] : '';
            $bookblock['the_classes'][] = (!empty($bookblock['bookblock_bg_params']['background_color_opacity'])) ? $bookblock['bookblock_bg_params']['background_color_opacity'] : '';
            $bookblock['the_classes'][] = (!empty($bookblock['bookblock_bg_params']['border_color'])) ? $bookblock['bookblock_bg_params']['border_color'] : '';
            $bookblock['the_classes'][] = (!empty($bookblock['bookblock_bg_params']['background_img'])) ? 'isRel' : '';
            if (!empty($bookblock['bookblock_bg_params']['background_img_opacity']) || !empty($bookblock['bookblock_bg_params']['background_color']) || !empty($bookblock['bookblock_bg_params']['border_color'])) {
                $bookblock['the_classes'][] = 'padd-all-md';
            }
            $bookblock['classes'] = (!empty($bookblock['the_classes'])) ? implode(' ', $bookblock['the_classes']) : '';
            if (!empty($bookblock['bookblock_playlists'])) {
                foreach ($bookblock['bookblock_playlists'] as $pl_key => $pl) {
                    $bookblock['bookblock_playlists'][$pl_key]['permalink'] = woody_get_permalink($pl['pl_post_id']);
                    $pl_confId = get_field('field_5b338ff331b17', $pl['pl_post_id']);
                    $bookblock['bookblock_playlists'][$pl_key]['pl_conf_id'] = $pl_confId;
                    if (!empty($pl_confId)) {
                        $pl_lang = pll_get_post_language($pl['pl_post_id']);
                        $pl_params = apply_filters('woody_hawwwai_playlist_render', $pl_confId, $pl_lang, [], 'json');
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

            $bookblock['texts'] = apply_filters('woody_bookblock_custom_texts', [
                    'pl_select' => __('Que voulez-vous réserver ?', 'woody-theme'),
                    'pl_default_option' => false,
                    'pl_default_option_text' => __('Que voulez-vous réserver ?', 'woody-theme'),
                    'daterange_input' => __('Choisissez vos dates de réservation', 'woody-theme'),
                    'single_date_input' => __('Choisissez vos dates de réservation', 'woody-theme'),
                    'duration_select' => __('Durée du séjour', 'woody-theme'),
                    'duration_default_option' => __('Durée du séjour', 'woody-theme'),
                    'placeholders' => [
                        'daterange_input' => __('Choisissez vos dates', 'woody-theme'),
                        'single_date_input' => __('Date d\'arrivée', 'woody-theme')
                    ],
                    'adults' => __('adulte(s)', 'woody-theme'),
                    'children' => __('enfant(s)', 'woody-theme'),
                    'search' => __('Rechercher', 'woody-theme')
                ]
            );

            return \Timber::compile($woody_components[$bookblock['bookblock_woody_tpl']], $bookblock);
        }
    }
}
