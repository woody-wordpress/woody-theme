<?php

/**
 * Template
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

abstract class WoodyTheme_TemplateAbstract
{
    protected $twig_tpl;

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
                    foreach ($val as $val2) {
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
        $this->setGlobals();
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

        if (empty($this->globals['post_type']) && !empty($this->context['post_type'])) {
            $this->globals['post_type'] = $this->context['post_type'];
        }

        if (empty($this->globals['post_modified']) && !empty($this->context['post']->post_modified)) {
            $this->globals['post_modified'] = $this->context['post']->post_modified;
        }

        if (empty($this->globals['post_image']) && !empty($this->context['metas']['og:image']['#attributes']['content'])) {
            $this->globals['post_image'] = $this->context['metas']['og:image']['#attributes']['content'];
        }

        if (empty($this->globals['page_type']) && !empty($this->context['page_type'])) {
            $this->globals['page_type'] = $this->context['page_type'];
        }

        if (empty($this->globals['sheet_id']) && !empty($this->context['sheet_id'])) {
            $this->globals['sheet_id'] = $this->context['sheet_id'];
        }

        if (empty($this->globals['sheet_current_aspect']) && !empty($this->context['sheet_current_aspect'])) {
            $this->globals['sheet_current_aspect'] = $this->context['sheet_current_aspect'];
        }

        if (empty($this->globals['woody_options_pages'])) {
            $this->globals['woody_options_pages'] = $this->getWoodyOptionsPagesValues();
        }

        if (empty($this->globals['tags'])) {
            $this->globals['tags'] = $this->getTags($this->context['post_id']);
        }

        if (empty($this->globals['areas']) && !empty($this->context['post'])) {
            $this->globals['areas'] = apply_filters('woody_addon_search_list_areas', null, $this->context['post']);
        }

        if (empty($this->globals['area']) && !empty($this->context['post'])) {
            $this->globals['area'] = apply_filters('woody_addon_search_area', null, $this->context['post']);
        }

        if (empty($this->globals['current_lang'])) {
            $this->globals['current_lang'] = pll_current_language();
        }

        if (empty($this->globals['current_season'])) {
            $this->globals['current_season'] = apply_filters('woody_pll_current_season', null);
        }

        if (empty($this->globals['current_locale'])) {
            $this->globals['current_locale'] = apply_filters('woody_pll_current_language', null);
        }

        if (empty($this->globals['current_locale_region'])) {
            $this->globals['current_locale_region'] = pll_current_language('locale');
        }

        if (empty($this->globals['languages'])) {
            $this->globals['languages'] = apply_filters('woody_pll_the_locales', null);
        }

        if (empty($this->globals['ancestors']) && !empty($this->context['post'])) {
            $this->globals['ancestors'] = $this->getAncestors($this->context['post']);
        }

        if (empty($this->globals['env'])) {
            $this->globals['env'] = WP_ENV;
        }

        if (empty($this->globals['site_key'])) {
            $this->globals['site_key'] = WP_SITE_KEY;
        }

        if (empty($this->globals['dist_dir'])) {
            $this->globals['dist_dir'] = WP_DIST_DIR;
        }

        if (empty($this->globals['dist_url'])) {
            $this->globals['dist_url'] = WP_DIST_URL;
        }

        if (!array_key_exists('is_mobile', $this->globals) || !is_bool($this->globals['is_mobile'])) {
            $this->globals['is_mobile'] = $this->context['is_mobile'];
        }

        if (empty($this->globals['site_name'])) {
            $this->globals['site_name'] = get_bloginfo('name');
        }

        if (empty($this->globals['context']) && !empty($this->context['page_type'])) {
            $this->globals['context'] = $this->getGlobalContext($this->context['page_type']);
        }

        if (empty($this->globals['map_keys'])) {
            $this->globals['map_keys'] = Woody\Modules\Plugin\Plugin::getMapKeys();
        }
    }

    private function getAncestors($post)
    {
        $return = [];

        if (!empty($post) && is_object($post)) {
            // On ajoute toutes les pages parentes
            $depth = 1;
            $ancestors_ids = get_post_ancestors($post->ID);
            if (!empty($ancestors_ids) && is_array($ancestors_ids)) {
                $ancestors_ids = array_reverse($ancestors_ids);
                foreach ($ancestors_ids as $ancestor_id) {
                    $return['chapter' . $depth] = get_the_title($ancestor_id);
                    ++$depth;
                }
            }

            // Si il s'agit d'une fiche on range tout dans le Chapitre Offres SIT
            if ($post->post_type === 'touristic_sheet') {
                $return['chapter' . $depth] = 'Offres SIT';
                ++$depth;
            }

            // On ajoute la page courante
            $return['chapter' . $depth] = get_the_title($post->ID);
        }

        return $return;
    }

    private function getTags($post_id)
    {
        $return = [];
        $taxonomies = ['places', 'seasons', 'themes', 'targets'];
        $taxonomies = apply_filters('woody_datalayer_tags', $taxonomies);

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
        return apply_filters('woody_options_pages', []);
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
        $this->context['current_url'] = woody_get_permalink();
        $this->context['site_key'] = WP_SITE_KEY;

        // Default values
        $this->context['post'] = null;
        $this->context['post_title'] = null;
        $this->context['sheet_id'] = null;
        $this->context['page_type'] = null;
        $this->context['metas'] = [];
        $this->context['is_mobile'] = wp_is_mobile();

        $this->context['enabled_woody_options'] = WOODY_OPTIONS;
        $this->context['woody_access_staging'] = WOODY_ACCESS_STAGING;

        // SEO Context
        $the_title = get_field('woodyseo_meta_title');
        $this->context['title'] = (empty($the_title)) ? html_entity_decode(get_the_title()) . ' | ' . $this->context['site']['name'] : woody_untokenize($the_title);
        $this->context['title'] = apply_filters('woody_seo_transform_pattern', $this->context['title']);
        $this->context['metas'] = $this->setMetadata();
        $this->context['head_top'] = (empty($this->context['head_top'])) ? [] : $this->context['head_top'];
        $this->context['head_top'][] = get_field('woody_custom_meta', 'options');
        $this->context['head_top'][] = get_field('woody_custom_meta_' . pll_current_language(), 'options');
        $this->context['head_top'] = apply_filters('woody_custom_meta', $this->context['head_top']);

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
        if ($is_mirror_page && !empty($mirror_page['mirror_page_reference'])) {
            $this->context['mirror_id'] = get_the_ID();
            $this->context['post_id'] = $mirror_page['mirror_page_reference'];
            $this->context['post'] = get_post($this->context['post_id']);
            $this->context['post_title'] = get_the_title();
        } else {
            $this->context['post'] = get_post();
            if (!empty($this->context['post'])) {
                $this->context['post_title'] = apply_filters('the_title', $this->context['post']->post_title);
                $this->context['post_id'] = $this->context['post']->ID;
                if (!empty($this->context['post_id'])) {
                    $this->context['sheet_id'] = get_post_type($this->context['post_id']) === 'touristic_sheet' ? (int) get_post_meta($this->context['post_id'], 'touristic_sheet_id')[0] : false;
                }
            }
        }

        if (!empty($this->context['post'])) {
            $this->context['post_type'] = $this->context['post']->post_type;
            $this->context['page_type'] = getTermsSlugs($this->context['post_id'], 'page_type', true);
        }

        if (!empty($this->context['page_type'])) {
            $this->context['body_class'] = $this->context['body_class'] . ' woodypage-' . $this->context['page_type'];
        }

        if (!empty($this->context['woody_access_staging'])) {
            $this->context['body_class'] .= ' woody_staging';
        }

        if ($this->context['is_mobile'] === true) {
            $this->context['body_class'] .= ' is_mobile';
        }

        // Define Woody Components
        $this->addWoodyComponents();

        // GTM
        $this->addGTM();

        // Schema Graph
        $this->addSchemaGraph();

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

        // Add addDealsBlock
        if (in_array('deals', $this->context['enabled_woody_options'])) {
            $tools_blocks['deals_block'] = $this->addDealsBlock();
            $this->context['deals_block'] = apply_filters('deals_block', $tools_blocks['deals_block']);
            $this->context['deals_block_mobile'] = apply_filters('deals_block_mobile', $tools_blocks['deals_block']);
        }

        if (in_array('insitu', $this->context['enabled_woody_options'])) {
            $tools_blocks['preparespot_switcher'] = $this->addPrepareSpotSwitcher();
            $this->context['preparespot_switcher'] = apply_filters('preparespot_switcher', $tools_blocks['preparespot_switcher']);
        }

        // Added Tools from addons
        $tools_blocks = apply_filters('woody_tools_blocks', $tools_blocks, $this->context);

        // Add more tools
        $this->context['subtheme_more_tools'] = apply_filters('more_tools', [], $tools_blocks);

        // Define SubWoodyTheme_TemplateParts
        $this->addHeaderFooter($tools_blocks);

        // Set a global dist dir
        $this->context['dist_dir'] = WP_DIST_DIR;
    }

    private function getCanonical($post_id)
    {
        if (!empty(get_field('woodyseo_canonical_url', $post_id))) {
            // S'il y a une url canonique renseignée, elle est prioritaire
            $permalink = get_field('woodyseo_canonical_url', $post_id);
        } else {
            if (!empty($post_id) && get_post_type($post_id) == 'page') {
                $page_type = getTermsSlugs($post_id, 'page_type', true);

                // On vérifie si la page est de type miroir
                if ($page_type == 'mirror_page') {
                    // On remplace l'id de post courant par l'id de post de référence de la page miroir
                    $post_id = get_field('mirror_page_reference', $post_id);
                }
            }

            $permalink = woody_get_permalink($post_id);
        }

        return apply_filters('woody_canonical_url', $permalink, $post_id);
    }

    private function setMetadata()
    {
        $return = [];
        $woody_lang_enable = (defined('WOODY_LANG_ENABLE') && is_array(WOODY_LANG_ENABLE)) ? WOODY_LANG_ENABLE : [];

        // ******************************* //
        // Définition des metas statiques
        // ******************************* //

        $return = [
            'canonical' => [
                '#tag' => 'link',
                '#attributes' => [
                    'rel' => 'canonical',
                    'href' => $this->getCanonical($this->context['post_id'])
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
                    'content' => empty($this->context['site']['name']) ? '' : $this->context['site']['name']
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
        if (!empty($woody_lang_enable)) {
            $current_lang = apply_filters('woody_pll_current_language', null);
            foreach ($woody_lang_enable as $lang) {
                if ($lang == $current_lang) {
                    continue;
                }

                $pll_lang = get_term_by('slug', $lang, 'language');
                $pll_lang_data = (empty($pll_lang)) ? '' : maybe_unserialize($pll_lang->description);
                $pll_locale = (empty($pll_lang_data)) ? '' : $pll_lang_data['locale'];
                $return['og:locale:alternate_' . $lang] = [
                    '#tag' => 'meta',
                    '#attributes' => [
                        'property' => 'og:locale:alternate',
                        'content' => $pll_locale
                        ]
                    ];
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
                $return['robots']['#attributes']['content'] .= ', noindex';
            }

            if ($woody_seo_data['woodyseo_follow'] === false) {
                $return['robots']['#attributes']['content'] .= ', nofollow';
            }

            // No index no follow sur tous les modèles
            if (get_post_type() == "woody_model") {
                $return['robots']['#attributes']['content'] = strpos($return['robots']['#attributes']['content'], 'noindex') === false ? $return['robots']['#attributes']['content'] . ', noindex' : $return['robots']['#attributes']['content'];
                $return['robots']['#attributes']['content'] = strpos($return['robots']['#attributes']['content'], 'nofollow') === false ? $return['robots']['#attributes']['content'] . ', nofollow' : $return['robots']['#attributes']['content'];
            }

            // On ajoute un balise noindex/nofollow sur toutes les pages des langues non activées
            if (empty($woody_lang_enable) || !in_array(pll_current_language(), $woody_lang_enable)) {
                $robots_noindex = strpos($return['robots']['#attributes']['content'], 'noindex');
                if (!$robots_noindex) {
                    $return['robots']['#attributes']['content'] .= ', noindex';
                }

                $robots_nofollow = strpos($return['robots']['#attributes']['content'], 'nofollow');
                if (!$robots_nofollow) {
                    $return['robots']['#attributes']['content'] .= ', nofollow';
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

            $this->context['home_url'] = pll_home_url();
            $this->context['page_parts'] = $SubWoodyTheme_TemplateParts->getParts();
        }
    }

    private function addGTM()
    {
        $this->context['gtm'] = [
            'id' => (WP_ENV == 'prod' || WP_SITE_KEY == 'woody-sandbox') ? WOODY_GTM : null,
            'datalayer' => []
        ];
    }

    private function addSchemaGraph()
    {
        $schema_graph = [
            "@context" => "https://schema.org",
            "@graph" => []
        ];

        // Main Website
        $website = [
            "@type" => "WebSite",
            "url" => $this->context['http_host'],
            "name" => trim($this->context['site']['name']),
            "description" => trim($this->context['site']['description']),
        ];

        if(!empty($this->context['woody_options_pages']['search_url'])) {
            $website['potentialAction'] = empty($website['potentialAction']) ? [] : $website['potentialAction'];
            $website['potentialAction'][] = [
                "@type" => "SearchAction",
                "target" => woody_get_permalink($this->context['woody_options_pages']['search_url']) . "?query={search_term_string}",
                "query-input" => "required name=search_term_string"
            ];
        }

        // Add to graph
        $schema_graph["@graph"][] = $website;

        // Tourist Information Center
        if (is_front_page()) {
            $is_tourist_information_center = (bool) get_field('woody_tourist_information_center', 'options');
            if ($is_tourist_information_center) {

                $touristic_center = [
                    "@type" => "TouristInformationCenter",
                    "url" => $this->context['http_host'],
                    "name" => trim($this->context['title'])
                ];

                $woody_tourist_informations = get_field('woody_tourist_informations', 'options');
                if(!empty($woody_tourist_informations['woody_tourist_information_city'])) {
                    $touristic_center['address'] = [];
                    $touristic_center['address'][] = [
                        "@type" => "PostalAddress",
                        "addressLocality" => $woody_tourist_informations['woody_tourist_information_city'],
                        "addressCountry" => $woody_tourist_informations['woody_tourist_information_country'],
                        "addressRegion" => $woody_tourist_informations['woody_tourist_information_region'],
                        "postalCode" => $woody_tourist_informations['woody_tourist_information_postal'],
                        "streetAddress" => $woody_tourist_informations['woody_tourist_information_address'],
                    ];
                }

                //TODO: Utilisé par les 2 alpes
                $more_tourist_informations = apply_filters('woody_tourist_more_informations', '');

                // Add to graph
                $schema_graph["@graph"][] = $touristic_center;
            }
        } elseif(!empty($this->context['post'])) {
            $schema_type = get_field('woodyseo_schema_type');
            $schema_type = (empty($schema_type)) ? "WebPage" : $schema_type;

            $webpage = [
                "@type" => $schema_type,
                "url" => $this->context['current_url'],
                "headline" => trim($this->context['title']),
                "inLanguage" => $this->context['site']['language'],
                "datePublished" => $this->context['post']->post_date,
                "dateModified" => $this->context['post']->post_modified,
                "description" => $this->context['description'],
            ];

            // Get Image
            $image = get_field('focus_img');
            $image = (empty($image)) ? get_field('field_5b0e5ddfd4b1b') : $image;
            if (!empty($image) && !empty($image['sizes']) && !empty($image['sizes']['ratio_2_1'])) {
                $webpage['image'][] = $image['sizes']['ratio_2_1'];
            }

            // Get Author
            $profil_name = null;
            $profil_type = get_field('profil_type');
            if (!empty($profil_type) && $profil_type == 'existing_profile') {
                $existing_profile_id = get_field('linked_profile');
                if (!empty($existing_profile_id)) {
                    $profil_name = get_the_title($existing_profile_id);
                }
            } else {
                $profil_name = get_field('profil_name');
            }

            if(!empty($profil_name)) {
                $webpage['author'] = [];
                $webpage['author'][] = [
                    "@type" => "Person",
                    "@name" => $profil_name
                ];
            }

            // Add to graph
            $schema_graph["@graph"][] = $webpage;
        }

        $this->context['schema_graph'] = apply_filters('woody_seo_schema_graph', $schema_graph);
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

        // Add external languages
        $languages = apply_filters('woody_langs_data', $languages);

        if (!empty($languages) && (is_countable($languages) ? count($languages) : 0) != 1) {
            $data = $this->createSwitcher($languages);

            // Set a default template
            $tpl = apply_filters('lang_switcher_button', null);
            $template = has_filter('lang_switcher_button') ? $this->context['woody_components'][$tpl['template']] : $this->context['woody_components']['woody_widgets-lang_switcher-tpl_01'];

            // Allow data override
            $data = apply_filters('lang_switcher_data', $data);

            $return = (empty($data)) ? '' : \Timber::compile($template, $data);
            return $return;
        }
    }

    private function addLanguageSwitcherReveal()
    {
        // Get polylang languages
        $languages = apply_filters('woody_pll_the_languages', 'auto');

        // Add external languages
        $languages = apply_filters('woody_langs_data', $languages);

        if (!empty($languages) && (is_countable($languages) ? count($languages) : 0) != 1) {
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
        $autoselect_id = empty($_GET['autoselect_id']) ? '' : 'autoselect_id=' . $_GET['autoselect_id'];
        $page = empty($_GET['page']) ? '' : 'page=' . $_GET['page'];
        $output_params = empty($autoselect_id) ? '' : $autoselect_id . '&';
        $output_params .= empty($page) ? '' : $page . '&';
        $output_params = substr($output_params, 0, -1);
        $output_params = empty($output_params) ? '' : '?' . $output_params;
        $woody_lang_enable = (defined('WOODY_LANG_ENABLE') && is_array(WOODY_LANG_ENABLE)) ? WOODY_LANG_ENABLE : [];
        if (!empty($languages)) {
            foreach ($languages as $language) {
                if (!empty($language['current_lang'])) {
                    $data['current_lang'] = substr($language['locale'], 0, 2);
                    $data['langs'][$language['slug']]['url'] = $language['url'] . $output_params;
                    $data['langs'][$language['slug']]['name'] = strpos($language['name'], '(') ? substr($language['name'], 0, strpos($language['name'], '(')) : $language['name'];
                    $data['langs'][$language['slug']]['locale'] = substr($language['locale'], 0, 2);
                    $data['langs'][$language['slug']]['no_translation'] = $language['no_translation'];
                    $data['langs'][$language['slug']]['is_current'] = true;
                    $data['langs'][$language['slug']]['season'] = empty($language['season']) ? '' : $language['season'];
                    $data['langs'][$language['slug']]['external'] = false;
                    $data['langs'][$language['slug']]['flag_class'] = woody_pll_get_lang_by_locale($language['locale']);
                } else {
                    $data['langs'][$language['slug']]['url'] = $language['url'] . $output_params;
                    $data['langs'][$language['slug']]['name'] = strpos($language['name'], '(') ? substr($language['name'], 0, strpos($language['name'], '(')) : $language['name'];
                    $data['langs'][$language['slug']]['locale'] = substr($language['locale'], 0, 2);
                    $data['langs'][$language['slug']]['no_translation'] = $language['no_translation'];
                    $data['langs'][$language['slug']]['season'] = empty($language['season']) ? '' : $language['season'];
                    $data['langs'][$language['slug']]['external'] = false;
                    $data['langs'][$language['slug']]['flag_class'] = woody_pll_get_lang_by_locale($language['locale']);
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
                $data['flags'] = (empty($languages_customization['flags'])) ? false : $languages_customization['flags'];
                if (!empty($languages_customization['external_langs'])) {
                    foreach ($languages_customization['external_langs'] as $lang_key => $language) {
                        if (!empty($data['langs'][$lang_key])) {
                            $data['langs'][$lang_key]['url'] = $language['url'];
                            $data['langs'][$lang_key]['name'] = $language['name'];
                            $data['langs'][$lang_key]['locale'] = (empty($language['locale'])) ? $lang_key : substr($language['locale'], 0, 2);
                            $data['langs'][$lang_key]['target'] = '_blank';
                            $data['langs'][$lang_key]['external'] = true;
                        } elseif (!is_user_logged_in()) {
                            unset($data['langs'][$lang_key]);
                        }
                    }
                }

                if (!is_user_logged_in() && !empty($languages_customization['hide_langs'])) {
                    foreach ($data['langs'] as $lang_key => $language) {
                        if (in_array($language['locale'], $languages_customization['hide_langs'])) {
                            unset($data['langs'][$lang_key]);
                        }
                    }
                }
            }
        }

        if(!empty($data['langs'])) {
            foreach ($data['langs'] as $lang_key => $language) {
                $is_in_switcher = is_user_logged_in() || in_array($lang_key, $woody_lang_enable) || $this->isExternalLanguage($language);
                if(!$is_in_switcher) {
                    unset($data['langs'][$lang_key]);
                }
            }
        }

        if (!empty($data['langs']) && count($data['langs']) == 1) {
            return[];
        }

        return $data;
    }

    private function isExternalLanguage($language)
    {
        if(!empty($language['external'])) {
            return true;
        }

        return false;
    }

    private function addDealsBlock()
    {
        $deals_post_id = apply_filters('woody_get_field_option', 'deals_page_url');
        if (!empty($deals_post_id)) {
            $data = [];
            $data['deals_page_url'] = woody_get_permalink(pll_get_post($deals_post_id));

            // Set a default template
            $tpl = apply_filters('deals_block_tpl', null);
            $template = empty($tpl['template']) ? $this->context['woody_components']['woody_widgets-deals_block-tpl_01'] : $tpl['template'];

            // Allow data override
            $data = apply_filters('deals_block_data', $data);

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

    private function getGlobalContext($post_type)
    {
        if($post_type == 'woody_rdbk_leaflets' || ($post_type == 'touristic_sheet' && $_GET['roadbook'])) {
            return 'tipy';
        }
        return 'website';
    }
}
