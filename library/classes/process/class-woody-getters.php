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
     * Return : Retourne un ensemble de posts compilés avec une donnée compatbile Woody
     * @param    current_post - Un objet Timber\Post
     * @param    query_form - Un tableau des champs servant à créer la query
     * @return   the_items - Tableau de contenus compilés + infos complémentaires
     *
     */
    public function getAutoFocusData($current_post, $query_form)
    {

        $process = new WoodyTheme_WoodyProcess;
        $query_result = $process->processWoodyQuery($current_post, $query_form);

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
    }

    /**
     *
     * Nom : getManualFocus_data
     * Auteur : Benoit Bouchaud
     * Return : Retourne un ensemble de posts compilés avec une donnée compatbile Woody
     * @param    layout - Layout acf sous forme de tableau
     * @return   the_items - Tableau de contenus compilés + infos complémentaires
     *
     */
    public function getManualFocusData($layout)
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
                $the_items['items'][$key] = $this->getCustomPreview($item_wrapper['custom_content'], $layout);
                // La donnée de la vignette correspond à un post sélectionné
            } elseif ($item_wrapper['content_selection_type'] == 'existing_content' && !empty($item_wrapper['existing_content']['content_selection'])) {
                $item = $item_wrapper['existing_content'];
                $status = $item['content_selection']->post_status;
                if ($status !== 'publish') {
                    continue;
                }
                switch ($item['content_selection']->post_type) {
                    case 'page':
                        $post_preview = $this->getPagePreview($layout, $item['content_selection'], $clickable);
                        break;
                    case 'touristic_sheet':
                        $post_preview = $this->getTouristicSheetPreview($layout, $item['content_selection']);
                        break;
                    case 'woody_topic':
                        $post_preview = $this->getTopicPreview($layout, $item['content_selection']);
                        break;
                }
                $the_items['items'][$key] = (!empty($post_preview)) ?  $post_preview : '';
            }
        }

        if (!empty($the_items['items']) && is_array($the_items['items']) && $layout['focused_sort'] == 'random') {
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
    public function getAutoFocusSheetData($layout)
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
     * @author: Jérémy Legendre
     * Retourne un tableau de données relatives au Topics
     * @param layout
     * @return items
     */
    public function getAutoFocusTopicsData($layout)
    {
        //TODO: call processWoodyQuery
        $items = [];

        $feeds = [];
        foreach ($layout['topic_newspaper'] as $term_id) {
            $term = get_term($term_id, 'topic_newspaper');
            $feeds[] = $term->name;
        }
        $time = !empty($layout['publish_date']) ? strtotime($layout['publish_date']) : 0;
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

        if ($layout['focused_sort'] == 'title') {
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
        }

        $result = new \WP_Query($args);

        if (!empty($result->posts)) {
            foreach ($result->posts as $post) {
                $item = Timber::get_post($post->ID);
                $items['items'][] = $this->getTopicPreview($layout, $item);
            }
        }

        if ($layout['focused_sort'] == 'random') {
            shuffle($items['items']);
        } elseif ($layout['focused_sort'] == 'date') {
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
     * @param    item_wrapper - Données acf sous forme de tableau
     * @return   data - Un tableau de données
     *
     */
    public function getPagePreview($item_wrapper, $item, $clickable = true)
    {
        $data = [];

        $data['page_type'] = getTermsSlugs($item->ID, 'page_type', true);
        $data['post_id'] = $item->ID;

        if (!empty(get_field('focus_title', $item->ID))) {
            $data['title'] = $this->tools->replacePattern(get_field('focus_title', $item->ID), $item);
        } elseif (!empty(get_the_title($item->ID))) {
            $data['title'] = $this->tools->replacePattern(get_the_title($item->ID), $item);
        }

        if (!empty($item_wrapper) && !empty($item_wrapper['display_elements']) && is_array($item_wrapper['display_elements'])) {
            if (in_array('pretitle', $item_wrapper['display_elements'])) {
                $data['pretitle'] = $this->tools->replacePattern($this->tools->getFieldAndFallback($item, 'focus_pretitle', get_field('page_heading_heading', $item->ID), 'pretitle', $item, 'field_5b87f20257a1d'), $item);
            }
            if (in_array('subtitle', $item_wrapper['display_elements'])) {
                $data['subtitle'] = $this->tools->replacePattern($this->tools->getFieldAndFallback($item, 'focus_subtitle', get_field('page_heading_heading', $item->ID), 'subtitle', $item, 'field_5b87f23b57a1e'), $item);
            }
            if (in_array('icon', $item_wrapper['display_elements'])) {
                $data['woody_icon'] = $item->get_field('focus_woody_icon');
                $data['icon_type'] = 'picto';
            }
            if (in_array('description', $item_wrapper['display_elements'])) {
                $data['description'] = $this->tools->replacePattern($this->tools->getFieldAndFallback($item, 'focus_description', $item, 'field_5b2bbbfaec6b2'), $item);
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
            $data['link']['link_label'] = $this->tools->getFieldAndFallBack($item, 'focus_button_title', $item);
            if (empty($data['link']['link_label'])) {
                $data['link']['link_label'] = __('Lire la suite', 'woody-theme');
            }
        }

        if (!empty($item_wrapper['display_img'])) {
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

        // $post_type = get_post_terms($item->ID, 'page_type');

        return $data;
    }

    /**
     *
     * Nom : getCustomPreview
     * Auteur : Benoit Bouchaud
     * Return : Retourne les données d'une preview basée sur des champs custom
     * @param    item - Un tableau de données (Vignette créée dans le backoffice - N'est pas directement liéée à un contenu existant)
     * @param    item_wrapper - Tableau des données du champ acf
     * @return   data - Un tableau de données
     *
     */
    public function getCustomPreview($item, $item_wrapper = null)
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
        if (!empty($item_wrapper) && !empty($item_wrapper['display_button'])) {
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
     * @param    layout Le wrapper du champ de mise en avant
     * @param    post - objet post wp
     * @return   data - Un tableau de données
     *
     */

    function getTouristicSheetPreview($layout = null, $post)
    {
        if (!is_object($post) || empty($post)) {
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
            'title' => (!empty($item['title'])) ? replacePattern($item['title']) : '',
            'link' => [
                'url' => apply_filters('woody_get_permalink', $post->ID),
                'target' => (!empty($item['targetBlank'])) ? '_blank' : '',
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
                $data['description'] = (!empty($item['desc'])) ? replacePattern($item['desc']) : '';
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

        if (!empty($item['bordereau'])) {
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
     * @author Jérémy Legendre
     * @param   item_wrapper
     * @param   item
     * @return  data
     */
    function getTopicPreview($item_wrapper, $item)
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

        if (!empty($item_wrapper['display_button'])) {
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
}
