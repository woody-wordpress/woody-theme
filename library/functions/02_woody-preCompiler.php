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

    // On transforme la donnée des posts récupérés pour coller aux templates de blocs Woody
    if (!empty($focused_posts->posts)) {
        foreach ($focused_posts->posts as $key => $post) {
            $post = Timber::get_post($post->ID);
            $data = [];

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

     if (!empty($item->get_field('focus_title'))) {
         $data['title'] = $item->get_field('focus_title');
     } elseif (!empty($item->get_title())) {
         $data['title'] = $item->get_title();
     }

     if (in_array('pretitle', $item_wrapper['display_elements'])) {
         if (!empty($item->get_field('focus_pretitle'))) {
             $data['pretitle'] = $item->get_field('focus_pretitle');
         } elseif (!empty($item->get_field('pretitle'))) {
             $data['pretitle'] = $item->get_field('pretitle');
         }
     }

     if (in_array('subtitle', $item_wrapper['display_elements'])) {
         if (!empty($item->get_field('focus_subtitle'))) {
             $data['subtitle'] = $item->get_field('focus_subtitle');
         } elseif (!empty($item->get_field('subtitle'))) {
             $data['subtitle'] = $item->get_field('subtitle');
         }
     }

     if (in_array('icon', $item_wrapper['display_elements'])) {
         if (!empty($item->get_field('focus_icon'))) {
             $data['icon'] = $item->get_field('focus_icon');
         } elseif (!empty($item->get_field('icon'))) {
             $data['icon'] = $item->get_field('icon');
         }
     }

     if (in_array('description', $item_wrapper['display_elements'])) {
         if (!empty($item->get_field('focus_description'))) {
             $data['description'] = $item->get_field('focus_description');
         } elseif (!empty($item->get_field('description'))) {
             $data['description'] = $item->get_field('description');
         }
     }

     if (!empty($item->get_field('focus_img'))) {
         $data['img'] = $item->get_field('focus_img');
     } elseif (!empty($item->get_field('field_5b0e5ddfd4b1b'))) {
         // Get focus img if exists
         $data['img'] = $item->get_field('field_5b0e5ddfd4b1b');
     }

     $data['link']['url'] = $item->get_path();

     return $data;
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
 * @param    group_id - Le post id du groupe ACF /**** !!! Différent de l'id du post dont on récupère les valeurs ****\
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
