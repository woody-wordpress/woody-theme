<?php

function getComponentItem($layout, $context)
{
    $return = '';
    $layout['default_marker'] = $context['default_marker'];
    // Traitements spécifique en fonction du type de layout
    switch ($layout['acf_fc_layout']) {
        case 'manual_focus':
        case 'auto_focus':
        case 'auto_focus_sheets':
        case 'focus_trip_components':
            $return = formatFocusesData($layout, $context['post'], $context['woody_components']);
            break;
        case 'geo_map':
            $return = formatGeomapData($layout, $context['woody_components']);
            break;
        case 'content_list':
            $return = formatFullContentList($layout, $context['post'], $context['woody_components']);
            break;
        case 'weather':
            $vars['account'] = $layout['weather_account'];
            $vars['nb_days'] = $layout['weather_count_days'];
            $the_weather = apply_filters('woody_weather', $vars);
            $the_weather['bg_color'] = (!empty($layout['weather_bg_params']['background_color'])) ? $layout['weather_bg_params']['background_color'] : '';
            $the_weather['bg_img'] = $layout['weather_bg_img'];
            $return = Timber::compile($context['woody_components'][$layout['woody_tpl']], $the_weather);
            break;
        case 'call_to_action':
            // TODO: Case à enlever lorsque les "Anciens champs" seront supprimés du backoffice (utile pour les anciens liens de CTA uniquement)
            $layout['modal_id'] = uniqid($layout['acf_fc_layout'] . '_');
            $return = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
            break;
        case 'gallery':
            // Ajout des données Instagram + champs personnaliés dans le contexte des images
            if (!empty($layout['gallery_items'])) {
                foreach ($layout['gallery_items'] as $key => $media_item) {
                    $layout['gallery_items'][$key]['attachment_more_data'] = getAttachmentMoreData($media_item['ID']);
                }
            }
            $return = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
            break;
        case 'links':
            $layout['woody_tpl'] = 'blocks-links-tpl_01';
            $return = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
            break;
        case 'tabs_group':
            $layout['tabs'] = nestedGridsComponents($layout['tabs'], 'tab_woody_tpl', 'tabs', $context);
            $return = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
            break;
        case 'slides_group':
            $layout['slides'] = nestedGridsComponents($layout['slides'], 'slide_woody_tpl', 'slides', $context);
            $return = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
            break;
        case 'socialwall':
            $layout['gallery_items'] = [];
            if ($layout['socialwall_type'] == 'manual') {
                foreach ($layout['socialwall_manual'] as $key => $media_item) {
                    // On ajoute une entrée "gallery_items" pour être compatible avec le tpl woody
                    $layout['gallery_items'][] = $media_item;
                    $layout['gallery_items'][$key]['attachment_more_data'] = getAttachmentMoreData($media_item['ID']);
                }
            } elseif ($layout['socialwall_type'] == 'auto') {
                // On récupère les images en fonction des termes sélectionnés
                $layout['gallery_items'] = (!empty($layout['socialwall_auto'])) ? getAttachmentsByTerms('attachment_hashtags', $layout['socialwall_auto']) : '';
                if (!empty($layout['gallery_items'])) {
                    foreach ($layout['gallery_items'] as $key => $media_item) {
                        $layout['gallery_items'][$key]['attachment_more_data'] = getAttachmentMoreData($media_item['ID']);
                    }
                }
            }
            $return = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
            break;
        case 'semantic_view':
            $layout['items'] = getSemanticViewData($layout);
            $return = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
            break;
        default:
            $return = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
    }
    return $return;
}

function getSemanticViewData($layout)
{
    $return = [];

    if ($layout['semantic_view_type'] == 'sisters') {
        $parent_id = wp_get_post_parent_id($layout['post']['ID']);
    } else {
        $parent_id = $layout['post']['ID'];
    }

    if (!empty($layout['semantic_view_page_types'])) {
        $tax_query = [
            'relation' => 'AND',
            'page_type' => array(
                'taxonomy' => 'page_type',
                'terms' => $layout['semantic_view_page_types'],
                'field' => 'term_id',
                'operator' => 'IN'
            ),
        ];
    }

    $the_query = [
        'post_type' => 'page',
        'post_parent' => $parent_id,
        'post__not_in' => [$layout['post']['ID']]
    ];

    $the_query['tax_query'] = (!empty($tax_query)) ? $tax_query : '';


    $query_result = new WP_query($the_query);

    if (!empty($query_result->posts)) {
        foreach ($query_result->posts as $key => $post) {
            $data = [];
            $post = Timber::get_post($post->ID);
            $data = getPagePreview($layout, $post);
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
            $return[$key] = $data;
        }
    }

    return $return;
}


/**
 *
 * Nom : getAutoFocus_data
 * Auteur : Benoit Bouchaud
 * Return : Retourne un ensemble de posts avec une donnée compatbile Woody
 * @param    the_post - Un objet Timber\Post
 * @param    query_form - Champs de formulaire permettant de monter la query
 * @return   the_items - Un tableau de données
 *
 */
function getAutoFocus_data($the_post, $query_form, $paginate = false, $uniqid = 0, $ignore_maxnum = false)
{
    $the_items = [];
    $tax_query = [];


    // Création du paramètre tax_query pour la wp_query
    // Référence : https://codex.wordpress.org/Class_Reference/WP_Query
    if (!empty($query_form['focused_content_type'])) {
        $tax_query = [
            'relation' => 'AND',
            'page_type' => array(
                'taxonomy' => 'page_type',
                'terms' => $query_form['focused_content_type'],
                'field' => 'term_id',
                'operator' => 'IN'
            ),
        ];
    }

    // Si des termes ont été choisi pour filtrer les résultats
    // on créé tableau custom_tax à passer au paramètre tax_query
    $custom_tax = [];
    if (!empty($query_form['focused_taxonomy_terms'])) {

        // On récupère la relation choisie (ET/OU) entre les termes
        // et on génère un tableau de term_id pour chaque taxonomie
        $tax_query['custom_tax']['relation'] = (!empty($query_form['focused_taxonomy_terms_andor'])) ? $query_form['focused_taxonomy_terms_andor'] : 'OR';
        foreach ($query_form['focused_taxonomy_terms'] as $focused_term_key => $focused_term) {
            $term = get_term($focused_term);
            $custom_tax[$term->taxonomy][] = $focused_term;
        }
        foreach ($custom_tax as $taxo => $terms) {
            $tax_query['custom_tax'][] = array(
                'taxonomy' => $taxo,
                'terms' => $terms,
                'field' => 'term_id',
                'operator' => 'IN'
            );
        }
    }

    // Si l'on trouve des filtres dans le formulaire
    if (!empty($query_form['filters_apply'])) {
        foreach ($query_form['filters_apply'] as $filter_key => $filter) {

            // On ajoute des paramètres de taxonomies à la query
            if (strpos($filter_key, 'taxonomy_terms') !== false) {
                $tax_query[$filter_key] = [];
                $tax_query[$filter_key]['relation'] = $filter['andor'];
                if (!is_array($filter['terms'])) {
                    $term = get_term($filter['terms']);
                    $filter_tax[$filter_key][$term->taxonomy][] = $filter['terms'];
                } else {
                    foreach ($filter['terms'] as $focused_term) {
                        $term = get_term($focused_term);
                        $filter_tax[$filter_key][$term->taxonomy][] = $focused_term;
                    }
                }

                foreach ($filter_tax[$filter_key] as $taxo => $terms) {
                    $tax_query[$filter_key][] = array(
                        'taxonomy' => $taxo,
                        'terms' => $terms,
                        'field' => 'term_id',
                        'operator' => 'IN'
                    );
                }
                // On ajoute des paramètres de meta_query à la query
            } elseif (strpos($filter_key, 'filter_trip_price') !== false) {
                $the_meta_query[] = [
                    'key'        => 'the_price_price',
                    'value'        => $filter['min'],
                    'type'      => 'NUMERIC',
                    'compare'    => '>='
                ];
                $the_meta_query[] = [
                    'key'        => 'the_price_price',
                    'value'        => $filter['max'],
                    'type'      => 'NUMERIC',
                    'compare'    => '<='
                ];
            } elseif (strpos($filter_key, 'filter_trip_duration') !== false) {
                $the_meta_query[] = [
                    'key'        => 'the_duration_count_days',
                    'value'        => $filter['min'],
                    'type'      => 'NUMERIC',
                    'compare'    => '>='
                ];
                $the_meta_query[] = [
                    'key'        => 'the_duration_count_days',
                    'value'        => $filter['max'],
                    'type'      => 'NUMERIC',
                    'compare'    => '<='
                ];
            }
        }
    }

    switch ($query_form['focused_sort']) {
        case 'random':
            $orderby = 'rand';
            $order = 'ASC';
            break;
        case 'created_desc':
            $orderby = 'post_date';
            $order = 'DESC';
            break;
        case 'created_asc':
            $orderby = 'post_date';
            $order = 'ASC';
            break;
        case 'menu_order':
            $orderby = 'menu_order';
            $order = 'ASC';
            break;
        default:
    }

    if ($orderby == 'rand' && $paginate == true) {
        $seed = date("dmY");
        $orderby = 'RAND(' . $seed . ')';
    }

    // On créé la wp_query en fonction des choix faits dans le backoffice
    // NB : si aucun choix n'a été fait, on remonte automatiquement tous les contenus de type page
    $the_query = [
        'post_type' => 'page',
        'posts_per_page' => (!empty($query_form['focused_count'])) ? $query_form['focused_count'] : 16,
        'post_status' => 'publish',
        'post__not_in' => array($the_post->ID),
        'order' => $order,
        'orderby' => $orderby,
    ];

    if ($ignore_maxnum === true) {
        $the_query['posts_per_page'] = -1;
    }

    if ($paginate == true) {
        $explode_uniqid = explode('_', $uniqid);
        $the_page_name = 'section_' . $explode_uniqid[1] . '_' . $explode_uniqid[4];
        $the_page = (!empty($_GET[$the_page_name])) ? htmlentities(stripslashes($_GET[$the_page_name])) : '';
        $the_query['paged'] = (!empty($the_page)) ? $the_page : 1;
    }

    $the_query['tax_query'] = (!empty($tax_query)) ? $tax_query : '';

    // Si Hiérarchie = Enfants directs de la page
    // On passe le post ID dans le paramètre post_parent de la query
    if ($query_form['focused_hierarchy'] == 'child_of') {
        $the_query['post_parent'] = $the_post->ID;
    }

    // Si Hiérarchie = Pages de même niveau
    // On passe le parent_post_ID dans le paramètre post_parent de la query
    if ($query_form['focused_hierarchy'] == 'brother_of') {
        $the_query['post_parent'] = $the_post->post_parent;
    }

    // Si on trouve une metaquery (recherche sur champs ACF)
    // On définit une relation AND par défaut
    if (!empty($the_meta_query)) {
        $the_meta_query_relation = [
            'relation' => 'AND'
        ];
        $the_query['meta_query'] = array_merge($the_meta_query_relation, $the_meta_query);
    }

    // On créé la wp_query avec les paramètres définis
    $query_result = new WP_Query($the_query);

    // On transforme la donnée des posts récupérés pour coller aux templates de blocs Woody
    if (!empty($query_result->posts)) {
        foreach ($query_result->posts as $key => $post) {

            // On vérifie si la page est de type miroir
            $page_type = get_the_terms($post->ID, 'page_type');
            if ($page_type[0]->slug == 'mirror_page') {
                $mirror = get_field('mirror_page_reference', $post->ID);
                if (!empty(get_post($mirror))) {
                    $post = get_post($mirror);
                }
            }

            $data = [];
            $post = Timber::get_post($post->ID);
            $data = getPagePreview($query_form, $post);

            // $data['link']['title'] = (!empty($query_form['links_label'])) ? $query_form['links_label'] : '';
            $the_items['items'][$key] = $data;
        }
        $the_items['max_num_pages'] = $query_result->max_num_pages;
        $the_items['wp_query'] = $query_result;
    }

    return $the_items;
}

/**
 *
 * Nom : getManualFocus_data
 * Auteur : Benoit Bouchaud
 * Return : Retourne un ensemble de posts avec une donnée compatbile Woody
 * @param    items - Tous les contenus crées ou sélectionnés dans une sélectio manuelle
 * @return   the_items - Un tableau de données
 *
 */
function getManualFocus_data($layout)
{
    $the_items = [];
    $clickable = true;
    foreach ($layout['content_selection'] as $key => $item_wrapper) {
        $item_wrapper['content_selection_type'] = $layout['acf_fc_layout'] == 'focus_trip_components' ? 'existing_content' : $item_wrapper['content_selection_type'];
        if (!empty($item_wrapper['existing_content']['trip_component'])) {
            $item_wrapper['existing_content']['content_selection'] = $item_wrapper['existing_content']['trip_component'];
            $clickable = (!empty($item_wrapper['existing_content']['clickable_component'])) ? true : false;
        }

        // La donnée de la vignette est saisie en backoffice
        if ($item_wrapper['content_selection_type'] == 'custom_content' && !empty($item_wrapper['custom_content'])) {
            $the_items['items'][$key] = getCustomPreview($item_wrapper['custom_content'], $layout);
        // La donnée de la vignette correspond à un post sélectionné
        } elseif ($item_wrapper['content_selection_type'] == 'existing_content' && !empty($item_wrapper['existing_content']['content_selection'])) {
            $item = $item_wrapper['existing_content'];
            $status = $item['content_selection']->post_status;
            if ($status !== 'publish') {
                continue;
            }
            switch ($item['content_selection']->post_type) {
                case 'page':
                $post_preview = getPagePreview($layout, $item['content_selection'], $clickable);
                break;
                case 'touristic_sheet':
                $post_preview = getTouristicSheetPreview($layout, $item['content_selection']);
                break;
                case 'woody_topic':
                $post_preview = getTopicPreview($layout, $item['content_selection']);
                break;
            }
            $the_items['items'][$key] = (!empty($post_preview)) ?  $post_preview : '';
        }
    }

    if ($layout['focused_sort'] == 'random') {
        shuffle($the_items['items']);
    }

    return $the_items;
}

/**
 *
 * Nom : getAutoFocusSheetData
 * Auteur : Benoit Bouchaud
 * Return : Retourne un tableau de données relatives aux foches SIT
 * @param    confId L'identifiant de conf de la playlist
 * @return   items - Un tableau de données
 *
 */
function getAutoFocusSheetData($layout)
{
    $items = [];
    if (!empty($layout['playlist_conf_id'])) {
        $confId = $layout['playlist_conf_id'];
        $lang = pll_current_language();
        $playlist = apply_filters('woody_hawwwai_playlist_render', $confId, pll_current_language(), array(), 'json');
        if (!empty($playlist['items'])) {
            foreach ($playlist['items'] as $key => $item) {
                $wpSheetNode = apply_filters('woody_hawwwai_get_post_by_sheet_id', $item['sheetId'], $lang, ['publish']);
                //TODO: $wpSheetNode->getPost() retourne parfois un tableau. Dans ce cas, on récupère le 1ier objet à l'interieur - voir plugin
                if (!empty($wpSheetNode)) {
                    if (is_array($wpSheetNode)) {
                        $wpSheetNode = current($wpSheetNode);
                    }
                    $items['items'][] = getTouristicSheetPreview($layout, $wpSheetNode->getPost());
                }
            }
        }
    }

    return $items;
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
function formatFocusesData($layout, $current_post, $twigPaths)
{
    $return = '';
    $the_items = [];
    if ($layout['acf_fc_layout'] == 'manual_focus' || $layout['acf_fc_layout'] == 'focus_trip_components') {
        $the_items = getManualFocus_data($layout);
    } elseif ($layout['acf_fc_layout'] == 'auto_focus') {
        $the_items = getAutoFocus_data($current_post, $layout);
    } elseif ($layout['acf_fc_layout'] == 'auto_focus_sheets' && !empty($layout['playlist_conf_id'])) {
        $the_items = getAutoFocusSheetData($layout);
    }
    if (!empty($the_items)) {
        foreach ($the_items['items'] as $item_key => $item) {
            if (!empty($item['description'])) {
                $the_items['items'][$item_key]['description'] = str_replace(['[', ']'], '', $item['description']);
            }
        }

        $the_items['no_padding'] = (!empty($layout['focus_no_padding'])) ? $layout['focus_no_padding'] : '';
        $the_items['block_titles'] = getFocusBlockTitles($layout);
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

        $return = Timber::compile($twigPaths[$layout['woody_tpl']], $the_items);
    }

    return $return;
}

function getItems($current_post, $layout, $paginate)
{
    $the_items = getAutoFocus_data($current_post, $layout['the_list_elements']['list_el_req_fields'], $paginate, $layout['uniqid']);
    $the_items['no_padding'] = (!empty($layout['the_list_elements']['list_no_padding'])) ? $layout['the_list_elements']['list_no_padding'] : '';
    $the_items['display_button'] = (!empty($layout['the_list_elements']['list_el_req_fields']['display_button'])) ? $layout['the_list_elements']['list_el_req_fields']['display_button'] : '';

    return $the_items;
}

function updateListFilterTax($the_list, $filter_index, $param)
{
    foreach ($the_list['filters'][$filter_index]['list_filter_custom_terms'] as $filter_term_key => $filter_term) {
        if ($filter_term['value'] == $param) {
            $the_list['filters'][$filter_index]['list_filter_custom_terms'][$filter_term_key]['checked'] = true;
        }
    }

    return $the_list;
}

function getMinMax($post_data, $data_key)
{
    $minmax = [
        'min' => 0,
        'max' => ''
    ];
    if (strpos($data_key, 'max')) {
        $minmax['max'] = $post_data[$data_key];
        $minmax['min'] = !empty($post_data[str_replace('max', 'min', $data_key)]) ? $post_data[str_replace('max', 'min', $data_key)] : 0;
    } else {
        $minmax['min'] = $post_data[$data_key];
        $minmax['max'] = isset($post_data[str_replace('min', 'max', $data_key)]) ? $post_data[str_replace('min', 'max', $data_key)] : '';
    }

    return $minmax;
}

function formatFullContentList($layout, $current_post, $twigPaths)
{
    $the_list = [];
    $the_list['permalink'] = get_permalink($current_post->ID);
    $the_list['uniqid'] = $layout['uniqid'];
    $the_list['has_map'] = false;
    $the_list['filters'] = !empty($layout['the_list_filters']) && !empty($layout['the_list_filters']['list_filters']) ? $layout['the_list_filters']['list_filters'] : [];
    $paginate = ($layout['the_list_pager']['list_pager_type'] == 'basic_pager') ? true : false;
    $url_parameters['filters'] = false;
    $the_items = getItems($current_post, $layout, $paginate);

    // Handle filters :
    if (!empty($the_list['filters'])) {
        // TAXONOMY | DURATION | PRICE | CUSTOM TERM
        foreach ($the_list['filters'] as $key => $filter) {
            switch ($filter['list_filter_type']) {
                case 'taxonomy':
                    $taxonomy = $filter['list_filter_taxonomy'];
                    $terms = get_terms($taxonomy, ['hide_empty' => false]);
                    foreach ($terms as $term_key => $term) {
                        $the_list['filters'][$key]['list_filter_custom_terms'][] = [
                            'value' => $term->term_id,
                            'label' => $term->name
                        ];
                    }
                    break;

                case 'custom_terms':
                    foreach ($filter['list_filter_custom_terms'] as $term_key => $term) {
                        $term = get_term($term['value']);
                        $the_list['filters'][$key]['list_filter_custom_terms'][$term_key] = [
                            'value' => $term->term_id,
                            'label' => $term->name
                        ];
                    }
                    break;

                case 'price':
                case 'duration':
                    $field = $filter['list_filter_type'] == 'price' ? 'the_price_price' : 'the_duration_count_days';
                    $the_list['filters'][$key]['minmax']['max'] = getMinMaxWoodyFieldValues($the_items['wp_query']->query_vars, $field);
                    $the_list['filters'][$key]['minmax']['min'] = getMinMaxWoodyFieldValues($the_items['wp_query']->query_vars, $field, 'min');
                    break;
            }
        }
        $the_list['filters']['button'] = (!empty($layout['the_list_filters']['filter_button'])) ? $layout['the_list_filters']['filter_button'] : '';
        $the_list['filters']['reset'] = (!empty($layout['the_list_filters']['reset_button'])) ? $layout['the_list_filters']['reset_button'] : '';
        $the_list['filters']['display']['background_img'] = (!empty($layout['the_list_filters']['background_img'])) ? $layout['the_list_filters']['background_img'] : '';
        $the_list['filters']['display']['classes'][] = (!empty($layout['the_list_filters']['background_color'])) ? $layout['the_list_filters']['background_color'] : '';
        $the_list['filters']['display']['classes'][] = (!empty($layout['the_list_filters']['background_img_opacity'])) ? $layout['the_list_filters']['background_img_opacity'] : '';
        $the_list['filters']['display']['classes'][] = (!empty($layout['the_list_filters']['border_color'])) ? $layout['the_list_filters']['border_color'] : '';
        $the_list['filters']['display']['classes'] = implode(' ', $the_list['filters']['display']['classes']);
    }

    // Handle POST DATA ( AJAX from filter-list.js )
    $post_data = filter_input_array(INPUT_POST);
    $url_parameters['filters'] = isset($post_data['filters']) ? getUrlParametersForContentList($post_data) : false;

    // If Click reset, need to know which section must be reset to default (default filters)
    // If changing page, need to get data to keep filters.
    // If click research, need to update data anf filters.
    $reset = isset($post_data['reset']) ? $post_data['reset'] : false;
    $section_reset = isset($post_data['section_reset']) ? $post_data['section_reset'] : false;
    if ($section_reset) {
        $post_data['section_reset'] = $section_reset;
    }
    if (null == $post_data) {
        $post_data = setDataFromGetParameters($layout);
    }
    $post_data['reset'] = $reset;

    if ($post_data) {
        if (!empty($post_data) && $post_data['reset'] != 1 && isset($post_data['uniqid']) && $post_data['uniqid'] == $layout['uniqid']) {
            foreach ($the_items['items'] as $key => $item) {
                // Check if item can pass through filters
                foreach ($post_data as $data_key => $data_values) {
                    if (strpos($data_key, 'taxonomy_terms') !== false) {
                        // Update layout values
                        $filter_index = str_replace('taxonomy_terms_', '', $data_key);
                        $andor = $layout['the_list_filters']['list_filters'][$filter_index]['list_filter_andor'];
                        $layout['the_list_elements']['list_el_req_fields']['filters_apply']['filter_' . $data_key]['andor'] = $andor;
                        $layout['the_list_elements']['list_el_req_fields']['filters_apply']['filter_' . $data_key]['terms'] = $data_values;

                        // Update filter value on load
                        if ($the_list['filters'][$filter_index]['list_filter_type'] == 'taxonomy' || $the_list['filters'][$filter_index]['list_filter_type'] == 'custom_terms') {
                            if (!is_array($data_values)) {
                                $the_list = updateListFilterTax($the_list, $filter_index, $data_values);
                            } else {
                                foreach ($data_values as $term_key => $term) {
                                    $the_list = updateListFilterTax($the_list, $filter_index, $term);
                                }
                            }
                        }
                    } elseif (strpos($data_key, 'trip_price') !== false) {
                        $filter_index = str_replace('trip_price_', '', $data_key);
                        $minmax = getMinMax($post_data, $data_key);
                        $replacement = strpos($filter_index, '_min') !== false ? '_min' : '_max';

                        // Update value
                        $filter_index = str_replace($replacement, '', $filter_index);
                        $the_list['filters'][$filter_index]['minmax']['default_min'] = round($minmax['min']);
                        $layout['the_list_elements']['list_el_req_fields']['filters_apply']['filter_trip_price' . $filter_index]['min'] = $minmax['min'];
                        $the_list['filters'][$filter_index]['minmax']['default_max'] = round($minmax['max']);
                        $layout['the_list_elements']['list_el_req_fields']['filters_apply']['filter_trip_price' . $filter_index]['max'] = $minmax['max'];
                    } elseif (strpos($data_key, 'trip_duration') !== false) {
                        $filter_index = str_replace('trip_duration_', '', $data_key);
                        $minmax = getMinMax($post_data, $data_key);
                        $replacement = strpos($filter_index, '_min') !== false ? '_min' : '_max';

                        // Update value
                        $filter_index = str_replace($replacement, '', $filter_index);
                        $the_list['filters'][$filter_index]['minmax']['default_min'] = $minmax['min'];
                        $layout['the_list_elements']['list_el_req_fields']['filters_apply']['filter_trip_duration' . $filter_index]['min'] = $minmax['min'];
                        $the_list['filters'][$filter_index]['minmax']['default_max'] = $minmax['max'];
                        $layout['the_list_elements']['list_el_req_fields']['filters_apply']['filter_trip_duration' . $filter_index]['max'] = $minmax['max'];
                    }
                }
            }
            // URL GET parameters based on filters
        } else {
            $post_data = [];
        }
        $the_items = getItems($current_post, $layout, $paginate);
    }
    // Html Grid based on items
    $the_list['the_grid'] = Timber::compile($twigPaths[$layout['the_list_elements']['listgrid_woody_tpl']], $the_items);

    if (!empty($the_items['items']) && !empty($the_items['wp_query']->found_posts)) {
        $the_list['items_count'] = $the_items['wp_query']->found_posts;
        $the_list['items_count_type'] = $the_list['items_count'] > 1 ? 'plural' : 'singular';
    } else {
        $the_list['items_count_type'] = 'empty';
        $the_items = [
            'empty' => 'Désolé, aucun contenu ne correspond à votre recherche'
        ];
    }
    $the_items['max_num_pages'] = empty($the_items['max_num_pages']) ? 1 : $the_items['max_num_pages'];
    $max_num_pages = $the_items['max_num_pages'];

    $the_list['filters']['the_map'] = creatListMapFilter($current_post, $layout, $paginate, $the_list['filters'], $twigPaths);
    if (!empty($the_list['filters']['the_map'])) {
        foreach ($the_list['filters'] as $filter_key => $filter) {
            if (is_numeric($filter_key) && $filter['list_filter_type'] == 'map') {
                unset($the_list['filters'][$filter_key]);
            }
        }
        $the_list['has_map'] = true;
    } else {
        unset($the_list['filters']['the_map']);
    }

    if (!empty($layout['the_list_pager']) && $layout['the_list_pager']['list_pager_type'] != 'none') {
        $the_list['pager'] = formatListPager($layout['the_list_pager'], $max_num_pages, $the_list['uniqid'], $url_parameters['filters']);
        $the_list['pager_position'] = $layout['the_list_pager']['list_pager_position'];
    }

    $return = Timber::compile($twigPaths[$layout['the_list_filters']['listfilter_woody_tpl']], $the_list);
    return $return;
}

function creatListMapFilter($current_post, $layout, $paginate, $filters, $twigPaths)
{
    if (!empty($filters)) {
        foreach ($filters as $key => $filter) {
            if (is_numeric($key)) {
                if (isset($filter['list_filter_type']) && $filter['list_filter_type'] == 'map') {
                    $every_items = getAutoFocus_data($current_post, $layout['the_list_elements']['list_el_req_fields'], $paginate, $layout['uniqid'], true);
                    if (!empty($every_items['items'])) {
                        foreach ($every_items['items'] as $item) {
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

                                $filters[$key]['markers'][] = [
                                    'map_position' => [
                                        'lat' => $item['location']['lat'],
                                        'lng' => $item['location']['lng']
                                    ],
                                    'compiled_marker' => $layout['default_marker'],
                                    'marker_thumb_html' => Timber::compile($twigPaths['cards-geomap_card-tpl_01'], $the_marker)
                                ];
                            }
                        }
                    }
                    return $filters[$key];
                }
            }
        }
    }
}

/**
 * Create pagination if needed
 * @param   max_num_pages
 * @param   uniqid          section id of list content
 * @return  return          pagination html elements
 */
function formatListPager($pager_params, $max_num_pages, $uniqid, $filters = false)
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
 * Set data from parameters GET to keep filters when changing page
 * @param   uniqid  form data
 * @return  return  form data updated based on $_GET parameters
 */
function setDataFromGetParameters($layout)
{
    $return = [];
    $uniqid = $layout['uniqid'];
    $tax_index = 0;
    $price_index = 0;
    $duration_index = 0;

    if (!empty($layout['the_list_filters']) && !empty($layout['the_list_filters']['list_filters']) && is_array($layout['the_list_filters']['list_filters'])) {
        foreach ($layout['the_list_filters']['list_filters'] as $index => $filter) {
            if ($filter['list_filter_type'] == "taxonomy") {
                $tax_index = $index;
            } elseif ($filter['list_filter_type'] == "price") {
                $price_index = $index;
            } elseif ($filter['list_filter_type'] == "duration") {
                $duration_index = $index;
            }
        }
    }

    $params = filter_input_array(INPUT_GET);
    if (!empty($params)) {
        foreach ($params as $param_key => $param) {
            if ($param_key == $uniqid) {
                $return['uniqid'] = $uniqid;
                foreach ($param as $key => $values) {
                    switch ($key) {
                        case 'price':
                            $return['trip_price_' . $price_index . '_min'] = (float) $values['min'];
                            $return['trip_price_' . $price_index . '_max'] = (float) $values['max'];
                            break;
                        case 'duration':
                            $return['trip_duration_' . $duration_index . '_min'] = (float) $values['min'];
                            $return['trip_duration_' . $duration_index . '_max'] = (float) $values['max'];
                            break;
                        case 'terms':
                            $return['taxonomy_terms_' . $tax_index] = $values;
                            break;
                    }
                }
            }
        }
    }

    return $return;
}

/**
 * Format url parameters to send filters infos to other pages.
 * @param post_data data send by AJAX call (values of filter form)
 * @return return   (array)
 */
function getUrlParametersForContentList($post_data)
{
    $return = [];
    $post_data['section_reset'] = isset($post_data['section_reset']) ? $post_data['section_reset'] : false;

    foreach ($post_data['filters'] as $f_key => $filter) {
        if ($f_key != $post_data['section_reset']) {
            parse_str($filter, $values);

            $return[$values['uniqid']] = [];
            foreach ($values as $key => $value) {
                if (strpos($key, 'taxonomy_terms') !== false) {
                    $return[$values['uniqid']]['terms'] = $value;
                } elseif (strpos($key, 'trip_price') !== false) {
                    if (strpos($key, 'min') !== false) {
                        $return[$values['uniqid']]['price']['min'] = $value;
                    } else {
                        $return[$values['uniqid']]['price']['max'] = $value;
                    }
                } elseif (strpos($key, 'trip_duration') !== false) {
                    if (strpos($key, 'min') !== false) {
                        $return[$values['uniqid']]['duration']['min'] = $value;
                    } else {
                        $return[$values['uniqid']]['duration']['max'] = $value;
                    }
                }
            }
        }
    }

    foreach ($return as $key => $filter) {
        foreach ($filter as $v_key => $value) {
            if (empty($value)) {
                unset($return[$key][$v_key]);
            }
        }
    }

    return $return;
}

function formatGeomapData($layout, $twigPaths)
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
                        // TODO: choose if geojson files can be used to add markers to the map
                        // Code below add marker to Map
                        // $lng = !empty($feature['geometry']['coordinates'][0]) ? (string)$feature['geometry']['coordinates'][0] : "0" ;
                        // $lat = !empty($feature['geometry']['coordinates'][1]) ? (string)$feature['geometry']['coordinates'][1] : "0" ;
                        // $marker = [
                        //     'bo_marker_title' => '',
                        //     'description' => '',
                        //     'img' => false,
                        //     'link' => '',
                        //     'map_position' => [
                        //         'address' => $feature['properties']['name'],
                        //         'lat' => $lat,
                        //         'lng' => $lng
                        //     ],
                        //     'marker_color' => 'primary',
                        //     'marker_style' => 'pin',
                        //     'marker_woody_icon' => '',
                        //     'title' => ''
                        // ];
                        // $layout['markers'][] = $marker;

                        // Remove markers from map
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
            $layout['markers'][$key]['compiled_marker']  = Timber::compile('/_objects/markerObject.twig', $marker);

            if (!empty($marker['title']) || !empty($marker['description']) || !empty($marker['img'])) {
                $the_marker['item']['title'] = (!empty($marker['title'])) ? $marker['title'] : '';
                $the_marker['item']['description'] = (!empty($marker['description'])) ? $marker['description'] : '';
                if (!empty($marker['img'])) {
                    $the_marker['image_style'] = 'ratio_16_9';
                    $the_marker['item']['img'] = $marker['img'];
                }
                $the_marker['item']['link'] = (!empty($marker['link'])) ? $marker['link'] : '';
                $layout['markers'][$key]['marker_thumb_html']  = Timber::compile($twigPaths['cards-geomap_card-tpl_01'], $the_marker);
            }
        }
    }

    $return = Timber::compile($twigPaths[$layout['woody_tpl']], $layout);
    return $return;
}

/**
 *
 * Nom : getCustomPreview
 * Auteur : Benoit Bouchaud
 * Return : Retourne les données d'une preview basée sur des champs custom
 * @param    item - Un tableau de données (Vignette créée dans le backoffice - N'est pas directement liéée à un contenu existant)
 * @return   data - Un tableau de données
 *
 */
function getCustomPreview($item, $item_wrapper = null)
{
    $data = [];
    $data = [
        'title' => (!empty($item['title'])) ? getTransformedPattern($item['title']) : '',
        'pretitle' => (!empty($item['pretitle'])) ? getTransformedPattern($item['pretitle']) : '',
        'subtitle' => (!empty($item['subtitle'])) ? getTransformedPattern($item['subtitle']) : '',
        'icon_type' => (!empty($item['icon_type'])) ? $item['icon_type'] : '',
        'woody_icon' => (!empty($item['woody_icon'])) ? $item['woody_icon'] : '',
        'icon_img' => (!empty($item['icon_img']['url'])) ? [
            'sizes' => [
                'thumbnail' => $item['icon_img']['sizes']['medium']
            ],
            'alt' =>  $item['icon_img']['alt'],

        ] : '',
        'description' => (!empty($item['description'])) ? getTransformedPattern($item['description']) : '',
    ];

    if ($item['action_type'] == 'file' && !empty($item['file']['url'])) {
        $data['link'] = [
            'url' => (!empty($item['file']['url'])) ? $item['file']['url'] : '',
            'title' => __('Télécharger', 'woody-theme'),
            'target' => '_blank',
        ];
    } else {
        $data['link'] = [
            'url' => (!empty($item['link']['url'])) ? $item['link']['url'] : '',
            'title' => (!empty($item['link']['title'])) ? $item['link']['title'] : '',
            'target' => (!empty($item['link']['target'])) ? $item['link']['target'] : '',
        ];
    }


    // On affiche le bouton si "Afficher le bouton" est coché
    if (!empty($item_wrapper) && !empty($item_wrapper['display_button'])) {
        $data['display_button'] = true;
    }

    // On récupère le choix de média afin d'envoyer une image OU une vidéo
    if ($item['media_type'] == 'img' && !empty($item['img'])) {
        $data['img'] = $item['img'];
        $data['img']['attachment_more_data'] = getAttachmentMoreData($item['img']['ID']);
    } elseif ($item['media_type'] == 'movie' && !empty($item['movie'])) {
        $data['movie'] = $item['movie'];
    }
    return $data;
}

/**
 *
 * Nom : getTouristicSheetPreview
 * Auteur : Benoit Bouchaud
 * Return : Retourne les données d'une preview basée sur des fiches SIT
 * @param    layout Le wrapper du champ de mise en avant
 * @param    sheet_id - id
 * @return   data - Un tableau de données
 *
 */

function getTouristicSheetPreview($layout = null, $post)
{
    $data = [];
    $lang = pll_current_language();
    $languages = apply_filters('woody_pll_the_languages', 'auto');
    //for season
    foreach ($languages as $language) {
        $code_lang = $lang;
        if ($language['current_lang']) {
            $code_lang = substr($language['locale'], 0, 2);
        }
    }

    // $sheet_data = $sheet_data == null ? apply_filters('woody_hawwwai_sheet_render', $sheet_id, $lang, array(), 'json', 'item') : $sheet_data;
    if (empty($post)) {
        return;
    }

    $raw_item = get_field('touristic_raw_item', $post->ID);
    if (!empty($raw_item)) {
        $item = json_decode(base64_decode($raw_item), true);
    } else {
        $sheet_id = get_field('touristic_sheet_id', $post->ID);
        $items = apply_filters('woody_hawwwai_sheet_render', $sheet_id, $lang, array(), 'json', 'item');
        if (!empty($items['items']) && is_array($items['items'])) {
            $item = current($items['items']);
        }
    }

    $data = [
        'title' => (!empty($item['title'])) ? getTransformedPattern($item['title']) : '',
        'link' => [
            'url' => apply_filters('woody_get_permalink', $post->ID),
            'target' => $item['targetBlank'] ? '_blank' : '',
        ],
    ];
    if (!empty($layout['display_img'])) {
        $data['img'] = [
            'resizer' => true,
            'url' => (!empty($item['img']['url'])) ? $item['img']['url']['manual'] : '',
            'alt' => (!empty($item['img']['alt'])) ? $item['img']['alt'] : '',
            'title' => (!empty($item['img']['title'])) ? $item['img']['title'] : ''
        ];
    }
    if (!empty($layout['deal_mode'])) {
        if (!empty($item['deals'])) {
            $data['title'] = $item['deals']['list'][0]['nom'][$code_lang];
        }
    }
    if (is_array($layout['display_elements'])) {
        if (in_array('sheet_type', $layout['display_elements'])) {
            $data['sheet_type'] = (!empty($item['type'])) ? $item['type'] : '';
            if (!empty($layout['deal_mode'])) {
                if (!empty($item['deals'])) {
                    $data['sheet_type'] = $item['title'];
                }
            }
        }
        if (in_array('description', $layout['display_elements'])) {
            $data['description'] = (!empty($item['desc'])) ? getTransformedPattern($item['desc']) : '';
            if (!empty($layout['deal_mode'])) {
                if (!empty($item['deals']['list'][0]['description'][$lang])) {
                    $data['description'] = $item['deals']['list'][0]['description'][$lang];
                }
            }
        }
        if (in_array('sheet_town', $layout['display_elements'])) {
            $data['sheet_town'] = (!empty($item['town'])) ? $item['town'] : '';
        }

        if (in_array('price', $layout['display_elements'])) {
            $data['the_price']['price'] = (!empty($item['tariffs']['price'])) ? $item['tariffs']['price'] : '';
            $data['the_price']['prefix_price'] = (!empty($item['tariffs']['label'])) ? $item['tariffs']['label'] : '';
        }
        if (in_array('bookable', $layout['display_elements'])) {
            $data['booking'] = (!empty($item['booking']['link'])) ? $item['booking'] : '';
        }
    }

    if (!empty($layout['display_button'])) {
        $data['link']['link_label'] = get_field('sheet_button_title', 'options');
        if (empty($data['link']['link_label'])) {
            $data['link']['link_label'] = __('Lire la suite', 'woody-theme');
        }
    }

    $data['location'] = [];
    $data['location']['lat'] = (!empty($item['gps'])) ? $item['gps']['latitude'] : '';
    $data['location']['lng'] = (!empty($item['gps'])) ? $item['gps']['longitude'] : '';

    if ($item['bordereau'] === 'HOT' or $item['bordereau'] == 'HPA') {
        $rating = [];
        for ($i = 0; $i <= $item['ratings'][0]['value']; $i++) {
            $rating[] = '<span class="wicon wicon-031-etoile-pleine"><span>';
        }
        if (is_array($layout['display_elements'])) {
            if (in_array('sheet_rating', $layout['display_elements'])) {
                $data['sheet_rating'] = implode('', $rating);
            }
        }
    }

    if (!empty($item['dates'])) {
        $data['date'] = $item['dates'][0];
    }
    $data['date'] = (!empty($item['dates'])) ? $item['dates'][0] : '';

    if (is_array($layout['display_elements'])) {
        if (in_array('sheet_itinerary', $layout['display_elements'])) {
            $data['sheet_itinerary']['locomotions'] = (!empty($item['locomotions'])) ? $item['locomotions'] : '';
            $data['sheet_itinerary']['length'] = (!empty($item['itineraryLength'])) ? $item['itineraryLength']['value'] . $item['itineraryLength']['unit'] : '';
        }
    }

    $data['sheet_id'] = get_field('touristic_sheet_id', $post->ID);

    return $data;
}

/**
 *
 * Nom : getFocusBlockTitles
 * Auteur : Benoit Bouchaud
 * Return : Retourne les données d'es champs titre du bloc
 * @param    layout - data du layout focus en tableau
 * @return   data - Un tableau de données
 *
 */

function getFocusBlockTitles($layout)
{
    $data = [];

    $data['title'] = (!empty($layout['title'])) ? $layout['title'] : '';
    $data['pretitle'] = (!empty($layout['pretitle'])) ? $layout['pretitle'] : '';
    $data['subtitle'] = (!empty($layout['subtitle'])) ? $layout['subtitle'] : '';
    $data['icon_type'] = (!empty($layout['icon_type'])) ? $layout['icon_type'] : '';
    $data['icon_img'] = (!empty($layout['icon_img'])) ? $layout['icon_img'] : '';
    $data['woody_icon'] = (!empty($layout['woody_icon'])) ? $layout['woody_icon'] : '';

    return $data;
}

/**
 *
 * Nom : getPagePreview
 * Auteur : Benoit Bouchaud
 * Return : Retourne la donnée de base d'un post pour afficher une preview
 * @param    item - Un objet Timber\Post
 * @return   data - Un tableau de données
 *
 */
function getPagePreview($item_wrapper, $item, $clickable = true)
{
    $data = [];

    $data['page_type'] = getTermsSlugs($item->ID, 'page_type', true);
    $data['post_id'] = $item->ID;

    if (!empty(get_field('focus_title', $item->ID))) {
        $data['title'] = getTransformedPattern(get_field('focus_title', $item->ID), $item);
    } elseif (!empty(get_the_title($item->ID))) {
        $data['title'] = getTransformedPattern(get_the_title($item->ID), $item);
    }

    if (!empty($item_wrapper) && !empty($item_wrapper['display_elements']) && is_array($item_wrapper['display_elements'])) {
        if (in_array('pretitle', $item_wrapper['display_elements'])) {
            $data['pretitle'] = getTransformedPattern(getFieldAndFallback($item, 'focus_pretitle', get_field('page_heading_heading', $item->ID), 'pretitle', $item, 'field_5b87f20257a1d'), $item);
        }
        if (in_array('subtitle', $item_wrapper['display_elements'])) {
            $data['subtitle'] = getTransformedPattern(getFieldAndFallback($item, 'focus_subtitle', get_field('page_heading_heading', $item->ID), 'subtitle', $item, 'field_5b87f23b57a1e'), $item);
        }
        if (in_array('icon', $item_wrapper['display_elements'])) {
            $data['woody_icon'] = $item->get_field('focus_woody_icon');
            $data['icon_type'] = 'picto';
        }
        if (in_array('description', $item_wrapper['display_elements'])) {
            $data['description'] = getTransformedPattern(getFieldAndFallback($item, 'focus_description', $item, 'field_5b2bbbfaec6b2'), $item);
        }
        if (in_array('price', $item_wrapper['display_elements'])) {
            $data['the_price'] = $item->get_field('field_5b6c670eb54f2');
        }
        if (in_array('duration', $item_wrapper['display_elements'])) {
            $data['the_duration'] = $item->get_field('field_5b6c5e7cb54ee');
        }
        if (in_array('length', $item_wrapper['display_elements'])) {
            $data['the_length'] = $item->get_field('field_5b95423386e8f');
        }

        foreach ($item_wrapper['display_elements'] as $display) {
            if (strpos($display, '_') === 0) {
                $tax = ltrim($display, '_');
                $data['terms'][$tax] = getPrimaryTerm($tax, $item->ID, array('name', 'slug', 'term_id'));
            }
        }
    }

    $data['the_peoples'] = get_field('field_5b6d54a10381f', $item->ID);

    if ($clickable && !empty($item_wrapper['display_button'])) {
        $data['link']['link_label'] = getFieldAndFallBack($item, 'focus_button_title', $item);
        if (empty($data['link']['link_label'])) {
            $data['link']['link_label'] = __('Lire la suite', 'woody-theme');
        }
    }

    if (!empty($item_wrapper['display_img'])) {
        $data['img'] = getFieldAndFallback($item, 'focus_img', $item, 'field_5b0e5ddfd4b1b');
        if (empty($data['img'])) {
            $video = getFieldAndFallback($item, 'field_5b0e5df0d4b1c', $item);
            $data['img'] = !empty($video) ? $video['movie_poster_file'] : '';
        }
    }

    $data['location'] = [];
    $data['location']['lat'] = (!empty(get_field('post_latitude', $item->ID))) ? get_field('post_latitude', $item->ID) : '';
    $data['location']['lng'] = (!empty(get_field('post_longitude', $item->ID))) ? get_field('post_longitude', $item->ID) : '';
    $data['img']['attachment_more_data'] = (!empty($data['img'])) ? getAttachmentMoreData($data['img']['ID']) : '';
    if ($clickable) {
        $data['link']['url'] = get_permalink($item->ID);
    }

    // $post_type = get_post_terms($item->ID, 'page_type');

    return $data;
}

/**
 * @author Jérémy Legendre
 * @param   item_wrapper
 * @param   item
 * @return  data Array of data
 */
function getTopicPreview($item_wrapper, $item)
{
    $data = [];
    $data['post_id'] = $item->ID;
    $data['title'] = !empty($item->post_title) ? $item->post_title : '' ;

    if (!empty($item->woody_topic_img)) {
        $img = [
            'url' => 'https://api.tourism-system.com/resize/crop/%width%/%height%/70/' . base64_encode($item->woody_topic_img) . '/image.jpg',
            'resizer' => true
        ];
        $data['img'] = $img;
    }

    if (!empty($item->woody_topic_desc)) {
        $data['description'] = $item->woody_topic_desc;
    }

    if (!empty($item_wrapper['display_button'])) {
        $data['link']['link_label'] = getFieldAndFallBack($item, 'focus_button_title', $item);
        if (empty($data['link']['link_label'])) {
            $data['link']['link_label'] = __('Lire la suite', 'woody-theme');
        }
    }

    $data['link']['url'] = !empty($item->woody_topic_url) ? $item->woody_topic_url : '';

    return $data;
}

/**
 *
 * Nom : getFieldAndFallback
 * Auteur : Benoit Bouchaud
 * Return : Retourne un tableau de classes de personnalisation d'affichage
 * @param    item - Le scope (un objet post)
 * @param    field - Le champ prioritaire
 * @param    fallback - Le champ de remplacement
 * @return   data - Un tableau de données
 *
 **/
function getFieldAndFallback($item, $field, $fallback_item, $fallback_field = '', $lastfallback_item = '', $lastfallback_field = '')
{
    if (!empty(get_field($field, $item->ID))) {
        $value = get_field($field, $item->ID);
    } elseif (!empty($fallback_item) && is_array($fallback_item) && !empty($fallback_item[$fallback_field])) {
        $value = $fallback_item[$fallback_field];
    } elseif (!empty($fallback_item) && is_object($fallback_item) && !empty(get_field($fallback_field, $fallback_item->ID))) {
        $value = get_field($fallback_field, $fallback_item->ID);
    } elseif (!empty($lastfallback_item) && !empty(get_field($lastfallback_field, $lastfallback_item->ID))) {
        $value = get_field($lastfallback_field, $lastfallback_item->ID);
    } else {
        $value = '';
    }
    return $value;
}

/**
 *
 * Nom : getDisplayOptions
 * Auteur : Benoit Bouchaud
 * Return : Retourne un tableau de classes de personnalisation d'affichage
 * @param    scope - Le tableau contenant les infos d'affichage
 * @return   display - Un tableau de données
 *
 */

function getDisplayOptions($scope)
{
    $display = [];
    $classes_array = [];

    $display['gridContainer'] = (empty($scope['display_fullwidth'])) ? 'grid-container' : '';
    $display['background_img'] = (!empty($scope['background_img'])) ? $scope['background_img'] : '';
    $display['parallax'] = (!empty($scope['parallax'])) ? $scope['parallax'] : '';
    $classes_array[] = (!empty($display['background_img'])) ? 'isRel' : '';
    $classes_array[] = (!empty($scope['background_color'])) ? $scope['background_color'] : '';
    $classes_array[] = (!empty($scope['border_color'])) ? $scope['border_color'] : '';
    $classes_array[] = (!empty($scope['background_img_opacity'])) ? $scope['background_img_opacity'] : '';
    $classes_array[] = (!empty($scope['scope_paddings']['scope_padding_top'])) ? $scope['scope_paddings']['scope_padding_top'] : '';
    $classes_array[] = (!empty($scope['scope_paddings']['scope_padding_bottom'])) ? $scope['scope_paddings']['scope_padding_bottom'] : '';
    $classes_array[] = (!empty($scope['scope_margins']['scope_margin_top'])) ?  $scope['scope_margins']['scope_margin_top'] : '';
    $classes_array[] = (!empty($scope['scope_margins']['scope_margin_bottom'])) ? $scope['scope_margins']['scope_margin_bottom'] : '';
    $display['section_divider'] = (!empty($scope['section_divider'])) ? $scope['section_divider'] : '';

    // On transforme le tableau en une chaine de caractères
    $display['classes'] = implode(' ', $classes_array);

    return $display;
}

/**
 *
 * Nom : getAttchmentsByTerms
 * Auteur : Benoit Bouchaud
 * Return : Retourne un tableau d'objets image au format acf_image
 * @param    taxonomy - Le slug du vocabulaire dans lequel on recherche
 * @param    terms - Les termes ciblés dans le vocabulaire
 * @param    query_args - Un tableau d'arguments pour la wp_query
 * @return   attachements - Un tableau d'objets images au format "ACF"
 *
 */
function getAttachmentsByTerms($taxonomy, $terms = array(), $query_args = array())
{

    // On définit certains arguments par défaut pour la requête
    $default_args = [
        'size' => -1,
        'operator' => 'IN',
        'relation' => 'OR',
        'post_mime_type' => 'image' // Could be image/gif for gif only, video, video/mp4, application, application.pdf, ...
    ];
    $query_args = array_merge($default_args, $query_args);

    // On créé la requête
    $get_attachments = [
        'post_type'      => 'attachment',
        'post_status' => 'inherit',
        'post_mime_type' => $query_args['post_mime_type'],
        'posts_per_page' => $query_args['size'],
        'nopaging' => true,
        'tax_query' => array(
            array(
                'taxonomy' => $taxonomy,
                'terms' => $terms,
                'field' => 'term_id',
                'relation' => $query_args['relation'],
                'operator' => $query_args['operator']
            )
        )
    ];

    $attachments = new WP_Query($get_attachments);
    $acf_attachements = [];
    foreach ($attachments->posts as $key => $attachment) {
        // On transforme chacune des images en objet image ACF pour être compatible avec le tpl Woody
        $acf_attachment = acf_get_attachment($attachment);
        $acf_attachements[] = $acf_attachment;
    }
    return $acf_attachements;
}

/**
 *
 * Nom : nestedGridsComponents
 * Auteur : Benoit Bouchaud
 * Return : Retourne un DOM html
 * @param    scope - L'élément parent qui contient les grilles
 * @param    gridTplField - Le slug du champ 'Template'
 * @param    uniqIid_prefix - Un préfixe d'id, si besoin de créer un id unique (tabs)
 * @return   scope - Un DOM Html
 *
 */
function nestedGridsComponents($scope = [], $gridTplField, $uniqIid_prefix = '', $context)
{
    $woodyTwigsPaths = getWoodyTwigPaths();
    foreach ($scope as $grid_key => $grid) {
        $grid_content = [];
        if (!empty($uniqIid_prefix) && is_numeric($grid_key)) {
            $scope[$grid_key]['el_id'] = $uniqIid_prefix . '-' . uniqid();
        }

        // On compile les tpls woody pour chaque bloc ajouté dans l'onglet
        if (!empty($grid['light_section_content'])) {
            foreach ($grid['light_section_content'] as $layout) {
                $grid_content['items'][] = getComponentItem($layout, $context);
            }

            // On compile le tpl de grille woody choisi avec le DOM de chaque bloc
            $scope[$grid_key]['light_section_content'] = Timber::compile($woodyTwigsPaths[$grid[$gridTplField]], $grid_content);
        }
    }

    if (!empty($uniqIid_prefix)) {
        $scope['group_id'] = $uniqIid_prefix . '-' . uniqid();
    }

    return $scope;
}

function formatVisualEffectData($effects)
{
    $return = [];
    foreach ($effects as $effect_key => $effect) {
        if (!empty($effect) && is_array($effect)) {
            switch ($effect_key) {
                case 'transform':
                    foreach ($effect as $transform) {
                        switch ($transform['transform_type']) {
                            case 'trnslt-top':
                            case 'trnslt-bottom':
                            case 'trnslt-left':
                            case 'trnslt-right':
                                $return['transform'][] = $transform['transform_type'] . '-' . $transform['transform_trnslt_value'];
                                break;
                            case 'rotate-left':
                            case 'rotate-right':
                                $return['transform'][] = $transform['transform_type'] . '-' . $transform['transform_rotate_value'];
                                break;
                        }
                    }
                    break;
            }
        }
    }

    if (!empty($return['transform'])) {
        $return['transform'] = implode('_', $return['transform']);
    }

    return $return;
}

function getSectionBannerFiles($filename)
{
    if (file_exists(get_stylesheet_directory() . '/views/section_banner/section_' . $filename . '.twig')) {
        $file = file_get_contents(get_stylesheet_directory() . '/views/section_banner/section_' . $filename . '.twig');
    } else {
        $file = file_get_contents(get_template_directory() . '/views/section_banner/section_' . $filename . '.twig');
    }
    return $file;
}

/**
 *
 * Nom : getTransformedPattern
 * Auteur : Jérémy Legendre
 * Return : Retourne la string avec le pattern modifié (devenu le count de la playlist)
 * @param    item - Le scope (un objet post)
 * @param    str  - La phrase (titre, surtitre, sous-titre, description)
 * @return   return - La phrase modifiée
 *
 **/
function getTransformedPattern($str, $item = null)
{
    $return = '';

    if ($item != null) {
        $pattern = "/%nombre%/";
        preg_match($pattern, $str, $matches);
        if (!empty($matches)) {
            $confId = $item->get_field('playlist_conf_id');
            $playlist = apply_filters('woody_hawwwai_playlist_render', $confId, pll_current_language(), array(), 'json');

            if (!empty($playlist)) {
                $nbResults = !empty($playlist['playlist']['total']) ? $playlist['playlist']['total'] : false;
                if (!$nbResults) {
                    $return = $str;
                } else {
                    foreach ($matches as $match) {
                        $new_str = str_replace(['%nombre%'], $nbResults, $match);
                        $return = preg_replace($pattern, $new_str, $str);
                    }
                }
            }
        } else {
            $return = $str;
        }
    } else {
        // Ne concerne pas de playlists
        $pattern = "/(%[a-zA-Z]+%)/";
        preg_match($pattern, $str, $matches);
        if (!empty($matches)) {
            foreach ($matches as $match) {
                $new_str = str_replace(['%'], '', $match);
                $return = preg_replace($pattern, $new_str, $str);
            }
        } else {
            $return = $str;
        }
    }
    return $return;
}
