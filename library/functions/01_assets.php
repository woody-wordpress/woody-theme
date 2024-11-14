<?php

use Symfony\Component\Finder\Finder;
use WoodyLibrary\Library\WoodyLibrary\WoodyLibrary;

/**
 *
 * Nom : getAcfGroupFields
 * Auteur : Benoit Bouchaud
 * Return : Retourne un tableau avec les valeurs des champs d'un groupe ACF poyr un post donné
 * @param    group_id - La clé du groupe ACF
 * @return   page_teaser_fields - Un tableau de données
 *
 */
function getAcfGroupFields($group_id, $post = null, $display_empty = false)
{
    if (is_null($post)) {
        $post = get_post();
    }

    if (!empty($post)) {
        $post_id = $post->ID;
        $the_fields = [];
        $fields = acf_get_fields($group_id);

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $field_value = false;
                if (!empty($field['name'])) {
                    $field_value = get_field($field['name'], $post_id);
                }

                if ($display_empty) {
                    $the_fields[$field['name']] = $field_value;
                } elseif ($field_value && !empty($field_value)) {
                    $the_fields[$field['name']] = $field_value;
                }
            }
        }

        return $the_fields;
    }
}

/**
 *
 * Nom : getTermsSlugs
 * Auteur : Benoit Bouchaud
 * Return : Retourne un tableau de termes
 * @param    postId - Le post dans lequel on recherche
 * @param    taxonomy - Le slug du vocabulaire dans lequel on recherche
 * @param    implode - Booleén => retourne une chaine de caractères si true
 * @return   slugs - Un tableau de slugs de termes
 *
 */
function getTermsSlugs($postId, $taxonomy, $implode = false)
{
    $return = [];

    $terms = get_the_terms($postId, $taxonomy);
    if (!empty($terms)) {
        foreach ($terms as $term) {
            $return[] = $term->slug;
        }

        if ($implode == true) {
            $return = implode(' ', $return);
        }
    }


    return $return;
}

/**
 *
 * Nom : humanDays
 * Auteur : Benoit Bouchaud
 * Return : Retourne une chaine de caractères (jours) en fonction d'un nombre donné
 * @param    number - int
 * @return   human_string - Un chaine de caractères
 *
 */

function humanDays($number)
{
    $return = '';

    $week = __('semaine', 'woody-theme');
    $weeks = __('semaines', 'woody-theme');
    $day = __('jour', 'woody-theme');
    $days = __('jours', 'woody-theme');

    if ($number % 7 === 0) {
        $week_number = $number / 7;
        $return = $week_number > 1 ? $week_number . ' ' . $weeks : $week_number . ' ' . $week;
    } elseif ($number > 1) {
        $return = $number . ' ' . $days;
    } else {
        $return = $number . ' ' . $day;
    }

    return $return;
}

/**
 *
 * Nom : getWoodyIcons
 * Auteur : Benoit Bouchaud
 * Return : Un tableau
 * @return   the_icons - La liste de tous les icones du site
 *
 */
function getWoodyIcons()
{
    //TODO: Récupérer une variable globale en fonction du set d'icones choisis dans le thème pour remplacer '/src/icons/icons_set_01'
    $core_icons = woodyIconsFolder(get_template_directory() . '/src/icons/icons_set_01');


    $station_icons = [];
    if ((in_array('ski_resort', WOODY_OPTIONS))) {
        $station_icons = woodyIconsFolder(get_template_directory() . '/src/icons/icons_set_stations');
    }

    $site_icons = woodyIconsFolder(get_stylesheet_directory() . '/src/icons');

    $return = array_merge($core_icons, $site_icons, $station_icons);

    apply_filters('woody_add_more_icons', $return);

    return $return;
}

function woodyIconsFolder($folder)
{
    $return = [];
    $icons_folder = wp_cache_get('woody_icons_folder', 'woody');
    if (empty($icons_folder) || !array_key_exists($folder, $icons_folder)) {
        $icons_finder = new Finder();
        $icons_finder->files()->name('*.svg')->in($folder);
        foreach ($icons_finder as $icon) {
            $icon_name = str_replace('.svg', '', $icon->getRelativePathname());
            $icon_class = 'wicon-' . $icon_name;
            $icon_human_name = str_replace('-', ' ', $icon_name);
            $icon_human_name = substr($icon_human_name, 4);
            $icon_human_name = ucfirst($icon_human_name);
            $return[$icon_class] = $icon_human_name;
        }

        $icons_folder[$folder] = $return;
        asort($return);
        wp_cache_set('woody_icons_folder', $icons_folder, 'woody');
    } else {
        $return = $icons_folder[$folder];
    }

    return $return;
}

/**
 *
 * Nom : getWoodyTwigPaths
 * Auteur : Benoit Bouchaud
 * Return : Un tableau
 * @return   the_icons - La liste de tous les icones du site
 *
 */
function getWoodyTwigPaths()
{
    $woodyTwigsPaths = wp_cache_get('woody_twig_paths', 'woody');
    if (empty($woodyTwigsPaths)) {
        $woodyLibrary = new WoodyLibrary();
        $woodyComponents = getWoodyComponents();
        $woodyTwigsPaths = $woodyLibrary->getTwigsPaths($woodyComponents);
        wp_cache_set('woody_twig_paths', $woodyTwigsPaths, 'woody');
    }

    return $woodyTwigsPaths;
}

/**
 *
 * Nom : getWoodyComponents
 * Auteur : Léo Poiroux
 * Return : Un tableau
 * @return   the_icons - La liste de tous les icones du site
 *
 */
function getWoodyComponents()
{
    $woodyComponents = wp_cache_get('woody_components', 'woody');
    if (empty($woodyComponents)) {
        $woodyLibrary = new WoodyLibrary();
        $woodyComponents = $woodyLibrary->getComponents();
        wp_cache_set('woody_components', $woodyComponents, 'woody');
    }

    return $woodyComponents;
}

/**
 *
 * Nom : isWoodyNewTpl
 * Auteur : Orphée Besson
 * Return : Booleen
 * @param    string - La date à tester sous la forme 'YYYY-MM-DD'
 * @param    string - La timezone
 * @return   boolean
 *
 */
function isWoodyNewTpl($date, $timezone = 'Europe/Paris')
{
    \Moment\Moment::setLocale('fr_FR');
    $m = new \Moment\Moment($date);
    $m->setTimezone($timezone);

    $difference = $m->fromNow()->getMonths();
    $direction = $m->fromNow()->getDirection();

    // Si la différence est inférieure ou égale à 1 mois ou la date de création se trouve dans le futur par rapport à la date courante, le template est considéré comme nouveau
    if ($difference <= 1 || $direction == 'future') {
        return true;
    } else {
        return false;
    }
}


function getPageTaxonomies()
{
    $taxonomies = wp_cache_get('woody_website_pages_taxonomies', 'woody');
    if (empty($taxonomies)) {
        $taxonomies = get_object_taxonomies('page', 'objects');
        unset($taxonomies['language']);
        unset($taxonomies['page_type']);
        unset($taxonomies['post_translations']);
        wp_cache_set('woody_website_pages_taxonomies', $taxonomies, 'woody');
    }

    return $taxonomies;
}

function getPageTerms($post_id)
{
    $return = [];

    $taxonomies = getPageTaxonomies();
    foreach ($taxonomies as $taxonomy) {
        $terms = wp_get_post_terms($post_id, $taxonomy->name);
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $return[] = 'term-' . $term->slug;
            }
        }
    }

    return $return;
}

function getPrimaryTerm($taxonomy, $post_id, $fields = [])
{
    $return = null;

    $fieldPrimaryTax = get_field('field_5d7bada38eedf', $post_id);
    if (!empty($fieldPrimaryTax['primary_' . $taxonomy])) {
        $primary_term = get_term(pll_get_term($fieldPrimaryTax['primary_' . $taxonomy]));
        if (!is_wp_error($primary_term) && !empty($primary_term)) {
            if (empty($fields)) {
                $return = $primary_term;
            } else {
                $return = [];
                foreach ($fields as $field) {
                    $return[$field] = $primary_term->$field;
                }
            }
        }
    }

    return $return;
}

/**
 *
 * Nom : getPostRootAncestor
 * Auteur : Thomas Navarro
 * Return : Retourne le parent racine d'un post
 * @param    postID INT : id d'une page enfant
 * @return   return - INT : l'id d'un parent
 *
 */
function getPostRootAncestor($postID, $root_level = 1)
{
    $return = 0;
    $ancestors = get_post_ancestors($postID);
    if (!empty($ancestors)) {
        // Get last ancestors
        $root = (is_countable($ancestors) ? count($ancestors) : 0) - $root_level;
        if ($root < 0) {
            return;
        } else {
            $return = $ancestors[$root];
        }
    }

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
function isWoodyInstagram($attachment_id)
{
    $attachment_types = get_the_terms($attachment_id, 'attachment_types');
    if (!empty($attachment_types)) {
        foreach ($attachment_types as $attachment_type) {
            if ($attachment_type->slug == 'instagram') {
                return true;
            }
        }
    }

    return false;
}

/**
 *
 * Nom : getPostRootAncestor
 * Auteur : Benoit Bouchaud
 * Return : Retourne une chaîne de caractères trandsformée
 * @param    $token - STR : la chaîne à transformer
 * @return   return - STR : la chaîne transfomée
 *
 */
function woody_untokenize($token)
{
    // On définit les correspondances des tokens
    $patterns = [
        '%site_name%' => get_bloginfo('name'),
        '%post_title%' => get_the_title(),
        '%hero_title%' => get_field('field_5b041d61adb72'),
        '%hero_desc%' => get_field('field_5b041dbfadb74'),
        '%teaser_desc%' => get_field('field_5b2bbbfaec6b2'),
        '%focus_title%' => get_field('field_5b0d380e04203'),
        '%focus_desc%' => get_field('field_5b0d382404204')
    ];

    // On remplace les token par les valeurs des champs correspondants
    if (!empty($token)) {
        foreach ($patterns as $pattern_key => $pattern) {
            $token = str_replace($pattern_key, $pattern, $token);
        }
    }

    // On retire les balises html (pour les descriptions essentiellement)
    $token = str_replace('&nbsp; ', '', $token);
    $token = trim(html_entity_decode(strip_tags($token)));

    // on formate ici notamment les guillemets
    $token = wptexturize($token);

    // On limite la chaine à +/- 150 caractères sans couper de mot
    if (strlen($token) > 170) {
        $token = substr($token, 0, strpos($token, ' ', 150));
    }

    return $token;
}

/*
 * @noRector
 ** Minutes to Hours converter
*/
function minuteConvert($num)
{
    $hours = floor($num / 60);
    return [
        'hours' => $hours,
        'minutes' => round((($num / 60) - $hours) * 60)
    ];
}


function foundEmbedProvider($url)
{
    // On définit le provider
    if (strpos($url, 'youtu') != false) { // Match youtube.com & youtu.be
        $provider = 'youtube';
    } elseif (strpos($url, 'dailymotion') != false) {
        $provider = 'dailymotion';
    } elseif (strpos($url, 'vimeo') != false) {
        $provider = 'vimeo';
    } else {
        $provider = 'unknown';
    }

    return $provider;
}

/**
 *
 * Nom : embedProviderThumbnail
 * Auteur : Benoit Bouchaud
 * Return : Retourne l'url d'une miniature de vidéo YT, DailyMotion ou Vimeo
 * @param    $embed - STR : iframe oEmbed
 * @return   return - STR : url de la miniature
 *
 */
function embedProviderThumbnail($embed)
{
    $return = null;

    // On récupère l'attribut src de l'iframe
    $src = embedProviderUrl($embed);

    // On cherche à savoir si c'est Youtube ou Dailymotion ou Vimeo
    $provider = foundEmbedProvider($src);

    // On définit l'url de la miniature en fonction du provider
    switch ($provider) {
        case 'youtube':
            $regex = '/^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/';
            preg_match($regex, $src, $matches);
            $video_id = !empty($matches[1]) ? $matches[1] : null;
            if (!empty($video_id)) {
                $return = wp_cache_get('thumbnail_youtube_' . $video_id);
                if (empty($return)) {
                    $thumb_url = 'https://i.ytimg.com/vi_webp/' . $video_id . '/maxresdefault.webp';
                    $response = wp_remote_head($thumb_url);
                    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                        $return = $thumb_url;
                        wp_cache_set('thumbnail_youtube_' . $video_id, $return);
                    } else {
                        $thumb_url = 'https://i.ytimg.com/vi_webp/' . $video_id . '/sddefault.webp';
                        $response = wp_remote_head($thumb_url);
                        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                            $return = $thumb_url;
                            wp_cache_set('thumbnail_youtube_' . $video_id, $return);
                        } else {
                            $thumb_url = 'https://i.ytimg.com/vi/' . $video_id . '/hqdefault.jpg';
                            $response = wp_remote_head($thumb_url);
                            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                                $return = $thumb_url;
                                wp_cache_set('thumbnail_youtube_' . $video_id, $return);
                            }
                        }
                    }
                }
            }

            break;
        case 'dailymotion':
            $regex = '/(?<=\/video\/)(.*)/';
            preg_match($regex, $src, $matches);
            $video_id = !empty($matches[0]) ? $matches[0] : null;
            if (!empty($video_id)) {
                $return = wp_cache_get('thumbnail_dailymotion_' . $video_id);
                if (empty($return)) {
                    $response = wp_remote_get('https://api.dailymotion.com/video/' . $video_id . '?fields=id,thumbnail_720_url,title&thumbnail_ratio=widescreen');
                    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) === 200) {
                        $hash = json_decode($response['body'], true);
                        $return = $hash['thumbnail_720_url'];
                        wp_cache_set('thumbnail_dailymotion_' . $video_id, $return);
                    }
                }
            }

            break;
        case 'vimeo':
            $regex = '/([0-9]+)/';
            preg_match($regex, $src, $matches);
            $video_id = !empty($matches[0]) ? $matches[0] : null;
            if (!empty($video_id)) {
                $return = wp_cache_get('thumbnail_vimeo_' . $video_id);
                if (empty($return)) {
                    $response = wp_remote_get('https://vimeo.com/' . $video_id);
                    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) === 200) {
                        if (preg_match('/<meta property="og:image" content="(.*?)"/', $response['body'], $matches)) {
                            $return = $matches[1];
                            wp_cache_set('thumbnail_vimeo_' . $video_id, $return);
                        }
                    }
                }
            }
            break;
    }

    if (empty($return)) {
        $return = 'https://api.cloudly.space/resize/crop/640/360/75/aHR0cHM6Ly9hcGkudG91cmlzbS1zeXN0ZW0uY29tL3N0YXRpYy9hc3NldHMvaW1hZ2VzL3Jlc2l6ZXIvd29vZHlfNDA0LmpwZw==/404.jpg';
    }

    return $return;
}

/**
 *
 * Nom : embedProviderUrl
 * Return : Retourne le titre d'une vidéo YT, DailyMotion ou Vimeo
 */
function embedProviderTitle($embed)
{
    // On récupère l'attribut src de l'iframe
    if (strpos($embed, 'title=') !== false) {
        preg_match('#title="(.+?)"#', $embed, $embed_matches);
        return $embed_matches[1];
    }
}

/**
 *
 * Nom : embedProviderUrl
 * Return : Retourne l'url d'une vidéo YT, DailyMotion ou Vimeo
 */
function embedProviderUrl($embed)
{
    // On récupère l'attribut src de l'iframe
    if (strpos($embed, 'src=') !== false) {
        preg_match('#src="(.+?)"#', $embed, $embed_matches);
        return $embed_matches[1];
    }

    return $embed;
}

function embedVideo($embed)
{
    $return = '';

    $provider = foundEmbedProvider($embed);

    // On définit l'url de la miniature en fonction du provider
    switch ($provider) {
        case 'unknown':
            return;
        case 'youtube':
            $regex = '/(v=*)(.*)/';
            preg_match($regex, $embed, $matches);
            if (!empty($matches[2])) {
                $return = '<iframe class="lazyloaded" width="640" height="360" data-src="https://www.youtube.com/embed/'.$matches[2].'" src="https://www.youtube.com/embed/'.$matches[2].'" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>';
            }

            break;
        case 'dailymotion':
            $regex = '/(?<=\/video\/)(.*)/';
            preg_match($regex, $embed, $matches);
            if (!empty($matches[0])) {
                $return = '<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;"> <iframe style="width:100%;height:100%;position:absolute;left:0px;top:0px;overflow:hidden" frameborder="0" type="text/html" src="https://www.dailymotion.com/embed/video/'.$matches[0].'" width="100%" height="100%" allowfullscreen > </iframe> </div>';
            }

            break;
        case 'vimeo':
            $regex = '/(.com\/*)(.*)/';
            preg_match($regex, $embed, $matches);
            if (!empty($matches[2])) {
                $return = '<iframe src="https://player.vimeo.com/video/'.$matches[2].'?h=b2f1716794" width="640" height="360" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
                    <p><a href="https://vimeo.com/'.$matches[2].'"></a> from <a href="https://vimeo.com/colibris"></a> on <a href="https://vimeo.com">Vimeo</a>.</p>';
            }

            break;
    }

    return $return;
}

/**
 * formatDate
 *
 * @param string $date
 * @param string $format
 * @return string $formated_date
 *
 * @link https://github.com/fightbulc/moment.php
 * @link https://www.php.net/manual/fr/datetime.format.php
 */
function formatDate($date, $format = 'd F Y', $locale = null)
{
    $formated_date = '';
    if (empty($locale)) {
        $locale = empty(pll_current_language()) ? PLL_DEFAULT_LOCALE : pll_current_language('locale');
    }

    if (preg_match('#([a-z]{2}_[A-Z]{2})#i', $locale, $matches)) {
        if (in_array($matches[0], ['en_AU', 'en_NZ', 'en_SG'])) {
            $locale = 'en_GB';
        } elseif (in_array($matches[0], ['fr_BE', 'fr_CH', 'br_BR'])) {
            $locale = 'fr_FR';
        } else {
            $locale = $locale;
        }
    }

    \Moment\Moment::setLocale($locale);
    $m = new \Moment\Moment();
    $m->setTimezone(date_default_timezone_get());
    $m->setTimestamp(strtotime($date));

    return $m->format($format);
}
