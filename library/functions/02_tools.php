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
                $image = json_decode($curl);
                $image = $image->thumbnail_large_url;
            }
            break;
          default:
            break;
        }

        // If not set 404
        if (empty($image)) {
            $image = 'https://api.tourism-system.com/static/assets/images/resizer/img_404.jpg';
        }

        // Get Thumbs by API
        if (!empty($image) && strpos($image, 'http') !== false) {
            $hash = base64_encode($image);
            $thumbnail = 'https://api.tourism-system.com/resize/crop/' . $width . '/' . $height . '/80/' . $hash . '/image.jpg';
        }
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
    $thumbnail = rc_getImageResizedFromApi($image_style_infos['width'], $image_style_infos['height'], $image_path);
    return $thumbnail;
}

/**
 * [rc_getImageResizedFromApi description]
 * @param  [type] $image_style [description]
 * @param  [type] $image       [description]
 * @return [type]              [description]
 */
function rc_getImageResizedFromApi($width, $height, $image_path)
{
    if (!empty($image_path) && strpos($image_path, 'http') !== false && strpos($image_path, 'rc-dev') == false) {
        $hash_path = base64_encode($image_path);
        $image_croped_path = 'https://api.tourism-system.com/resize/crop/' . $width . '/' . $height . '/80/' . $hash_path . '/image.jpg';
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
    } catch (Exception $e) {
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
    $str = preg_replace('@[^a-z0-9]+@', '-', $str);
    $str = trim($str);

    return $str;
}

/**
 * [rc_is Check if every $key isset in an array]
 * @param  [type] $val     [description]
 * @param  array  $keys    [description]
 * @param  [type] $default [description]
 * @return array          [description]
 */
function rc_is($val, $keys = array(), $default = null)
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
 * @param [type] $xml
 * @param array $options
 * @return void
 */
function rc_xmlToArray($xml, $options = array())
{
    $defaults = array(
      'namespaceSeparator' => ':',//you may want this to be something other than a colon
      'attributePrefix' => '@',   //to distinguish between attributes and nodes with the same name
      'alwaysArray' => array(),   //array of xml tag names which should always become arrays
      'autoArray' => true,        //only create arrays for tags which appear more than once
      'textContent' => '$',       //key used for the text content of elements
      'autoText' => true,         //skip textContent key if node has no attributes or child nodes
      'keySearch' => false,       //optional search and replace on tag and attribute names
      'keyReplace' => false       //replace values for above search values (as passed to str_replace())
  );
    $options = array_merge($defaults, $options);
    $namespaces = $xml->getDocNamespaces();
    $namespaces[''] = null; //add base (empty) namespace

    //get attributes from all namespaces
    $attributesArray = array();
    foreach ($namespaces as $prefix => $namespace) {
        foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
            //replace characters in attribute name
            if ($options['keySearch']) {
                $attributeName =
                  str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
            }
            $attributeKey = $options['attributePrefix']
                  . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
                  . $attributeName;
            $attributesArray[$attributeKey] = (string)$attribute;
        }
    }

    //get child nodes from all namespaces
    $tagsArray = array();
    foreach ($namespaces as $prefix => $namespace) {
        foreach ($xml->children($namespace) as $childXml) {
            //recurse into child nodes
            $childArray = rc_xmlToArray($childXml, $options);
            list($childTagName, $childProperties) = each($childArray);

            //replace characters in tag name
            if ($options['keySearch']) {
                $childTagName =
                  str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
            }
            //add namespace prefix, if any
            if ($prefix) {
                $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;
            }

            if (!isset($tagsArray[$childTagName])) {
                //only entry with this key
                //test if tags of this type should always be arrays, no matter the element count
                $tagsArray[$childTagName] =
                      in_array($childTagName, $options['alwaysArray']) || !$options['autoArray']
                      ? array($childProperties) : $childProperties;
            } elseif (
              is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
              === range(0, count($tagsArray[$childTagName]) - 1)
          ) {
                //key already exists and is integer indexed array
                $tagsArray[$childTagName][] = $childProperties;
            } else {
                //key exists so convert to integer indexed array with previous value in position 0
                $tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
            }
        }
    }

    //get text content of node
    $textContentArray = array();
    $plainText = trim((string)$xml);
    if ($plainText !== '') {
        $textContentArray[$options['textContent']] = $plainText;
    }

    //stick it all together
    $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
          ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;

    //return node as array
    return array(
      $xml->getName() => $propertiesArray
  );
}
