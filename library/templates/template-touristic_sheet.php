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

        $sheet_id = $this->context['post']->touristic_sheet_id;
        $sheet_lang = $this->context['post']->touristic_sheet_lang;
        // $season = null;

        $this->context['lang'] = $sheet_lang;
        $this->context['fetcherType'] = 'website_'.WP_ENV;
        $this->context['destinationName'] = null;
        $this->context['playlistId'] = null;

        // Get API auth data
        $credentials = get_option('woody_credentials');
        if (!empty($credentials)) {
            $this->context['apiLogin'] = $credentials['public_login'];
            $this->context['apiPassword'] = $credentials['public_password'];
        } else {
            print_r('No API woody_hawwwai_sheet_render set');
            exit;
        }

        /** ************************
         * Appel apirender pour récupérer le DOM de la fiche
         ************************ **/
        $params = [];
        // Set season param if required
        // if (!is_null($season)) {
        //     $params['season'] = $season;
        // }

        $this->context['sheet_tourism'] = apply_filters('woody_hawwwai_sheet_render', $sheet_id, $sheet_lang, $params);

        // Set METAS

        $this->context['metas'] = [];
        foreach ($this->context['sheet_tourism']['metas'] as $key_meta => $meta) {
            // Remove doubled metas (apirender & wordpress)
            if (!empty($meta['#attributes']['property']) &&
                ($meta['#attributes']['property'] == 'og:type'
                || $meta['#attributes']['property'] == 'og:url'
                || $meta['#attributes']['property'] == 'og:title')
            ) {
                continue;
            }

            if (!empty($meta['#attributes']['name']) &&
                ($meta['#attributes']['name'] == 'twitter:card'
                || $meta['#attributes']['name'] == 'twitter:title')
            ) {
                continue;
            }

            if (!empty($meta['#attributes']['name']) &&
                ($meta['#attributes']['name'] == 'twitter:card'
                || $meta['#attributes']['name'] == 'twitter:title')
            ) {
                continue;
            }

            if (!empty($meta['#attributes']['rel']) && $meta['#attributes']['rel'] == 'canonical') {
                continue;
            }

            // TODO: Check rels of every langs and remove non english rel
            if (WP_SITE_KEY == 'crt-bretagne' && !empty($meta['#attributes']['rel']) && $meta['#attributes']['rel'] == 'alternate') {
                continue;
            }

            // Extract tags
            $tag = '<'.$meta['#tag'];
            foreach ($meta['#attributes'] as $key_attr => $attribute) {
                $tag .= ' '.$key_attr.'="'.$attribute.'"';
            }
            $tag .= ' />';
            $this->context['metas'][] = $tag;
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
        if (!empty($this->context['post']->touristic_sheet_id)) {
            $headers['x-ts-idfiche'] = $this->context['post']->touristic_sheet_id;
        }
        if (!empty($this->context['sheet_tourism']['apirender_uri'])) {
            $headers['x-apirender-url'] = $this->context['sheet_tourism']['apirender_uri'];
        }
        return $headers;
    }
}
