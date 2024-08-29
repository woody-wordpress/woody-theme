<?php

/**
 * Admin Menus
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.28.35
 *
 */

namespace Woody\WoodyTheme\library\classes\menus;

class Admin
{
    private $current_lang;

    public $menu_post_ids;

    public $pages_options;

    public function __construct()
    {
        $this->registerHooks();
        if (function_exists('pll_current_language')) {
            $this->current_lang = pll_current_language();
        }

        $this->pages_options = $this->setPagesOptions();
    }

    public function registerHooks()
    {
        add_action('admin_menu', [$this, 'addMenuMainPages'], 11);
        add_action('admin_menu', [$this, 'addMenusOptionsPages'], 11);

        add_action('acf/init', [$this, 'addOptionsPagesFields'], 11);
        add_action('acf/init', [$this, 'addSubmenuFieldGroups'], 11);

        // TODO: Décommenter pour l'administration en back-office
        // add_action('acf/init', [$this, 'addMenuFieldGroups'], 11);
    }

    /**
     *
     * menuPostIds
     * @author Thomas Navarro
     * @return ids array | Retourne un tableau de post ID des entrées de menu
     *
     */
    public function setMenuPostIds($menu)
    {
        // TODO: Décommenter pour l'administration en back-office
        // Récupère les post_ids de la metabox `Structure` et génère les sous-menus en conséquence
        // $menu_posts = apply_filters('woody_get_field_option', 'field_generate_' . $menu['menu_slug']);

        // foreach ($menu_posts as $menu_post) {
        //     $this->menu_post_ids[] = $menu_post['post']->ID;
        // }

        //TODO: Commenter pour l'administration en back-office
        // Permet de définir les liens du menu principal dans son thème enfant
        $this->menu_post_ids = apply_filters('woody/menus/set_menu_post_ids', []);
    }

    /**
     *
     * acfJsonStore
     * @author Thomas Navarro
     * @return acf_json_store array | Répertorie tout les acf-json dispo pour la génération des pages d'options
     *
     */
    public function acfJsonStore()
    {
        $acf_json_keys = [
            'link' => [
                'acf_key' => 'group_link',
                'description' => 'Groupe de champs ACF comportant un lien'
            ],
            'link_icon' => [
                'acf_key' => 'group_link_icon',
                'description' => 'Groupe de champs ACF comportant un icône et un lien'
            ],
            'submenus_fields' => [
                'acf_key' => 'group_fields_for_submenus',
                'description' => 'Groupe de champs ACF comportant les champs dispo pour les sous-menus'
            ],
            'submenus' => [
                'acf_key' => 'group_submenus',
                'description' => 'Groupe de champs ACF pour générer les sous-menus'
            ],
        ];

        // Permet d'ajouter un acf-json au store selon les besoins
        $acf_json_store = apply_filters('woody/menus/acf_group_keys', $acf_json_keys);
        return $acf_json_store;
    }

    /**
     *
     * setPagesOptions
     * @author Thomas Navarro
     * @return options array | Retourne les paramètres de chaque page d'options crée
     *
     */
    public function setPagesOptions()
    {
        $acf_json_store = $this->acfJsonStore();

        $options = [
            'main-menu' => [
                'page_title'    => 'Administation du menu principal',
                'menu_title'    => 'Menu principal',
                'menu_slug'     => 'main-menu-' . $this->current_lang,
                'parent_slug'   => 'custom-menus',
                'capability'    => 'woody_menus',
                'acf_group_key' => $acf_json_store['submenus']['acf_key']
            ],
            'sub_pages' => [
                'legal-menu' => [
                    'page_title'    => 'Administration du menu infos légales',
                    'menu_title'    => 'Menu infos légales',
                    'menu_slug'     => 'legal-menu-' . $this->current_lang,
                    'parent_slug'   => 'custom-menus',
                    'capability'    => 'woody_menus',
                    'acf_group_key' => $acf_json_store['link']['acf_key'],
                ],
                'pro-menu' => [
                    'page_title'    => 'Administration du menu pro',
                    'menu_title'    => 'Menu pro',
                    'menu_slug'     => 'pro-menu-' . $this->current_lang,
                    'parent_slug'   => 'custom-menus',
                    'capability'    => 'woody_menus',
                    'acf_group_key' => $acf_json_store['link']['acf_key'],
                ],
                'social-menu' => [
                    'page_title'    => 'Administration des liens des réseaux sociaux',
                    'menu_title'    => 'Réseaux sociaux',
                    'menu_slug'     => 'social-menu-' . $this->current_lang,
                    'parent_slug'   => 'custom-menus',
                    'capability'    => 'woody_menus',
                    'acf_group_key' => $acf_json_store['link_icon']['acf_key'],
                ]
            ],
            'pages' => []
        ];

        // Permet d'ajouter une page d'option selon les besoins
        $extended_pages = apply_filters('woody/menus/create_pages_options', $options['pages'], $acf_json_store);
        $options['pages'] = $extended_pages;

        // Permet d'ajouter une sous-page d'option selon les besoins
        $extended_sub_pages = apply_filters('woody/menus/create_sub_pages_options', $options['sub_pages'], $acf_json_store);
        $options['sub_pages'] = $extended_sub_pages;

        return $options;
    }

    /**
     *
     * addMenuMainPages
     * @author Thomas Navarro
     * @see WoodyTheme_Cleanup_Admin->customMenusPage()
     * @return void | Ajoute la page d'option du menu principal
     *
     */
    public function addMenuMainPages()
    {
        if (function_exists('acf_add_options_sub_page')) {
            acf_add_options_sub_page($this->pages_options['main-menu']);
        }
    }

    /**
     *
     * addMenusOptionsPages
     * @author Thomas Navarro
     * @return void | Ajoute les pages d'options au menu WP
     *
     */
    public function addMenusOptionsPages()
    {
        // Options page
        if (function_exists('acf_add_options_page') && !empty($this->pages_options['pages'])) {
            foreach ($this->pages_options['pages'] as $page) {
                acf_add_options_page($page);
            }
        }

        // Options subpage
        if (function_exists('acf_add_options_sub_page') && !empty($this->pages_options['sub_pages'])) {
            foreach ($this->pages_options['sub_pages'] as $sub_page) {
                acf_add_options_sub_page($sub_page);
            }
        }
    }

    /**
     *
     * addOptionsPagesFields
     * @author Thomas Navarro
     * @return void | Ajoute les champs au pages d'options en se basant sur un acf-json
     *
     */
    public function addOptionsPagesFields()
    {
        $pages_options = array_merge($this->pages_options['pages'], $this->pages_options['sub_pages']);

        foreach ($pages_options as $page) {
            if (!empty($page['acf_group_key'])) {
                $fields = acf_get_fields($page['acf_group_key']);

                $group = [
                    'key' => 'group_' . $page['menu_slug'],
                    'title' => $page['menu_title'],
                    'location' => [
                        [
                            [
                                'param' => 'options_page',
                                'operator' => '==',
                                'value' => $page['menu_slug'],
                            ],
                        ],
                    ],
                ];

                // Créer un groupe de champs s'il n'existe pas
                foreach ($fields as $key => $field) {
                    if ($field['type'] == 'group') {
                        $fields[$key]['key'] = 'field_' . $page['menu_slug'];
                        $fields[$key]['name'] = $page['menu_slug'];

                        $group['fields'] = $fields;
                    } else {
                        $group['fields'][$key]['key'] = 'field_' . $page['menu_slug'];
                        $group['fields'][$key]['name'] = $page['menu_slug'];
                        $group['fields'][$key]['type'] = 'group';
                        $group['fields'][$key]['sub_fields'] = $fields;
                    }
                }

                acf_add_local_field_group($group);
            }
        }
    }

    /**
     *
     * addMenuFieldGroups
     * @author Thomas Navarro
     * @return void | Ajoute une metabox `Structure` permettant de paramètrer les entrées du menu principal
     *
     */
    // TODO: Décommenter pour l'administration en back-office
    // public function addMenuFieldGroups()
    // {
    //     $page = $this->pages_options['main-menu'];

    //     $group = [
    //         'key' => 'group_generate_' . $page['menu_slug'],
    //         'title' => 'Structure du menu',
    //         'position' => 'side',
    //         'fields' => [[
    //             'key' => 'field_generate_' . $page['menu_slug'],
    //             'name' => 'generate_' . $page['menu_slug'],
    //             'type' => 'repeater',
    //             'layout' => 'block',
    //             'button_label' => 'Ajouter une page au menu',
    //             'sub_fields' => [[
    //                 'key' => 'field_menu_post',
    //                 'name' => 'post',
    //                 'type' => 'post_object',
    //                 'return_format' => 'object',
    //                 'ui' => 1,
    //             ]]
    //         ]],
    //         'location' => [
    //             [
    //                 [
    //                     'param' => 'options_page',
    //                     'operator' => '==',
    //                     'value' => $page['menu_slug'],
    //                 ],
    //             ],
    //         ],
    //     ];

    //     acf_add_local_field_group($group);
    // }

    /**
     *
     * addSubmenuFieldGroups
     * @author Thomas Navarro
     * @depends setMenuPostIds
     * @return void | Ajoute les champs au sous-menu en se basant sur un acf-json (group_submenus)
     *
     */
    public function addSubmenuFieldGroups()
    {
        $page = $this->pages_options['main-menu'];

        $group = [
            'key' => 'group_' . $page['menu_slug'],
            'title' => 'Administration des sous-menus',
            'location' => [
                [
                    [
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => $page['menu_slug'],
                    ],
                ],
            ],
        ];

        $this->setMenuPostIds($page);

        if (!empty($this->menu_post_ids)) {
            // Utilisez l'index car `group_submenus.json` contient des champs avec des accordéon
            $index = 1;

            if (!empty($page['acf_group_key'])) {
                $sub_fields = acf_get_fields($page['acf_group_key']);
            }

            foreach ($this->menu_post_ids as $post_id) {
                $key = 'field_submenu_' . $post_id;
                $post = get_post($post_id);
                if (!empty($post)) {
                    $label = $post->post_title;
                    $name = 'submenu_' . $post_id;

                    $group['fields'][] = [
                        'key' => 'tab_' . $key,
                        'label' => $label,
                        'type' => 'tab',
                        'placement' => 'left',
                        'endpoint' => 0
                    ];
                    $group['fields'][$index] = [
                        'key' => $key,
                        'name' => $name,
                        'type' => 'group',
                        'sub_fields' => (empty($sub_fields[$index]['sub_fields'])) ? '' : $sub_fields[$index]['sub_fields']
                    ];
                    $index += 2;
                }
            }
        } else {
            $group['fields'][] = [
                'key' => 'field_submenu_warn',
                'type' => 'message',
                'message' => '<p style="color: rgba(0,0,0,.5); font-size:18px"><em>Aucune page n\'est renseignée…</em></p><p>Pour administrer vos sous-menus vous devez ajouter des pages dans l\'onglet <b>Structure</b> puis enregistrer</p>',
            ];
        }

        acf_add_local_field_group($group);
    }
}
