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
     * Nom : getCatalogFocusData
     * Auteur : Orphée Besson
     * Return : Retourne un ensemble de posts sous forme de tableau avec une donnée compatbile Woody
     * @param    current_post - Un objet Timber\Post
     * @param    wrapper - Un tableau des champs
     * @return   the_items - Tableau de contenus compilés + infos complémentaires
     *
     */
    public function getCatalogFocusData($current_post, $wrapper, $twigPaths, $paginate = false, $uniqid = 0, $ingore_maxnum = false, $posts_in = null, $filters = null)
    {
        $the_items = [];

        // Formatage des pages parentes (contenu manuel uniquement autorisé)
        $the_items = $this->getManualFocusData($wrapper);

        // Formatage des pages enfantes (contenu manuel ou automaique autorisé)
        if(!empty($the_items) && !empty($the_items['items'])) {
            foreach ($the_items['items'] as $key_parent_item => $parent_item) {
                $the_items['items'][$key_parent_item]['subcontent'] = [];

                if (!empty($wrapper['content_selection'][$key_parent_item]) && !empty($wrapper['content_selection'][$key_parent_item]['subcontent'])) {
                    $subwrapper = $wrapper['content_selection'][$key_parent_item]['subcontent'];
                    // Contenu existant
                    if ($subwrapper['subcontent_selection_type'] == 'existing_content' && !empty($subwrapper['existing_content']) && !empty($subwrapper['existing_content']['content_selection'])) {
                        foreach ($subwrapper['existing_content']['content_selection'] as $key => $post_id) {
                            $post = get_post($post_id);
                            if (!empty($post) && $post->post_status == 'publish') {
                                $post_preview = $this->getAnyPostPreview($wrapper, $post);
                                $the_items['items'][$key_parent_item]['subcontent']['items'][$key] = $post_preview;
                                $the_items['items'][$key_parent_item]['subcontent']['items'][$key]['real_index'] = $key;
                            }
                        }
                    // Contenu automatique
                    } elseif ($subwrapper['subcontent_selection_type'] == 'auto_content' && !empty($subwrapper['auto_content'])) {
                        // On récupère les variables utiles à la query et on les merge dans un seul tableau
                        $params = empty($subwrapper['auto_content']) ? $subwrapper : array_merge($subwrapper, $subwrapper['auto_content']);
                        unset($params['auto_content']);
                        $the_items['items'][$key_parent_item]['subcontent'] = $this->getAutoFocusData($current_post, $params, $paginate, $uniqid, $ingore_maxnum, $posts_in, $filters);
                    }

                    if (!empty($wrapper['mobile_behaviour'])) {
                        $subwrapper['mobile_behaviour'] = $wrapper['mobile_behaviour'];
                    }

                    // On compile le template
                    if (!empty($the_items['items'][$key_parent_item]['subcontent']['items']) && is_array($the_items['items'][$key_parent_item]['subcontent']['items']) && !empty($subwrapper)) {
                        $the_items['items'][$key_parent_item]['subcontent']['html'] = compileFocusesLayouts($the_items['items'][$key_parent_item]['subcontent'], $subwrapper, $twigPaths);
                    }
                }
            }
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
                    $the_items['items'][$key]['real_index'] = $key;
                // La donnée de la vignette correspond à un post sélectionné
                } elseif ($item_wrapper['content_selection_type'] == 'existing_content' && !empty($item_wrapper['existing_content']['content_selection'])) {
                    $item = $item_wrapper['existing_content'];
                    $post = get_post($item['content_selection']);
                    if (!empty($post) && $post->post_status == 'publish') {
                        $post_preview = $this->getAnyPostPreview($wrapper, $post, $clickable);

                        $the_items['items'][$key] = $post_preview;
                        $the_items['items'][$key]['real_index'] = $key;
                    }
                }
            }
        }

        if (!empty($the_items['items']) && is_array($the_items['items']) && (!empty($wrapper['focused_sort']) && $wrapper['focused_sort'] == 'random')) {
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

            // On vérifie si la playlist choisie est précochée pour filtrer les fiches retournées
            if(!empty($wrapper['autoselect_id'])) {
                $playlist_params['autoselect_id'] = $wrapper['autoselect_id'];
            }

            // TODO optimization 'outOfMemory' : remplacer un appel sur l'api standard qui nous renverrait juste les IDs des fiches de la playliste (plustôt que de formater la donnée)
            // de toute façon l'item sera formater via la lib-hawwwai (on le fait donc actuellement 2 fois)
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
     *
     * Nom : getHighlightsFocusData
     * Auteur : Orphée Besson
     * Return : Retourne un ensemble de posts sous forme de tableau avec une donnée compatbile Woody
     * @param    current_post - Un objet Timber\Post
     * @param    wrapper - Un tableau des champs
     * @return   the_items - Tableau de contenus compilés + infos complémentaires
     *
     */
    public function getHighlightsFocusData($wrapper)
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

                // pretitle (date period)
                $pretitle = '';
                if (!empty($item_wrapper['highlight_start_date'])) {
                    $formatted_start_date = formatDate($item_wrapper['highlight_start_date'], 'l d F Y');

                    $start_day = formatDate($item_wrapper['highlight_start_date'], 'l d');
                    $start_month = formatDate($item_wrapper['highlight_start_date'], 'F');
                    $start_year = formatDate($item_wrapper['highlight_start_date'], 'Y');

                    $pretitle = __('Le', 'woody-theme') . ' ' . $start_day . ' ' . $start_month . ' ' . $start_year;

                    if(!empty($item_wrapper['highlight_end_date'])) {
                        $formatted_end_date = formatDate($item_wrapper['highlight_end_date'], 'l d F Y');

                        $end_day = formatDate($item_wrapper['highlight_end_date'], 'l d');
                        $end_month = formatDate($item_wrapper['highlight_end_date'], 'F');
                        $end_year = formatDate($item_wrapper['highlight_end_date'], 'Y');

                        // On vérifie si les dates sont dans la même année
                        if($start_year === $end_year) {
                            // On vérifie si les dates sont dans le même mois
                            if($start_month === $end_month) {
                                $pretitle = __('Du', 'woody-theme') . ' ' . $start_day . ' ' . __('au', 'woody-theme') . ' ' . $end_day . ' ' . $start_month . ' ' . $start_year;
                            } else {
                                $pretitle = __('Du', 'woody-theme') . ' ' . $start_day . ' ' . $start_month . ' ' . __('au', 'woody-theme') . ' ' . $end_day . ' ' . $end_month . ' ' . $start_year;
                            }
                        } else {
                            // Si les années sont différentes
                            $pretitle = __('Du', 'woody-theme') . ' ' . $formatted_start_date . ' ' . __('au', 'woody-theme') . ' ' . $formatted_end_date;
                        }
                    }
                }

                // La donnée de la vignette
                if ($item_wrapper['content_selection_type'] == 'existing_content' && !empty($item_wrapper['existing_content']['content_selection'])) {
                    // La donnée de la vignette correspond à un post sélectionné
                    $item = $item_wrapper['existing_content'];
                    $post = get_post($item['content_selection']);
                    if (!empty($post) && $post->post_status == 'publish') {
                        $post_preview = $this->getAnyPostPreview($wrapper, $post, $clickable);

                        $the_items['items'][$key] = $post_preview;
                        $the_items['items'][$key]['real_index'] = $key;
                        if (!empty($pretitle)) {
                            $the_items['items'][$key]['pretitle'] = $pretitle;
                        }
                    }
                } else if ($item_wrapper['content_selection_type'] == 'custom_content') {
                    // La donnée de la vignette correspond à un contenu libre
                    $the_items['items'][$key] = $this->getCustomPreview($item_wrapper['custom_content'], $wrapper, $item_wrapper['content_selection_type']);
                    $the_items['items'][$key]['real_index'] = $key;
                    if (!empty($pretitle)) {
                        $the_items['items'][$key]['pretitle'] = $pretitle;
                    }
                }
            }
        }

        return $the_items;
    }

    /**
     *
     * Nom : formatHighlightsTimeline
     * Auteur : Orphée Besson
     * Return : Retourne un tableau formaté pour la timeline du bloc "Temps forts"
     * @param    wrapper - Un tableau des champs
     * @return   return - Tableau de contenus compilés
     *
     */
    public function formatHighlightsTimeline($wrapper)
    {
        $return = [];

        if(!empty($wrapper['highlights_start_date']) && !empty($wrapper['highlights_end_date'])) {
            $return['start_date'] = [
                'raw' => $wrapper['highlights_start_date'],
                'formatted' => formatDate($wrapper['highlights_start_date'], 'M Y')
            ];
            $return['end_date'] = [
                'raw' => $wrapper['highlights_end_date'],
                'formatted' => formatDate($wrapper['highlights_end_date'], 'M Y')
            ];

            if (!empty($wrapper['content_selection'])) {
                foreach ($wrapper['content_selection'] as $key => $item_wrapper) {
                    $return['items'][$key] = empty($item_wrapper['highlight_start_date']) ? '' : $item_wrapper['highlight_start_date'];
                }
            }
        }

        return $return;
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

            if (!empty($wrapper['display_elements_documents']) && in_array('description', $wrapper['display_elements_documents'])) {
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
                'link_label' => __('Télécharger', 'woody-theme')
            ];

            $data['page_type'] = 'attachment';
        }

        return $data;
    }

    /**
     *
     * Nom : getAnyPostPreview
     * Auteur : Orphée Besson
     * Return : Retourne la donnée de base d'un post pour afficher une preview selon le post_type
     * @param    wrapper - Données du layout acf sous forme de tableau
     * @param    post - Un objet Timber\Post
     * @param    clickable - Un booléen
     * @return   post_preview - Un tableau de données
     *
     */
    public function getAnyPostPreview($wrapper, $post, $clickable = true)
    {
        $post_preview = [];

        switch ($post->post_type) {
            case 'touristic_sheet':
                $post_preview = $this->getTouristicSheetPreview($wrapper, $post);
                break;
            case 'youbook_product':
                $post_preview = $this->getYoubookPreview($wrapper, $post->ID);
                break;
            case 'woody_topic':
                $post_preview = $this->getTopicPreview($wrapper, $post);
                break;
            case 'page':
                $post_preview = $this->getPagePreview($wrapper, $post, $clickable);
                break;
            default:
                $post_preview = apply_filters('woody_custom_manual_focus', $post->ID, $wrapper);
                break;
        }

        return $post_preview;
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

            // Ajout du coeur de favoris
            if (in_array('favorites', $wrapper['display_elements'])) {
                $data['favorites'] = true;
            }

            foreach ($wrapper['display_elements'] as $display) {
                if (strpos($display, '_') === 0) {
                    $tax = ltrim($display, '_');
                    $primary_term = getPrimaryTerm($tax, $post->ID, array('name', 'slug', 'term_id'));
                    if (!empty($primary_term)) {
                        $data['terms'][$tax] = $primary_term;
                    }
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

        if(!empty($wrapper['live_preview'])) {
            // Le live preview permet de visualiser le média principal de la page + description et bouton au clic sur la mise en avant
            // Si aucun média n'est retourné par la fonction getLivePreview, cette fonctionnalité est inutile
            // Sinon, les champs affichés dans le live preview sont supprimés de la $data d'origine
            $data['live_preview'] = $this->getLivePreview($original_item, $post, $data);
            if($data['live_preview']['att_id']) {
                unset($data['link']);
                unset($data['description']);
            } else {
                unset($data['live_preview']);
            }
        }

        $data['cardDisplayOptions'] = empty($wrapper['display_elements']) ? [] : $wrapper['display_elements'];

        return apply_filters('woody_custom_pagePreview', $data, $wrapper);
    }

    private function getLivePreview($original_item, $post, $data)
    {
        $return = [
            'title' => $original_item->post_title,
            'description' => $data['description'],
            'link' => $data['link'],
        ];

        $movie = $this->tools->getFieldAndFallback($original_item, 'field_5b0e5df0d4b1c', '', '', $post, '', $data['page_type']);
        if(!empty($movie['mp4_movie_file']) && !empty($movie['mp4_movie_file']['ID'])) {
            $return['att_id'] = $movie['mp4_movie_file']['ID'];
            $return['media_type'] = 'movie';
        } elseif(!empty($movie['movie_webm_file']) && !empty($movie['movie_webm_file']['ID'])) {
            $return['att_id'] = $movie['movie_webm_file']['ID'];
            $return['media_type'] = 'movie';
        } else {
            $img = getFieldAndFallback($original_item, 'field_5b0e5ddfd4b1b', $post, 'focus_img', '', '', $data['page_type']);
            if(!empty($img) && !empty($img['ID'])) {
                $return['att_id'] = $img['ID'];
                $return['media_type'] = 'img';
            }
        }

        return $return;
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
            ],
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

        // Ajout du coeur de favoris
        if (in_array('favorites', $wrapper['display_elements'])) {
            $data['favorites'] = true;
        }

        $data['cardDisplayOptions'] = $wrapper['display_elements'];

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

        $sheet_item = woody_hawwwailib_item($post->ID);

        $sheet_title = empty($sheet_item['businessName']) ? '' : $sheet_item['businessName'];

        $data = [
            'title' => $sheet_title,
            'post_id' => $post->ID,
            'link' => [
                'url' => woody_get_permalink($post->ID),
                'target' => (empty($sheet_item['targetBlank'])) ? '' : '_blank',
            ],
        ];

        if (!empty($wrapper['display_img'])) {
            $url = (!empty($sheet_item['mainImg']) && !empty($sheet_item['mainImg']['url']) && !empty($sheet_item['mainImg']['url']['manual'])) ? str_replace('api.tourism-system.com', 'api.cloudly.space', $sheet_item['mainImg']['url']['manual']) : '';
            $data['img'] = [
                'resizer' => true,
                'url' => !empty($url) ? $url : '',
                'alt' => (empty($sheet_item['mainImg']['alt'])) ? '' : $sheet_item['mainImg']['alt'],
                'title' => (empty($sheet_item['mainImg']['title'])) ? '' : $sheet_item['mainImg']['title']
            ];
        }

        if (!empty($wrapper['deal_mode']) && !empty($sheet_item['deals'])) {
            $data['title'] = $sheet_item['deals']['list'][$deal_index]['nom'][$current_lang];
        }

        if (!empty($wrapper['display_elements']) && is_array($wrapper['display_elements'])) {
            if (in_array('sheet_type', $wrapper['display_elements'])) {
                $criterias = empty($sheet_item['criterias']) ? [] : $sheet_item['criterias'];
                $data['sheet_type'] = empty($criterias) ? '' : mb_strtoupper($criterias[0]['_criterion']);
                if (!empty($wrapper['deal_mode']) && !empty($sheet_item['deals'])) {
                    $data['sheet_type'] = $sheet_title;
                }
            }

            if (in_array('description', $wrapper['display_elements'])) {
                $sheet_description = empty($sheet_item['description']) ? '' : strip_tags(mb_convert_encoding($sheet_item['description'], 'UTF-8', 'UTF-8'));
                $sheet_slogan = empty($sheet_item['slogan']) ? '' : mb_convert_encoding($sheet_item['slogan'], 'UTF-8', 'UTF-8');
                $data['description'] = empty($sheet_slogan) ? $sheet_description : $sheet_slogan;
                if (!empty($wrapper['deal_mode']) && !empty($sheet_item['deals']['list'][$deal_index]['description'][$current_lang])) {
                    $data['description'] = $sheet_item['deals']['list'][$deal_index]['description'][$current_lang];
                }
            }

            if (in_array('sheet_itinerary', $wrapper['display_elements'])) {
                $data['sheet_itinerary']['locomotions'] = (empty($sheet_item['locomotions'])) ? '' : $sheet_item['locomotions'];
                $data['sheet_itinerary']['length'] = (empty($sheet_item['itineraryLength'])) ? '' : $sheet_item['itineraryLength']['value'] . $sheet_item['itineraryLength']['unit'];
            }

            if (in_array('sheet_town', $wrapper['display_elements'])) {
                $data['sheet_town'] = (empty($sheet_item['locality'])) ? '' : $sheet_item['locality'];
            }

            if (in_array('price', $wrapper['display_elements'])) {
                $data['the_price']['free'] = isset($sheet_item['referenceTariff']['price']) && $sheet_item['referenceTariff']['price'] == '0';
                $data['the_price']['price'] = (empty($sheet_item['referenceTariff']['price'])) ? '' : $sheet_item['referenceTariff']['price'];
                $data['the_price']['prefix_price'] = (empty($sheet_item['referenceTariff']['label'])) ? '' : $sheet_item['referenceTariff']['label'];
            }

            if (in_array('bookable', $wrapper['display_elements'])) {
                $data['booking'] = [
                    'link' => (empty($sheet_item['bookingUrl'])) ? '' : $sheet_item['bookingUrl']
                ];
            }

            if (in_array('address', $wrapper['display_elements'])) {
                $address = array_filter([$sheet_item['contacts']['establishment']['address1'], $sheet_item['contacts']['establishment']['address2'], $sheet_item['contacts']['establishment']['address3'], $sheet_item['contacts']['establishment']['zipCode']]);

                $fullAddress = implode(' ', $address);

                $data['address'] = (empty($fullAddress)) ? '' : $fullAddress;
            }

            if (in_array('phone', $wrapper['display_elements'])) {
                if (!empty($sheet_item['contacts']['establishment']['phones']) && !empty($sheet_item['contacts']['establishment']['phones'][0])) {
                    $data['phone'] = [
                        'number' => $sheet_item['contacts']['establishment']['phones'][0],
                        'text' => __('Appeler', 'woody-theme')
                    ];
                }
            }

            // Ajout du coeur de favoris
            if (in_array('favorites', $wrapper['display_elements'])) {
                $data['favorites'] = true;
            }

            if (in_array('website', $wrapper['display_elements'])) {
                if (!empty($sheet_item['contacts']['establishment']['websites']) && !empty($sheet_item['contacts']['establishment']['websites'][0])) {
                    $data['website'] = $sheet_item['contacts']['establishment']['websites'][0];
                }
            }

            if (in_array('phone', $wrapper['display_elements'])) {
                console_log($sheet_item);
                if (!empty($sheet_item['contacts']['establishment']['phones']) && !empty($sheet_item['contacts']['establishment']['phones'][0])) {
                    $data['phone'] = $sheet_item['contacts']['establishment']['phones'][0];
                }
            }
        }

        if (!empty($wrapper['display_button'])) {
            $data['link']['link_label'] = __('Lire la suite', 'woody-theme');
        }

        if (!empty($sheet_item['bordereau']) && ($sheet_item['bordereau'] === 'HOT' || $sheet_item['bordereau'] == 'HPA' || $sheet_item['bordereau'] == 'HLO')) {
            $rating = [];
            if (!empty($sheet_item['labelRatings']) && (!empty(current($sheet_item['labelRatings'])))) {
                for ($i = 0; $i < current($sheet_item['labelRatings'])['repeated']; ++$i) {
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
        $data['location']['lat'] = empty($sheet_item['geolocations']) && empty($sheet_item['geolocations']['latitude']) ? '' : $sheet_item['geolocations']['latitude'];
        $data['location']['lng'] = empty($sheet_item['geolocations']) && empty($sheet_item['geolocations']['longitude']) ? '' : $sheet_item['geolocations']['longitude'];

        // Parcourir tout le tableau de dates et afficher la 1ère date non passée
        $woody_sheet_bordereaux_with_dates = get_field('hawwwai_sheet_bordereaux_with_dates', 'options');
        $woody_sheet_bordereaux_with_dates = empty($woody_sheet_bordereaux_with_dates) ? [] : $woody_sheet_bordereaux_with_dates;
        if (($sheet_item['bordereau'] == 'FMA' || in_array($sheet_item['bordereau'], $woody_sheet_bordereaux_with_dates)) && !empty($sheet_item['dates'])) {
            $today = time();
            $current_year = getdate()['year'];
            foreach ($sheet_item['dates'] as $date) {
                $enddate = strtotime($date['end']['endDate']);

                if ($today < $enddate) {
                    $data['date'] = $date;
                    $data['date']['display_time'] = false;
                    $data['date']['display_year'] = !empty($date['end']['year']) && $date['end']['year'] > $current_year ? true : false;
                    if (!empty($data['date']['display_year']) && empty($data['date']['start']['year'])) {
                        $data['date']['start']['year'] = formatDate($data['date']['start']['startDate'], 'Y');
                    }
                    break 1 ;
                }
            }
        }

        $data['sheet_id'] = get_field('touristic_sheet_id', $post->ID);

        // Critère
        if (!empty($sheet_item['itemData'])) {
            foreach ($sheet_item['itemData'] as $item) {
                if (array_key_exists('criteria', $item)) {
                    $data['criteria'] = $item['criteria'];
                }
            }
        }

        $data['cardDisplayOptions'] = $wrapper['display_elements'];

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
                'url' =>  'https://api.cloudly.space/resize/crop/%width%/%height%/75/' .  str_replace(array("+", "/"), array("-", "_"), base64_encode($woody_topic_img)) . '/image.webp',
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

    /**
     * @author Orphée Besson
     * @param   wrapper - Données du layout acf sous forme de tableau
     * @param   post_id - ID du post
     * @return  data - Un tableau de données
     */
    public function getYoubookPreview($wrapper, $post_id)
    {
        $data = [];

        if(!empty($post_id) || is_numeric($post_id)) {
            $post_id = is_int($post_id) ? $post_id : $post_id['post_id'];
            $product = woody_youbook_item($post_id);
            $explode = explode('/', $product['images'][0]['url']);
            $data = [
                'page_type' => 'youbook',
                'post_id' => $post_id,
                'product_id' => $product['id'],
                'title' => $product['name'],
                'img' => [
                    'url' => 'https://api.cloudly.space/resize/crop/%width%/%height%/75/'.base64_encode($product['images'][0]['url']).'/'.end($explode),
                    'resizer' => true,
                    'alt' => $product['name'],
                ],
            ];

            if(in_array('description', $wrapper['display_elements']) && (!empty($product['descriptions']) && !empty($product['descriptions'][0]) && !empty($product['descriptions'][0]['content']))) {
                $data['description'] = $product['descriptions'][0]['content'];
            }

            if(is_array($product['meetingPoint']['location']) && !empty($product['meetingPoint']['location']['locality']) && in_array('town', $wrapper['display_elements'])) {
                $data['sheet_town'] = $product['meetingPoint']['location']['locality'];
            }

            if(in_array('favorites', $wrapper['display_elements'])) {
                $data['favorites'] = true;
            }

            $data['display_button'] = true;
            $data['link'] = [
                'title' => __('Réserver', 'woody-theme'),
                'url' => woody_get_permalink($post_id),
                'target' => '_blank'
            ];

            if(!empty($product['priceFrom']) && in_array('price', $wrapper['display_elements'])) {
                $data['the_price'] = [
                    'price' => $product['priceFrom'],
                    'prefix_price' => true,
                    'suffix_price' => __('par personne', 'woody-theme'),
                    'currency' => [
                        'label' => !empty($product['currency']) && $product['currency'] === 'EUR' ? '€' : ''
                    ]
                ];
            }

            if(in_array('display_button', $wrapper)) {
                $data['link']['link_label'] = __('Réserver', 'woody-theme');
            }
        }

        return apply_filters('woody_custom_productPreview', $data, $wrapper, $post_id, $product);
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
            // TAXONOMY | DURATION | PRICE | CUSTOM TERM | POST_CREATED
            foreach ($filter_wrapper['list_filters'] as $key => $filter) {
                switch ($filter['list_filter_type']) {
                    case 'created':
                        $return[$key] = ['filter_type' => 'created'];
                        switch ($filter['datepicker_type']) {
                            case 'from_single':
                                $return[$key]['datepicker_type'] = 'single';
                                $return[$key]['inputname'] = 'cfrom';
                                break;
                            case 'to_single':
                                $return[$key]['datepicker_type'] = 'single';
                                $return[$key]['inputname'] = 'cto';
                                break;
                            default:
                                $return[$key]['datepicker_type'] = 'daterange';
                                $return[$key]['inputname'] = 'cbtw';

                                break;
                        }

                        $return[$key]['filter_name'] = $filter['list_filter_name'];
                        break;
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
                        // REVIEW tmapsV2_refactoring : remove tmaps_confid / parse map_params
                        $filter['map_params'] = WoodyTheme_WoodyProcessTools::getMapParams($filter['list_filter_map']);
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
                    case 'keyword':
                        $return[$key]['filter_name'] = $filter['list_filter_name'];
                        $return[$key]['filter_type'] = $filter['list_filter_type'];
                        break;
                    case 'profil':
                        $return[$key]['filter_name'] = $filter['list_filter_name'];
                        $return[$key]['filter_type'] = $filter['list_filter_type'];
                        foreach ($filter['list_filter_profil'] as $profil_key => $profil_id) {
                            $profil = get_post_meta($profil_id);
                            $return[$key]['list_filter_profil'][$profil_key] = [
                                'value' => $profil_id,
                                'label' => $profil['profile_firstname'][0] . ' ' . $profil['profile_lastname'][0],
                            ];
                        }
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
        $sheet_item = woody_hawwwailib_item($post->ID);
        $sheet_url = woody_get_permalink($post->ID);

        $data['title'] = empty($sheet_item['businessName']) ? '' : $sheet_item['businessName'];
        $data['link']['url'] = $sheet_url;
        $data['link']['target'] = empty($sheet_item['targetBlank']) ? '' : '_blank';

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

            if (!empty($wrapper['profile_focus_order'])) {
                switch ($wrapper['profile_focus_order']) {
                    case 'created_desc':
                        $args['orderby'] = 'date';
                        $args['order'] = 'DESC';
                        break;
                    case 'created_asc':
                        $args['orderby'] = 'date';
                        $args['order'] = 'ASC';
                        break;
                    case 'alphabetical_order':
                        $args['orderby'] = 'title';
                        $args['order'] = 'ASC';
                        break;
                    case 'random':
                        $args['orderby'] = 'rand';
                        $args['order'] = 'ASC';
                        break;
                    default:
                        $args['orderby'] = 'rand';
                        $args['order'] = 'ASC';
                        break;
                }
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

        if(!empty($wrapper['profiles_filters'])) {
            foreach ($wrapper['profiles_filters'] as $key_filter => $filter) {
                $data['filters'][$key_filter] = [
                    'parent' => [
                        'name' => empty($filter['name']) ? get_term($filter['profiles_parent_category'], 'profile_category')->name : $filter['name']
                    ]
                ];

                $children_filters = get_terms('profile_category', [
                    'parent' => $filter['profiles_parent_category'],
                    'orderby' => 'name',
                    'order' => 'ASC',
                    'hide_empty' => false
                ]);

                if (!empty($children_filters)) {
                    foreach ($children_filters as $key_children_filter => $children_filter) {
                        $data['filters'][$key_filter]['children'][$key_children_filter] = [
                            'name' => $children_filter->name,
                            'term_id' => $children_filter->term_id
                        ];
                    }
                }
            }

            $data['reset_button'] = $wrapper['reset_button'];
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

        $data['terms_ids'] = '';

        $profile_categories_terms = get_the_terms($post, 'profile_category');

        if(!empty($profile_categories_terms)) {
            $data['terms_ids'] = implode(', ', wp_list_pluck($profile_categories_terms, 'term_id'));
        }

        if (!empty($wrapper['profile_focus_display'])) {
            if (in_array('complement', $wrapper['profile_focus_display'])) {
                $data['complement'] = get_field('profile_complement', $post->ID);
            }

            if (in_array('label', $wrapper['profile_focus_display'])) {
                $data['label'] = get_field('profile_label', $post->ID);
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

            if (in_array('website', $wrapper['profile_focus_display'])) {
                $data['socials']['website'] = get_field('profile_contacts_profile_socials_profile_website', $post->ID);
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
