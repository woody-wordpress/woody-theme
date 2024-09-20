<?php

/**
 * Twig filters
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Timber_Filters
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('timber/twig', [$this, 'addToTwig']);
    }

    public function addToTwig($twig)
    {
        // Functions Native WP
        $twig->addFunction(new Twig_SimpleFunction('bloginfo', 'bloginfo'));
        $twig->addFunction(new Twig_SimpleFunction('__', '__'));
        $twig->addFunction(new Twig_SimpleFunction('translate', 'translate'));
        $twig->addFunction(new Twig_SimpleFunction('_e', '_e'));
        $twig->addFunction(new Twig_SimpleFunction('_n', '_n'));
        $twig->addFunction(new Twig_SimpleFunction('_x', '_x'));
        $twig->addFunction(new Twig_SimpleFunction('_ex', '_ex'));
        $twig->addFunction(new Twig_SimpleFunction('_nx', '_nx'));
        $twig->addFunction(new Twig_SimpleFunction('_n_noop', '_n_noop'));
        $twig->addFunction(new Twig_SimpleFunction('_nx_noop', '_nx_noop'));
        $twig->addFunction(new Twig_SimpleFunction('translate_nooped_plural', 'translate_nooped_plural'));
        $twig->addFunction(new Twig_SimpleFunction('shortcode', 'do_shortcode'));
        $twig->addFunction(new Twig_SimpleFunction('woodyAddonAssetPath', 'woody_addon_asset_path'));

        // Filters Native WP
        $twig->addFilter(new Twig_SimpleFilter('stripshortcodes', 'strip_shortcodes'));
        $twig->addFilter(new Twig_SimpleFilter('array', [$this, 'to_array']));
        $twig->addFilter(new Twig_SimpleFilter('excerpt', 'wp_trim_words'));
        $twig->addFilter(new Twig_SimpleFilter('sanitize', 'sanitize_title'));
        $twig->addFilter(new Twig_SimpleFilter('shortcodes', 'do_shortcode'));
        $twig->addFilter(new Twig_SimpleFilter('apply_filters', function (...$args) {
            $tag = current(array_splice($args, 1, 1));
            return apply_filters_ref_array($tag, $args);
        }));

        // Filters Custom Woody
        $twig->addFilter(new Twig_SimpleFilter('phone_click', [$this, 'phoneClick']));
        $twig->addFilter(new Twig_SimpleFilter('humanize_filesize', [$this, 'humanizeFilesize']));
        $twig->addFilter(new Twig_SimpleFilter('ellipsis', [$this, 'ellipsis']));
        $twig->addFilter(new Twig_SimpleFilter('random_number', [$this, 'random_number']));
        $twig->addFilter(new Twig_SimpleFilter('createdFrom', [$this, 'createdFrom']));
        $twig->addFilter(new Twig_SimpleFilter('getPermalink', [$this, 'getPermalink']));
        $twig->addFilter(new Twig_SimpleFilter('theRootAncestor', [$this, 'theRootAncestor']));
        $twig->addFilter(new Twig_SimpleFilter('pluralizeUnit', [$this, 'pluralizeUnit']));
        $twig->addFilter(new Twig_SimpleFilter('base64Encode', [$this, 'base64Encode']));
        $twig->addFilter(new Twig_SimpleFilter('base64_encode', [$this, 'base64Encode']));
        $twig->addFilter(new Twig_SimpleFilter('seed', [$this, 'seed']));
        $twig->addFilter(new Twig_SimpleFilter('translate', [$this, 'translate']));
        $twig->addFilter(new Twig_SimpleFilter('json_encode', [$this, 'jsonEncode']));
        $twig->addFilter(new Twig_SimpleFilter('zeroise', [$this, 'zeroise']));
        $twig->addFilter(new Twig_SimpleFilter('spacing', [$this, 'spacing']));
        $twig->addFilter(new Twig_SimpleFilter('url_domain', [$this, 'url_domain']));
        $twig->addFilter(new Twig_SimpleFilter('html_class', [$this, 'html_class']));
        $twig->addFilter(new Twig_SimpleFilter('hidePhoneNumber', [$this, 'hidePhoneNumber']));
        $twig->addFilter(new Twig_SimpleFilter('beautifyPhoneNumber', [$this, 'beautifyPhoneNumber']));

        // Debug Woody
        $twig->addFilter(new Twig_SimpleFilter('dump', [$this, 'dump']));
        $twig->addFilter(new Twig_SimpleFilter('rcd', [$this, 'rcd']));
        $twig->addFilter(new Twig_SimpleFilter('wd', [$this, 'wd']));
        $twig->addFilter(new Twig_SimpleFilter('console_log', [$this, 'console_log']));


        return $twig;
    }

    public function to_array($arr)
    {
        if (is_array($arr)) {
            return $arr;
        }

        return array($arr);
    }

    public function jsonEncode($array)
    {
        if (!empty($array) && is_array($array)) {
            return json_encode($array, JSON_THROW_ON_ERROR);
        }
    }

    public function zeroise($value, $threshold)
    {
        return zeroise($value, $threshold);
    }

    public function spacing($text)
    {
        return preg_replace('#\s+#', ' ', $text);
    }

    public function html_class($val)
    {
        return (empty($val)) ? '' : ' ' . $val;
    }

    public function url_domain($url)
    {
        $return = $url;

        if (!empty($url)) {
            $url = parse_url($url);

            if (!empty($url['host'])) {
                $return = $url['host'];
            }
        }

        return $return;
    }

    public function phoneClick($text)
    {
        return substr($text, 0, -2) . '<span class="hidden-number">▒▒</span>';
    }

    public function hidePhoneNumber($phoneNumber, $count)
    {
        $phoneNumber = $this->beautifyPhoneNumber($phoneNumber);
        $phoneNumber = substr($phoneNumber, 0, -$count);

        return $phoneNumber;
    }

    public function beautifyPhoneNumber($phoneNumber)
    {
        if (strlen($phoneNumber) == 10) {
            $phoneNumber = str_replace(' ', '', $phoneNumber);
            $phoneNumber = wordwrap($phoneNumber, 2, ' ', true);
        }

        return $phoneNumber;
    }

    public function humanizeFilesize($bytes, $decimals = 0)
    {
        $sz = [];
        $factor = floor((strlen($bytes) - 1) / 3);
        if ($factor > 0) {
            $sz = 'KMGT';
        }

        return sprintf("%.{$decimals}f", $bytes / 1024 ** $factor) . @$sz[$factor - 1] . 'B';
    }

    public function ellipsis($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true)
    {
        if (is_array($ending)) {
            extract($ending);
        }

        if ($considerHtml) {
            if (mb_strlen(preg_replace('#<.*?>#', '', $text)) <= $length) {
                return $text;
            }

            $totalLength = mb_strlen($ending);
            $openTags = [];
            $truncate = '';
            preg_match_all('#(<\/?([\w+]+)[^>]*>)?([^<>]*)#', $text, $tags, PREG_SET_ORDER);
            foreach ($tags as $tag) {
                if (!preg_match('#img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param#s', $tag[2])) {
                    if (preg_match('#<[\w]+[^>]*>#s', $tag[0])) {
                        array_unshift($openTags, $tag[2]);
                    } elseif (preg_match('#<\/([\w]+)[^>]*>#s', $tag[0], $closeTag)) {
                        $pos = array_search($closeTag[1], $openTags);
                        if ($pos !== false) {
                            array_splice($openTags, $pos, 1);
                        }
                    }
                }

                $truncate .= $tag[1];
                $contentLength = mb_strlen(preg_replace('#&[0-9a-z]{2,8};|&\#\d{1,7};|&\#x[0-9a-f]{1,6};#i', ' ', $tag[3]));
                if ($contentLength + $totalLength > $length) {
                    $left = $length - $totalLength;
                    $entitiesLength = 0;
                    if (preg_match_all('#&[0-9a-z]{2,8};|&\#\d{1,7};|&\#x[0-9a-f]{1,6};#i', $tag[3], $entities, PREG_OFFSET_CAPTURE) !== null) {
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entitiesLength <= $left) {
                                --$left;
                                $entitiesLength += mb_strlen($entity[0]);
                            } else {
                                break;
                            }
                        }
                    }

                    $truncate .= mb_substr($tag[3], 0, $left + $entitiesLength, 'UTF-8');
                    break;
                } else {
                    $truncate .= $tag[3];
                    $totalLength += $contentLength;
                }

                if ($totalLength >= $length) {
                    break;
                }
            }
        } else {
            $text = strip_tags($text);
            if (mb_strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = mb_substr($text, 0, $length - strlen($ending), 'UTF-8');
            }
        }

        if (!$exact) {
            $spacepos = mb_strrpos($truncate, ' ');
            if (isset($spacepos)) {
                if ($considerHtml) {
                    $bits = mb_substr($truncate, $spacepos);
                    preg_match_all('#<\/([a-z]+)>#', $bits, $droppedTags, PREG_SET_ORDER);
                    if (!empty($droppedTags)) {
                        foreach ($droppedTags as $closingTag) {
                            if (!in_array($closingTag[1], $openTags)) {
                                array_unshift($openTags, $closingTag[1]);
                            }
                        }
                    }
                }

                $truncate = mb_substr($truncate, 0, $spacepos);
            }
        }

        $truncate .= $ending;
        if ($considerHtml) {
            foreach ($openTags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }

        return $truncate;
    }

    public function random_number($text)
    {
        return uniqid();
    }

    public function base64Encode($text)
    {
        if (empty($text)) {
            return;
        }

        return base64_encode($text);
    }

    // Debug
    public function dump($text)
    {
        return rcd($text);
    }

    public function rcd($text)
    {
        return rcd($text);
    }

    public function wd($text, $label = '')
    {
        return wd($text, $label);
    }

    public function console_log($text, $label = 'twig debug')
    {
        return console_log($text, $label);
    }

    public function createdFrom($date, $timezone = 'Europe/Paris')
    {
        $locale = function_exists('pll_current_language') ? pll_current_language('locale') : 'fr_FR';

        if ($locale == 'br_BR') {
            $locale = 'fr_FR';
        } elseif($locale == 'en_AU' || $locale == 'en_NZ' || $locale == 'en_SG'){
            $locale = 'en_GB';
        }

        // https://github.com/fightbulc/moment.php
        \Moment\Moment::setLocale($locale);
        $m = new \Moment\Moment(substr($date, 0, 19));
        $m->setTimezone($timezone);

        return $m->fromNow()->getRelative();
    }

    public function getPermalink($post_id, $pll = false)
    {
        if ($pll && function_exists('pll_get_post')) {
            return woody_get_permalink(pll_get_post($post_id));
        } else {
            return woody_get_permalink($post_id);
        }
    }

    public function theRootAncestor($post_id)
    {
        return getPostRootAncestor($post_id) ?: get_the_id();
    }

    public function pluralizeUnit($amount, $singular_unit, $plural_unit = false)
    {
        if ((int) $amount === 1 || empty($plural_unit)) {
            return $amount . ' ' . $singular_unit;
        }

        return $amount . ' ' . $plural_unit;
    }

    public function seed($text)
    {
        return date("dmY");
    }

    public function translate($text)
    {
        switch ($text) {
            case 'day':
                $text = __('jour', 'woody-theme');
                break;
            case 'days':
                $text = __('jours', 'woody-theme');
                break;
            case 'week':
                $text = __('semaine', 'woody-theme');
                break;
            case 'weeks':
                $text = __('semaines', 'woody-theme');
                break;
            case 'month':
            case 'months':
                $text = __('mois', 'woody-theme');
                break;
        }

        return $text;
    }
}
