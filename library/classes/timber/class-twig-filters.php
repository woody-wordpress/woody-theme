<?php

/**
 * Twig filters
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Twig_Filters
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
        //$twig->addExtension(new Twig_Extension_StringLoader());
        $twig->addFilter(new Twig_SimpleFilter('phone_click', [$this, 'phoneClick']));
        $twig->addFilter(new Twig_SimpleFilter('humanize_filesize', [$this, 'humanizeFilesize']));
        $twig->addFilter(new Twig_SimpleFilter('ellipsis', [$this, 'ellipsis']));
        $twig->addFilter(new Twig_SimpleFilter('random_number', [$this, 'random_number']));
        $twig->addFilter(new Twig_SimpleFilter('createdFrom', [$this, 'createdFrom']));

        $twig->addFilter(new Twig_SimpleFilter('getPermalink', [$this, 'getPermalink']));
        $twig->addFilter(new Twig_SimpleFilter('theRootAncestor', [$this, 'theRootAncestor']));
        $twig->addFilter(new Twig_SimpleFilter('pluralizeUnit', [$this, 'pluralizeUnit']));

        $twig->addFilter(new Twig_SimpleFilter('base64Encode', [$this, 'base64Encode']));
        $twig->addFilter(new Twig_SimpleFilter('seed', [$this, 'seed']));

        // debug
        $twig->addFilter(new Twig_SimpleFilter('dump', [$this, 'dump']));
        $twig->addFilter(new Twig_SimpleFilter('rcd', [$this, 'rcd']));
        $twig->addFilter(new Twig_SimpleFilter('wd', [$this, 'wd']));


        return $twig;
    }

    public function phoneClick($text)
    {
        return substr($text, 0, -2) . '<span class="hidden-number">▒▒</span>';
    }

    public function humanizeFilesize($bytes, $decimals = 0)
    {
        $factor = floor((strlen($bytes) - 1) / 3);
        if ($factor > 0) {
            $sz = 'KMGT';
        }
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor - 1] . 'B';
    }

    public function ellipsis($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true)
    {
        if (is_array($ending)) {
            extract($ending);
        }
        if ($considerHtml) {
            if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            $totalLength = mb_strlen($ending);
            $openTags = array();
            $truncate = '';
            preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
            foreach ($tags as $tag) {
                if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
                    if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
                        array_unshift($openTags, $tag[2]);
                    } elseif (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
                        $pos = array_search($closeTag[1], $openTags);
                        if ($pos !== false) {
                            array_splice($openTags, $pos, 1);
                        }
                    }
                }
                $truncate .= $tag[1];
                $contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
                if ($contentLength + $totalLength > $length) {
                    $left = $length - $totalLength;
                    $entitiesLength = 0;
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entitiesLength <= $left) {
                                $left--;
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
                    preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
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
        $encoded = base64_encode($text);
        return $encoded;
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

    public function createdFrom($date, $timezone = 'Europe/Paris')
    {
        if (function_exists('pll_current_language')) {
            $locale = pll_current_language('locale');
        } else {
            $locale = 'fr_FR';
        }

        // https://github.com/fightbulc/moment.php
        \Moment\Moment::setLocale($locale);
        $m = new \Moment\Moment(substr($date, 0, 19));
        $m->setTimezone($timezone);

        return $m->fromNow()->getRelative();
    }

    public function getPermalink($post_id)
    {
        return apply_filters('woody_get_permalink', $post_id);
    }

    public function theRootAncestor($post_id)
    {
        $root_id = getPostRootAncestor($post_id) ? getPostRootAncestor($post_id) : get_the_id();
        return $root_id;
    }

    public function pluralizeUnit($amount, $singular_unit, $plural_unit = false)
    {
        if ((int) $amount === 1 || empty($plural_unit)) {
            return $amount . '<span class="unit"> ' . $singular_unit . '</span>';
        }

        return $amount . ' ' . $plural_unit;
    }

    public function seed($text)
    {
        $seed = date("dmY");

        return $seed;
    }
}
