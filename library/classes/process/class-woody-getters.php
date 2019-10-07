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
    public function getAutoFocusData($current_post, $wrapper, $paginate = false, $uniqid = 0, $ingore_maxnum = false)
    {

        $the_items = [];
        $process = new WoodyTheme_WoodyProcess;
        $query_result = $process->processWoodyQuery($current_post, $wrapper, $paginate, $uniqid, $ingore_maxnum);

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
                $post = \Timber::get_post($post->ID);
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
        foreach ($wrapper['content_selection'] as $key => $item_wrapper) {
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
                $the_items['items'][$key] = (!empty($post_preview)) ?  $post_preview : '';
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
     * Return : Retourne un tableau de données relatives aux foches SIT
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
                    //TODO: $wpSheetNode->getPost() retourne parfois un tableau. Dans ce cas, on récupère le 1ier objet à l'interieur - voir plugin
                    if (!empty($wpSheetNode)) {
                        if (is_array($wpSheetNode)) {
                            $wpSheetNode = current($wpSheetNode);
                        }
                        $items['items'][] = $this->getTouristicSheetPreview($wrapper, $wpSheetNode->getPost());
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
        //TODO: call processWoodyQuery
        $items = [];

        $feeds = [];
        foreach ($wrapper['topic_newspaper'] as $term_id) {
            $term = get_term($term_id, 'topic_newspaper');
            $feeds[] = $term->name;
        }
        $time = !empty($wrapper['publish_date']) ? strtotime($wrapper['publish_date']) : 0;
        $args = [
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'post_type' => 'woody_topic',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'woody_topic_feed',
                    'value' => $feeds,
                    'compare' => 'IN'
                ),
                array(
                    'key' => 'woody_topic_publication',
                    'value' => $time,
                    'compare' => '>'
                )
            )
        ];

        if ($wrapper['focused_sort'] == 'title') {
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
        }

        $result = new \WP_Query($args);

        if (!empty($result->posts)) {
            foreach ($result->posts as $post) {
                $item = Timber::get_post($post->ID);
                $items['items'][] = $this->getTopicPreview($wrapper, $item);
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

        $data['page_type'] = getTermsSlugs($item->ID, 'page_type', true);
        $data['post_id'] = $item->ID;

        if (!empty(get_field('focus_title', $item->ID))) {
            $data['title'] = $this->tools->replacePattern(get_field('focus_title', $item->ID), $item);
        } elseif (!empty(get_the_title($item->ID))) {
            $data['title'] = $this->tools->replacePattern(get_the_title($item->ID), $item);
        }

        if (!empty($wrapper) && !empty($wrapper['display_elements']) && is_array($wrapper['display_elements'])) {
            if (in_array('pretitle', $wrapper['display_elements'])) {
                $data['pretitle'] = $this->tools->replacePattern($this->tools->getFieldAndFallback($item, 'focus_pretitle', get_field('page_heading_heading', $item->ID), 'pretitle', $item, 'field_5b87f20257a1d'), $item);
            }
            if (in_array('subtitle', $wrapper['display_elements'])) {
                $data['subtitle'] = $this->tools->replacePattern($this->tools->getFieldAndFallback($item, 'focus_subtitle', get_field('page_heading_heading', $item->ID), 'subtitle', $item, 'field_5b87f23b57a1e'), $item);
            }
            if (in_array('icon', $wrapper['display_elements'])) {
                $data['woody_icon'] = $item->get_field('focus_woody_icon');
                $data['icon_type'] = 'picto';
            }
            if (in_array('description', $wrapper['display_elements'])) {
                $data['description'] = $this->tools->replacePattern($this->tools->getFieldAndFallback($item, 'focus_description', $item, 'field_5b2bbbfaec6b2'), $item);
            }
            if (in_array('price', $wrapper['display_elements'])) {
                $data['the_price'] = $item->get_field('field_5b6c670eb54f2');
            }
            if (in_array('duration', $wrapper['display_elements'])) {
                $data['the_duration'] = $item->get_field('field_5b6c5e7cb54ee');
            }
            if (in_array('length', $wrapper['display_elements'])) {
                $data['the_length'] = $item->get_field('field_5b95423386e8f');
            }

            foreach ($wrapper['display_elements'] as $display) {
                if (strpos($display, '_') === 0) {
                    $tax = ltrim($display, '_');
                    $data['terms'][$tax] = getPrimaryTerm($tax, $item->ID, array('name', 'slug', 'term_id'));
                }
            }
        }

        $data['the_peoples'] = get_field('field_5b6d54a10381f', $item->ID);

        if ($clickable && !empty($wrapper['display_button'])) {
            $data['link']['link_label'] = $this->tools->getFieldAndFallBack($item, 'focus_button_title', $item);
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
            'title' => (!empty($item['title'])) ? $this->tools->replacePattern($item['title']) : '',
            'pretitle' => (!empty($item['pretitle'])) ? $this->tools->replacePattern($item['pretitle']) : '',
            'subtitle' => (!empty($item['subtitle'])) ? $this->tools->replacePattern($item['subtitle']) : '',
            'icon_type' => (!empty($item['icon_type'])) ? $item['icon_type'] : '',
            'woody_icon' => (!empty($item['woody_icon'])) ? $item['woody_icon'] : '',
            'icon_img' => (!empty($item['icon_img']['url'])) ? [
                'sizes' => [
                    'thumbnail' => $item['icon_img']['sizes']['medium']
                ],
                'alt' =>  $item['icon_img']['alt'],

            ] : '',
            'description' => (!empty($item['description'])) ? $this->tools->replacePattern($item['description']) : '',
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

    function getTouristicSheetPreview($wrapper = null, $item)
    {
        if (!is_object($item) || empty($item)) {
            return;
        }

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

        $raw_item = get_field('touristic_raw_item', $item->ID);
        if (!empty($raw_item)) {
            $sheet = json_decode(base64_decode($raw_item), true);
        } else {
            $sheet_id = get_field('touristic_sheet_id', $item->ID);
            $items = apply_filters('woody_hawwwai_sheet_render', $sheet_id, $lang, array(), 'json', 'item');
            if (!empty($items['items']) && is_array($items['items'])) {
                $sheet = current($items['items']);
            }
        }

        $data = [
            'title' => (!empty($sheet['title'])) ? $this->tools->replacePattern($sheet['title']) : '',
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
                $data['title'] = $sheet['deals']['list'][0]['nom'][$code_lang];
            }
        }
        if (is_array($wrapper['display_elements'])) {
            if (in_array('sheet_type', $wrapper['display_elements'])) {
                $data['sheet_type'] = (!empty($sheet['type'])) ? $sheet['type'] : '';
                if (!empty($wrapper['deal_mode'])) {
                    if (!empty($sheet['deals'])) {
                        $data['sheet_type'] = $sheet['title'];
                    }
                }
            }
            if (in_array('description', $wrapper['display_elements'])) {
                $data['description'] = (!empty($sheet['desc'])) ? replacePattern($sheet['desc']) : '';
                if (!empty($wrapper['deal_mode'])) {
                    if (!empty($sheet['deals']['list'][0]['description'][$lang])) {
                        $data['description'] = $sheet['deals']['list'][0]['description'][$lang];
                    }
                }
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
        }

        if (!empty($wrapper['display_button'])) {
            $data['link']['link_label'] = get_field('sheet_button_title', 'options');
            if (empty($data['link']['link_label'])) {
                $data['link']['link_label'] = __('Lire la suite', 'woody-theme');
            }
        }

        $data['location'] = [];
        $data['location']['lat'] = (!empty($sheet['gps'])) ? $sheet['gps']['latitude'] : '';
        $data['location']['lng'] = (!empty($sheet['gps'])) ? $sheet['gps']['longitude'] : '';

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

        if (!empty($sheet['dates'])) {
            $data['date'] = $sheet['dates'][0];
        }
        $data['date'] = (!empty($sheet['dates'])) ? $sheet['dates'][0] : '';

        if (is_array($wrapper['display_elements'])) {
            if (in_array('sheet_itinerary', $wrapper['display_elements'])) {
                $data['sheet_itinerary']['locomotions'] = (!empty($sheet['locomotions'])) ? $sheet['locomotions'] : '';
                $data['sheet_itinerary']['length'] = (!empty($sheet['itineraryLength'])) ? $sheet['itineraryLength']['value'] . $sheet['itineraryLength']['unit'] : '';
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
    function getTopicPreview($wrapper, $item)
    {
        $data = [];
        $data['post_id'] = $item->ID;
        $data['title'] = !empty($item->post_title) ? $item->post_title : '';

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

        if (!empty($wrapper['display_button'])) {
            $data['link']['link_label'] = $this->tools->getFieldAndFallBack($item, 'focus_button_title', $item);
            if (empty($data['link']['link_label'])) {
                $data['link']['link_label'] = __('Lire la suite', 'woody-theme');
            }
        }

        if (!empty($item->woody_topic_publication)) {
            $data['date'] = (int) $item->woody_topic_publication;
        }

        $data['link']['url'] = !empty($item->woody_topic_url) ? $item->woody_topic_url : '';

        return $data;
    }

    public function getListFilters($filter_wrapper, $list_items)
    {
        $return = [];
        if (!empty($filter_wrapper)) {
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
                                'label' => $term->name
                            ];
                        }
                        $return[$key]['filter_name'] = $filter['list_filter_name'];
                        break;

                    case 'custom_terms':
                        $return[$key] = ['filter_type' => 'custom_terms'];
                        foreach ($filter['list_filter_custom_terms'] as $term_key => $term) {
                            $term = get_term($term['value']);
                            $return[$key]['list_filter_custom_terms'][$term_key] = [
                                'value' => $term->term_id,
                                'label' => $term->name
                            ];
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
                        $return[$key]['minmax']['max'] = $this->tools->getMinMaxWoodyFieldValues($list_items['wp_query']->query_vars, $field);
                        $return[$key]['minmax']['min'] = $this->tools->getMinMaxWoodyFieldValues($list_items['wp_query']->query_vars, $field, 'min');
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
}
