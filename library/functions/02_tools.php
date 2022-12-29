<?php
/**
* Toolbox
*
* @package WoodyTheme
* @since WoodyTheme 1.0.0
*/

/**
* [rc_getVideoID Récupérer l'id de vidéo]
* @param  string $url  [Url de la vidéo]
* @param  string $type [Type de la vidéo]
* @return string       [Retourne l'id]
*/
function rc_getVideoID($url, $type = '')
{
    // If Type == 0 or 1 or 2
    if (is_numeric($type)) {
        $types = array('youtube', 'vimeo', 'dailymotion');
        $type = $types[$type];
    } else {
        $type = rc_getVideoType($url);
    }

    $id = '';
    if (strpos($url, 'http') !== false) {
        $url = parse_url($url);
        switch ($type) {
            // Youtube
            case 'youtube':
            if ($url['host'] == 'youtu.be') {
                $id = str_replace('/', '', $url['path']);
            } elseif (strpos($url['path'], 'embed') !== false) {
                $id = str_replace('/embed/', '', $url['path']);
            } elseif (!empty($url['query'])) {
                $query = explode('&', $url['query']);
                foreach ($query as $param) {
                    if (strpos($param, 'v=') !== false) {
                        $id = str_replace('v=', '', $param);
                        break;
                    }
                }
            }

            break;
            // Vimeo
            case 'vimeo':
            if (!empty($url['path'])) {
                $path = explode('/', $url['path']);
                if (!empty($path[1])) {
                    $id = $path[1];
                }
            }

            break;
            // Dailymotion : ne fonctionne pas avec fresco.js
            case 'dailymotion':
            if (!empty($url['path'])) {
                $path = explode('/', $url['path']);
                if (!empty(end($path))) {
                    $videoname = explode('_', end($path));
                    $id = current($videoname);
                }
            }

            break;
            default:
            break;
        }
    }

    return $id;
}

/**
* [rc_getVideoThumbnail Récupérer la miniature d'une vidéo]
* @param  string  $url    [Url de la video]
* @param  string  $type   [Type de la vidéo]
* @param  integer $width  [Largeur de la vidéo]
* @param  integer $height [Hauteur de la vidéo]
* @return string          [Retourne l'url de la miniature]
*/
function rc_getVideoThumbnail($url, $type = '', $width = 427, $height = 240)
{
    $image = [];
    // If Type == 0 or 1 or 2
    if (is_numeric($type)) {
        $types = array('youtube', 'vimeo', 'dailymotion');
        $type = $types[$type];
    } else {
        $type = rc_getVideoType($url);
    }

    $thumbnail = '';
    $id = rc_getVideoID($url, $type);
    if (!empty($id)) {
        switch ($type) {
            // Youtube
            case 'youtube':
            $image = 'https://img.youtube.com/vi/'. $id .'/hqdefault.jpg';
            break;
            // Vimeo
            case 'vimeo':
            $curl = rc_curl("https://vimeo.com/api/v2/video/". $id .".php");
            if (!empty($curl)) {
                $image = unserialize($curl);
                $image = $image[0]['thumbnail_large'];
            }

            break;
            // Dailymotion : ne fonctionne pas avec fresco.js
            case 'dailymotion':
            $curl = rc_curl("https://api.dailymotion.com/video/". $id ."?fields=thumbnail_large_url");
            if (!empty($curl)) {
                $image = json_decode($curl, null, 512, JSON_THROW_ON_ERROR);
                $image = $image->thumbnail_large_url;
            }

            break;
            default:
            break;
        }

        $thumbnail = rc_getImageResizedFromApi($width, $height, $image);
    }

    return $thumbnail;
}

/**
* [rc_getVideoIframe Récupérer l'iframe' d'une vidéo]
* @param  string  $url      [Url de la vidéo]
* @param  string  $type     [Type de la vidéo]
* @param  boolean $autoplay [Ajoute l'autoplay si vrai]
* @return string            [Retourne l'url source de l'iframe]
*/
function rc_getVideoIframe($url, $type = '', $autoplay = false)
{
    $embed_url = '';

    // If Type == 0 or 1 or 2
    if (is_numeric($type)) {
        $types = array('youtube', 'vimeo', 'dailymotion');
        $type = $types[$type];
    } else {
        $type = rc_getVideoType($url);
    }

    if (!empty($type)) {
        $id = rc_getVideoID($url, $type);
        if (!empty($id)) {
            switch ($type) {
                // Youtube
                case 'youtube':
                $embed_url = '//www.youtube.com/embed/'. $id .'?rel=0';
                if ($autoplay) {
                    $embed_url .= '&autoplay=1&showinfo=0&modestbranding=1';
                }

                break;
                // Vimeo
                case 'vimeo':
                $embed_url = '//player.vimeo.com/video/' . $id;
                if ($autoplay) {
                    $embed_url .= '?autoplay=1';
                }

                break;
                // Dailymotion : ne fonctionne pas avec fresco.js
                case 'dailymotion':
                $embed_url = '//www.dailymotion.com/embed/video/' . $id;
                if ($autoplay) {
                    $embed_url .= '?autoPlay=1';
                }

                break;
                default:
                break;
            }
        }
    }

    return $embed_url;
}

function rc_getVideoType($url)
{
    $type = false;

    if (strpos($url, 'youtu') !== false) {
        $type = 'youtube';
    } elseif (strpos($url, 'vimeo') !== false) {
        $type = 'vimeo';
    } elseif (strpos($url, 'dailymotion') !== false || strpos($url, 'dai.ly') !== false) {
        $type = 'dailymotion';
    }

    return $type;
}

/**
* [rc_getImageResizedFromApi  Redimensionner une image via l'API]
* @param  array  $image_style [Nom du style d'image]
* @param  string $image_path  [Url de l'image]
* @return string              [Url de l'image redimensionnée]
*/
function rc_getImageStyleByApi($image_style, $image_path)
{
    $image_style_infos = rc_getImageStyle($image_style);
    return rc_getImageResizedFromApi($image_style_infos['width'], $image_style_infos['height'], $image_path);
}

/**
* [rc_getImageResizedFromApi description]
* @param  [type] $image_style [description]
* @param  [type] $image       [description]
* @return [type]              [description]
*/
function rc_getImageResizedFromApi($width, $height, $image_path = null, $quality = '75')
{
    if (empty($image_path) || strpos($image_path, 'http') == false) {
        $image_path = 'https://api.cloudly.space/static/assets/images/resizer/img_404.jpg';
    }

    if (strpos($image_path, 'rc-dev') == false) {
        $hash_path = str_replace(array("+", "/"), array("-", "_"), base64_encode($image_path));
        $image_croped_path = 'https://api.cloudly.space/resize/crop/' . $width . '/' . $height . '/' . $quality . '/' . $hash_path . '/image.jpg';
    } else {
        $image_croped_path = $image_path;
    }

    return $image_croped_path;
}


/**
* [rc_curl description]
* @param  [type] $url [description]
* @return [type]      [description]
*/
function rc_curl($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 45);

    try {
        $data = curl_exec($ch);
    } catch (Exception $exception) {
        $data = '';
    }

    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode>=200 && $httpcode<300) {
        return $data;
    }
}

/**
* [rc_getAlias Transforme du texte en alias]
* @param  string $str     [Chaine de caractere à transformer]
* @param  string $charset [Charset]
* @return string          [Alias de la chaine d'origine]
*/
function rc_getAlias($str, $charset='utf-8')
{
    $str = htmlentities($str, ENT_NOQUOTES, $charset);
    $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
    $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
    $str = strtolower($str);
    $str = preg_replace('#[^a-z0-9]+#', '-', $str);

    return trim($str);
}

/**
* [rc_is Check if every $key isset in an array]
* @param  [type] $val     [description]
* @param  array  $keys    [description]
* @param  [type] $default [description]
* @return array          [description]
*/
function rc_is($val, $keys = [], $default = null)
{
    foreach ($keys as $key) {
        if (empty($val[$key])) {
            return $default;
        } else {
            $val = $val[$key];
        }
    }

    return $val;
}

/**
* rc_xmlToArray
*
* @param [type] $xmlstr
* @return void
*/

function rc_xmlToArray($xmlstr)
{
    $doc = new DOMDocument();
    $doc->loadXML($xmlstr);

    $root = $doc->documentElement;
    $output = rc_domnodeToArray($root);
    if (!empty($output['@root'])) {
        $output['@root'] = $root->tagName;
    }

    if ((is_array($output) || is_object($output))) {
        return $output;
    }
}

function rc_domnodeToArray($node)
{
    $output = [];
    switch ($node->nodeType) {
        case XML_CDATA_SECTION_NODE:
        case XML_TEXT_NODE:
        $output = trim($node->textContent);
        break;
        case XML_ELEMENT_NODE:
        for ($i=0, $m=$node->childNodes->length; $i<$m; ++$i) {
            $child = $node->childNodes->item($i);
            $v = rc_domnodeToArray($child);
            if (property_exists($child, 'tagName') && $child->tagName !== null) {
                $t = $child->tagName;
                if (!isset($output[$t])) {
                    $output[$t] = [];
                }

                $output[$t][] = $v;
            } elseif ($v || $v === '0') {
                $output = (string) $v;
            }
        }

        if ($node->attributes->length && !is_array($output)) { //Has attributes but isn't an array
            $output = array('@content'=>$output); //Change output into an array.
        }

        if (is_array($output)) {
            if ($node->attributes->length) {
                $a = [];
                foreach ($node->attributes as $attrName => $attrNode) {
                    $a[$attrName] = (string) $attrNode->value;
                }

                $output['@attributes'] = $a;
            }

            foreach ($output as $t => $v) {
                if (is_array($v) && count($v)==1 && $t != '@attributes') {
                    $output[$t] = $v[0];
                }
            }
        }

        break;
    }

    return $output;
}
