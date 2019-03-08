<?php
/**
 * Template
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

abstract class WoodyTheme_TemplateAbstract
{
    protected $context = [];

    // Force les classes filles à définir cette méthode
    abstract protected function setTwigTpl();
    abstract protected function extendContext();
    abstract protected function registerHooks();
    abstract protected function getHeaders();

    public function __construct()
    {
        $this->registerHooks();
        $this->initContext();
        $this->setTwigTpl();
        $this->extendContext();

        $headers = $this->getHeaders();
        if (!empty($headers)) {
            foreach ($headers as $key => $val) {
                // allow val to be an array of value to set
                if (is_array($val)) {
                    foreach ($val as $key2 => $val2) {
                        header($key . ': ' . $val2, false);
                    }
                } else {
                    header($key . ': ' . $val);
                }
            }
        }
    }

    public function render()
    {
        if (!empty($this->twig_tpl) && !empty($this->context)) {
            Timber::render($this->twig_tpl, $this->context);
        }
    }

    private function initContext()
    {
        $this->context = Timber::get_context();
        $this->context['title'] = wp_title(null, false);
        $this->context['current_url'] = get_permalink();
        $this->context['site_key'] = WP_SITE_KEY;

        /******************************************************************************
         * Sommes nous dans le cas d'une page miroir ?
         ******************************************************************************/
        $mirror_page = getAcfGroupFields('group_5c6432b3c0c45');
        if (!empty($mirror_page['mirror_page_reference'])) {
            $this->context['post_id'] = $mirror_page['mirror_page_reference'];
            $this->context['post'] = get_post($this->context['post_id']);
            $this->context['post_title'] = get_the_title();
            $this->context['metas'][] = sprintf('<link rel="canonical" href="%s" />', get_permalink($this->context['post_id']));
        } else {
            $this->context['post'] = get_post();
            $this->context['post_title'] = $this->context['post']->post_title;
            $this->context['post_id'] = $this->context['post']->ID;
        }

        $this->context['timberpost'] = Timber::get_post($this->context['post_id']);
        $this->context['page_type'] = getTermsSlugs($this->context['post_id'], 'page_type', true);
        if (!empty($this->context['page_type'])) {
            $this->context['body_class'] = $this->context['body_class'] . ' woodypage-' . $this->context['page_type'];
        }

        // Define Woody Components
        $this->addWoodyComponents();

        // Define SubWoodyTheme_TemplateParts
        $this->addHeaderFooter();

        // GTM
        $this->addGTM();

        // Added Icons
        $this->addIcons();

        // Add langSwitcher
        $this->addLanguageSwitcher();

        // Add langSwitcher
        $this->addSeasonSwitcher();

        // Add addEsSearchBlock
        $this->addEsSearchBlock();

        // Set a global dist dir
        $this->context['dist_dir'] = WP_DIST_DIR;
    }

    private function addWoodyComponents()
    {
        $this->context['woody_components'] = getWoodyTwigPaths();
    }

    private function addHeaderFooter()
    {
        // Define SubWoodyTheme_TemplateParts
        if (class_exists('SubWoodyTheme_TemplateParts')) {
            $SubWoodyTheme_TemplateParts = new SubWoodyTheme_TemplateParts($this->context['woody_components']);
            if (!empty($SubWoodyTheme_TemplateParts->website_logo)) {
                $this->context['website_logo'] = $SubWoodyTheme_TemplateParts->website_logo;
            }
            $this->context['page_parts'] = $SubWoodyTheme_TemplateParts->getParts();
        }
    }

    private function addGTM()
    {
        $this->context['gtm'] = RC_GTM;
    }

    private function addIcons()
    {
        // Icons
        $icons = ['favicon', '16', '32', '64', '120', '128', '152', '167', '180', '192'];
        foreach ($icons as $icon) {
            $icon_ext = ($icon == 'favicon') ? $icon . '.ico' : 'favicon.' . $icon . 'w-' . $icon . 'h.png';
            if (file_exists(WP_CONTENT_DIR . '/dist/' . WP_SITE_KEY . '/favicon/' . $icon_ext)) {
                $this->context['icons'][$icon] = WP_HOME . '/app/dist/' . WP_SITE_KEY . '/favicon/' . $icon_ext;
            }
        }
    }

    private function addSeasonSwitcher()
    {
        // Get polylang languages
        $languages = apply_filters('woody_pll_the_seasons', null);

        if (!empty($languages)) {
            $data = $this->createSwitcher($languages);

            // Set a default template
            $template = $this->context['woody_components']['woody_widgets-season_switcher-tpl_01'];
            $this->context['season_switcher'] = Timber::compile($template, $data);
            $this->context['season_switcher_mobile'] = Timber::compile($template, $data);
        }
    }

    private function addLanguageSwitcher()
    {
        // Get polylang languages
        $languages = apply_filters('woody_pll_the_languages', 'auto');

        if (!empty($languages) && count($languages) != 1) {
            $data = $this->createSwitcher($languages);

            // Set a default template
            $template = $this->context['woody_components']['woody_widgets-lang_switcher-tpl_01'];
            $this->context['lang_switcher'] = Timber::compile($template, $data);
            $this->context['lang_switcher_mobile'] = Timber::compile($template, $data);
        }
    }

    private function createSwitcher($languages)
    {
        $data = [];

        // Save the $_GET
        $autoselect_id = !empty($_GET['autoselect_id']) ? 'autoselect_id='.$_GET['autoselect_id'] : '';
        $page = !empty($_GET['page']) ? 'page='.$_GET['page'] : '';
        $output_params = !empty($autoselect_id) ? $autoselect_id.'&' : '';
        $output_params .= !empty($page) ? $page.'&' : '';
        $output_params = substr($output_params, 0, -1);
        $output_params = !empty($output_params) ? '?'.$output_params : '';

        if (!empty($languages)) {
            foreach ($languages as $language) {
                if (!empty($language['current_lang'])) {
                    $data['current_lang'] = substr($language['locale'], 0, 2);
                    $data['langs'][$language['slug']]['url'] = $language['url'] . $output_params;
                    $data['langs'][$language['slug']]['name'] = strpos($language['name'], '(') ? substr($language['name'], 0, strpos($language['name'], '(')) : $language['name'];
                    $data['langs'][$language['slug']]['locale'] = substr($language['locale'], 0, 2);
                    $data['langs'][$language['slug']]['no_translation'] = $language['no_translation'];
                    $data['langs'][$language['slug']]['is_current'] = true;
                    $data['langs'][$language['slug']]['season'] = !empty($language['season']) ? $language['season'] : '';
                } else {
                    $data['langs'][$language['slug']]['url'] = $language['url'] . $output_params;
                    $data['langs'][$language['slug']]['name'] = strpos($language['name'], '(') ? substr($language['name'], 0, strpos($language['name'], '(')) : $language['name'];
                    $data['langs'][$language['slug']]['locale'] = substr($language['locale'], 0, 2);
                    $data['langs'][$language['slug']]['no_translation'] = $language['no_translation'];
                    $data['langs'][$language['slug']]['season'] = !empty($language['season']) ? $language['season'] : '';
                }
            }
        }

        // Get potential external languages
        if (class_exists('SubWoodyTheme_Languages')) {
            $SubWoodyTheme_Languages = new SubWoodyTheme_Languages($this->context['woody_components']);
            if (method_exists($SubWoodyTheme_Languages, 'languagesCustomization')) {
                $languages_customization = $SubWoodyTheme_Languages->languagesCustomization();
                if (!empty($languages_customization['template'])) {
                    $template = $languages_customization['template'];
                }
                $data['flags'] = (!empty($languages_customization['flags'])) ? $languages_customization['flags'] : false;
                if (!empty($languages_customization['external_langs'])) {
                    foreach ($languages_customization['external_langs'] as $lang_key => $language) {
                        $data['langs'][$lang_key]['url'] = $language['url'];
                        $data['langs'][$lang_key]['name'] = $language['name'];
                        $data['langs'][$lang_key]['locale'] = (!empty($language['locale'])) ? substr($language['locale'], 0, 2) : $lang_key;
                        $data['langs'][$lang_key]['target'] = '_blank';
                    }
                }
            }
        }

        if (!empty($data['langs']) && count($data['langs']) == 1) {
            return;
        }

        return $data;
    }

    private function addEsSearchBlock()
    {
        $data = [];

        $search_post_id = get_field('es_search_page_url', 'option');

        if (!empty($search_post_id)) {
            $data['search_url'] = get_permalink(pll_get_post($search_post_id));

            $suggest = get_field('es_search_block_suggests', 'option');
            if (!empty($suggest) && !empty($suggest['suggest_pages'])) {
                $data['suggest']['title'] = (!empty($suggest['suggest_title'])) ? $suggest['suggest_title'] : '';
                foreach ($suggest['suggest_pages'] as $page) {
                    $t_page = pll_get_post($page['suggest_page']);
                    if (!empty($t_page)) {
                        $post = Timber::get_post($t_page);
                        if (!empty($post)) {
                            $data['suggest']['pages'][] = getPagePreview('', $post);
                        }
                    }
                }
            }

            if (class_exists('SubWoodyTheme_esSearch')) {
                $SubWoodyTheme_esSearch = new SubWoodyTheme_esSearch($this->context['woody_components']);
                if (method_exists($SubWoodyTheme_esSearch, 'esSearchBlockCustomization')) {
                    $esSearchBlockCustomization = $SubWoodyTheme_esSearch->esSearchBlockCustomization();
                    if (!empty($esSearchBlockCustomization['template'])) {
                        $template = $languages_customization['template'];
                    }
                }
            }

            // Set a default template
            $template = $this->context['woody_components']['woody_widgets-es_search_block-tpl_01'];
            $this->context['es_search_block'] = Timber::compile($template, $data);
            $this->context['es_search_block_mobile'] = Timber::compile($template, $data);
        }
    }
}
