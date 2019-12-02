<?php
/**
 * Template
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Template_TouristicSheet extends WoodyTheme_TemplateAbstract
{
    protected $twig_tpl;

    public function __construct()
    {
        parent::__construct();
    }

    protected function registerHooks()
    {
        add_filter('wpseo_title', [$this, 'filterTouristicSheetWpseoTitle']);
    }

    protected function getHeaders()
    {
        return $this->sheetHeaders();
    }

    protected function setTwigTpl()
    {
        $this->twig_tpl = 'touristic_sheet.twig';
    }

    protected function extendContext()
    {
        // override Body Classes
        $this->context['body_class'] .= ' apirender apirender-wordpress';

        $sheet_id = get_field('touristic_sheet_id', $this->context['post_id']);
        $sheet_lang = pll_get_post_language($this->context['post_id']);
        $sheet_code_lang = apply_filters('woody_pll_get_post_language', $this->context['post_id']);

        $this->context['lang'] = $sheet_code_lang;
        if ($sheet_lang != $sheet_code_lang) {
            $this->context['season'] = $sheet_lang;
        }

        /** ************************
         * Appel apirender pour récupérer le DOM de la fiche
         ************************ **/
        $this->context['fetcherType'] = 'website_' . WP_ENV;
        $this->context['destinationName'] = null;
        $this->context['playlistId'] = null;
        $this->context['sheet_tourism'] = apply_filters('woody_hawwwai_sheet_render', $sheet_id, $sheet_lang, []);

        // Set METAS
        if (!empty($this->context['sheet_tourism'])) {
            foreach ($this->context['sheet_tourism']['metas'] as $key_meta => $meta) {
                // Remove doubled metas (apirender & wordpress)
                if (
                    !empty($meta['#attributes']['property']) && ($meta['#attributes']['property'] == 'og:type'
                        || $meta['#attributes']['property'] == 'og:url'
                        || $meta['#attributes']['property'] == 'og:title')
                ) {
                    continue;
                }

                if (
                    !empty($meta['#attributes']['name']) && ($meta['#attributes']['name'] == 'twitter:card'
                        || $meta['#attributes']['name'] == 'twitter:title')
                ) {
                    continue;
                }

                if (
                    !empty($meta['#attributes']['name']) && ($meta['#attributes']['name'] == 'twitter:card'
                        || $meta['#attributes']['name'] == 'twitter:title')
                ) {
                    continue;
                }

                if (!empty($meta['#attributes']['rel']) && $meta['#attributes']['rel'] == 'canonical') {
                    continue;
                }

                $woody_lang_enable = get_option('woody_lang_enable', []);
                if (!empty($meta['#attributes']['hreflang']) && !in_array($meta['#attributes']['hreflang'], $woody_lang_enable)) {
                    continue;
                }

                // Extract tags
                $tag = '<' . $meta['#tag'];
                foreach ($meta['#attributes'] as $key_attr => $attribute) {
                    $tag .= ' ' . $key_attr . '="' . $attribute . '"';
                }
                $tag .= ' />';
                $this->context['metas'][] = $tag;
            }
        } else {
            status_header('410');
        }
    }

    public function filterTouristicSheetWpseoTitle($title)
    {
        // Si title commence par une langue en 2 caractère
        if (substr($title, 3, 1) == '-') {
            $title = substr($title, 10);
        } else {
            $title = substr($title, 6);
        }

        // Remove idFiche
        $title = preg_replace('/ #[0-9]+/', '', $title);

        return $title;
    }

    /***************************
     * Configuration des HTTP headers
     *****************************/
    public function sheetHeaders()
    {
        $headers = [];
        if (!empty($this->context['sheet_tourism']['modified'])) {
            $headers['Last-Modified'] =  gmdate('D, d M Y H:i:s', strtotime($this->context['sheet_tourism']['modified'])) . ' GMT';
        }
        // if (!empty($this->context['sheet_tourism']['playlistId'])) {
        //     $headers['x-ts-idplaylist'] = $this->context['sheet_tourism']['playlistId'];
        // }
        $sheet_id = get_field('touristic_sheet_id', $this->context['post_id']);
        if (!empty($sheet_id)) {
            $headers['x-ts-idfiche'] = $sheet_id;
        }
        
        if (!empty($this->context['sheet_tourism']['apirender_uri'])) {
            $headers['x-apirender-url'] = $this->context['sheet_tourism']['apirender_uri'];
        }
        return $headers;
    }
}
