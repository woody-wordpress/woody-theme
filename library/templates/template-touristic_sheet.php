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
        add_action('send_headers', [$this, 'add_sheet_headers'], 10, 1);
        do_action('send_headers', [$this, 'add_sheet_headers']);
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

        $partialSheet = apply_filters('wp_woody_hawwwai_sheet_render', $sheet_id, $sheet_lang, $params);
        if (empty($partialSheet)) {
            print_r('Error while fetching API Render content for Sheet #' .$sheet_id);
            exit;
        }

        // Set METAS
        // TODO (Doubled set metas (apirender & wordpress))
        $this->context['metas'] = [];
        foreach ($partialSheet['metas'] as $key_meta => $meta) {
            $tag = '<'.$meta['#tag'];
            foreach ($meta['#attributes'] as $key_attr => $attribute) {
                $tag .= ' '.$key_attr.'="'.$attribute.'"';
            }
            $tag .= ' />';
            $this->context['metas'][] = $tag;
        }

        // Get Content
        $this->context['sheet_template'] = $partialSheet['content'];
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
    public function add_sheet_headers()
    {
        global $sheet_id;
        global $partialSheet;

        header('Vary: Cookie, Accept-Encoding');
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age = 0');
        if (!is_admin()) {
            header('Cache-Control: public, max-age=604800, must-revalidate');
        }
        header('Last-Modified: ' .gmdate('D, d M Y H:i:s', strtotime($partialSheet['modified'])).' GMT', false);
        header('x-ts-idfiche: ' .$sheet_id);
    }
}
