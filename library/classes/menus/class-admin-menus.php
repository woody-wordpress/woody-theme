<?php

/**
 * Admin Menus
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.28.35
 */

class WoodyTheme_Admin_Menus
{
    public function __construct()
    {
        $this->registerHooks();
        $this->current_lang = pll_current_language();
        $this->menu_post_ids = $this->menuPostIds();
        $this->pages_options = $this->setPagesOptions();
    }

    /**
     *
     * Nom : menuPostIds
     * Auteur : Thomas Navarro
     * Return : Retourne un tableau de post ID des entrées de menu
     * @return   ids - array
     *
     */
    public function menuPostIds()
    {
        $default = [];
        $ids = apply_filters('woody/menus/menu_post_ids', $default);

        return $ids;
    }

    /**
     *
     * Nom : setPagesOptions
     * Auteur : Thomas Navarro
     * Return : Retourne les paramètres de chaque page d'options crée
     * @return   options - array
     *
     */
    public function setPagesOptions()
    {
        $default['main_menu_page'] = [
            'main-menu' => [
                'page_title'    => 'Administation du menu principal',
                'menu_title'    => 'Menu principal',
                'menu_slug'     => 'main-menu-' . $this->current_lang,
                'parent_slug'   => 'custom-menus',
                'capability'    => 'edit_pages',
                'acf_group_key' => 'group_submenus'
            ]
        ];

        $default['options_pages'] = [];
        $default['sub_pages'] = [
            'topheader-menu' => [
                'page_title'    => 'Administration du menu haut de page',
                'menu_title'    => 'Menu Haut de page',
                'menu_slug'     => 'topheader-menu-' . $this->current_lang,
                'parent_slug'   => 'custom-menus',
                'capability'    => 'edit_pages',
                'acf_group_key' => 'group_link_icon',
            ],
            'legal-menu' => [
                'page_title'    => 'Administration du menu infos légales',
                'menu_title'    => 'Menu infos légales',
                'menu_slug'     => 'legal-menu-' . $this->current_lang,
                'parent_slug'   => 'custom-menus',
                'capability'    => 'edit_pages',
                'acf_group_key' => 'group_link',
            ],
            'pro-menu' => [
                'page_title'    => 'Administration du menu pro',
                'menu_title'    => 'Menu pro',
                'menu_slug'     => 'pro-menu-' . $this->current_lang,
                'parent_slug'   => 'custom-menus',
                'capability'    => 'edit_pages',
                'acf_group_key' => 'group_link',
            ],
            'social-menu' => [
                'page_title'    => 'Administration des liens des réseaux sociaux',
                'menu_title'    => 'Réseaux sociaux',
                'menu_slug'     => 'social-menu-' . $this->current_lang,
                'parent_slug'   => 'custom-menus',
                'capability'    => 'edit_pages',
                'acf_group_key' => 'group_link_icon',
            ]
        ];

        $options = apply_filters('woody/menus/set_pages_options', $default);

        return $options;
    }

    /**
     *
     * Nom : addMenuMainPages
     * Auteur : Thomas Navarro
     * Return : Ajoute la page d'option du menu principal
     * @return   void
     *
     */
    public function addMenuMainPages()
    {
        if (function_exists('acf_add_options_sub_page')) {
            acf_add_options_sub_page($this->options_pages['main_menu_page']['main-menu']);
        }
    }

    /**
     *
     * Nom : addMenusOptionsPages
     * Auteur : Thomas Navarro
     * Return : Ajoute les pages d'options au menu WP
     * @return   void
     *
     */
    public function addMenusOptionsPages()
    {
        if (function_exists('acf_add_options_page') and !empty($this->options_pages['options_pages'])) {
            foreach ($this->options_pages['options_pages'] as $key => $options_page) {
                acf_add_options_page($options_page);
            }
        }

        if (function_exists('acf_add_options_sub_page') and !empty($this->options_pages['sub_pages'])) {
            foreach ($this->options_pages['sub_pages'] as $key => $sub_page) {
                acf_add_options_sub_page($sub_page);
            }
        }
    }

    public function addOptionsPagesFields()
    {
        foreach ($this->options_pages as $page) {
            if (strpos($page['menu_slug'], 'main-menu') === false) {
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

                    //? Create a group field if it does not exist
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
    }

    public function addSubmenuFieldGroups()
    {
        $page = $this->options_pages['main-menu'];

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

        if (!empty($this->menu_post_ids)) {
            foreach ($this->menu_post_ids as $post_id) {
                $key = 'field_submenu_' . $post_id;
                $label = get_post($post_id)->post_title;
                $name = 'submenu_' . $post_id;

                $group['fields'][] = [
                    'key' => 'tab_' . $key,
                    'label' => $label,
                    'name' => '',
                    'type' => 'tab',
                    'placement' => 'left',
                    'endpoint' => 0
                ];
                $group['fields'][] = [
                    'key' => $key,
                    'label' => '',
                    'name' => $name,
                    'type' => 'group',
                ];
            }
            if (!empty($page['acf_group_key'])) {
                $submenu_fields = acf_get_fields($page['acf_group_key']);

                foreach ($submenu_fields as $submenu_key => $submenu) {
                    if (!empty($submenu['sub_fields'])) {
                        $group['fields'][$submenu_key]['sub_fields'] = $submenu['sub_fields'];
                    }
                }
            }
        }

        acf_add_local_field_group($group);
    }
}
