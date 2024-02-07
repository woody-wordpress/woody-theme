<?php

namespace WoodyProcess\Process;

use WoodyProcess\Tools\WoodyTheme_WoodyProcessTools;
use WoodyProcess\Compilers\WoodyTheme_WoodyCompilers;

/**
 * Dispatch Woody data processing
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.10.0
 * @author Jeremy Legendre - Benoit Bouchaud
 */


class WoodyTheme_WoodyProcess
{
    protected $tools;

    protected $compilers;

    public function __construct()
    {
        $this->tools = new WoodyTheme_WoodyProcessTools();
        $this->compilers = new WoodyTheme_WoodyCompilers();
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('posts_orderby', [$this, 'postsOrderby'], 10, 2);
    }

    public function postsOrderby($args, $wp_query)
    {
        $lon_postmeta = null;
        // On surcharge l'ordre quand on veut faire du tri par géolocalisation
        if (!empty($wp_query->query['orderby']) && is_string($wp_query->query['orderby']) && strpos($wp_query->query['orderby'], 'geoloc') !== false) {
            $post_id = explode('_', $wp_query->query['orderby']);
            $post_id = (is_array($post_id)) ? end($post_id) : null;
            if (!empty($post_id)) {
                $lat = get_field('post_latitude', $post_id);
                $lon = get_field('post_longitude', $post_id);

                if (!empty($lat) && !empty($lon) && !empty($wp_query->meta_query->queries)) {
                    foreach ($wp_query->meta_query->queries as $key => $queries) {
                        if (!empty($queries['key']) && $queries['key'] == 'post_latitude') {
                            $lat_postmeta = ($key == 0) ? 'wp_postmeta' : 'mt' . $key;
                        } elseif (!empty($queries['key']) && $queries['key'] == 'post_longitude') {
                            $lon_postmeta = ($key == 0) ? 'wp_postmeta' : 'mt' . $key;
                        }
                    }

                    if (!empty($lat_postmeta) && !empty($lat_postmeta)) {
                        $lat_operator = ($lat < 0) ? '+' : '-';
                        $lon_operator = ($lon < 0) ? '+' : '-';
                        return sprintf('(POW((%s.meta_value%s%s),2) + POW((%s.meta_value%s%s),2))', $lat_postmeta, $lat_operator, abs($lat), $lon_postmeta, $lon_operator, abs($lon));
                    }
                }
            }
        }

        return $args;
    }

    /**
     *
     * Nom : processWoodyLayouts
     * Auteur : Benoit Bouchaud - Jeremy Legendre
     * Return : Dispatch le traitement des données en fonction du layout ACF utilisé
     * @param    layout - La donnée du layout acf sous forme de tableau
     * @param    context - Le contexte global de la page sous forme de tableau
     * @return   return - Code HTML
     *
     */
    public function processWoodyLayouts($layout, $context)
    {
        $return = '';

        $layout['default_marker'] = empty($context['default_marker']) ? '' : $context['default_marker'];

        // Traitements spécifique en fonction du type de layout
        switch ($layout['acf_fc_layout']) {
            case 'manual_focus':
            case 'auto_focus':
            case 'auto_focus_sheets':
            case 'auto_focus_topics':
            case 'focus_trip_components':
            case 'profile_focus':
                // TODO: les cases auto_focus_topics + auto_focus_sheets + focus_trip_components doivent être ajoutés via le filtre woody_custom_layout depuis leurs addons respectifs
                $return = $this->compilers->formatFocusesData($layout, $context['post'], $context['woody_components']);
                break;
            case 'manual_focus_minisheet':
                $return = $this->compilers->formatMinisheetData($layout, $context['woody_components']);
                break;
            case 'geo_map':
                $return = $this->compilers->formatGeomapData($layout, $context['woody_components']);
                break;
            case 'content_list':
                $return = $this->compilers->formatListContent($layout, $context['post'], $context['woody_components']);
                break;
            case 'gallery':
                // Ajout des données Instagram + champs personnalisés dans le contexte des images
                $layout['gallery_type'] = empty($layout['gallery_type']) ? "manual" : $layout['gallery_type'];
                $layout['is_mobile'] = wp_is_mobile();

                switch ($layout['gallery_type']) {
                    case 'auto':
                        $layout['gallery_items'] = $this->tools->getAttachmentsByMultipleTerms($layout["gallery_tags"], $layout['gallery_taxonomy_terms_andor'], $layout['gallery_count']);

                        foreach ($layout['gallery_items'] as $key => $attachment) {
                            $layout['gallery_items'][$key]['attachment_more_data'] = $this->tools->getAttachmentMoreData($layout['gallery_items'][$key]['ID']);
                        }

                        break;
                    case 'manual':
                    default:
                        if (!empty($layout['gallery_items'])) {
                            foreach ($layout['gallery_items'] as $key => $media_item) {
                                $layout['gallery_items'][$key]['attachment_more_data'] = $this->tools->getAttachmentMoreData($media_item['ID']);
                                if (isset($context['print_rdbk']) && !empty($context['print_rdbk'])) {
                                    $layout['gallery_items'][$key]['lazy'] = 'disabled';
                                }

                                if (!empty($layout['gallery_items'][$key]['attachment_more_data']['linked_video'])) {
                                    $layout['gallery_items'][$key]['attachment_more_data']['linked_video_iframe'] = embedVideo($layout['gallery_items'][$key]['attachment_more_data']['linked_video']);
                                    $layout['gallery_items'][$key]['attachment_more_data']['linked_video_thumbnail'] = embedProviderThumbnail($layout['gallery_items'][$key]['attachment_more_data']['linked_video']);
                                }
                            }
                        }

                        break;
                }

                $layout['display'] = $this->tools->getDisplayOptions($layout);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'interactive_gallery':
                // Ajout des données Instagram + champs personnalisés dans le contexte des images
                if (!empty($layout['interactive_gallery_items'])) {
                    foreach ($layout['interactive_gallery_items'] as $key => $media_item) {
                        $layout['interactive_gallery_items'][$key]['img_mobile_url'] =  (empty($layout['interactive_gallery_items'][$key]['interactive_gallery_photo']['sizes'])) ? '' : $layout['interactive_gallery_items'][$key]['interactive_gallery_photo']['sizes']['ratio_4_3_medium'];
                        $layout['interactive_gallery_items'][$key]['interactive_gallery_photo']['attachment_more_data'] = $this->tools->getAttachmentMoreData($media_item['interactive_gallery_photo']['ID']);
                    }
                }

                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'links':
                $layout['woody_tpl'] = 'blocks-links-tpl_01';
                if (!empty($layout['analytics_event'])) {
                    $layout['analytics'] = [
                        'name' => $layout['analytics_event'],
                        'event' => str_replace('-', '_', sanitize_title($layout['analytics_event']))
                    ];

                    unset($layout['analytics_event']);
                }
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'tabs_group':
                $layout['tabs'] = $this->processWoodySubLayouts($layout['tabs'], 'tab_woody_tpl', 'tabs', $context);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'slides_group':
                $layout['slides'] = $this->processWoodySubLayouts($layout['slides'], 'slide_woody_tpl', 'slides', $context);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'socialwall':
                $layout['gallery_items'] = [];
                if ($layout['socialwall_type'] == 'manual') {
                    if (!empty($layout['socialwall_manual'])) {
                        foreach ($layout['socialwall_manual'] as $key => $media_item) {
                            // On ajoute une entrée "gallery_items" pour être compatible avec le tpl woody
                            $layout['gallery_items'][] = $media_item;
                            $layout['gallery_items'][$key]['attachment_more_data'] = $this->tools->getAttachmentMoreData($media_item['ID']);
                        }
                    }
                } elseif ($layout['socialwall_type'] == 'auto') {
                    // On récupère les images en fonction des termes sélectionnés
                    $layout['gallery_items'] = (empty($layout['socialwall_auto'])) ? '' : $this->tools->getAttachmentsByTerms('attachment_hashtags', $layout['socialwall_auto']);
                    if (!empty($layout['gallery_items'])) {
                        foreach ($layout['gallery_items'] as $key => $media_item) {
                            $layout['gallery_items'][$key]['attachment_more_data'] = $this->tools->getAttachmentMoreData($media_item['ID']);
                        }
                    }
                }

                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'semantic_view':
                $return = $this->compilers->formatSemanticViewData($layout, $context['woody_components']);
                break;
            case 'audio_player':
                $layout['woody_tpl'] = 'blocks-audio-tpl_01';
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'eye_candy_img':
                $layout['woody_tpl'] = 'blocks-eye_candy_img-tpl_01';
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'page_summary':
                if (!empty($layout['summary_bg_params'])) {
                    $layout['display'] = $this->tools->getDisplayOptions($layout['summary_bg_params']);
                }

                $layout['summary'] = $this->compilers->formatSummaryItems($context['post_id']);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'free_text':
                $layout['block_titles'] = $this->tools->getBlockTitles($layout, '', 'generic_');
                $layout['text'] = $this->tools->replacePattern($layout['text'], get_the_ID());
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'call_to_action':
                $opts = [
                    'hide_description' => true,
                ];
                $layout['block_titles'] = $this->tools->getBlockTitles($layout, '', 'generic_', $opts);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'quote':
                $layout['display'] = $this->tools->getDisplayOptions($layout['quote_bg_params']);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'feature':
                $layout['display'] = $this->tools->getDisplayOptions($layout);
                if (!empty($layout['icon_img']) && !empty($layout['icon_img']['sizes']) && !empty($layout['icon_img']['sizes']['ratio_free_small'])) {
                    $layout['icon_img']['sizes']['ratio_free'] = $layout['icon_img']['sizes']['ratio_free_small'];
                }

                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'feature_v2':
                $layout['display'] = $this->tools->getDisplayOptions($layout['feature_block_bg_params']);
                $layout['items'] = $this->compilers->formatFeatureItems($layout);
                $layout['no_padding'] = $layout['feature_no_padding'];
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'spacer_block':
                $return = '<!-- WOODY-COMPONENT-SPACER -->';
                break;
            case 'story':
                $layout['display'] = $this->tools->getDisplayOptions($layout['story_bg_params']);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'testimonials':
                $layout = $this->compilers->formatTestimonials($layout);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'link_social_shares':
                if ($layout['default_parameters'] == false && !empty($layout['active_shares'])) {
                    $layout['active_shares'] = getActiveShares($layout['active_shares']);
                } else {
                    $layout['active_shares'] = getActiveShares();
                }

                $layout['block_titles'] = $this->tools->getBlockTitles($layout, '', 'shares_');
                $layout['display'] = $this->tools->getDisplayOptions($layout);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'movie':
                $layout['movie_url'] = embedProviderUrl($layout['movie']);
                $layout['movie_thumbnail'] = embedProviderThumbnail($layout['movie']);
                $layout['movie_title_fallback'] = (empty($layout['movie_title'])) ? embedProviderTitle($layout['movie']) : __("Titre de la vidéo manquant", 'woody-theme');
                $layout['movie_caption_fallback'] = (empty($layout['movie_caption'])) ? embedProviderTitle($layout['movie']) : __("Légende de la vidéo manquante", 'woody-theme');
                $layout['movie_uploadDate'] = formatDate($context['post']->post_modified, 'Y-m-d\TH:i:sP');
                $layout['movie_ratio'] = (empty($layout['movie_ratio'])) ? '16_9' : $layout['movie_ratio'];
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            case 'booking_block':
                $return = $this->compilers->formatBookBlock($context['post'], $context['woody_components'], $layout);
                break;
            case 'timeline':
                $layout['display'] = $this->tools->getDisplayOptions($layout['timeline_bg_params']);
                $return = \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
                break;
            default:


                // On autorise le traitement des layouts depuis un code externe
                // ! MUST Use if (is_array($layout)) in the add_filter or get a PHP WARNING when doing :
                // ! if ($layout['acf_fc_layout'] == 'some_bloc..')
                // ? Use : if (is_array($layout) && $layout['acf_fc_layout'] == 'some_bloc..') {}
                $layout = apply_filters('woody_custom_layout', $layout, $context);

                // On compile le $layout uniquement si ça n'a pas déjà été fait
                $return = is_array($layout) ? \Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout) : $layout;
        }

        return $return;
    }

    /**
     *
     * Nom : processWoodySubLayouts
     * Auteur : Benoit Bouchaud
     * Return : Retourne un DOM html
     * @param    scope - L'élément parent qui contient les grilles
     * @param    gridTplField - Le slug du champ 'Template'
     * @param    uniqIid_prefix - Un préfixe d'id, si besoin de créer un id unique (tabs)
     * @return   scope - Un DOM Html
     *
     */
    public function processWoodySubLayouts($wrapper = [], $gridTplField, $uniqIid_prefix = '', $context)
    {
        if (!empty($wrapper)) {
            foreach ($wrapper as $grid_key => $grid) {
                $grid_content = [];
                $grid_content['no_padding'] = empty($grid['scope_no_padding']) ? false : $grid['scope_no_padding'];
                if (!empty($uniqIid_prefix) && is_numeric($grid_key)) {
                    $wrapper[$grid_key]['el_id'] = $uniqIid_prefix . '-' . uniqid();
                }

                // On compile les tpls woody pour chaque bloc ajouté dans l'onglet
                if (!empty($grid['light_section_content']) && is_array($grid['light_section_content'])) {
                    foreach ($grid['light_section_content'] as $layout) {
                        $device_display_block = $this->tools->getDeviceDisplayBlockResponsive($layout);

                        switch ($device_display_block) {
                            case 'mobile':
                                if (wp_is_mobile()) {
                                    $grid_content['items'][] = $this->processWoodyLayouts($layout, $context);
                                }

                                break;
                            case 'desktop':
                                if (!wp_is_mobile()) {
                                    $grid_content['items'][] = $this->processWoodyLayouts($layout, $context);
                                }

                                break;
                                // if $device_display_block is empty, we display the block for each device (mobile & desktop), so no test is required
                            default:
                                $grid_content['items'][] = $this->processWoodyLayouts($layout, $context);
                                break;
                        }
                    }

                    // On compile le tpl de grille woody choisi avec le DOM de chaque bloc
                    $wrapper[$grid_key]['light_section_content'] = \Timber::compile($context['woody_components'][$grid[$gridTplField]], $grid_content);
                }
            }

            if (!empty($uniqIid_prefix)) {
                $wrapper['group_id'] = $uniqIid_prefix . '-' . uniqid();
            }
        }

        return $wrapper;
    }

    /**
     *
     * Nom : processWoodyQuery
     * Auteur : Benoit Bouchaud - Jeremy Legendre
     * Return : Le résultat de la wp_query sous forme d'objet
     * @param    the_post - Un objet Timber\Post
     * @param    query_form - Champs de formulaire permettant de monter la query
     * @return   query_result - Un objet
     *
     */
    public function processWoodyQuery($the_post, $query_form, $paginate = false, $uniqid = 0, $ignore_maxnum = false, $posts_in, $filters)
    {
        $the_meta_query = [];
        $query_result = new \stdClass();
        $tax_query = [];

        // Création du paramètre tax_query pour la wp_query
        // Référence : https://codex.wordpress.org/Class_Reference/WP_Query
        // Pour une mise en avant de page, on peut filtrer sur le type de publication
        if (((empty($query_form['focused_type'])) || $query_form['focused_type'] != 'documents') && !empty($query_form['focused_content_type'])) {
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

        // Pour une mise en avant de documents, on peut filtrer sur la catégorie de média
        if (!empty($query_form['focused_type']) && $query_form['focused_type'] == 'documents' && !empty($query_form['focused_media_terms'])) {
            $tax_query = [
                'relation' => 'AND',
                'page_type' => array(
                    'taxonomy' => 'attachment_categories',
                    'terms' => $query_form['focused_media_terms'],
                    'field' => 'term_id',
                    'operator' => 'IN'
                ),
            ];
        }

        // Si des termes ont été choisi pour filtrer les résultats
        // on créé tableau custom_tax à passer au paramètre tax_query
        $custom_tax = [];
        if (!empty($query_form['focused_taxonomy_terms'])) {
            $operator = "IN";

            // On récupère la relation choisie (ET/OU) entre les termes
            // et on génère un tableau de term_id pour chaque taxonomie
            // Si la valeur est NONE, on passe en relation AND et on change l'operator de IN en NOT IN (correspond a AUCUN DES TERMES)
            if (!empty($query_form['focused_taxonomy_terms_andor']) && $query_form['focused_taxonomy_terms_andor'] == "NONE") {
                $tax_query['custom_tax']['relation'] = "AND";
                $operator = "NOT IN";
            } else {
                $tax_query['custom_tax']['relation'] = (empty($query_form['focused_taxonomy_terms_andor'])) ? 'OR' : $query_form['focused_taxonomy_terms_andor'];
            }

            // Si la valeur n'est pas un tableau (== int), on pousse cette valeur dans un tableau
            if (is_numeric($query_form['focused_taxonomy_terms'])) {
                $query_form['focused_taxonomy_terms'] = [$query_form['focused_taxonomy_terms']];
            }

            // Pour chaque entrée du tableau focus_taxonomy_terms
            foreach ($query_form['focused_taxonomy_terms'] as $focused_term) {
                // Si l'entrée est un post id (Aucun filtre n'a été utilisé en front)
                $term = get_term($focused_term);
                if (!empty($term) && !is_wp_error($term) && is_object($term)) {
                    $custom_tax[$term->taxonomy][] = $focused_term;
                }

                foreach ($custom_tax as $taxo => $terms) {
                    foreach ($terms as $term) {
                        $tax_query['custom_tax'][] = array(
                            'taxonomy' => $taxo,
                            'terms' => [$term],
                            'field' => 'term_id',
                            'operator' => $operator
                        );
                    }
                }
            }
        } elseif (!empty($query_form['filtered_taxonomy_terms'])) { // Si des filtres de taxonomie ont été utilisés en front
            // On applique le comportement entre TOUS les filtres
            $tax_query['custom_tax']['relation'] = 'AND';

            // Pour chaque séléction de filtre envoyée, on créé une custom_tax
            foreach ($query_form['filtered_taxonomy_terms'] as $filter_key => $term_filter) {
                // On récupère l'index du filtre dans la clé du param GET
                $exploded_key = explode('_', $filter_key);
                $index = $exploded_key[2];

                $tax_query['custom_tax'][$index] = [];

                // On récupère la relation AND/OR choisie dans le backoffice
                $tax_query['custom_tax'][$index] = [
                    'relation' => (empty($filters['list_filters'][$index]['list_filter_andor'])) ? 'OR' : $filters['list_filters'][$index]['list_filter_andor']
                ];

                // Si on reçoit le paramètre en tant qu'identifiant (select/radio) => on le pousse dans un tableau
                $term_filter = (is_array($term_filter)) ? $term_filter : [$term_filter];

                foreach ($term_filter as $term) {
                    $the_wp_term = get_term($term);
                    $tax_query['custom_tax'][$index][] = array(
                        'taxonomy' => $the_wp_term->taxonomy,
                        'terms' => [$term],
                        'field' => 'term_id',
                        'operator' => 'IN'
                    );
                }
            }
        }

        // On retourne les contenus dont le prix et compris entre 2 valeurs
        if (!empty($query_form['focused_trip_price'])) {
            if (!empty($query_form['focused_trip_price']['min'])) {
                $the_meta_query[] = [
                    'key'        => 'the_price_price',
                    'value'        => $query_form['focused_trip_price']['min'],
                    'type'      => 'NUMERIC',
                    'compare'    => '>='
                ];
            }

            if (!empty($query_form['focused_trip_price']['max'])) {
                $the_meta_query[] = [
                    'key'        => 'the_price_price',
                    'value'        => $query_form['focused_trip_price']['max'],
                    'type'      => 'NUMERIC',
                    'compare'    => '<='
                ];
            }
        }

        // On retourne les contenus dont la durée et comprise entre 2 valeurs
        if (!empty($query_form['focused_trip_duration'])) {
            if (!empty($query_form['focused_trip_duration']['min'])) {
                $the_meta_query[] = [
                    'key'        => 'the_duration_count_days',
                    'value'        => $query_form['focused_trip_duration']['min'],
                    'type'      => 'NUMERIC',
                    'compare'    => '>='
                ];
            }

            if (!empty($query_form['focused_trip_duration']['max'])) {
                $the_meta_query[] = [
                    'key'        => 'the_duration_count_days',
                    'value'        => $query_form['focused_trip_duration']['max'],
                    'type'      => 'NUMERIC',
                    'compare'    => '<='
                ];
            }
        }

        // On trie les contenus en fonction d'un ordre donné
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
                $orderby = 'post_parent menu_order ID';
                $order = 'ASC';
                break;
            case 'geoloc':
                $orderby = 'geoloc_' . $the_post->ID;
                $order = 'ASC';

                $the_meta_query[] = [
                    'key'        => 'post_latitude',
                    'compare' => '!=',
                    'value' => ''
                ];

                $the_meta_query[] = [
                    'key'        => 'post_longitude',
                    'compare' => '!=',
                    'value' => ''
                ];

                break;
            default:
                $orderby = 'rand';
                $order = 'ASC';
        }

        // On enregistre le tri aléatoire pour la journée en cours (pagination)
        if ($orderby == 'rand' && $paginate == true) {
            $seed = (empty($query_form['seed'])) ? date("dmY") : $query_form['seed'];
            $orderby = 'RAND(' . $seed . ')';
        }

        // On créé la wp_query en fonction des choix faits dans le backoffice
        // NB : si aucun choix n'a été fait, on remonte automatiquement tous les contenus de type page
        $post_type = (!empty($query_form['focused_type']) && $query_form['focused_type'] == 'documents') ? 'attachment' : 'page';

        $excluded_posts = (!empty($query_form['exclude_post']) && !empty($query_form['excluded_posts'])) ? $query_form['excluded_posts'] : [];

        $the_query = [
            'post_type' => $post_type,
            'posts_per_page' => (empty($query_form['focused_count'])) ? 12 : $query_form['focused_count'],
            'post_status' => (!empty($query_form['focused_type']) && $query_form['focused_type'] == 'documents') ? ['inherit', 'publish'] : 'publish',
            'post__not_in' => !empty($excluded_posts) ? array_merge(array($the_post->ID), $excluded_posts) : array($the_post->ID),
            'order' => $order,
            'orderby' => $orderby,
            'lang' => pll_get_post_language($the_post->ID),
        ];

        if (!empty($query_form[$uniqid.'_keywords'])) {
            $the_query['s'] = $query_form[$uniqid.'_keywords'];
        }

        if (!empty($posts_in)) {
            $the_query['post__in'] = $posts_in;
        }

        // Retourne tous les posts correspondant à la query
        if ($ignore_maxnum === true) {
            $the_query['posts_per_page'] = -1;
        }

        // On récupère l'offset de la page
        if ($paginate == true) {
            $the_page_offset = (empty($_GET[$uniqid])) ? '' : htmlentities(stripslashes($_GET[$uniqid]));
            $the_query['paged'] = (empty($the_page_offset)) ? 1 : $the_page_offset;
        }

        // On ajoute la date_query
        $the_query['date_query'] = (empty($query_form['focus_date_query'])) ? '' : $query_form['focus_date_query'];

        // On ajoute la tax_query
        $the_query['tax_query'] = (empty($tax_query)) ? '' : $tax_query;

        // Si Hiérarchie = Enfants directs de la page
        // On passe le post ID dans le paramètre post_parent de la query
        if ((empty($query_form['focused_type']) || $query_form['focused_type'] != 'documents') && $query_form['focused_hierarchy'] == 'child_of') {
            $the_query['post_parent'] = $the_post->ID;
        }

        // Si Hiérarchie = Pages de même niveau
        // On passe le parent_post_ID dans le paramètre post_parent de la query
        if ((empty($query_form['focused_type']) || $query_form['focused_type'] != 'documents') && $query_form['focused_hierarchy'] == 'brother_of') {
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

        // On passe les arguments dans un filtre
        $the_query = apply_filters('custom_process_woody_query_arguments', $the_query, $query_form);

        // On créé la wp_query avec les paramètres définis
        $query_result = new \WP_Query($the_query);

        // Si on ordonne par geoloc, il faut trier les résultats reçus
        $query_result = apply_filters('custom_process_woody_query', $query_result, $query_form, $the_post);

        return $query_result;
    }

    public function processWoodySections($sections, $context)
    {
        $return = [];

        if (!empty($sections) && is_array($sections)) {
            foreach ($sections as $section_id => $section) {
                $section = apply_filters('section_data_before_render', $section);
                $the_header = '';
                $the_layout = '';

                if (!empty($section['woody_icon']) || !empty($section['icon_img']) || !empty($section['pretitle']) || !empty($section['title']) || !empty($section['subtitle']) || !empty($section['description'])) {
                    $the_header = \Timber::compile($context['woody_components']['section-section_header-tpl_01'], $section);
                }

                // Pour chaque bloc d'une section, on compile les données dans un template Woody
                // Puis on les compile dans le template de grille Woody selectionné
                $components = [];
                $components['no_padding'] = $section['scope_no_padding'];
                $components['alignment'] = (empty($section['section_alignment'])) ? '' : $section['section_alignment'];

                //Calcul de l'ordre des blocs en responsive
                if (!empty($section['section_mobile_order'])) {
                    $resp_order = explode("-", $section['section_mobile_order']);
                }

                if (!empty($section['section_content'])) {
                    foreach ($section['section_content'] as $layout_id => $layout) {
                        // On définit un uniqid court à utiliser dans les filtres de listes en paramètre GET
                        // Uniqid long : section . $section_id . '_section_content' . $layout_id

                        $layout['uniqid'] = 's' . $section_id . 'sc' . $layout_id;
                        $layout['visual_effects'] = (empty($layout['visual_effects'])) ? '' : $this->tools->formatVisualEffectData($layout['visual_effects']);

                        $components['resp_order'][] = (empty($resp_order[$layout_id])) ? '' : $resp_order[$layout_id];


                        $device_display_block = $this->tools->getDeviceDisplayBlockResponsive($layout);

                        switch ($device_display_block) {
                            case 'mobile':
                                if (wp_is_mobile()) {
                                    $components['items'][] = $this->processWoodyLayouts($layout, $context);
                                }

                                break;
                            case 'desktop':
                                if (!wp_is_mobile()) {
                                    $components['items'][] = $this->processWoodyLayouts($layout, $context);
                                }

                                break;
                                // if $device_display_block is empty, we display the block for each device (mobile & desktop), so no test is required
                            default:
                                $components['items'][] = $this->processWoodyLayouts($layout, $context);
                                break;
                        }
                    }

                    // On retire les items retournés vides par processWoodyLayouts
                    if (!empty($components['items'])) {
                        $components['items'] = array_filter($components['items']);
                    };

                    if (!empty($section['section_woody_tpl']) && !empty($components['items'])) {
                        $the_layout = \Timber::compile($context['woody_components'][$section['section_woody_tpl']], $components);
                    }
                }

                // On récupère les données d'affichage personnalisables
                $display = $this->tools->getDisplayOptions($section);

                // On ajoute les class personnalisées de section dans la liste des class d'affichage
                if(!empty($section['section_class'])) {
                    if(empty($display['classes'])) {
                        $display['classes'] = $section['section_class'];
                    } else {
                        $display['classes'] .=  ' ' . $section['section_class'];
                    }
                }

                // On ajoute les animations dans les données envoyées aux sections
                if (!empty($display['section_animations']) && !empty($section['section_animations'])) {
                    $display['animations'] = $section['section_animations'];
                }

                // On récupère le titre du sommaire et on le formate pour être un id
                if (!empty($section['display_in_summary']) && (!empty($section['section_summary_title']))) {
                    $summary_id = sanitize_title($section['section_summary_title']);
                }

                // On ajoute les 3 parties compilées d'une section + ses paramètres d'affichage
                // puis on compile le tout dans le template de section Woody
                $the_section = [
                    'header' => $the_header,
                    'layout' => $the_layout,
                    'display' => $display
                ];

                if (!empty($summary_id)) {
                    $the_section['summary_id'] = $summary_id;
                }

                if (!empty($section['section_banner'])) {
                    foreach ($section['section_banner'] as $banner) {
                        $the_section[$banner] = $this->tools->getSectionBannerFiles($banner);
                    }
                }

                //On permet de personnaliser la donnée de la section
                $the_section = apply_filters('woody_section_custom_data', $the_section, $section);

                // On récupère l'option "Masquer les sections vides"
                $hide_empty_sections = get_field('hide_empty_sections', 'option');


                if ($section['hide_section']) {
                    $the_section['is_empty'] = true;
                    $return[] = \Timber::compile($context['woody_components']['section-section_full-tpl_01'], $the_section);
                } elseif (!empty($the_section['layout'])) {
                    $return[] = \Timber::compile($context['woody_components']['section-section_full-tpl_01'], $the_section);
                } elseif (!empty($hide_empty_sections)) {
                    if (is_user_logged_in()) {
                        // Si l'utilisateur est connecté, on compile le twig empty_section
                        $return[] = \Timber::compile('parts/empty_section.twig', $the_section);
                    } else {
                        $the_section['is_empty'] = true;
                        $return[] = \Timber::compile($context['woody_components']['section-section_full-tpl_01'], $the_section);
                    }
                } else {
                    $return[] = \Timber::compile($context['woody_components']['section-section_full-tpl_01'], $the_section);
                }
            }
        }

        return $return;
    }
}
