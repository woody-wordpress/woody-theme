<?php

namespace WoodyProcess\Getters;

use Woody\Modules\GroupQuotation\GroupQuotation;
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
        $this->tools = new WoodyTheme_WoodyProcessTools();
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
        $process = new WoodyTheme_WoodyProcess();
        $query_result = $process->processWoodyQuery($current_post, $wrapper, $paginate, $uniqid, $ingore_maxnum, $posts_in, $filters);
        $pinned_content_id = 0;

        // On vérifie si du contenu épinglé a été ajouté et on récupère son ID
        if (!empty($wrapper['focused_pinnable']) && !empty($wrapper['pinnable_selection'])) {
            $pinned_content_id = $wrapper['pinnable_selection']->ID;
        }

        // On transforme la donnée des posts récupérés pour coller aux templates de blocs Woody
        if (!empty($query_result->posts)) {
            foreach ($query_result->posts as $key => $post) {
                $data = '';

                //On formate les données en fonction du type de mise en avant
                if (!empty($wrapper['focused_type']) && $wrapper['focused_type'] == 'documents') {
                    $data = $this->getAttachmentPreview($wrapper, $post);
                } elseif ($pinned_content_id != $post->ID) {
                    $data = $this->getPagePreview($wrapper, $post);
                }

                if (!empty($data)) {
                    $the_items['items'][$key] = $data;
                }
            }

            $the_items['max_num_pages'] = $query_result->max_num_pages;
            $the_items['wp_query'] = $query_result;
        }

        // On vérifie si du contenu épinglé a été ajouté et on traite les données
        if (!empty($pinned_content_id) && is_array($the_items['items'])) {
            switch ($wrapper['pinnable_selection']->post_type) {
                case 'touristic_sheet':
                    $pinned_post_preview = $this->getTouristicSheetPreview($wrapper, $wrapper['pinnable_selection']);
                    break;
                case 'woody_topic':
                    $pinned_post_preview = $this->getTopicPreview($wrapper, $wrapper['pinnable_selection']);
                    break;
                default:
                    $pinned_post_preview = $this->getPagePreview($wrapper, $wrapper['pinnable_selection']);
                    break;
            }

            // $focused_pinnable[] = (!empty($pinned_post_preview)) ?  $pinned_post_preview : [];

            // on ajoute le contenu épinglé au début du tableau
            array_unshift($the_items['items'], $pinned_post_preview);
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
                    $clickable = !empty($item_wrapper['existing_content']['clickable_component']);
                }

                // La donnée de la vignette est saisie en backoffice
                if ($item_wrapper['content_selection_type'] == 'custom_content' && !empty($item_wrapper['custom_content'])) {
                    $the_items['items'][$key] = $this->getCustomPreview($item_wrapper['custom_content'], $wrapper, $item_wrapper['content_selection_type']);
                // La donnée de la vignette correspond à un post sélectionné
                } elseif ($item_wrapper['content_selection_type'] == 'existing_content' && !empty($item_wrapper['existing_content']['content_selection'])) {
                    $item = $item_wrapper['existing_content'];
                    $post = get_post($item['content_selection']);
                    if (!empty($post) && $post->post_status == 'publish') {
                        switch ($post->post_type) {
                            case 'touristic_sheet':
                                $post_preview = $this->getTouristicSheetPreview($wrapper, $post);
                                break;
                            case 'woody_topic':
                                $post_preview = $this->getTopicPreview($wrapper, $post);
                                break;
                            default:
                                $post_preview = $this->getPagePreview($wrapper, $post, $clickable);
                                break;
                        }

                        $the_items['items'][$key] = (empty($post_preview)) ? [] : $post_preview;
                    }
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
    public function getAutoFocusSheetData($wrapper, $playlist_params = [])
    {
        $items = [];
        if (!empty($wrapper['playlist_conf_id'])) {
            $confId = $wrapper['playlist_conf_id'];
            $lang = apply_filters('woody_autofocus_sheet_lang', pll_current_language());
            $playlist = apply_filters('woody_hawwwai_playlist_render', $confId, pll_current_language(), $playlist_params, 'json');
            if (!empty($playlist['items'])) {
                foreach ($playlist['items'] as $item) {
                    $wpSheetNode = apply_filters('woody_hawwwai_get_post_by_sheet_id', $item['sheetId'], $lang, ['publish']);
                    if (!empty($wpSheetNode)) {
                        if (is_array($wpSheetNode)) {
                            $wpSheetNode = current($wpSheetNode);
                        }

                        if (!empty($wrapper['deal_mode'])) {
                            if (!empty($item["deals"])) {
                                foreach ($item["deals"]['list'] as $index => $deal) {
                                    $items['items'][] = $this->getTouristicSheetPreview($wrapper, $wpSheetNode->getPost(), $index);
                                }
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
            $tax_query['relation'] = (empty($wrapper['focused_taxonomy_terms_andor'])) ? 'OR' : $wrapper['focused_taxonomy_terms_andor'];

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

        $time = empty($wrapper['publish_date']) ? 0 : strtotime($wrapper['publish_date']);
        $args = [
            'posts_per_page' => empty($wrapper['focused_count']) ? 9 : (int) $wrapper['focused_count'],
            'post_status' => 'publish',
            'post_type' => 'woody_topic',
            'meta_query' => array(
                'relation' => 'AND',
            ),
            'tax_query' => empty($tax_query) ? '' : $tax_query
        ];

        if (!empty($time)) {
            $args['meta_query'][] = array(
                'key' => 'woody_topic_publication',
                'value' => $time,
                'compare' => '>'
            );
        }

        if (!empty($feeds)) {
            $args['meta_query'][] = array(
                'key' => 'woody_topic_category',
                'value' => $feeds,
                'compare' => 'IN'
            );
        }

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

    public function getAttachmentPreview($wrapper, $post)
    {
        $data = [];
        if (is_object($post)) {
            $data = $this->getPagePreview($wrapper, $post, true, true);

            if (!empty($wrapper['display_elements']) && in_array('description', $wrapper['display_elements'])) {
                $data['description'] = $post->post_content;
            }

            if (!empty($wrapper['display_img'])) {
                if (wp_attachment_is_image($post->ID)) {
                    $data['img'] = acf_get_attachment($post);
                } else {
                    $data['img'] = get_field('attachment_focus_img', $post->ID);
                }

                if (!empty($data['img'])) {
                    $data['img']['attachment_more_data'] = $this->tools->getAttachmentMoreData($data['img']['ID']);
                }
            }

            $data['link'] = [
                'url' => wp_get_attachment_url($post->ID),
                'link_label' => __('Télécharger')
            ];

            $data['page_type'] = 'attachment';
        }

        return $data;
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
    public function getPagePreview($wrapper, $post, $clickable = true, $is_attachment = false)
    {
        $fields_profil = [];
        $data = [];

        if (!is_object($post)) {
            return;
        }

        $data['page_type'] = getTermsSlugs($post->ID, 'page_type', true);
        $data['post_id'] = $post->ID;

        if (!empty(get_field('focus_title', $post->ID))) {
            $data['title'] = $this->tools->replacePattern(get_field('focus_title', $post->ID), $post->ID);
        } elseif (!empty(get_the_title($post->ID))) {
            $data['title'] = $this->tools->replacePattern(get_the_title($post->ID), $post->ID);
        }

        $original_item = $post;

        // On vérifie si la page est de type miroir
        if ($data['page_type'] == 'mirror_page') {
            // On retourne la page de référence de la page miroir
            $mirror_id = get_field('mirror_page_reference', $post->ID);
            $mirror_post = get_post($mirror_id);

            // On remplace l'objet de post courant par l'objet de post de référence de la page miroir
            if (!empty($mirror_post) && $mirror_post->post_status == 'publish') {
                $post = $mirror_post;
            }
        }

        if (!empty($wrapper) && !empty($wrapper['display_elements']) && is_array($wrapper['display_elements'])) {
            if (empty($is_attachment) && in_array('pretitle', $wrapper['display_elements'])) {
                $data['pretitle'] = $this->tools->replacePattern($this->tools->getFieldAndFallback($original_item, 'focus_pretitle', get_field('page_heading_heading', $post->ID), 'pretitle', $post, 'field_5b87f20257a1d', $data['page_type']), $original_item->ID);
            }

            if (empty($is_attachment) && in_array('subtitle', $wrapper['display_elements'])) {
                $data['subtitle'] = $this->tools->replacePattern($this->tools->getFieldAndFallback($original_item, 'focus_subtitle', get_field('page_heading_heading', $post->ID), 'subtitle', $post, 'field_5b87f23b57a1e', $data['page_type']), $original_item->ID);
            }

            if (empty($is_attachment) && in_array('icon', $wrapper['display_elements'])) {
                $data['woody_icon'] = get_field('focus_woody_icon', $original_item->ID);
                $data['icon_type'] = 'picto';
            }

            if (empty($is_attachment) && in_array('description', $wrapper['display_elements'])) {
                $data['description'] = $this->tools->replacePattern($this->tools->getFieldAndFallback($original_item, 'focus_description', '', '', $post, 'field_5b2bbbfaec6b2', $data['page_type']), $original_item->ID);
            }

            if (in_array('created', $wrapper['display_elements'])) {
                $data['created'] = get_the_date('', $post->ID);
            }

            if (empty($is_attachment) && in_array('price', $wrapper['display_elements'])) {
                $price_type = get_field('the_price_price_type', $post->ID);
                // TODO: passer par le filtre woody_custom_pagePreview dans l'addon-group-quotation
                if ($price_type == "component_based") {
                    $groupQuotation = new GroupQuotation();
                    $trip_infos = getAcfGroupFields('group_5b6c5e6ff381d', $post);
                    $data['the_price'] = $groupQuotation->calculTripPrice($trip_infos['the_price'], $post);
                } else {
                    $data['the_price'] = get_field('field_5b6c670eb54f2', $post->ID);
                }
            }

            if (empty($is_attachment) && in_array('duration', $wrapper['display_elements'])) {
                $data['the_duration'] = get_field('field_5b6c5e7cb54ee', $post->ID);
            }

            if (empty($is_attachment) && in_array('length', $wrapper['display_elements'])) {
                $data['the_length'] = get_field('field_5b95423386e8f', $post->ID);
            }

            if (empty($is_attachment) && in_array('linked_profil', $wrapper['display_elements'])) {
                $profil_type = get_field('profil_type', $post->ID);

                if ($profil_type == 'existing_profile') {
                    $existing_profile_id = get_field('linked_profile', $post->ID);

                    if (!empty($existing_profile_id)) {
                        $fields_profil = [
                            'name' => get_the_title($existing_profile_id),
                            'img' => get_field('profile_picture', $existing_profile_id),
                        ];
                    }
                } else {
                    $fields_profil = [
                        'name' => get_field('profil_name', $post->ID),
                        'img' => get_field('profil_img', $post->ID),
                    ];
                }

                if ($fields_profil['img']) {
                    $data['profil']['img'] = $fields_profil['img'];
                    $data['profil']['img']['attachment_more_data'] = $this->tools->getAttachmentMoreData($fields_profil['img']['ID']);
                }

                if ($fields_profil['name']) {
                    $data['profil']['name'] = $fields_profil['name'];
                }
            }

            foreach ($wrapper['display_elements'] as $display) {
                if (strpos($display, '_') === 0) {
                    $tax = ltrim($display, '_');
                    $data['terms'][$tax] = getPrimaryTerm($tax, $post->ID, array('name', 'slug', 'term_id'));
                }
            }
        }

        $data['the_peoples'] = get_field('field_5b6d54a10381f', $post->ID);

        if (empty($is_attachment) && $clickable) {
            $data['link']['link_label'] = $this->tools->replacePattern($this->tools->getFieldAndFallBack($original_item, 'focus_button_title', '', '', $post, '', $data['page_type']), $original_item->ID);
            if (empty($data['link']['link_label'])) {
                $data['link']['link_label'] = __('Lire la suite', 'woody-theme');
            }
        }

        if (empty($is_attachment) && !empty($wrapper['display_img'])) {
            $data['img'] = $this->tools->getFieldAndFallback($original_item, 'focus_img', '', '', $post, 'field_5b0e5ddfd4b1b', $data['page_type']);
            if (empty($data['img'])) {
                $video = $this->tools->getFieldAndFallback($original_item, 'field_5b0e5df0d4b1c', '', '', $post, '', $data['page_type']);
                $data['img'] = empty($video) ? '' : $video['movie_poster_file'];
            }

            if (!empty($data['img'])) {
                $data['img']['attachment_more_data'] = $this->tools->getAttachmentMoreData($data['img']['ID']);

                // On génère un tableau des urls de toutes les images complémentaires de mise en avant
                if (!empty($wrapper['display_slideshow'])) {
                    $slideshow = get_field('focus_secondary_img', $post->ID);

                    if (!empty($slideshow)) {
                        foreach ($slideshow as $slide_key => $slide) {
                            foreach ($slide['sizes'] as $size_key => $size) {
                                if (strpos($size_key, 'height') === false && strpos($size_key, 'width') === false) {
                                    $slideshow_data['srcs'][$slide_key][$size_key] = $size;
                                }
                            }

                            $slideshow_data['alts'][$slide_key] = $slide['alt'];
                        }

                        $data['slideshow'] = $slideshow_data;
                    }
                }
            }
        }

        $data['location'] = [];
        $lat = get_field('post_latitude', $post->ID);
        $lng = get_field('post_longitude', $post->ID);
        $data['location']['lat'] = (empty($lat)) ? '' : str_replace(',', '.', $lat);
        $data['location']['lng'] = (empty($lng)) ? '' : str_replace(',', '.', $lng);

        if (empty($is_attachment) && $clickable) {
            $data['link']['url'] = woody_get_permalink($original_item->ID);
        }

        return apply_filters('woody_custom_pagePreview', $data, $wrapper);
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
    public function getCustomPreview($item, $wrapper = null, $content_type = null)
    {
        $remove_ellipsis = (!empty($content_type)) && ($content_type == 'custom_content');
        $data = [
            'title' => (empty($item['title'])) ? '' : $item['title'],
            'pretitle' => (empty($item['pretitle'])) ? '' : $item['pretitle'],
            'subtitle' => (empty($item['subtitle'])) ? '' : $item['subtitle'],
            'icon_type' => (empty($item['icon_type'])) ? '' : $item['icon_type'],
            'woody_icon' => (empty($item['woody_icon'])) ? '' : $item['woody_icon'],
            'icon_img' => (empty($item['icon_img']['url'])) ? '' : [
                'sizes' => [
                    'thumbnail' => $item['icon_img']['sizes']['medium'],
                    'ratio_free' => $item['icon_img']['sizes']['ratio_free_small']
                ],
                'alt' =>  $item['icon_img']['alt'],

            ],
            'description' => (empty($item['description'])) ? '' : $item['description'],
            'ellipsis' => 999,
            'remove_ellipsis' => $remove_ellipsis,
            'location' => [
                'lat' => empty($item['latitude']) ? '' : str_replace(',', '.', $item['latitude']),
                'lng' => empty($item['longitude']) ? '' : str_replace(',', '.', $item['longitude'])
            ]
        ];

        if ($item['action_type'] == 'file' && !empty($item['file']['url'])) {
            $data['link'] = [
                'url' => (empty($item['file']['url'])) ? '' : $item['file']['url'],
                'title' => __('Télécharger', 'woody-theme'),
                'target' => '_blank',
            ];
        } else {
            $data['link'] = [
                'url' => (empty($item['link']['url'])) ? '' : $item['link']['url'],
                'title' => (empty($item['link']['title'])) ? '' : $item['link']['title'],
                'target' => (empty($item['link']['target'])) ? '' : $item['link']['target'],
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
    public function getTouristicSheetPreview($wrapper = null, $post, $deal_index = 0)
    {
        if (!is_object($post) || empty($post)) {
            return;
        }

        $current_lang = pll_current_language();
        $languages = apply_filters('woody_pll_the_languages', 'auto');

        // Seasons
        foreach ($languages as $language) {
            if ($language['current_lang']) {
                $current_lang = substr($language['locale'], 0, 2);
            }
        }

        $sheet_item = woody_hawwwai_item($post->ID);

        $data = [
            'title' => (empty($sheet_item['title'])) ? '' : $sheet_item['title'],
            'post_id' => $post->ID,
            'link' => [
                'url' => woody_get_permalink($post->ID),
                'target' => (empty($sheet_item['targetBlank'])) ? '' : '_blank',
            ],
        ];
        if (!empty($wrapper['display_img'])) {
            $data['img'] = [
                'resizer' => true,
                'url' => (!empty($sheet_item['img']['url']) && !empty($sheet_item['img']['url']['manual'])) ? str_replace('api.tourism-system.com', 'api.cloudly.space', $sheet_item['img']['url']['manual']) : '',
                'alt' => (empty($sheet_item['img']['alt'])) ? '' : $sheet_item['img']['alt'],
                'title' => (empty($sheet_item['img']['title'])) ? '' : $sheet_item['img']['title']
            ];
        }

        if (!empty($wrapper['deal_mode']) && !empty($sheet_item['deals'])) {
            $data['title'] = $sheet_item['deals']['list'][$deal_index]['nom'][$current_lang];
        }

        if (!empty($wrapper['display_elements']) && is_array($wrapper['display_elements'])) {
            if (in_array('sheet_type', $wrapper['display_elements'])) {
                $data['sheet_type'] = (empty($sheet_item['type'])) ? '' : $sheet_item['type'];
                if (!empty($wrapper['deal_mode']) && !empty($sheet_item['deals'])) {
                    $data['sheet_type'] = $sheet_item['title'];
                }
            }

            if (in_array('description', $wrapper['display_elements'])) {
                $data['description'] = (empty($sheet_item['desc'])) ? '' : $sheet_item['desc'];
                if (!empty($wrapper['deal_mode']) && !empty($sheet_item['deals']['list'][$deal_index]['description'][$current_lang])) {
                    $data['description'] = $sheet_item['deals']['list'][$deal_index]['description'][$current_lang];
                }
            }

            if (in_array('sheet_itinerary', $wrapper['display_elements'])) {
                $data['sheet_itinerary']['locomotions'] = (empty($sheet_item['locomotions'])) ? '' : $sheet_item['locomotions'];
                $data['sheet_itinerary']['length'] = (empty($sheet_item['itineraryLength'])) ? '' : $sheet_item['itineraryLength']['value'] . $sheet_item['itineraryLength']['unit'];
            }

            if (in_array('sheet_town', $wrapper['display_elements'])) {
                $data['sheet_town'] = (empty($sheet_item['town'])) ? '' : $sheet_item['town'];
            }

            if (in_array('price', $wrapper['display_elements'])) {
                $data['the_price']['price'] = (empty($sheet_item['tariffs']['price'])) ? '' : $sheet_item['tariffs']['price'];
                $data['the_price']['prefix_price'] = (empty($sheet_item['tariffs']['label'])) ? '' : $sheet_item['tariffs']['label'];
            }

            if (in_array('bookable', $wrapper['display_elements'])) {
                $data['booking'] = (empty($sheet_item['booking']['link'])) ? '' : $sheet_item['booking'];
            }

            if (in_array('address', $wrapper['display_elements'])) {
                $data['address'] = (empty($sheet_item['address'])) ? '' : $sheet_item['address'];
            }

            if (in_array('phone', $wrapper['display_elements'])) {
                $data['phone'] = (empty($sheet_item['phone'])) ? '' : $sheet_item['phone'];
            }

            if (in_array('website', $wrapper['display_elements'])) {
                $data['website'] = (empty($sheet_item['website'])) ? '' : $sheet_item['website'];
            }
        }

        if (!empty($wrapper['display_button'])) {
            $data['link']['link_label'] = __('Lire la suite', 'woody-theme');
        }

        if (!empty($sheet_item['bordereau']) && ($sheet_item['bordereau'] === 'HOT' || $sheet_item['bordereau'] == 'HPA')) {
            $rating = [];
            if (!empty($sheet_item['ratings'])) {
                for ($i = 0; $i < $sheet_item['ratings'][0]['value']; ++$i) {
                    $rating[] = '<span class="wicon wicon-031-etoile-pleine"><span>';
                }
            }

            if (!empty($wrapper['display_elements']) && is_array($wrapper['display_elements']) && in_array('sheet_rating', $wrapper['display_elements'])) {
                $data['sheet_rating'] = implode('', $rating);
            }
        }

        if (is_array($wrapper['display_elements']) && in_array('grade', $wrapper['display_elements'])) {
            $data['grade'] = true;
        }

        $data['location'] = [];
        $data['location']['lat'] = (empty($sheet_item['gps'])) ? '' : $sheet_item['gps']['latitude'];
        $data['location']['lng'] = (empty($sheet_item['gps'])) ? '' : $sheet_item['gps']['longitude'];

        // Parcourir tout le tableau de dates et afficher la 1ère date non passée
        if ($sheet_item['bordereau'] == 'FMA' && !empty($sheet_item['dates'])) {
            $today = time();
            foreach ($sheet_item['dates'] as $date) {
                $enddate= strtotime($date['end']['endDate']);
                if ($today < $enddate) {
                    $data['date'] = $date;
                    break 1 ;
                }
            }
        }

        $data['sheet_id'] = get_field('touristic_sheet_id', $post->ID);

        return apply_filters('woody_custom_sheetPreview', $data, $wrapper, $post, $sheet_item);
    }

    /**
     * @author Jérémy Legendre
     * @param   wrapper - Données du layout acf sous forme de tableau
     * @param   post - Objet post wp
     * @return  data - Un tableau de données
     */
    public function getTopicPreview($wrapper, $post)
    {
        $data = [
            'post_id'   => $post->ID,
            'title'     => empty($post->post_title) ? '' : $post->post_title,
            'subtitle'  => empty($post->woody_topic_blogname) ? '' : $post->woody_topic_blogname
        ];

        $woody_topic_img = get_field('woody_topic_img', $post->ID);
        $woody_topic_attachment = get_field('woody_topic_attachment', $post->ID);
        if (!empty($woody_topic_img) && !$woody_topic_attachment) {
            $data['img'] = [
                'url' =>  'https://api.cloudly.space/resize/crop/%width%/%height%/75/' .  str_replace(array("+", "/"), array("-", "_"), base64_encode($woody_topic_img)) . '/image.jpg',
                'resizer' => true
            ];
        } elseif (!empty($woody_topic_attachment)) {
            $data['img'] = [
                'url' => empty(wp_get_attachment_image_src($woody_topic_attachment)) ? '' : wp_get_attachment_image_src($woody_topic_attachment)[0],
                'resizer' => true
            ];
        }

        $desc = get_field('woody_topic_desc', $post->ID);
        if (!empty($desc)) {
            $data['description'] = strlen($desc) > 256 ? substr($desc, 0, 256) : $desc ;
        }

        $url = get_field('woody_topic_url', $post->ID);
        if (!empty($url)) {
            $data['link'] = [
                'url' => $url,
                'title' => __('Découvrir', 'woody-theme'),
                'link_label' => __('Découvrir', 'woody-theme'),
                'target' => '_blank',
            ];
        }

        $lat = get_field('post_latitude', $post->ID);
        $lng = get_field('post_longitude', $post->ID);
        if (!empty($lat) && !empty($lng)) {
            $data['location'] = [];
            $data['location']['lat'] = (empty($lat)) ? '' : str_replace(',', '.', $lat);
            $data['location']['lng'] = (empty($lng)) ? '' : str_replace(',', '.', $lng);
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
                                    $filtered_terms = (is_array($filtered_terms)) ? $filtered_terms : [$filtered_terms];
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
                                    $filtered_terms = (is_array($filtered_terms)) ? $filtered_terms : [$filtered_terms];
                                    if (in_array($term->term_id, $filtered_terms)) {
                                        $return[$key]['list_filter_custom_terms'][$term_key]['checked'] = true;
                                    }
                                }
                            }
                        }

                        $return[$key]['filter_name'] = $filter['list_filter_name'];
                        break;

                    case 'map':
                        if (empty($filter['list_filter_map_params']['tmaps_confid']) && !empty(get_field('tmaps_confid', 'option'))) {
                            $filter['list_filter_map_params']['tmaps_confid'] = get_field('tmaps_confid', 'option');
                        }

                        $return['the_map'] = $filter;
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
                            $return[$key]['minmax']['default_max'] = (empty($active_filters['focused_trip_' . $filter['list_filter_type']]['max'])) ? false : $active_filters['focused_trip_' . $filter['list_filter_type']]['max'];
                            $return[$key]['minmax']['default_min'] = (empty($active_filters['focused_trip_' . $filter['list_filter_type']]['min'])) ? false : $active_filters['focused_trip_' . $filter['list_filter_type']]['min'];
                        }

                        $return[$key]['filter_name'] = $filter['list_filter_name'];
                        break;
                }
            }

            $return['button'] = (empty($filter_wrapper['filter_button'])) ? '' : $filter_wrapper['filter_button'];
            $return['reset'] = (empty($filter_wrapper['reset_button'])) ? '' : $filter_wrapper['reset_button'];
            $return['open_auto'] = (empty($filter_wrapper['listfilter_open_auto'])) ? '' : $filter_wrapper['listfilter_open_auto'];
            $return['display']['background_img'] = (empty($filter_wrapper['background_img'])) ? '' : $filter_wrapper['background_img'];
            $return['display']['classes'][] = (empty($filter_wrapper['background_color'])) ? '' : $filter_wrapper['background_color'];
            $return['display']['classes'][] = (empty($filter_wrapper['background_color_opacity'])) ? '' : $filter_wrapper['background_color_opacity'];
            $return['display']['classes'][] = (empty($filter_wrapper['background_img_opacity'])) ? '' : $filter_wrapper['background_img_opacity'];
            $return['display']['classes'][] = (empty($filter_wrapper['border_color'])) ? '' : $filter_wrapper['border_color'];
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
        $sheet_item = woody_hawwwai_item($post->ID);
        $sheet_url = woody_get_permalink($post->ID);

        $data['title'] = empty($sheet_item['title']) ? '' : $sheet_item['title'];
        $data['link']['url'] = $sheet_url;
        $data['link']['target'] = empty($sheet_item['targetBlank']) ? '' : '_blank';

        //TODO: Récupérer les infos de réservation de la fiche
        /*if ($sheet_item['booking']['central']) {
            $data['booking']['prefix'] = 'TODO';
            $data['booking']['price'] = 'TODO';
            $data['booking']['link'] = 'TODO';
        }*/

        // Display Imgs
        if (!empty($sheet_item['allImgs'])) {
            foreach ($sheet_item['allImgs'] as $key => $img) {
                $data['imgs'][$key] = [
                    'resizer' => true,
                    'url' => $img['manual'],
                    'alt' => '',
                    'title' => ''
                ];
            }
        }

        // Display options
        $data['anchors'] = [
            'detail' => [
                'url' => $sheet_url . '#establishment-detail',
                'title' => __('Informations prestataire', 'woody-theme'),
                'icon' => 'wicon-028-plus-02'
            ],
            'openings' => [
                'url' => $sheet_url . '#openings',
                'title' => __('Horaires', 'woody-theme'),
                'icon' => 'wicon-015-horloge'
            ],
            'map' => [
                'url' => $sheet_url . '#map',
                'title' => __('Carte', 'woody-theme'),
                'icon' => 'wicon-022-itineraire'
            ],
            'reviews' => [
                'url' => $sheet_url . '#reviews',
                'title' => __('Avis', 'woody-theme'),
                'icon' => 'wicon-012-smiley-bien'
            ],
            'contact' => [
                'url' => $sheet_url,
                'title' => __('Poser une question', 'woody-theme'),
                'icon' => 'wicon-016-bulle'
            ],
        ];

        $data = apply_filters('woody_custom_minisheet_data', $data, $wrapper);

        // Shuffle anchors positions
        if (!empty($data['anchors'])) {
            $data['anchors'] = array_values($data['anchors']);
            shuffle($data['anchors']);
        }

        //  Removes image if sum of anchors and images > 8
        if (!empty($data['imgs']) && $data['anchors']) {
            while ((is_countable($data['imgs']) ? count($data['imgs']) : 0) + (is_countable($data['anchors']) ? count($data['anchors']) : 0) > 8) {
                array_splice($data['imgs'], -1, 1);
            }
        }

        return $data;
    }

    public function getProfileFocusData($wrapper)
    {
        $data = [];
        if ($wrapper['profile_auto_focus']) {
            $args = [
                'post_type' => ['profile'],
                'post_status' => ['publish']
            ];

            if (!empty($wrapper['profile_focus_max'])) {
                $args['posts_per_page'] = $wrapper['profile_focus_max'];
            }

            if (!empty($wrapper['profile_focus_category'])) {
                foreach ($wrapper['profile_focus_category'] as $term_id) {
                    $terms_ids[] = $term_id;
                }

                $args['tax_query'] = [                     //(array) - use taxonomy parameters (available with Version 3.1).
                    'relation' => 'OR',                      //(string) - Possible values are 'AND' or 'OR' and is the equivalent of running a JOIN for each taxonomy
                    [
                        'taxonomy' => 'profile_category',
                        'field' => 'id',
                        'terms' => $terms_ids,
                        'operator' => 'IN'
                    ]
                ];
            }

            $the_query = new \WP_Query($args);
            if (!empty($the_query->posts)) {
                foreach ($the_query->posts as $post) {
                    $data['items'][] = $this->getProfilePreview($wrapper, $post);
                }
            }
        } elseif (!empty($wrapper['manual_profile_focus'])) {
            foreach ($wrapper['manual_profile_focus'] as $manual_profile) {
                $data['items'][] = $this->getProfilePreview($wrapper, $manual_profile['manual_profile']);
            }
        }

        return $data;
    }

    public function getProfilePreview($wrapper, $post)
    {
        $data = [
            'title' => $post->post_title,
            'firstname' => get_field('profile_firstname', $post->ID),
            'lastname' => get_field('profile_lastname', $post->ID),
            'img' => get_field('profile_picture', $post->ID)
        ];

        if (!empty($wrapper['profile_focus_display'])) {
            if (in_array('complement', $wrapper['profile_focus_display'])) {
                $data['complement'] = get_field('profile_complement', $post->ID);
            }

            if (in_array('description', $wrapper['profile_focus_display'])) {
                $data['description'] = get_field('profile_description', $post->ID);
            }

            if (in_array('birth', $wrapper['profile_focus_display'])) {
                $data['birth'] = get_field('profile_contacts_profile_birth', $post->ID);
            }

            if (in_array('nationality', $wrapper['profile_focus_display'])) {
                $data['nationality'] = get_field('profile_contacts_profile_nationality', $post->ID);
            }

            if (in_array('address', $wrapper['profile_focus_display'])) {
                $data['contacts']['address'] = get_field('profile_contacts_profile_address', $post->ID);
            }

            if (in_array('mail', $wrapper['profile_focus_display'])) {
                $data['contacts']['mail'] = get_field('profile_contacts_profile_mail', $post->ID);
            }

            if (in_array('mailto', $wrapper['profile_focus_display'])) {
                $email = empty($data['contacts']['mail']) ? get_field('profile_contacts_profile_mail', $post->ID) : $data['contacts']['mail'];
                $data['contacts']['mailto'] = base64_encode($email);
            }

            if (in_array('phone', $wrapper['profile_focus_display'])) {
                $data['contacts']['phone'] = get_field('profile_contacts_profile_phone', $post->ID);
            }

            if (in_array('mobile', $wrapper['profile_focus_display'])) {
                $data['contacts']['mobile'] = get_field('profile_contacts_profile_mobile', $post->ID);
            }

            if (in_array('linkedin', $wrapper['profile_focus_display'])) {
                $data['socials']['linkedin'] = get_field('profile_contacts_profile_socials_profile_linkedin', $post->ID);
            }

            if (in_array('twitter', $wrapper['profile_focus_display'])) {
                $data['socials']['twitter'] = get_field('profile_contacts_profile_socials_profile_twitter', $post->ID);
            }
        }

        if (!empty($wrapper['profile_focus_expressions'])) {
            $data['focus_expressions'] = $this->getProfileExpressions($post->ID, $wrapper['profile_focus_expressions']);
        }

        return $data;
    }

    public function getProfileExpressions($post_id, $focus_expressions)
    {
        $data = [];

        $profile_expressions = get_field('profile_expressions', $post_id);

        if (!empty($profile_expressions)) {
            $formatted_expressions = $this->formatProfileExpressions($profile_expressions);
            foreach ($focus_expressions as $expression_id) {
                if (!empty($formatted_expressions[$expression_id])) {
                    $data[$formatted_expressions[$expression_id]['order']] = $formatted_expressions[$expression_id];
                }
            }
        }

        return $data;
    }

    private function formatProfileExpressions($profile_expressions)
    {
        $data = [];
        foreach ($profile_expressions as $exp_key => $expression) {
            $data[$expression['profile_expression_category']->term_id] = [
                'order' => $exp_key,
                'title' => $expression['profile_expression_category']->name,
                'content' => (empty($expression['profile_expression_content'])) ? '' : $expression['profile_expression_content']
            ];
        }

        return $data;
    }
}
