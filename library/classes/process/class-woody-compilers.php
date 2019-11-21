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
        $this->tools = new WoodyTheme_WoodyProcessTools;
        $this->getter = new WoodyTheme_WoodyGetters;
        $this->registerHooks();
    }

    public function registerHooks()
    {
        add_action('save_post', [$this, 'cleanListFiltersTransients']);
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

        if ($wrapper['acf_fc_layout'] == 'manual_focus' || $wrapper['acf_fc_layout'] == 'focus_trip_components') {
            $the_items = $this->getter->getManualFocusData($wrapper);
        } elseif ($wrapper['acf_fc_layout'] == 'auto_focus') {
            $the_items = $this->getter->getAutoFocusData($current_post, $wrapper);
        } elseif ($wrapper['acf_fc_layout'] == 'auto_focus_sheets' && !empty($wrapper['playlist_conf_id'])) {
            $the_items = $this->getter->getAutoFocusSheetData($wrapper);
        } elseif ($wrapper['acf_fc_layout'] == 'auto_focus_topics') {
            $the_items = $this->getter->getAutoFocusTopicsData($wrapper);
        }

        if (!empty($the_items) && !empty($the_items['items']) && is_array($the_items['items'])) {
            foreach ($the_items['items'] as $item_key => $item) {
                if (!empty($item['description'])) {
                    $the_items['items'][$item_key]['description'] = str_replace(['[', ']'], '', $item['description']);
                }
            }

            $the_items['no_padding'] = (!empty($wrapper['focus_no_padding'])) ? $wrapper['focus_no_padding'] : '';
            $the_items['block_titles'] = $this->tools->getFocusBlockTitles($wrapper);
            $the_items['display_button'] = (!empty($wrapper['display_button'])) ? $wrapper['display_button'] : false;
            $the_items['display_img'] = (!empty($wrapper['display_img'])) ? $wrapper['display_img'] : false;
            $the_items['default_marker'] = $wrapper['default_marker'];
            $the_items['visual_effects'] = $wrapper['visual_effects'];


            if (!empty($wrapper['focus_map_params'])) {
                if (!empty($wrapper['focus_map_params']['tmaps_confid'])) {
                    $the_items['map_params']['tmaps_confid'] = $wrapper['focus_map_params']['tmaps_confid'];
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

            $return = Timber::compile($twigPaths[$wrapper['woody_tpl']], $the_items);
        }

        return $return;
    }

    /**
     * Create pagination if needed
     * @param   layout - Tableau des données du champ acf
     * @param   twigPaths - Liste des templates woody sous forme de tableau
     * @return  return - Code html
     */
    public function formatGeomapData($wrapper, $twigPaths)
    {
        $return = '';
        if (empty($wrapper['markers']) && empty($wrapper['routes'])) {
            return;
        }

        if (!empty($wrapper['routes'])) {
            foreach ($wrapper['routes'] as $key => $route) {
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

                    $wrapper['routes'][$key] = json_encode($wrapper['routes'][$key]);
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
                $wrapper['markers'][$key]['compiled_marker']  = Timber::compile('/_objects/markerObject.twig', $marker);

                if (!empty($marker['title']) || !empty($marker['description']) || !empty($marker['img'])) {
                    $the_marker['item']['title'] = (!empty($marker['title'])) ? $marker['title'] : '';
                    $the_marker['item']['description'] = (!empty($marker['description'])) ? $marker['description'] : '';
                    if (!empty($marker['img'])) {
                        $the_marker['image_style'] = 'ratio_16_9';
                        $the_marker['item']['img'] = $marker['img'];
                    }
                    $the_marker['item']['link'] = (!empty($marker['link'])) ? $marker['link'] : '';
                    $wrapper['markers'][$key]['marker_thumb_html']  = Timber::compile($twigPaths['cards-geomap_card-tpl_01'], $the_marker);
                }
            }
        }

        $return = Timber::compile($twigPaths[$wrapper['woody_tpl']], $wrapper);
        return $return;
    }

    function formatSemanticViewData($wrapper, $twigPaths)
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

            $the_query = [
                'post_type' => 'page',
                'post_parent' => $parent_id,
                'post__not_in' => [$post_id, $front_id],
                'tax_query' => (!empty($tax_query)) ? $tax_query : ''
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
                $post = Timber::get_post($post->ID);
                $data = getPagePreview($wrapper, $post);
                if (!empty($data['description'])) {
                    preg_match_all("/\[[^\]]*\]/", $data['description'], $matches);
                    if (!empty($matches[0])) {
                        foreach ($matches[0] as $match) {
                            $str = str_replace(['[', ']'], '', $match);
                            $link = '<a href="' . get_permalink(pll_get_post($post->ID)) . '">' . $str . '</a>';
                            $data['description'] = str_replace($match, $link, $data['description']);
                        }
                    }
                }
                $the_items['items'][$key] = $data;
            }
        }

        $return = Timber::compile($twigPaths[$wrapper['woody_tpl']], $the_items);

        return $return;
    }

    public function formatListContent($wrapper, $current_post, $twigPaths)
    {
        $return = '';

        // On définit des variables de base
        $the_list['permalink'] = get_permalink($current_post->ID);
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
        // On récupère les items par défaut et on les stocke dans un transient pour les passer aux filtres
        $transient_name = 'list_filters__post_' . $current_post->ID . '_' . $wrapper['uniqid'];
        $default_items = get_transient($transient_name);
        if (empty($default_items)) {
            $default_items = $this->getter->getAutoFocusData($current_post, $list_el_wrapper, $paginate, $wrapper['uniqid'], true);
            set_transient($transient_name, $default_items);

            // On crée/update l'option qui liste les transients pour pouvoir les supprimer lors d'un save_post
            $transient_list = get_option('list_filters_cache');
            if (empty($transient_list)) {
                add_option('list_filters_cache', [$transient_name]);
            } else {
                $transient_list[] = $transient_name;
                update_option('list_filters_cache', $transient_list);
            }
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
                        $list_el_wrapper['filtered_taxonomy_terms'][$result_key][] = $single_value;
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

        // On compile la grille des éléments
        $the_list['the_grid'] = Timber::compile($twigPaths[$wrapper['the_list_elements']['listgrid_woody_tpl']], $the_items);

        // On récupère le nombre de résultats
        $the_list = $this->tools->countFocusResults($the_items, $the_list);

        // Récupère la donnée des filtres de base
        if (!empty($wrapper['the_list_filters'])) {
            $the_list['filters'] = $this->getter->getListFilters($wrapper['the_list_filters'], $list_el_wrapper, $default_items);
            // Si on a trouvé un filtre de carte, on remplit le tableau the_map
            if (isset($the_list['filters']['the_map'])) {
                $map_items = $this->getter->getAutoFocusData($current_post, $list_el_wrapper, $paginate, $wrapper['uniqid'], true, $default_items_ids, $wrapper['the_list_filters']);

                $the_list['filters']['the_map'] = $this->formatListMapFilter($map_items, $wrapper['default_marker'], $twigPaths);
                $the_list['has_map'] = true;
            }
        }

        // Récupère la pagination compilée
        if ($paginate && !empty($the_items['max_num_pages'])) {
            $the_list['pager'] = $this->formatListPager($the_items['max_num_pages'], $wrapper['uniqid'], $list_el_wrapper['seed']);
        }

        $return = Timber::compile($twigPaths[$wrapper['the_list_filters']['listfilter_woody_tpl']], $the_list);
        return $return;
    }

    public function cleanListFiltersTransients()
    {
        $transient_list = get_option('list_filters_cache');
        if (!empty($transient_list)) {
            foreach ($transient_list as $transient) {
                delete_transient($transient);
            }
        }
        delete_option('list_filters_cache');
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

                    $return['markers'][] = [
                        'map_position' => [
                            'lat' => $item['location']['lat'],
                            'lng' => $item['location']['lng']
                        ],
                        'compiled_marker' => $marker,
                        'marker_thumb_html' => Timber::compile($twigPaths['cards-geomap_card-tpl_01'], $the_marker)
                    ];
                }
            }
        }
        return $return;
    }
}
