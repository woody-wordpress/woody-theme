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
    }
    /**
     *
     * Nom : formatFocusesData
     * Auteur : Benoit Bouchaud
     * Return : Retourne le html d'une mise en avant de contenu
     * @param    layout Le wrapper du champ de mise en avant
     * @param    current_post le post courant (pour les autofocus hierarchiques)
     * @param    twigPaths les chemins des templates woody
     * @return   items - Un tableau de données
     *
     */
    public function formatFocusesData($layout, $current_post, $twigPaths)
    {
        $return = '';
        $the_items = [];

        if ($layout['acf_fc_layout'] == 'manual_focus' || $layout['acf_fc_layout'] == 'focus_trip_components') {
            $the_items = $this->getter->getManualFocusData($layout);
        } elseif ($layout['acf_fc_layout'] == 'auto_focus') {
            $the_items = $this->getter->getAutoFocusData($current_post, $layout);
        } elseif ($layout['acf_fc_layout'] == 'auto_focus_sheets' && !empty($layout['playlist_conf_id'])) {
            $the_items = $this->getter->getAutoFocusSheetData($layout);
        } elseif ($layout['acf_fc_layout'] == 'auto_focus_topics') {
            $the_items = $this->getter->getAutoFocusTopicsData($layout);
        }

        if (!empty($the_items) && !empty($the_items['items']) && is_array($the_items['items'])) {
            foreach ($the_items['items'] as $item_key => $item) {
                if (!empty($item['description'])) {
                    $the_items['items'][$item_key]['description'] = str_replace(['[', ']'], '', $item['description']);
                }
            }

            $the_items['no_padding'] = (!empty($layout['focus_no_padding'])) ? $layout['focus_no_padding'] : '';
            $the_items['block_titles'] = $this->tools->getFocusBlockTitles($layout);
            $the_items['display_button'] = (!empty($layout['display_button'])) ? $layout['display_button'] : '';
            $the_items['default_marker'] = $layout['default_marker'];
            $the_items['visual_effects'] = $layout['visual_effects'];


            if (!empty($layout['focus_map_params'])) {
                if (!empty($layout['focus_map_params']['tmaps_confid'])) {
                    $the_items['map_params']['tmaps_confid'] = $layout['focus_map_params']['tmaps_confid'];
                }
                if (!empty($layout['focus_map_params']['map_height'])) {
                    $the_items['map_params']['map_height'] = $layout['focus_map_params']['map_height'];
                }
                if (!empty($layout['focus_map_params']['map_zoom_auto'])) {
                    $the_items['map_params']['map_zoom_auto'] = $layout['focus_map_params']['map_zoom_auto'];
                }
                if (!empty($layout['focus_map_params']['map_zoom'])) {
                    if (empty($the_items['map_params']['map_zoom_auto']) || $the_items['map_params']['map_zoom_auto'] === false) {
                        $the_items['map_params']['map_zoom'] = $layout['focus_map_params']['map_zoom'];
                    }
                }
            }

            $return = \Timber::compile($twigPaths[$layout['woody_tpl']], $the_items);
        }

        return $return;
    }

    /**
     * Create pagination if needed
     * @param   max_num_pages
     * @param   uniqid          section id of list content
     * @return  return          pagination html elements
     */
    public function formatListPager($max_num_pages, $uniqid, $filters = false)
    {
        $return = [];
        $explode_uniqid = explode('_', $uniqid);
        $the_page_name = 'section_' . $explode_uniqid[1] . '_' . $explode_uniqid[4];
        $get_the_page = (!empty($_GET[$the_page_name])) ? htmlentities(stripslashes($_GET[$the_page_name])) : 1;

        $pager_args = [
            'total' => $max_num_pages,
            'format' => '?' . $the_page_name . '=%#%#' . $uniqid,
            'current' => $get_the_page,
            'mid_size' => 3,
            'type' => 'list',
            'add_args' => $filters
        ];

        $return = paginate_links($pager_args);
        return $return;
    }

    /**
     * Create pagination if needed
     * @param   layout - Tableau des données du champ acf
     * @param   twigPaths - Liste des templates woody sous forme de tableau
     * @return  return - Code html
     */
    public function formatGeomapData($layout, $twigPaths)
    {
        $return = '';
        if (empty($layout['markers']) && empty($layout['routes'])) {
            return;
        }

        if (!empty($layout['routes'])) {
            foreach ($layout['routes'] as $key => $route) {
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

                    $layout['routes'][$key] = json_decode($route['route_file'], true);
                    foreach ($layout['routes'][$key]['features'] as $f_key => $feature) {
                        $layout['routes'][$key]['features'][$f_key]['route'] = true;

                        if ($parameters === true) {
                            $layout['routes'][$key]['features'][$f_key]['properties']['fill'] = $fill_color;
                            $layout['routes'][$key]['features'][$f_key]['properties']['stroke'] = $route_color;
                            $layout['routes'][$key]['features'][$f_key]['properties']['stroke-width'] = $stroke_thickness;
                        }
                        $fill_opacity = isset($layout['routes'][$key]['features'][$f_key]['properties']['fill-opacity']) ? $layout['routes'][$key]['features'][$f_key]['properties']['fill-opacity'] : 0;
                        $layout['routes'][$key]['features'][$f_key]['properties']['fill-opacity'] = $fill_opacity == 0 ? 0.5 : $fill_opacity;

                        // Route Fields aren't supposed to have markers.
                        if ($feature['geometry']['type'] == "Point") {
                            unset($layout['routes'][$key]['features'][$f_key]);
                        }
                    }

                    $layout['routes'][$key] = json_encode($layout['routes'][$key]);
                }
            }
        }

        if (!empty($layout['markers'])) {
            // Set boolean to fitBounds
            $layout['map_zoom_auto'] = ($layout['map_zoom_auto']) ? 'true' : 'false';

            // Calcul center of map
            $sum_lat = $sum_lng = 0;
            foreach ($layout['markers'] as $key => $marker) {
                if (!empty($marker['map_position']['lat'])) {
                    $sum_lat += $marker['map_position']['lat'];
                }
                if (!empty($marker['map_position']['lng'])) {
                    $sum_lng += $marker['map_position']['lng'];
                }
            }
            $layout['default_lat'] = $sum_lat / count($layout['markers']);
            $layout['default_lng'] = $sum_lng / count($layout['markers']);

            // Get markers
            foreach ($layout['markers'] as $key => $marker) {
                $the_marker = [];
                $marker['default_marker'] = $layout['default_marker'];
                if (empty($marker['title']) && empty($marker['description']) && empty($marker['img']) && !empty($marker['link']['url'])) {
                    $layout['markers'][$key]['marker_as_link'] = true;
                }
                $layout['markers'][$key]['compiled_marker']  = \Timber::compile('/_objects/markerObject.twig', $marker);

                if (!empty($marker['title']) || !empty($marker['description']) || !empty($marker['img'])) {
                    $the_marker['item']['title'] = (!empty($marker['title'])) ? $marker['title'] : '';
                    $the_marker['item']['description'] = (!empty($marker['description'])) ? $marker['description'] : '';
                    if (!empty($marker['img'])) {
                        $the_marker['image_style'] = 'ratio_16_9';
                        $the_marker['item']['img'] = $marker['img'];
                    }
                    $the_marker['item']['link'] = (!empty($marker['link'])) ? $marker['link'] : '';
                    $layout['markers'][$key]['marker_thumb_html']  = \Timber::compile($twigPaths['cards-geomap_card-tpl_01'], $the_marker);
                }
            }
        }

        $return = \Timber::compile($twigPaths[$layout['woody_tpl']], $layout);
        return $return;
    }
}
