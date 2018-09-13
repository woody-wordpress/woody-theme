<?php

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

function getAutoFocus_data($the_post, $query_form)
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
            'field' => 'taxonomy_term_id',
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
            'field' => 'taxonomy_term_id',
            'operator' => 'IN'
        );
        }
    }


    // On créé la wp_query en fonction des choix faits dans le backoffice
    // NB : si aucun choix n'a été fait, on remonte automatiquement tous les contenus de type page
    $the_query = [
    'post_type' => 'page',
    'posts_per_page' => -1,
];

    $the_query['tax_query'] = (!empty($tax_query)) ? $tax_query : '' ;

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

    // On créé la wp_query avec les paramètres définis
    $focused_posts = new WP_Query($the_query);

    // On transforme la donnée des posts récupérés pour coller aux templates de blocs Woody
    if (!empty($focused_posts->posts)) {
        foreach ($focused_posts->posts as $key => $post) {
            $data = [];
            $post = Timber::get_post($post->ID);
            $status = $post->post_status;
            if ($post->post_status === 'draft') {
                continue;
            }
            $data = getPagePreview($query_form, $post);
            // On ajoute un texte dans le bouton "Lire la suite" s'il a été saisi
            $data['link']['title'] = (!empty($query_form['links_label'])) ? $query_form['links_label'] : '';

            $the_items['items'][$key] = $data;
        }
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

    foreach ($layout['content_selection'] as $key => $item_wrapper) {
        // La donnée de la vignette est saisie en backoffice
        if ($item_wrapper['content_selection_type'] == 'custom_content' && !empty($item_wrapper['custom_content'])) {
            $the_items['items'][$key] = getCustomPreview($item_wrapper['custom_content']);

        // La donnée de la vignette correspond à un post sélectionné
        } elseif ($item_wrapper['content_selection_type'] == 'existing_content' && !empty($item_wrapper['existing_content']['content_selection'])) {
            $item = $item_wrapper['existing_content'];
            $status = $item['content_selection']->post_status;
            if ($status === 'draft') {
                continue;
            }
            if ($item['content_selection']->post_type == 'page') {
                $post_preview = getPagePreview($layout, $item['content_selection']);
            } elseif ($item['content_selection']->post_type == 'touristic_sheet') {
                $post_preview = getTouristicSheetPreview($item['content_selection']->custom['touristic_sheet_id'], $item);
            }

            $the_items['items'][$key] = (!empty($post_preview)) ?  $post_preview : '';
        }
    }

    return $the_items;
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

function getCustomPreview($item)
{
    $data = [];

    $data = [
    'title' => (!empty($item['title'])) ? $item['title'] : '',
    'pretitle' => (!empty($item['pretitle'])) ? $item['pretitle'] : '',
    'subtitle' => (!empty($item['subtitle'])) ? $item['subtitle'] : '',
    'icon' => (!empty($item['icon'])) ? $item['icon'] : '',
    'description' => (!empty($item['description'])) ? $item['description'] : '',
    'link' => [
        'url' => (!empty($item['link']['url'])) ? $item['link']['url'] : '',
        'title' => (!empty($item['link']['title'])) ? $item['link']['title'] : '',
        'target' => (!empty($item['link']['target'])) ? $item['link']['target'] : '',
    ]
];
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
 * Return : Retourne les données d'une preview basée sur des champs custom
 * @param    sheet_id - Un tableau de données
 * @return   data - Un tableau de données
 *
 */

function getTouristicSheetPreview($sheet_id, $sheet_wp)
{
    $data = [];
    //TODO: remplacer la langue 'fr' par la variable lang du post
    $sheet_data = apply_filters('wp_woody_hawwwai_sheet_render', $sheet_id, 'fr', array(), 'json', 'item');
    if (!empty($sheet_data['items'])) {
        foreach ($sheet_data['items'] as $key => $item) {
            $data = [
                'title' => (!empty($item['title'])) ? $item['title'] : '',
                'sheet_type' => (!empty($item['bordereau'])) ? $item['bordereau'] : '',
                'description' => (!empty($item['desc'])) ? $item['desc'] : '',
                'town' => (!empty($item['town'])) ? $item['town'] : '',
                'img' => [
                    'resizer' => true,
                    'url' => (!empty($item['img']['url'])) ? $item['img']['url']['manual'] : '',
                    'alt' => (!empty($item['img']['alt'])) ? $item['img']['alt'] : '',
                    'title' => (!empty($item['img']['title'])) ? $item['img']['title'] : ''
                ],
                'link' =>[
                    'url' => (!empty($sheet_wp['content_selection']->guid)) ? $sheet_wp['content_selection']->guid : '',
                ]
            ];

            $data['location'] = [];
            $data['location']['lat'] = (!empty($item['gps'])) ? $item['gps']['latitude'] : '';
            $data['location']['lng'] = (!empty($item['gps'])) ? $item['gps']['longitude'] : '';

            if ($item['bordereau'] === 'HOT' or $item['bordereau'] == 'HPA') {
                $rating = [];
                for ($i=0; $i <= $item['ratings'][0]['value']; $i++) {
                    $rating[] = '<span class="wicon wicon-031-etoile-pleine"><span>';
                }
                $data['rating'] = implode('', $rating);
            }
        }
    }

    \PC::debug($sheet_data, 'Item fiche');

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

function getPagePreview($item_wrapper, $item)
{
    $data = [];

    $data['page_type'] = getTermsSlugs($item->ID, 'page_type', true);

    if (!empty($item->get_field('focus_title'))) {
        $data['title'] = $item->get_field('focus_title');
    } elseif (!empty($item->get_title())) {
        $data['title'] = $item->get_title();
    }


    $fallback_field_group = $item->get_field('field_5b052bbab3867');

    if (is_array($item_wrapper['display_elements'])) {
        if (in_array('pretitle', $item_wrapper['display_elements'])) {
            $data['pretitle'] = getFieldAndFallback($item, 'focus_pretitle', $fallback_field_group, 'pretitle');
        }
        if (in_array('subtitle', $item_wrapper['display_elements'])) {
            $data['subtitle'] = getFieldAndFallback($item, 'focus_subtitle', $fallback_field_group, 'subtitle');
        }
        if (in_array('icon', $item_wrapper['display_elements'])) {
            $data['icon'] = getFieldAndFallback($item, 'focus_icon', $fallback_field_group, 'woody_icon');
        }
        if (in_array('description', $item_wrapper['display_elements'])) {
            $data['description'] = getFieldAndFallback($item, 'focus_description', $fallback_field_group, 'description');
        }
    }

    if (!empty($item_wrapper['display_button'])) {
        $data['link']['link_label'] = getFieldAndFallBack($item, 'focus_button_title', $item);
    }

    $data['location'] = [];
    $data['location']['lat'] = (!empty($item->get_field('post_latitude'))) ? $item->get_field('post_latitude') : '';
    $data['location']['lng'] = (!empty($item->get_field('post_longitude'))) ? $item->get_field('post_longitude') : '';
    $data['img'] = getFieldAndFallback($item, 'focus_img', $item, 'field_5b0e5ddfd4b1b');
    $data['img']['attachment_more_data'] = (!empty($data['img'])) ? getAttachmentMoreData($data['img']['ID']) : '';
    $data['link']['url'] = $item->get_path();

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
function getFieldAndFallback($item, $field, $fallback_item, $fallback_field = '')
{
    $value = [];

    if (!empty($item->get_field($field))) {
        $value = $item->get_field($field);
    } elseif (!empty($fallback_item) && is_array($fallback_item)) {
        $value = $fallback_item[$fallback_field];
    } elseif (!empty($fallback_item->get_field($fallback_field))) {
        $value = $fallback_item->get_field($fallback_field);
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
    $classes_array=[];

    $display['gridContainer'] = (empty($scope['display_fullwidth'])) ? 'grid-container' : '';
    $display['background_img'] = (!empty($scope['background_img'])) ? $scope['background_img'] : '';
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
    'post_per_page' => $query_args['size'],
    'nopaging' => true,
    'tax_query' => array(
        array(
            'taxonomy' => $taxonomy,
            'terms' => $terms,
            'field' => 'taxonomy_term_id',
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

function nestedGridsComponents($scope, $gridTplField, $uniqIid_prefix = '')
{
    $woodyTwigsPaths = getWoodyTwigPaths();
    global $post;
    \PC::debug($post->ID);
    foreach ($scope as $key => $grid) {
        $grid_content = [];
        if (!empty($uniqIid_prefix) && is_numeric($key)) {
            $scope[$key]['el_id'] = $uniqIid_prefix . '-' . uniqid();
        }

        // On compile les tpls woody pour chaque bloc ajouté dans l'onglet
        if (!empty($grid['light_section_content'])) {
            foreach ($grid['light_section_content'] as $layout) {
                switch ($layout['acf_fc_layout']) {
                    case 'auto_focus':
                    case 'manual_focus':
                        $grid_content['items'][] = formatFocusesData($layout, $post->ID, $woodyTwigsPaths);
                    break;
                    default:
                    if ($layout['acf_fc_layout'] == 'call_to_action') {
                        if (!empty($layout['cta_button_group']['add_modal'])) {
                            $layout['modal_id'] = 'cta-' . uniqid();
                        }
                    }
                }

                $grid_content['items'][] = Timber::compile($woodyTwigsPaths[$layout['woody_tpl']], $layout);
            }

            // On compile le tpl de grille woody choisi avec le DOM de chaque bloc
            $scope[$key]['light_section_content'] = Timber::compile($woodyTwigsPaths[$grid[$gridTplField]], $grid_content);
        }
    }
    if (!empty($uniqIid_prefix)) {
        $scope['group_id'] = $uniqIid_prefix . '-' . uniqid();
    }

    return $scope;
}


function formatFocusesData($layout, $current_post, $twigPaths)
{
    $return = '';

    if ($layout['acf_fc_layout'] == 'manual_focus') {
        $the_items = getManualFocus_data($layout);
    } else {
        $the_items = getAutoFocus_data($current_post, $layout);
    }
    $the_items['focus_no_padding'] = $layout['focus_no_padding'];
    $the_items['block_titles'] = getFocusBlockTitles($layout);
    $the_items['display_button'] = $layout['display_button'];

    $return = Timber::compile($twigPaths[$layout['woody_tpl']], $the_items);

    return $return;
}


/**
 *
 * Nom : isWoodyInstagram
 * Auteur : Benoit Bouchaud
 * Return : Booleen
 * @param    taxonomy - Le slug du vocabulaire dans lequel on recherche
 * @param    media_item - Le media (WP post)
 * @return   is_instagram - Booléen
 *
 */

function isWoodyInstagram($media_item, $is_instagram = false)
{
    $media_types = [];

    if (is_array($media_item)) {
        $the_id = $media_item['ID'];
    } elseif (is_numeric($media_item)) {
        $the_id = $media_item;
    }

    $media_terms = get_the_terms($the_id, 'attachment_types');

    if (!empty($media_terms)) {
        foreach ($media_terms as $key => $media_term) {
            $media_types[] = $media_term->slug;
        }

        if (in_array('instagram', $media_types)) {
            $is_instagram = true;
        }
    }

    return $is_instagram;
}


function getAttachmentMoreData($attachment_id)
{
    $attachment_data = [];

    $attachment_data['author'] = get_field('field_5b5585503c855', $attachment_id);
    $attachment_data['lat'] = get_field('field_5b55a88e70cbf', $attachment_id);
    $attachment_data['lng'] = get_field('field_5b55a89e70cc0', $attachment_id);
    $attachment_data['is_instagram'] = isWoodyInstagram($attachment_id);

    if (!empty($attachment_data['is_instagram'])) {
        $img_all_data = get_post_meta($attachment_id);
        $img_all_metadata = (!empty($img_all_data['_wp_attachment_metadata'][0])) ? unserialize($img_all_data['_wp_attachment_metadata'][0]) : '';
        $instagram_metadata = (!empty($img_all_metadata['woody-instagram'])) ? $img_all_metadata['woody-instagram'] : '';
        $attachment_data['instagram_metadata'] = $instagram_metadata;

        // rcd($attachment_data['instagram_metadata'], true);
    }

    return $attachment_data;
}

function getWoodyTwigPaths()
{
    $woodyTwigsPaths = [];
    $woodyComponents = wp_cache_get('woody_components');
    if (empty($woodyComponents)) {
        $woodyComponents = Woody::getComponents();
        wp_cache_set('woody_components', $woodyComponents);
    }

    $woodyTwigsPaths = Woody::getTwigsPaths($woodyComponents);

    return $woodyTwigsPaths;
}
