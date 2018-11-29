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
        $sheet_lang = rc_clean_season($sheet_lang);

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
            print_r('No API wp_woody_hawwwai_sheet_render set');
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

        $this->context['sheet_tourism'] = apply_filters('wp_woody_hawwwai_sheet_render', $sheet_id, $sheet_lang, $params);

        // Set METAS
        // TODO: (Doubled set metas (apirender & wordpress))
        $this->context['metas'] = [];
        foreach ($this->context['sheet_tourism']['metas'] as $key_meta => $meta) {
            $tag = '<'.$meta['#tag'];
            foreach ($meta['#attributes'] as $key_attr => $attribute) {
                $tag .= ' '.$key_attr.'="'.$attribute.'"';
            }
            $tag .= ' />';
            $this->context['metas'][] = $tag;
        }

        /*********************************************
         * Compilation de l'en tête de page
         *********************************************/
        // $page_teaser = [];

        // $page_teaser['classes'] = 'bg-black';
        // $page_teaser['breadcrumb'] = yoast_breadcrumb('<div class="breadcrumb-wrapper padd-top-sm padd-bottom-sm">', '</div>', false);
        // $this->context['page_teaser'] = Timber::compile($this->context['woody_components']['blocks-page_teaser-tpl_01'], $page_teaser);
    }

    public function filterTouristicSheetWpseoTitle($title)
    {
        $removeList = [
            // remove types #1
            'FMA - ',
            'PCU - ',
            'PNU - ',
            'PNA - ',
            'RES - ',
            'DEG - ',
            'HOT - ',
            'ASC - ',
            'LOI - ',
            'VIL - ',
            'HPA - ',
            'HLO - ',
            'ORG - ',
            'ITI - ',
            // remove langs #2
            'EN - ',
            'IT - ',
            'ES - ',
            'DE - ',
            'NL - ',
        ];

        return str_replace($removeList, '', $title);
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
