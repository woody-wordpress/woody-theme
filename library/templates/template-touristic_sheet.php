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
        $this->context['title'] = $this->filterTouristicSheetWoodySeoTitle($this->context['title']);

        // Complete METAS
        if (!empty($this->context['sheet_tourism'])) {
            $api_metas =  $this->context['sheet_tourism']['metas'];
            foreach ($api_metas as $meta_key => $meta) {
                if (!empty($meta['#attributes']['property'])) {
                    switch ($meta['#attributes']['property']) {
                        case 'og:type':
                        case 'og:url':
                                // On ignore les metas déjà définies et inutiles à surcharger
                                break;
                        case 'og:description':
                            // On supprime l'entrée og:description car la meta name="description" définie plus bas contient property="og:description"
                            unset($this->context['metas']['og:description']);
                            break;
                        default:
                        $this->context['metas'][$meta['#attributes']['property']] = $api_metas[$meta_key];
                            break;
                    }
                }

                if (!empty($meta['#attributes']['name'])) {
                    switch ($meta['#attributes']['name']) {
                        case 'twitter:url':
                             // On ignore les metas déjà définies et inutiles à surcharger
                            break;
                        default:
                        $this->context['metas'][$meta['#attributes']['name']] = $api_metas[$meta_key];
                            break;
                    }
                }
            }

            $woody_hawwwai_lang_disable = get_option('woody_hawwwai_lang_disable');
            if (is_array($woody_hawwwai_lang_disable) && !empty($this->context['metas'])) {
                foreach ($this->context['metas'] as $meta_key => $meta) {
                    if (!empty($meta['#attributes']['content']) && in_array($meta['#attributes']['content'], $woody_hawwwai_lang_disable)) {
                        unset($this->context['metas'][$meta_key]);
                    }
                }
            }
        }
    }

    public function filterTouristicSheetWoodySeoTitle($title)
    {
        // Si title commence par une langue en 2 caractère
        if (substr($title, 3, 1) == '-') {
            $title = substr($title, 13);
        } else {
            $title = substr($title, 8);
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
