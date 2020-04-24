<?php

namespace WoodyProcess\Getters;

use WoodyProcess\Process\WoodyTheme_WoodyProcess;
use WoodyProcess\Tools\WoodyTheme_WoodyProcessTools;

/**
 * Get Woody data from Acf fields
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.10.0
 * @author Jeremy Legendre - Benoit Bouchaud
 */



class WoodyTheme_WoodyGetters
{
    protected $tools;

    public function __construct()
    {
        $this->tools = new WoodyTheme_WoodyProcessTools;
    }
    /**
     *
     * Nom : getAutoFocus_data
     * Auteur : Benoit Bouchaud
     * Return : Retourne un ensemble de posts sous forme de tableau avec une donnée compatbile Woody
     * @param    current_post - Un objet Timber\Post
     * @param    query_form - Un tableau des champs servant à créer la query
     * @return   the_items - Tableau de contenus compilés + infos complémentaires
     *
     */
    public function getAutoFocusData($current_post, $wrapper, $paginate = false, $uniqid = 0, $ingore_maxnum = false, $posts_in = null, $filters = null)
    {
        $the_items = [];
        $process = new WoodyTheme_WoodyProcess;
        $query_result = $process->processWoodyQuery($current_post, $wrapper, $paginate, $uniqid, $ingore_maxnum, $posts_in, $filters);

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
                $data = $this->getPagePreview($wrapper, $post);

                // $data['link']['title'] = (!empty($wrapper['links_label'])) ? $wrapper['links_label'] : '';
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
     * Return : Retourne un ensemble de posts sous forme de tableau avec une donnée compatbile Woody
     * @param    wrapper - Données du layout acf sous forme de tableau
     * @return   the_items - Tableau de contenus compilés + infos complémentaires
     *
     */
    public function getManualFocusData($wrapper)
    {
        $the_items = [];
        $clickable = true;
        if (!empty($wrapper['content_selection'])) {
            foreach ($wrapper['content_selection'] as $key => $item_wrapper) {

                // Sommes-nous dans le cas d'une mise en avant de composants de séjours ?
                $item_wrapper['content_selection_type'] = $wrapper['acf_fc_layout'] == 'focus_trip_components' ? 'existing_content' : $item_wrapper['content_selection_type'];
                if (!empty($item_wrapper['existing_content']['trip_component'])) {
                    $item_wrapper['existing_content']['content_selection'] = $item_wrapper['existing_content']['trip_component'];
                    $clickable = (!empty($item_wrapper['existing_content']['clickable_component'])) ? true : false;
                }

                // La donnée de la vignette est saisie en backoffice
                if ($item_wrapper['content_selection_type'] == 'custom_content' && !empty($item_wrapper['custom_content'])) {
                    $the_items['items'][$key] = $this->getCustomPreview($item_wrapper['custom_content'], $wrapper);
                // La donnée de la vignette correspond à un post sélectionné
                } elseif ($item_wrapper['content_selection_type'] == 'existing_content' && !empty($item_wrapper['existing_content']['content_selection'])) {
                    $item = $item_wrapper['existing_content'];
                    $status = $item['content_selection']->post_status;
                    if ($status !== 'publish') {
                        continue;
                    }
                    switch ($item['content_selection']->post_type) {
                        case 'page':
                            $post_preview = $this->getPagePreview($wrapper, $item['content_selection'], $clickable);
                            break;
                        case 'touristic_sheet':
                            $post_preview = $this->getTouristicSheetPreview($wrapper, $item['content_selection']);
                            break;
                        case 'woody_topic':
                            $post_preview = $this->getTopicPreview($wrapper, $item['content_selection']);
                            break;
                    }
                    $the_items['items'][$key] = (!empty($post_preview)) ?  $post_preview : [];
                }
            }
        }

        if (!empty($the_items['items']) && is_array($the_items['items']) && $wrapper['focused_sort'] == 'random') {
            shuffle($the_items['items']);
        }

        return $the_items;
    }

    /**
     *
     * Nom : getAutoFocusSheetData
     * Auteur : Benoit Bouchaud
     * Return : Retourne un tableau de données relatives aux fiches SIT
     * @param    wrapper Données du layout acf sous forme de tableau
     * @return   items - Un tableau de données
     *
     */
    public function getAutoFocusSheetData($wrapper)
    {
        $items = [];
        if (!empty($wrapper['playlist_conf_id'])) {
            $confId = $wrapper['playlist_conf_id'];
            $lang = pll_current_language();
            $playlist = apply_filters('woody_hawwwai_playlist_render', $confId, pll_current_language(), array(), 'json');
            if (!empty($playlist['items'])) {
                foreach ($playlist['items'] as $key => $item) {
                    $wpSheetNode = apply_filters('woody_hawwwai_get_post_by_sheet_id', $item['sheetId'], $lang, ['publish']);
                    if (!empty($wpSheetNode)) {
                        if (is_array($wpSheetNode)) {
                            $wpSheetNode = current($wpSheetNode);
                        }

                        if ($wrapper['deal_mode'] && !empty($item["deals"])) {
                            foreach ($item["deals"]['list'] as $index => $deal) {
                                $items['items'][] = $this->getTouristicSheetPreview($wrapper, $wpSheetNode->getPost(), $index);
                            }
                        } else {
                            $items['items'][] = $this->getTouristicSheetPreview($wrapper, $wpSheetNode->getPost());
                        }
                    }
                }
            }
        }

        return $items;
    }

    /**
     * @author: Jérémy Legendre
     * Retourne un tableau de données relatives au Topics
     * @param wrapper - Données du layout acf sous forme de tableau
     * @return items - Posts sous forme de tableau
     */
    public function getAutoFocusTopicsData($wrapper)
    {
        $items = [
            'items' => []
        ];

        $feeds = [];
        if (!empty($wrapper['topic_category'])) {
            foreach ($wrapper['topic_category'] as $term_id) {
                $term = get_term($term_id, 'topic_category');
                $feeds[] = $term->name;
            }
        }

        $tax_query = [];
        $custom_tax = [];
        if (!empty($wrapper['focused_taxonomy_terms'])) {

            // On récupère la relation choisie (ET/OU) entre les termes
            // et on génère un tableau de term_id pour chaque taxonomie
            $tax_query['relation'] = (!empty($wrapper['focused_taxonomy_terms_andor'])) ? $wrapper['focused_taxonomy_terms_andor'] : 'OR';

            // Pour chaque entrée du tableau focus_taxonomy_terms
            foreach ($wrapper['focused_taxonomy_terms'] as $focused_term) {
                // Si l'entrée est un post id (Aucun filtre n'a été utilisé en front)
                $term = get_term($focused_term);
                if (!empty($term) && !is_wp_error($term) && is_object($term)) {
                    $custom_tax[$term->taxonomy][] = $focused_term;
                }

                foreach ($custom_tax as $taxo => $terms) {
                    foreach ($terms as $term) {
                        $tax_query[] = array(
                            'taxonomy' => $taxo,
                            'terms' => [$term],
                            'field' => 'term_id',
                            'operator' => 'IN'
                        );
                    }
                }
            }
        }

        $time = !empty($wrapper['publish_date']) ? strtotime($wrapper['publish_date']) : 0;
        $args = [
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'post_type' => 'woody_topic',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'woody_topic_category',
                    'value' => $feeds,
                    'compare' => 'IN'
                ),
                array(
                    'key' => 'woody_topic_publication',
                    'value' => $time,
                    'compare' => '>'
                )
            ),
            'tax_query' => !empty($tax_query) ? $tax_query : ''
        ];

        if ($wrapper['focused_sort'] == 'title') {
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
        }

        $result = new \WP_Query($args);

        if (!empty($result->posts)) {
            foreach ($result->posts as $post) {
                $items['items'][] = $this->getTopicPreview($wrapper, $post);
            }
        }

        if ($wrapper['focused_sort'] == 'random') {
            shuffle($items['items']);
        } elseif ($wrapper['focused_sort'] == 'date') {
            $date = [];
            foreach ($items['items'] as $key => $item) {
                $date[$key] = $item['date'];
            }
            array_multisort($date, SORT_DESC, $items['items']);
        }

        return $items;
    }

    /**
     *
     * Nom : getPagePreview
     * Auteur : Benoit Bouchaud
     * Return : Retourne la donnée de base d'un post pour afficher une preview
     * @param    item - Un objet Timber\Post
     * @param    wrapper - Données du layout acf sous forme de tableau
     * @return   data - Un tableau de données
     *
     */
    public function getPagePreview($wrapper, $item, $clickable = true)
    {
        $data = [];
        if (!is_object($item)) {
            return;
        }

        $data['page_type'] = getTermsSlugs($item->ID, 'page_type', true);
        $data['post_id'] = $item->ID;

        if (!empty(get_field('focus_title', $item->ID))) {
            $data['title'] = $this->tools->replacePattern(get_field('focus_title', $item->ID), $item->ID);
        } elseif (!empty(get_the_title($item->ID))) {
            $data['title'] = $this->tools->replacePattern(get_the_title($item->ID), $item->ID);
        }

        if (!empty($wrapper) && !empty($wrapper['display_elements']) && is_array($wrapper['display_elements'])) {
            if (in_array('pretitle', $wrapper['display_elements'])) {
                $data['pretitle'] = $this->tools->replacePattern($this->tools->getFieldAndFallback($item, 'focus_pretitle', get_field('page_heading_heading', $item->ID), 'pretitle', $item, 'field_5b87f20257a1d'), $item->ID);
            }
            if (in_array('subtitle', $wrapper['display_elements'])) {
                $data['subtitle'] = $this->tools->replacePattern($this->tools->getFieldAndFallback($item, 'focus_subtitle', get_field('page_heading_heading', $item->ID), 'subtitle', $item, 'field_5b87f23b57a1e'), $item->ID);
            }
            if (in_array('icon', $wrapper['display_elements'])) {
                $data['woody_icon'] = get_field('focus_woody_icon', $item->ID);
                $data['icon_type'] = 'picto';
            }
            if (in_array('description', $wrapper['display_elements'])) {
                $data['description'] = $this->tools->replacePattern($this->tools->getFieldAndFallback($item, 'focus_description', $item, 'field_5b2bbbfaec6b2'), $item->ID);
            }
            if (in_array('price', $wrapper['display_elements'])) {
                $data['the_price'] = get_field('field_5b6c670eb54f2', $item->ID);
            }
            if (in_array('duration', $wrapper['display_elements'])) {
                $data['the_duration'] = get_field('field_5b6c5e7cb54ee', $item->ID);
            }
            if (in_array('length', $wrapper['display_elements'])) {
                $data['the_length'] = get_field('field_5b95423386e8f', $item->ID);
            }

            foreach ($wrapper['display_elements'] as $display) {
                if (strpos($display, '_') === 0) {
                    $tax = ltrim($display, '_');
                    $data['terms'][$tax] = getPrimaryTerm($tax, $item->ID, array('name', 'slug', 'term_id'));
                }
            }
        }

        $data['the_peoples'] = get_field('field_5b6d54a10381f', $item->ID);

        if ($clickable) {
            $data['link']['link_label'] = $this->tools->replacePattern($this->tools->getFieldAndFallBack($item, 'focus_button_title', $item), $item->ID);
            if (empty($data['link']['link_label'])) {
                $data['link']['link_label'] = __('Lire la suite', 'woody-theme');
            }
        }

        if (!empty($wrapper['display_img'])) {
            $data['img'] = $this->tools->getFieldAndFallback($item, 'focus_img', $item, 'field_5b0e5ddfd4b1b');
            if (empty($data['img'])) {
                $video = $this->tools->getFieldAndFallback($item, 'field_5b0e5df0d4b1c', $item);
                $data['img'] = !empty($video) ? $video['movie_poster_file'] : '';
            }
        }

        $data['location'] = [];
        $lat = get_field('post_latitude', $item->ID);
        $lng = get_field('post_longitude', $item->ID);
        $data['location']['lat'] = (!empty($lat)) ? str_replace(',', '.', $lat) : '';
        $data['location']['lng'] = (!empty($lng)) ? str_replace(',', '.', $lng) : '';
        $data['img']['attachment_more_data'] = (!empty($data['img'])) ? $this->tools->getAttachmentMoreData($data['img']['ID']) : '';
        if ($clickable) {
            $data['link']['url'] = get_permalink($item->ID);
        }

        $data = apply_filters('woody_custom_pagePreview', $data, $wrapper);

        return $data;
    }

    /**
     *
     * Nom : getCustomPreview
     * Auteur : Benoit Bouchaud
     * Return : Retourne les données d'une preview basée sur des champs custom
     * @param    item - Un tableau de données (Vignette créée dans le backoffice - N'est pas directement liéée à un contenu existant)
     * @param    wrapper - Données du layout acf sous forme de tableau
     * @return   data - Un tableau de données
     *
     */
    public function getCustomPreview($item, $wrapper = null)
    {
        $data = [];
        $data = [
            'title' => (!empty($item['title'])) ? $item['title'] : '',
            'pretitle' => (!empty($item['pretitle'])) ? $item['pretitle'] : '',
            'subtitle' => (!empty($item['subtitle'])) ? $item['subtitle'] : '',
            'icon_type' => (!empty($item['icon_type'])) ? $item['icon_type'] : '',
            'woody_icon' => (!empty($item['woody_icon'])) ? $item['woody_icon'] : '',
            'icon_img' => (!empty($item['icon_img']['url'])) ? [
                'sizes' => [
                    'thumbnail' => $item['icon_img']['sizes']['medium']
                ],
                'alt' =>  $item['icon_img']['alt'],

            ] : '',
            'description' => (!empty($item['description'])) ? $item['description'] : '',
            'ellipsis' => 999,
            'location' => [
                'lat' => !empty($item['latitude']) ? str_replace(',', '.', $item['latitude']) : '',
                'lng' => !empty($item['longitude']) ? str_replace(',', '.', $item['longitude']) : ''
            ]
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
        if (!empty($wrapper) && !empty($wrapper['display_button'])) {
            $data['display_button'] = true;
        }

        // On récupère le choix de média afin d'envoyer une image OU une vidéo
        if ($item['media_type'] == 'img' && !empty($item['img'])) {
            $data['img'] = $item['img'];
            $data['img']['attachment_more_data'] = $this->tools->getAttachmentMoreData($item['img']['ID']);
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
     * @param    wrapper - Données du layout acf sous forme de tableau
     * @param    item - objet post wp
     * @return   data - Un tableau de données
     *
     */
    public function getTouristicSheetPreview($wrapper = null, $item, $deal_index = 0)
    {
        if (!is_object($item) || empty($item)) {
            return;
        }

        $data = [];

        $sheet = $this->tools->getTouristicSheetData($item);

        // $lang = pll_current_language();
        // $languages = apply_filters('woody_pll_the_languages', 'auto');
        // //for season
        // foreach ($languages as $language) {
        //     $code_lang = $lang;
        //     if ($language['current_lang']) {
        //         $code_lang = substr($language['locale'], 0, 2);
        //     }
        // }

        // $raw_item = get_field('touristic_raw_item', $item->ID);
        // if (!empty($raw_item)) {
        //     $sheet = json_decode(base64_decode($raw_item), true);
        // } else {
        //     $sheet_id = get_field('touristic_sheet_id', $item->ID);
        //     $items = apply_filters('woody_hawwwai_sheet_render', $sheet_id, $lang, array(), 'json', 'item');
        //     if (!empty($items['items']) && is_array($items['items'])) {
        //         $sheet = current($items['items']);
        //     }
        // }

        $data = [
            'title' => (!empty($sheet['title'])) ? $sheet['title'] : '',
            'link' => [
                'url' => apply_filters('woody_get_permalink', $item->ID),
                'target' => (!empty($sheet['targetBlank'])) ? '_blank' : '',
            ],
        ];
        if (!empty($wrapper['display_img'])) {
            $data['img'] = [
                'resizer' => true,
                'url' => (!empty($sheet['img']['url'])) ? $sheet['img']['url']['manual'] : '',
                'alt' => (!empty($sheet['img']['alt'])) ? $sheet['img']['alt'] : '',
                'title' => (!empty($sheet['img']['title'])) ? $sheet['img']['title'] : ''
            ];
        }
        if (!empty($wrapper['deal_mode'])) {
            if (!empty($sheet['deals'])) {
                $data['title'] = $sheet['deals']['list'][$deal_index]['nom'][$code_lang];
            }
        }
        if (!empty($wrapper['display_elements']) && is_array($wrapper['display_elements'])) {
            if (in_array('sheet_type', $wrapper['display_elements'])) {
                $data['sheet_type'] = (!empty($sheet['type'])) ? $sheet['type'] : '';
                if (!empty($wrapper['deal_mode'])) {
                    if (!empty($sheet['deals'])) {
                        $data['sheet_type'] = $sheet['title'];
                    }
                }
            }
            if (in_array('description', $wrapper['display_elements'])) {
                $data['description'] = (!empty($sheet['desc'])) ? $sheet['desc'] : '';
                if (!empty($wrapper['deal_mode'])) {
                    if (!empty($sheet['deals']['list'][$deal_index]['description'][$lang])) {
                        $data['description'] = $sheet['deals']['list'][$deal_index]['description'][$lang];
                    }
                }
            }

            if (in_array('sheet_itinerary', $wrapper['display_elements'])) {
                $data['sheet_itinerary']['locomotions'] = (!empty($sheet['locomotions'])) ? $sheet['locomotions'] : '';
                $data['sheet_itinerary']['length'] = (!empty($sheet['itineraryLength'])) ? $sheet['itineraryLength']['value'] . $sheet['itineraryLength']['unit'] : '';
            }

            if (in_array('sheet_town', $wrapper['display_elements'])) {
                $data['sheet_town'] = (!empty($sheet['town'])) ? $sheet['town'] : '';
            }

            if (in_array('price', $wrapper['display_elements'])) {
                $data['the_price']['price'] = (!empty($sheet['tariffs']['price'])) ? $sheet['tariffs']['price'] : '';
                $data['the_price']['prefix_price'] = (!empty($sheet['tariffs']['label'])) ? $sheet['tariffs']['label'] : '';
            }
            if (in_array('bookable', $wrapper['display_elements'])) {
                $data['booking'] = (!empty($sheet['booking']['link'])) ? $sheet['booking'] : '';
            }

            if (in_array('address', $wrapper['display_elements'])) {
                $data['address'] = (!empty($sheet['address'])) ? $sheet['address'] : '';
            }

            if (in_array('phone', $wrapper['display_elements'])) {
                $data['phone'] = (!empty($sheet['phone'])) ? $sheet['phone'] : '';
            }

            if (in_array('website', $wrapper['display_elements'])) {
                $data['website'] = (!empty($sheet['website'])) ? $sheet['website'] : '';
            }
        }

        if (!empty($wrapper['display_button'])) {
            $data['link']['link_label'] = get_field('sheet_button_title', 'options');
            if (empty($data['link']['link_label'])) {
                $data['link']['link_label'] = __('Lire la suite', 'woody-theme');
            }
        }

        if (!empty($sheet['bordereau'])) {
            if ($sheet['bordereau'] === 'HOT' or $sheet['bordereau'] == 'HPA') {
                $rating = [];
                for ($i = 0; $i <= $sheet['ratings'][0]['value']; $i++) {
                    $rating[] = '<span class="wicon wicon-031-etoile-pleine"><span>';
                }
                if (is_array($wrapper['display_elements'])) {
                    if (in_array('sheet_rating', $wrapper['display_elements'])) {
                        $data['sheet_rating'] = implode('', $rating);
                    }
                }
            }
        }

        $data['location'] = [];
        $data['location']['lat'] = (!empty($sheet['gps'])) ? $sheet['gps']['latitude'] : '';
        $data['location']['lng'] = (!empty($sheet['gps'])) ? $sheet['gps']['longitude'] : '';

        // Parcourir tout le tableau de dates et afficher la 1ère date non passée
        if (!empty($sheet['dates'])) {
            $today = time();
            foreach ($sheet['dates'] as $date) {
                $enddate= strtotime($date['end']['endDate']);
                if ($today < $enddate) {
                    $data['date'] = $date;
                    break 1 ;
                }
            }
        }

        $data['sheet_id'] = get_field('touristic_sheet_id', $item->ID);

        return $data;
    }

    /**
     * @author Jérémy Legendre
     * @param   wrapper - Données du layout acf sous forme de tableau
     * @param   item - Objet post wp
     * @return  data - Un tableau de données
     */
    public function getTopicPreview($wrapper, $item)
    {
        $data = [];
        $data['post_id'] = $item->ID;
        $data['title'] = !empty($item->post_title) ? $item->post_title : '';
        $data['pretitle'] = !empty($item->woody_topic_blogname) ? $item->woody_topic_blogname : '';
        // $data['subtitle'] = !empty($item->woody_topic_blogname) ? $item->woody_topic_blogname : '';

        if (!empty($item->woody_topic_img) && !$item->woody_topic_attachment) {
            $img = [
                'url' => 'https://api.tourism-system.com/resize/crop/%width%/%height%/70/' . base64_encode($item->woody_topic_img) . '/image.jpg',
                'resizer' => true
            ];
            $data['img'] = $img;
        } elseif (!empty($item->woody_topic_attachment)) {
            $url = !empty(wp_get_attachment_image_src($item->woody_topic_attachment)) ? wp_get_attachment_image_src($item->woody_topic_attachment)[0] : '';

            $data['img'] = [
                'url' => $url,
                'resizer' => true
            ];
        }

        if (!empty($item->woody_topic_desc)) {
            $data['description'] = strlen($item->woody_topic_desc) > 256 ? substr($item->woody_topic_desc, 0, 256) : $item->woody_topic_desc ;
        }

        if (!empty($item->woody_topic_url)) {
            $data['link'] = [
                'url' => !empty($item->woody_topic_url) ? $item->woody_topic_url : '',
                'title' => __('Découvrir', 'woody-theme'),
                'link_label' => __('Découvrir', 'woody-theme'),
                'target' => '_blank',
            ];
        }

        $lat = get_field('post_latitude', $item->ID);
        $lng = get_field('post_longitude', $item->ID);
        if (!empty($lat) && !empty($lng)) {
            $data['location'] = [];
            $data['location']['lat'] = (!empty($lat)) ? str_replace(',', '.', $lat) : '';
            $data['location']['lng'] = (!empty($lng)) ? str_replace(',', '.', $lng) : '';
        }

        return $data;
    }

    public function getListFilters($filter_wrapper, $active_filters, $default_items)
    {
        $return = [];
        // On transforme $active_filters['focused_taxonomy_terms'] en tableau
        if (empty($active_filters['focused_taxonomy_terms'])) {
            $active_filters['focused_taxonomy_terms'] = [];
        } elseif (is_numeric($active_filters['focused_taxonomy_terms'])) {
            $active_filters['focused_taxonomy_terms'] = [$active_filters['focused_taxonomy_terms']];
        }

        if (!empty($filter_wrapper) && !empty($filter_wrapper['list_filters'])) {
            // TAXONOMY | DURATION | PRICE | CUSTOM TERM
            foreach ($filter_wrapper['list_filters'] as $key => $filter) {
                switch ($filter['list_filter_type']) {
                    case 'taxonomy':
                        $return[$key] = ['filter_type' => 'custom_terms'];

                        $taxonomy = $filter['list_filter_taxonomy'];
                        $terms = get_terms($taxonomy, ['hide_empty' => false]);
                        foreach ($terms as $term_key => $term) {
                            $return[$key]['list_filter_custom_terms'][] = [
                                'value' => $term->term_id,
                                'label' => $term->name,
                            ];

                            if (!empty($active_filters['filtered_taxonomy_terms']) && is_array($active_filters['filtered_taxonomy_terms'])) {
                                foreach ($active_filters['filtered_taxonomy_terms'] as $filtered_terms) {
                                    // Si on reçoit le paramètre en tant qu'identifiant (select/radio) => on le pousse dans un tableau
                                    $filtered_terms = (!is_array($filtered_terms)) ? [$filtered_terms] : $filtered_terms;
                                    if (in_array($term->term_id, $filtered_terms)) {
                                        $return[$key]['list_filter_custom_terms'][$term_key]['checked'] = true;
                                    }
                                }
                            }
                        }
                        $return[$key]['filter_name'] = $filter['list_filter_name'];
                        break;

                    case 'custom_terms':
                        $return[$key] = ['filter_type' => 'custom_terms'];
                        foreach ($filter['list_filter_custom_terms'] as $term_key => $term) {
                            $term = get_term($term['value']);
                            $return[$key]['list_filter_custom_terms'][$term_key] = [
                                'value' => $term->term_id,
                                'label' => $term->name,
                            ];

                            if (!empty($active_filters['filtered_taxonomy_terms']) && is_array($active_filters['filtered_taxonomy_terms'])) {
                                foreach ($active_filters['filtered_taxonomy_terms'] as $filtered_terms) {
                                    // Si on reçoit le paramètre en tant qu'identifiant (select/radio) => on le pousse dans un tableau
                                    $filtered_terms = (!is_array($filtered_terms)) ? [$filtered_terms] : $filtered_terms;
                                    if (in_array($term->term_id, $filtered_terms)) {
                                        $return[$key]['list_filter_custom_terms'][$term_key]['checked'] = true;
                                    }
                                }
                            }
                        }
                        $return[$key]['filter_name'] = $filter['list_filter_name'];
                        break;

                    case 'map':
                        $return['the_map'] = [];
                        unset($filter_wrapper['list_filters'][$key]);
                        break;

                    case 'price':
                    case 'duration':
                        $return[$key]['filter_type'] = $filter['list_filter_type'];
                        $field = $filter['list_filter_type'] == 'price' ? 'the_price_price' : 'the_duration_count_days';

                        // On récupère les valeurs min et max des champs prix et durée parmi tous les posts choisis en backoffice
                        $return[$key]['minmax']['max'] = $this->tools->getMinMaxWoodyFieldValues($default_items['wp_query']->query_vars, $field);
                        $return[$key]['minmax']['min'] = $this->tools->getMinMaxWoodyFieldValues($default_items['wp_query']->query_vars, $field, 'min');

                        // Si le prix ou la durée ont été filtrés, on place les curseurs des sliders
                        if (!empty($active_filters['focused_trip_' . $filter['list_filter_type']])) {
                            $return[$key]['minmax']['default_max'] = (!empty($active_filters['focused_trip_' . $filter['list_filter_type']]['max'])) ? $active_filters['focused_trip_' . $filter['list_filter_type']]['max'] : false;
                            $return[$key]['minmax']['default_min'] = (!empty($active_filters['focused_trip_' . $filter['list_filter_type']]['min'])) ? $active_filters['focused_trip_' . $filter['list_filter_type']]['min'] : false;
                        }
                        $return[$key]['filter_name'] = $filter['list_filter_name'];
                        break;
                }
            }
            $return['button'] = (!empty($filter_wrapper['filter_button'])) ? $filter_wrapper['filter_button'] : '';
            $return['reset'] = (!empty($filter_wrapper['reset_button'])) ? $filter_wrapper['reset_button'] : '';
            $return['display']['background_img'] = (!empty($filter_wrapper['background_img'])) ? $filter_wrapper['background_img'] : '';
            $return['display']['classes'][] = (!empty($filter_wrapper['background_color'])) ? $filter_wrapper['background_color'] : '';
            $return['display']['classes'][] = (!empty($filter_wrapper['background_img_opacity'])) ? $filter_wrapper['background_img_opacity'] : '';
            $return['display']['classes'][] = (!empty($filter_wrapper['border_color'])) ? $filter_wrapper['border_color'] : '';
            $return['display']['classes'] = implode(' ', $return['display']['classes']);
        }
        return $return;
    }

    /**
     *
     * Nom : getManualFocusMiniSheetData
     * Auteur : Thomas Navarro
     * Return : Retourne un tableau de données compatible au format des mini-fiches
     * @param    wrapper array - Tableau de données du layout ACF
     * @return   data - array - Un tableau de données
     *
     */
    public function getManualFocusMiniSheetData($wrapper)
    {
        $data = [];
        $post = $wrapper['sheet_selection'];
        $sheet = $this->tools->getTouristicSheetData($post);
        $sheet_url = apply_filters('woody_get_permalink', $post->ID);

        $data['title'] = !empty($sheet['title']) ? $sheet['title'] : '';
        $data['link']['url'] = $sheet_url;
        $data['link']['target'] = !empty($sheet['targetBlank']) ? '_blank' : '';

        // Display Imgs
        if ($wrapper['display_img'] && !empty($sheet['allImgs'])) {
            foreach ($sheet['allImgs'] as $key => $img) {
                $data['imgs'][$key] = [
                    'resizer' => true,
                    'url' => $img['manual'],
                    'alt' => 'TODO',
                    'title' => 'TODO'
                ];
            }
        }

        // Display options
        $display_options = $wrapper['sheet_display'];
        if (in_array('detail', $display_options)) {
            // TODO : vérifier que la fiche propose des prestations
            $data['anchors']['detail']['url'] = $sheet_url . '#establishment-detail';
            $data['anchors']['detail']['title'] = __('Informations prestataire', 'woody-theme');
            $data['anchors']['detail']['icon'] = 'wicon-028-plus-02';
        }
        if (in_array('openings', $display_options)) {
            // TODO : vérifier que la fiche a des horaires d'ouvertures
            $data['anchors']['openings']['url'] = $sheet_url . '#openings';
            $data['anchors']['openings']['title'] = __('Horaires', 'woody-theme');
            $data['anchors']['openings']['icon'] = 'wicon-015-horloge';
        }
        if (in_array('map', $display_options)) {
            // TODO : vérifier que la fiche a une carte
            $data['anchors']['map']['url'] = $sheet_url . '#map';
            $data['anchors']['map']['title'] = __('Carte', 'woody-theme');
            $data['anchors']['map']['icon'] = 'wicon-022-itineraire';
        }
        if (in_array('reviews', $display_options) && !empty($sheet['reviews'])) {
            $data['anchors']['reviews']['url'] = $sheet_url . '#reviews';
            $data['anchors']['reviews']['title'] = __('Avis', 'woody-theme');
            $data['anchors']['reviews']['icon'] = 'wicon-012-smiley-bien';
        }
        if (in_array('contact', $display_options)) {
            $data['anchors']['contact']['url'] = $sheet_url;
            $data['anchors']['contact']['title'] = __('Poser une question', 'woody-theme');
            $data['anchors']['contact']['icon'] = 'wicon-016-bulle';
        }

        // Shuffle anchors positions
        if (!empty($data['anchors'])) {
            $data['anchors'] = array_values($data['anchors']);
            shuffle($data['anchors']);
        }

        //  Removes image if sum of anchors and images > 8
        if (!empty($data['imgs']) && $data['anchors']) {
            while (count($data['imgs']) + count($data['anchors']) > 8) {
                array_splice($data['imgs'], -1, 1);
            }
        }

        // TODO : Récupérer les infos de réservation de la fiche
        if ($sheet['booking']) {
            $data['booking']['prefix'] = 'TODO';
            $data['booking']['price'] = 'TODO';
            $data['booking']['link'] = 'TODO';
        }

        // Customize Sheet booking
        if ($wrapper['customize_sheet_booking']) {
            $booking_options = $wrapper['custom_sheet_booking'];

            foreach ($booking_options as $option) {
                $data['booking'][$option] = !empty($wrapper['custom_' . $option]) ? $wrapper['custom_' . $option] : '';
            }
        }

        return $data;
    }
}
