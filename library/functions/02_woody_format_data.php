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

    // Création du paramètre tax_query pour la wp_query
    // Référence : https://codex.wordpress.org/Class_Reference/WP_Query
    $tax_query = [
            'relation' => 'AND',
            'page_type' => array(
            'taxonomy' => 'page_type',
            'terms' => $query_form['focused_content_type'],
            'field' => 'taxonomy_term_id',
            'operator' => 'IN'
        ),
    ];

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
                    'post_type' => (!empty($query_form['focused_post_type'])) ? $query_form['focused_post_type'] : 'page',
                    'tax_query' => $tax_query,
                    'nopaging' => true,
                    'posts_per_page' => (!empty($query_form['focused_count'])) ? $query_form['focused_count'] : -1,
                ];



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
    // rcd($the_query, true);

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

function getManualFocus_data($items)
{
    $the_items = [];

    foreach ($items as $key => $item_wrapper) {
        // La donnée de la vignette est saisie en backoffice
        if ($item_wrapper['content_selection_type'] == 'custom_content' && !empty($item_wrapper['custom_content'])) {
            $the_items['items'][$key] = getCustomPreview($item_wrapper['custom_content']);

        // La donnée de la vignette correspond à un post sélectionné
        } elseif ($item_wrapper['content_selection_type'] == 'existing_content' && !empty($item_wrapper['existing_content'])) {
            $item = $item_wrapper['existing_content'];
            $status = $item['content_selection']->post_status;
            if ($status === 'draft') {
                continue;
            }
            $the_items['items'][$key] = getExistingPreview($item);
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
     } elseif ($item['media_type'] == 'movie' && !empty($item['movie'])) {
         $data['movie'] = $item['movie'];
     }

     return $data;
 }

 /**
 *
 * Nom : getExistingPreview
 * Auteur : Benoit Bouchaud
 * Return : Retourne les données d'une preview basée sur un post existant
 * @param    item - Un tableau contenant un objet Timber\Post + de la donnée
 * @return   data - Un tableau de données
 *
 */

 function getExistingPreview($item)
 {
     $data = [];

     if (empty($item['content_selection'])) {
         return;
     }

     $data = getPagePreview($item, $item['content_selection']);

     // On ajoute un texte dans le bouton "Lire la suite" s'il a été saisi
     $data['link']['title'] = (!empty($item['link_label'])) ? $item['link_label'] : '';

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
     if (in_array('pretitle', $item_wrapper['display_elements'])) {
         $data['pretitle'] = getFieldAndFallback($item, 'focus_pretitle', 'pretitle');
     }
     if (in_array('subtitle', $item_wrapper['display_elements'])) {
         $data['subtitle'] = getFieldAndFallback($item, 'focus_subtitle', 'subtitle');
     }
     if (in_array('icon', $item_wrapper['display_elements'])) {
         $data['icon'] = getFieldAndFallback($item, 'focus_icon', 'icon');
     }
     if (in_array('description', $item_wrapper['display_elements'])) {
         $data['description'] = getFieldAndFallback($item, 'focus_description', 'description');
     }

     $data['location'] = [];
     $data['location']['lat'] = (!empty($item->get_field('post_latitude'))) ? $item->get_field('post_latitude') : '';
     $data['location']['lng'] = (!empty($item->get_field('post_longitude'))) ? $item->get_field('post_longitude') : '';
     $data['img'] = getFieldAndFallback($item, 'focus_img', 'field_5b0e5ddfd4b1b');
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
 */
 function getFieldAndFallback($item, $field, $fallback)
 {
     $value = [];

     if (!empty($item->get_field($field))) {
         $value = $item->get_field($field);
     } elseif (!empty($item->get_field($fallback))) {
         $value = $item->get_field($fallback);
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

     if (!empty($scope['background_img'])) {
         $display['background_img'] = $scope['background_img'];
         $classes_array[] = 'isRel';
     }

     if (!empty($scope['background_color'])) {
         $classes_array[] = $scope['background_color'];
     }
     if (!empty($scope['background_img_opacity'])) {
         $classes_array[] = $scope['background_img_opacity'];
     }
     if (!empty($scope['scope_paddings']['scope_padding_top'])) {
         $classes_array[] = $scope['scope_paddings']['scope_padding_top'];
     }
     if (!empty($scope['scope_paddings']['scope_padding_bottom'])) {
         $classes_array[] = $scope['scope_paddings']['scope_padding_bottom'];
     }
     if (!empty($scope['scope_margins']['scope_margin_top'])) {
         $classes_array[] = $scope['scope_margins']['scope_margin_top'];
     }
     if (!empty($scope['scope_margins']['scope_margin_bottom'])) {
         $classes_array[] = $scope['scope_margins']['scope_margin_bottom'];
     }
     if (!empty($scope['section_divider'])) {
         $display['section_divider'] = $scope['section_divider'];
     }

     // On transforme le tableau en une chaine de caractères
     $display['classes'] = implode(' ', $classes_array);


     return $display;
 }

 /**
 *
 * Nom : getAcfGroupFields
 * Auteur : Benoit Bouchaud
 * Return : Retourne un tableau avec les valeurs des champs d'un groupe ACF poyr un post donné
 * @param    group_id - La clé du groupe ACF
 * @return   page_teaser_fields - Un tableau de données
 *
 */
 function getAcfGroupFields($group_id)
 {
     global $post;
     $post_id = $post->ID;

     $page_teaser_fields = array();

     $fields = acf_get_fields($group_id);

     if (!empty($fields)) {
         foreach ($fields as $field) {
             $field_value = false;
             if (!empty($field['name'])) {
                 $field_value = get_field($field['name'], $post_id);
             }

             if ($field_value && !empty($field_value)) {
                 $page_teaser_fields[$field['name']] = $field_value;
             }
         }
     }


     return $page_teaser_fields;
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
     $woodyComponents = Woody::getTwigsPaths();

     if (!empty($uniqIid_prefix)) {
         $scope['group_id'] = $uique_id . '-' . uniqid();
     }

     foreach ($scope as $key => $grid) {
         $grid_content = [];
         if (!empty($uniqIid_prefix)) {
             $scope[$key]['el_id'] = $uique_id . '-' . uniqid();
         }

         // On compile les tpls woody pour chaque bloc ajouté dans l'onglet
         if (!empty($grid['section_content'])) {
             foreach ($grid['section_content'] as $layout) {
                 $grid_content['items'][] = Timber::compile($woodyComponents[$layout['woody_tpl']], $layout);
             }
             // On compile le tpl de grille woody choisi avec le DOM de chaque bloc
             $scope[$key]['section_content'] = Timber::compile($woodyComponents[$grid[$gridTplField]], $grid_content);
         }
     }
     return $scope;
 }

 /**
 *
 * Nom : getTermsSlugs
 * Auteur : Benoit Bouchaud
 * Return : Retourne un tableau de termes
 * @param    taxonomy - Le slug du vocabulaire dans lequel on recherche
 * @param    postId - Le post dans lequel on recherche
 * @return   slugs - Un tableau de slugs de termes
 *
 */
function getTermsSlugs($postId, $taxonomy, $implode = false)
{
    $slugs = [];
    $terms = get_the_terms($postId, $taxonomy);
    foreach ($terms as $term) {
        $slugs[] = $term->slug;
    }

    if ($implode == true) {
        $slugs = implode(' ', $slugs);
    }

    return $slugs;
}
