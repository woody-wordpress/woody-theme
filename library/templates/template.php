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
    protected $globals = [];

    // Force les classes filles à définir cette méthode
    abstract protected function setTwigTpl();
    abstract protected function extendContext();
    abstract protected function registerHooks();
    abstract protected function getHeaders();

    public function __construct()
    {
        if (!class_exists('Timber')) {
            header('HTTP/1.1 503 Service Temporarily Unavailable');
            header('Status: 503 Service Temporarily Unavailable');
            header('Retry-After: 5');
            exit();
        }

        // Added Feature-Policy
        header('Feature-Policy: autoplay *');

        add_filter('timber_compile_data', [$this, 'timberCompileData']);

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

    public function timberCompileData($data)
    {
        $this->setGlobals(); //TODO: To test if we can move this on construct
        $data['globals'] = apply_filters('woody_timber_compile_globals', $this->globals);
        return $data;
    }

    private function setGlobals()
    {
        if (empty($this->globals['post_title']) && !empty($this->context['post_title'])) {
            $this->globals['post_title'] = $this->context['post_title'];
        }

        if (empty($this->globals['post_id']) && !empty($this->context['post_id'])) {
            $this->globals['post_id'] = $this->context['post_id'];
        }

        if (empty($this->globals['page_type']) && !empty($this->context['page_type'])) {
            $this->globals['page_type'] = $this->context['page_type'];
        }

        if (empty($this->globals['sheet_id']) && !empty($this->context['sheet_id'])) {
            $this->globals['sheet_id'] = $this->context['sheet_id'];
        }

        if (empty($this->globals['woody_options_pages'])) {
            $this->globals['woody_options_pages'] = $this->getWoodyOptionsPagesValues();
        }

        if (empty($this->globals['tags'])) {
            $this->globals['tags'] = $this->getTags($this->context['post_id']);
        }

        if (empty($this->globals['current_lang'])) {
            $this->globals['current_lang'] = apply_filters('woody_pll_current_language', null);
        }

        if (empty($this->globals['current_season'])) {
            $this->globals['current_season'] = apply_filters('woody_pll_current_season', null);
        }

        if (empty($this->globals['current_locale'])) {
            $this->globals['current_locale'] = pll_current_language();
        }

        if (empty($this->globals['languages'])) {
            $this->globals['languages'] = apply_filters('woody_pll_the_locales', null);
        }
    }

    private function getTags($post_id)
    {
        $return = [];
        $taxonomies = ['places', 'seasons', 'themes'];

        foreach ($taxonomies as $taxonomy) {
            $all_taxonomy = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            ));

            $all_terms = [];
            foreach ($all_taxonomy as $term) {
                $all_terms[$term->term_id] = $term->slug;
            }

            $return[$taxonomy] = [];
            $terms = get_the_terms($post_id, $taxonomy);
            if ($terms != false && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    if ($term->parent != 0) {
                        $return[$taxonomy][$all_terms[$term->parent]][] = $term->name;
                    } else {
                        $return[$taxonomy][] = $term->name;
                    }
                }
            }
        }

        return $return;
    }

    private function getWoodyOptionsPagesValues()
    {
        $return = [];

        $return['favorites_url'] = get_field('favorites_page_url', 'options');
        $return['search_url'] = get_field('es_search_page_url', 'options');
        $return['weather_url'] = get_field('weather_page_url', 'options');
        $return['tides_url']= get_field('tides_page_url', 'options');
        $return['disqus_instance_url'] = get_field('disqus_instance_url', 'options');

        return $return;
    }

    public function render()
    {
        if (!empty($this->twig_tpl) && !empty($this->context)) {
            $this->context = apply_filters('woody_theme_context', $this->context);
            \Timber::render($this->twig_tpl, $this->context);
        }
    }

    private function initContext()
    {
        $this->context = Timber::get_context();
        $this->context['post_id'] = get_the_ID();
        $this->context['current_url'] = get_permalink();
        $this->context['site_key'] = WP_SITE_KEY;

        // Default values
        $this->context['post'] = false;
        $this->context['post_title'] = false;
        $this->context['sheet_id'] = false;
        $this->context['page_type'] = false;
        $this->context['metas'] = [];

        $this->context['enabled_woody_options'] = WOODY_OPTIONS;
        $this->context['woody_access_staging'] = WOODY_ACCESS_STAGING;


        $the_title = get_field('woodyseo_meta_title');
        // SEO Context

        $this->context['title'] = (!empty($the_title)) ? woody_untokenize($the_title) : html_entity_decode(get_the_title()) . ' | ' . $this->context['site']['name'];
        $this->context['title'] = apply_filters('woody_seo_transform_pattern', $this->context['title']);
        $this->context['metas'] = $this->setMetadata();
        $this->context['custom_meta'] = get_field('woody_custom_meta', 'options');

        // Woody options pages
        $this->context['woody_options_pages'] = $this->getWoodyOptionsPagesValues();

        /******************************************************************************
         * Sommes nous dans le cas d'une page miroir ?
         ******************************************************************************/

        $terms = get_the_terms($this->context['post_id'], 'page_type');
        $is_mirror_page = false;
        if (!empty($terms) && is_array($terms)) {
            foreach ($terms as $term) {
                if ($term->slug == 'mirror_page') {
                    $is_mirror_page = true;
                    break 1;
                }
            }
        }

        $mirror_page = getAcfGroupFields('group_5c6432b3c0c45');
        if ($is_mirror_page === true && !empty($mirror_page['mirror_page_reference'])) {
            $this->context['mirror_id'] = get_the_ID();
            $this->context['post_id'] = $mirror_page['mirror_page_reference'];
            $this->context['post'] = get_post($this->context['post_id']);
            $this->context['post_title'] = get_the_title();
        } else {
            $this->context['post'] = get_post();
            if (!empty($this->context['post'])) {
                $this->context['post_title'] = $this->context['post']->post_title;
                $this->context['post_id'] = $this->context['post']->ID;
                if (!empty($this->context['post_id'])) {
                    $this->context['sheet_id'] = get_post_type($this->context['post_id']) === 'touristic_sheet' ? get_post_meta($this->context['post_id'], 'touristic_sheet_id')[0] : false;
                }
            }
        }

        if (!empty($this->context['post'])) {
            $this->context['page_type'] = getTermsSlugs($this->context['post_id'], 'page_type', true);
        }

        if (!empty($this->context['page_type'])) {
            $this->context['body_class'] = $this->context['body_class'] . ' woodypage-' . $this->context['page_type'];
        }

        if (!empty($this->context['woody_access_staging'])) {
            $this->context['body_class'] = $this->context['body_class'] . ' woody_staging';
        }

        // Define Woody Components
        $this->addWoodyComponents();

        // GTM
        $this->addGTM();

        // Added Icons
        $this->addIcons();

        $tools_blocks = [];

        // Add langSwitcher
        $tools_blocks['lang_switcher_button'] = $this->addLanguageSwitcherButton();
        $this->context['lang_switcher_button'] = apply_filters('lang_switcher', $tools_blocks['lang_switcher_button']);
        $this->context['lang_switcher_button_mobile'] = apply_filters('lang_switcher_mobile', $tools_blocks['lang_switcher_button']);

        $this->context['lang_switcher_reveal'] = $this->addLanguageSwitcherReveal();

        // Add langSwitcher
        $tools_blocks['season_switcher'] = $this->addSeasonSwitcher();
        $this->context['season_switcher'] = apply_filters('season_switcher', $tools_blocks['season_switcher']);
        $this->context['season_switcher_mobile'] = apply_filters('season_switcher_mobile', $tools_blocks['season_switcher']);

        // Add addEsSearchBlock
        $tools_blocks['es_search_button'] = $this->addEsSearchButton();
        $this->context['es_search_button'] = apply_filters('es_search_block', $tools_blocks['es_search_button']);
        $this->context['es_search_button_mobile'] = apply_filters('es_search_block_mobile', $tools_blocks['es_search_button']);

        $this->context['es_search_reveal'] = $this->addEsSearchReveal();

        // Add addFavoritesBlock
        if (in_array('favorites', $this->context['enabled_woody_options'])) {
            $tools_blocks['favorites_block'] = $this->addFavoritesBlock();
            $this->context['favorites_block'] = apply_filters('favorites_block', $tools_blocks['favorites_block']);
            $this->context['favorites_block_mobile'] = apply_filters('favorites_block_mobile', $tools_blocks['favorites_block']);
        }

        if (in_array('insitu', $this->context['enabled_woody_options'])) {
            $tools_blocks['preparespot_switcher'] = $this->addPrepareSpotSwitcher();
            $this->context['preparespot_switcher'] = apply_filters('preparespot_switcher', $tools_blocks['preparespot_switcher']);
        }

        // Add more tools
        $this->context['subtheme_more_tools'] = apply_filters('more_tools', [], $tools_blocks);

        // Define SubWoodyTheme_TemplateParts
        $this->addHeaderFooter($tools_blocks);

        // Set a global dist dir
        $this->context['dist_dir'] = WP_DIST_DIR;
    }

    private function setMetadata()
    {
        $return = [];

        // ******************************* //
        // Définition des metas statiques
        // ******************************* //

        $return = [
            'canonical' => [
                '#tag' => 'link',
                '#attributes' => [
                    'rel' => 'canonical',
                    'href' => apply_filters('woody_get_permalink', $this->context['post_id'])
                ]
            ],
            'charset' => [
                '#tag' => 'meta',
                '#attributes' => [
                    'charset' => $this->context['site']['charset'],
                ]
            ],
            'http-equiv' => [
                '#tag' => 'meta',
                '#attributes' => [
                    'http-equiv' => 'X-UA-Compatible',
                    'content' => 'IE=edge'
                ]
            ],
            'generator' => [ // Add generator (Pour julien check ERP)
                '#tag' => 'meta',
                '#attributes' => [
                    'name' => 'generator',
                    'content' => 'Raccourci Agency - WP'
                ]
            ],
            'viewport' => [
                '#tag' => 'meta',
                '#attributes' => [
                    'name' => 'viewport',
                    'content' => 'width=device-width,initial-scale=1'
                ]
            ],
            'robots' => [
                '#tag' => 'meta',
                '#attributes' => [
                    'name' => 'robots',
                    'content' => 'max-snippet:-1, max-image-preview:large, max-video-preview:-1'
                ]
            ],
            'og:type' => [
                '#tag' => 'meta',
                '#attributes' => [
                    'property' => 'og:type',
                    'content' => 'website'
                ]
            ],
            'og:url' => [
                '#tag' => 'meta',
                '#attributes' => [
                    'property' => 'og:url',
                    'content' => $this->context['current_url']
                ]
            ],
            'twitter:card' => [
                '#tag' => 'meta',
                '#attributes' => [
                    'name' => 'twitter:card',
                    'content' => 'summary_large_image'
                ]
            ],
        ];

        // ******************************* //
        // On ajoute les metas og:image et twitter:image (image de mise en avant ou image du visuel et accroche)
        // ******************************* //
        $image = get_field('focus_img');
        if (empty($image)) {
            $image = get_field('field_5b0e5ddfd4b1b');
        }

        if (!empty($image)) {
            $return['og:image'] = [
                '#tag' => 'meta',
                '#attributes' => [
                    'property' => 'og:image',
                    'content' => $image['sizes']['ratio_2_1']
                ]
            ];
            $return['twitter:image'] = [
                '#tag' => 'meta',
                '#attributes' => [
                    'property' => 'twitter:image',
                    'content' => $image['sizes']['ratio_2_1']
                ]
            ];
        }

        // ******************************* //
        // On ajoute la meta og:site_name
        // ******************************* //
        if (!empty($this->context['site']['name'])) {
            $return['og:site_name'] = [
                '#tag' => 'meta',
                '#attributes' => [
                    'property' => 'og:site_name',
                    'content' => !(empty($this->context['site']['name'])) ? $this->context['site']['name'] : ''
                ]
            ];
        }

        // ******************************* //
        // On ajoute les meta de localisation og:locale et og:locale:alternate pour chacune des langues du site
        // ******************************* //
        if (!empty(pll_current_language())) {
            $return['og:locale'] = [
                '#tag' => 'meta',
                '#attributes' => [
                    'property' => 'og:locale',
                    'content' => pll_current_language('locale')
                ]
            ];
        };

        // On récupère les langues activées pour ajouter une balise og:alternate dans les metas
        $woody_lang_enable = get_option('woody_lang_enable');
        if (is_array($woody_lang_enable)) {
            $current_lang = apply_filters('woody_pll_current_language', null);
            if (($key = array_search($current_lang, $woody_lang_enable)) !== false) {
                unset($woody_lang_enable[$key]);
            }

            if (!empty($woody_lang_enable)) {
                foreach ($woody_lang_enable as $lang) {
                    $pll_lang = get_term_by('slug', $lang, 'language');
                    $pll_lang_data = (!empty($pll_lang)) ? maybe_unserialize($pll_lang->description) : '';
                    $pll_locale = (!empty($pll_lang_data)) ? $pll_lang_data['locale'] : '';
                    $return['og:locale:alternate_' . $lang] = [
                    '#tag' => 'meta',
                    '#attributes' => [
                        'property' => 'og:locale:alternate',
                        'content' => $pll_locale
                        ]
                    ];
                }
            }
        }

        // ******************************* //
        // On récupère les informations saisies dans Woody SEO
        // ******************************* //
        $woody_seo_data = getAcfGroupFields('group_5d7f7cd5615c0', null, true);

        if (!empty($woody_seo_data)) {
            foreach ($woody_seo_data as $data_key => $data) {
                if (is_string($data)) {
                    $woody_seo_data[$data_key] = trim($data);
                    $data = apply_filters('woody_seo_transform_pattern', $data);
                }

                switch ($data_key) {
                    case 'woodyseo_meta_description':
                        $return['description'] = [
                            '#tag' => 'meta',
                            '#attributes' => [
                                'name' => 'description',
                                'content' => woody_untokenize($data)
                            ]
                        ];

                        if (!empty($data)) {
                            $return['description']['#attributes']['content'] = woody_untokenize($data);
                        } else {
                            $return['description']['#attributes']['content'] = strip_tags(get_field('page_teaser_desc'));
                        }

                        break;
                    case 'woodyseo_fb_title':

                        $return['og:title'] = [
                            '#tag' => 'meta',
                                '#attributes' => [
                                    'property' => 'og:title',
                                ]
                        ];

                        if (!empty($data)) {
                            $return['og:title']['#attributes']['content'] = woody_untokenize($data);
                        } elseif (!empty(get_field('woodyseo_meta_title'))) {
                            $return['og:title']['#attributes']['content'] = apply_filters('woody_seo_transform_pattern', woody_untokenize(get_field('woodyseo_meta_title')));
                        } else {
                            $return['og:title']['#attributes']['content'] = get_the_title() . ' | ' . $this->context['site']['name'];
                        }
                        break;
                    case 'woodyseo_fb_description':
                            $return['og:description'] = [
                                '#tag' => 'meta',
                                '#attributes' => [
                                    'property' => 'og:description',
                                ]
                            ];

                            if (!empty($data)) {
                                $return['og:description']['#attributes']['content'] = woody_untokenize($data);
                            } else {
                                $return['og:description']['#attributes']['content'] = $return['description']['#attributes']['content'];
                            }
                        break;
                    case 'woodyseo_fb_image':
                        if (!empty($data) && !empty($data['sizes'])) {
                            $return['og:image'] = [
                                '#tag' => 'meta',
                                '#attributes' => [
                                    'property' => 'og:image',
                                    'content' => $data['sizes']['ratio_2_1']
                                ]
                            ];
                        }
                        break;
                    case 'woodyseo_twitter_title':
                        $return['twitter:title'] = [
                            '#tag' => 'meta',
                            '#attributes' => [
                                'name' => 'twitter:title',
                            ]
                        ];

                        if (!empty($data)) {
                            $return['twitter:title']['#attributes']['content'] = woody_untokenize($data);
                        } elseif (!empty(get_field('woodyseo_meta_title'))) {
                            $return['twitter:title']['#attributes']['content'] = apply_filters('woody_seo_transform_pattern', woody_untokenize(get_field('woodyseo_meta_title')));
                        } else {
                            $return['twitter:title']['#attributes']['content'] = get_the_title() . ' | ' . $this->context['site']['name'];
                        }
                        break;
                    case 'woodyseo_twitter_description':
                        $return['twitter:description'] = [
                            '#tag' => 'meta',
                            '#attributes' => [
                                'name' => 'twitter:description',
                            ]
                        ];

                        if (!empty($data)) {
                            $return['twitter:description']['#attributes']['content'] = woody_untokenize($data);
                        } else {
                            $return['twitter:description']['#attributes']['content'] = $return['description']['#attributes']['content'];
                        }
                        break;
                    case 'woodyseo_twitter_image':
                        if (!empty($data) && !empty($data['sizes'])) {
                            $return['twitter:image'] = [
                                '#tag' => 'meta',
                                '#attributes' => [
                                    'name' => 'twitter:image',
                                    'content' => $data['sizes']['ratio_2_1']
                                ]
                            ];
                        }
                        break;
                }
            }

            if ($woody_seo_data['woodyseo_index'] === false) {
                $return['robots']['#attributes']['content'] = $return['robots']['#attributes']['content'] . ', noindex';
            }

            if ($woody_seo_data['woodyseo_follow'] === false) {
                $return['robots']['#attributes']['content'] = $return['robots']['#attributes']['content'] . ', nofollow';
            }

            // No index no follow sur tous les modèles
            if (get_post_type() == "woody_model") {
                $return['robots']['#attributes']['content'] = strpos($return['robots']['#attributes']['content'], 'noindex') === false ? $return['robots']['#attributes']['content'] . ', noindex' : $return['robots']['#attributes']['content'];
                $return['robots']['#attributes']['content'] = strpos($return['robots']['#attributes']['content'], 'nofollow') === false ? $return['robots']['#attributes']['content'] . ', nofollow' : $return['robots']['#attributes']['content'];
            }

            // On ajoute un balise noindex/nofollow sur toutes les pages des langues non activées
            $lang_enable = get_option('woody_lang_enable');
            if (is_array($lang_enable) && !in_array(pll_current_language(), $lang_enable)) {
                $robots_noindex = strpos($return['robots']['#attributes']['content'], 'noindex');
                if (!$robots_noindex) {
                    $return['robots']['#attributes']['content'] = $return['robots']['#attributes']['content'] . ', noindex';
                }

                $robots_nofollow = strpos($return['robots']['#attributes']['content'], 'nofollow');
                if (!$robots_nofollow) {
                    $return['robots']['#attributes']['content'] = $return['robots']['#attributes']['content'] . ', nofollow';
                }
            }
        }

        // On ajoute la meta desc à la racine du contexte pour y accéder rapidement
        if (!empty($return['description'])) {
            $this->context['description'] = $return['description']['#attributes']['content'];
        }

        // On permet la surcharge des metadata
        $return = apply_filters('woody_seo_edit_metas_array', $return);
        return $return;
    }

    private function addWoodyComponents()
    {
        $this->context['woody_components'] = getWoodyTwigPaths();
    }

    private function addHeaderFooter($tools_blocks = [])
    {
        // Define SubWoodyTheme_TemplateParts
        if (class_exists('SubWoodyTheme_TemplateParts')) {
            $SubWoodyTheme_TemplateParts = new SubWoodyTheme_TemplateParts($this->context['woody_components'], $tools_blocks);
            if (!empty($SubWoodyTheme_TemplateParts->mobile_logo)) {
                $this->context['mobile_logo'] = $SubWoodyTheme_TemplateParts->mobile_logo;
            }
            if (!empty($SubWoodyTheme_TemplateParts->website_logo)) {
                $this->context['website_logo'] = $SubWoodyTheme_TemplateParts->website_logo;
            }

            $pll_options = get_option('polylang');

            $this->context['home_url'] = pll_home_url();
            $this->context['page_parts'] = $SubWoodyTheme_TemplateParts->getParts();
        }
    }


    private function addGTM()
    {
        $this->context['gtm'] = WOODY_GTM;
    }

    private function addIcons()
    {
        $this->context['icons'] = apply_filters('woody_enqueue_favicons', null);
    }

    private function addSeasonSwitcher()
    {
        // Get polylang languages
        $languages = apply_filters('woody_pll_the_seasons', null);

        if (!empty($languages)) {
            $data = $this->createSwitcher($languages);

            // Set a default template
            $tpl = apply_filters('season_switcher_tpl', null);
            $template = has_filter('season_switcher_tpl') ? $this->context['woody_components'][$tpl['template']] : $this->context['woody_components']['woody_widgets-season_switcher-tpl_01'];

            $return = \Timber::compile($template, $data);
            return $return;
        }
    }

    private function addLanguageSwitcherButton()
    {
        $languages = apply_filters('woody_pll_the_languages', 'auto');

        if (!empty($languages) && count($languages) != 1) {
            $data = $this->createSwitcher($languages);

            // Set a default template
            $tpl = apply_filters('lang_switcher_button', null);
            $template = has_filter('lang_switcher_button') ? $this->context['woody_components'][$tpl['template']] : $this->context['woody_components']['woody_widgets-lang_switcher-tpl_01'];

            // Allow data override
            $data = apply_filters('lang_switcher_data', $data);

            $return = (!empty($data)) ? \Timber::compile($template, $data) : '';
            return $return;
        }
    }

    private function addLanguageSwitcherReveal()
    {
        // Get polylang languages
        $languages = apply_filters('woody_pll_the_languages', 'auto');

        if (!empty($languages) && count($languages) != 1) {
            $data = $this->createSwitcher($languages);

            // Set a default template
            $tpl = apply_filters('lang_switcher_reveal', null);
            $template = has_filter('lang_switcher_reveal') ? $this->context['woody_components'][$tpl['template']] : $this->context['woody_components']['reveals-lang_switcher-tpl_01'];

            // Allow data override
            $data = apply_filters('lang_switcher_data', $data);

            $compile = \Timber::compile($template, $data);
            $compile = apply_filters('lang_switcher_compile', $compile);

            return $compile;
        }
    }

    private function createSwitcher($languages)
    {
        $data = [];

        // Save the $_GET
        $autoselect_id = !empty($_GET['autoselect_id']) ? 'autoselect_id=' . $_GET['autoselect_id'] : '';
        $page = !empty($_GET['page']) ? 'page=' . $_GET['page'] : '';
        $output_params = !empty($autoselect_id) ? $autoselect_id . '&' : '';
        $output_params .= !empty($page) ? $page . '&' : '';
        $output_params = substr($output_params, 0, -1);
        $output_params = !empty($output_params) ? '?' . $output_params : '';

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
                // if (!empty($languages_customization['template'])) {
                //     $template = $languages_customization['template'];
                // }
                $data['flags'] = (!empty($languages_customization['flags'])) ? $languages_customization['flags'] : false;
                if (!empty($languages_customization['external_langs'])) {
                    foreach ($languages_customization['external_langs'] as $lang_key => $language) {
                        if (!empty($data['langs'][$lang_key])) {
                            $data['langs'][$lang_key]['url'] = $language['url'];
                            $data['langs'][$lang_key]['name'] = $language['name'];
                            $data['langs'][$lang_key]['locale'] = (!empty($language['locale'])) ? substr($language['locale'], 0, 2) : $lang_key;
                            $data['langs'][$lang_key]['target'] = '_blank';
                        } elseif (!is_user_logged_in()) {
                            unset($data['langs'][$lang_key]);
                        }
                    }
                }

                if (!is_user_logged_in() and !empty($languages_customization['hide_langs'])) {
                    foreach ($data['langs'] as $lang_key => $language) {
                        if (in_array($language['locale'], $languages_customization['hide_langs'])) {
                            unset($data['langs'][$lang_key]);
                        }
                    }
                }
            }
        }

        if (!empty($data['langs']) && count($data['langs']) == 1) {
            return[];
        }

        return $data;
    }

    private function addEsSearchButton()
    {
        $search_post_id = apply_filters('woody_get_field_option', 'es_search_page_url');

        if (!empty($search_post_id)) {

            // Set a default template
            $tpl = apply_filters('es_search_button', null);
            $template = has_filter('es_search_button') ? $tpl['template'] : $this->context['woody_components']['woody_widgets-es_search_block-tpl_01'];

            return \Timber::compile($template, []);
        }
    }

    private function addEsSearchReveal()
    {
        $search_post_id = apply_filters('woody_get_field_option', 'es_search_page_url');

        if (!empty($search_post_id)) {
            $data = [];
            $data['search_url'] = apply_filters('woody_get_permalink', pll_get_post($search_post_id));

            $suggest = apply_filters('woody_get_field_option', 'es_search_block_suggests');
            if (!empty($suggest) && !empty($suggest['suggest_pages'])) {
                $data['suggest']['title'] = __('Nos suggestions', 'woody-theme');
                foreach ($suggest['suggest_pages'] as $page) {
                    $t_page = pll_get_post($page['suggest_page']);
                    if (!empty($t_page)) {
                        $post = get_post($t_page);
                        if (!empty($post)) {
                            $data['suggest']['pages'][] = getPagePreview(['display_img' => true], $post);
                        }
                    }
                }
            }

            if (class_exists('SubWoodyTheme_esSearch')) {
                $SubWoodyTheme_esSearch = new SubWoodyTheme_esSearch($this->context['woody_components']);
                if (method_exists($SubWoodyTheme_esSearch, 'esSearchBlockCustomization')) {
                    $esSearchBlockCustomization = $SubWoodyTheme_esSearch->esSearchBlockCustomization();
                    if (!empty($esSearchBlockCustomization['template'])) {
                        $template = $esSearchBlockCustomization['template'];
                    }
                }
            }

            // Set a default template
            $tpl = apply_filters('es_search_reveal', null);
            $template = has_filter('es_search_reveal') ? $tpl['template'] : $this->context['woody_components']['reveals-es_search_block-tpl_01'];

            // Allow data override
            $data['tags'] = !empty($tpl['tags']) ? $tpl['tags'] : '';
            $data = apply_filters('es_search_block_data', $data);

            $compile = \Timber::compile($template, $data);
            $compile = apply_filters('es_search_compile', $compile);

            return $compile;
        }
    }

    private function addFavoritesBlock()
    {
        $favorites_post_id = apply_filters('woody_get_field_option', 'favorites_page_url');
        if (!empty($favorites_post_id)) {
            $data = [];
            $data['favorites_page_url'] = apply_filters('woody_get_permalink', pll_get_post($favorites_post_id));

            // Set a default template
            $tpl = apply_filters('favorites_block_tpl', null);
            $template = !empty($tpl['template']) ? $tpl['template'] : $this->context['woody_components']['woody_widgets-favorites_block-tpl_01'];

            // Allow data override
            $data = apply_filters('favorites_block_data', $data);

            return \Timber::compile($template, $data);
        }
    }

    private function addPrepareSpotSwitcher()
    {
        $data = [];
        if (!empty($this->context['post'])) {
            $data['switch'] = get_field('field_5e7a17ad5c29a', $this->context['post']->ID) ;
        }
        $template = $this->context['woody_components']['woody_widgets-prepare_onspot_switcher-tpl_01'];
        return Timber::compile($template, $data);
    }
}
