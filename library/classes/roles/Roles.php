<?php

/**
 * Roles
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

namespace Woody\WoodyTheme\library\classes\roles;

class Roles
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('woody_theme_update', [$this, 'addRoles'], 1);
        add_action('woody_theme_update', [$this, 'addCapabilities'], 10);
        add_filter('auth_cookie_expiration', [$this, 'authCookieExpirationFilter'], 10, 3);
        add_action('members_register_cap_groups', [$this, 'registerMembersGroups']);
        add_action('members_register_caps', [$this, 'membersRegisterCaps']);
        add_filter('redirection_role', fn ($role) => 'manage_redirection');
        add_filter('the_password_form', [$this, 'custom_password_form']);
        add_filter('bsr_capability', [$this, 'betterSearchReplaceCapability']);
    }

    /**
     * Registers the user capability group for the Members plugin.
     *
     * @link https://wordpress.org/plugins/members/
     */
    public function registerMembersGroups()
    {
        members_register_cap_group('woody', array(
            'label'    => __('Woody', 'woody'),
            'icon'     => 'dashicons-admin-settings',
            'priority' => 30,
        ));
    }

    public function membersRegisterCaps()
    {
        members_register_cap('manage_redirection', array(
            'label' => _x('Manage Redirections', '', 'woody'),
            'group' => 'woody',
        ));

        members_register_cap('woody_admin_tools', array(
            'label' => _x('Administration Woody', '', 'woody'),
            'group' => 'woody',
        ));
    }

    public function addRoles()
    {
        add_role('administrator', 'Administrateur');
        add_role('editor', 'Éditeur');
        add_role('redactor', 'Rédacteur');
        add_role('contributor', 'Contributeur');
        add_role('translator', 'Traducteur');
        add_role('mediatheque', 'Médiathèque');
    }

    public function authCookieExpirationFilter($expiration, $user_id, $remember)
    {
        if ($remember && !user_can($user_id, 'edit_posts')) {
            return YEAR_IN_SECONDS;
        }

        return $expiration;
    }


    /**
     *
     * Nom : custom_password_form
     * Auteur : Antoine Josset
     * Return : Change le formulaire de protection de pages wordpress
     *
     **/

    public function custom_password_form()
    {
        global $post;
        $vars = [
            'protected_form' => [
                'titre' => __('Connectez-vous !'),
                'label' =>  'pwbox-' . (empty($post->ID) ? random_int(0, mt_getrandmax()) : $post->ID),
                'intro' => __('Cette page est protégée par un mot de passe. </br>Pour accéder à cette page, veuillez saisir un mot de passe :'),
                'placeholder' => __('Votre mot de passe'),
                'action' => esc_url(site_url('wp-login.php?action=postpass', 'login_post')),
                'submit_value' => esc_attr__("Entrer"),
            ]
        ];

        $pswd = wp_hash_password($post->post_password);
        if (!empty($_COOKIE['wp-postpass_' . COOKIEHASH])) {
            $cookie_pswd = wp_unslash($_COOKIE['wp-postpass_' . COOKIEHASH]);
            if ($cookie_pswd != $pswd) {
                $vars['protected_form']['error_msg'] = __('Accés refusé. Mot de passe incorrect');
            }
        }

        $vars = apply_filters("woody_protected_form_data", $vars);

        return \Timber::compile('parts\protected_post.twig', $vars);
    }


    public function addCapabilities()
    {
        $capabilities = [
            'switch_themes' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'edit_themes' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'activate_plugins' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'edit_plugins' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'edit_users' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'edit_files' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'manage_options' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'moderate_comments' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'manage_categories' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'manage_links' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'upload_files' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'import' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'unfiltered_html' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'edit_posts' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => true,
            ],
            'edit_others_posts' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => true,
            ],
            'edit_published_posts' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => true,
            ],
            'publish_posts' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => true,
            ],
            'edit_pages' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'read' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => true,
                'translator' => true,
                'mediatheque' => true,
            ],
            'edit_others_pages' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'edit_published_pages' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'publish_pages' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => false,
                'translator' => true,
            ],
            'delete_pages' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'delete_others_pages' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'delete_published_pages' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'delete_posts' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'delete_others_posts' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'delete_published_posts' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'delete_private_posts' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'edit_private_posts' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'read_private_posts' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => false,
                'translator' => true,
            ],
            'delete_private_pages' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'edit_private_pages' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'read_private_pages' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => false,
                'translator' => true,
            ],
            'delete_users' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'create_users' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'unfiltered_upload' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => true,
                'translator' => true,
                'mediatheque' => true,
            ],
            'edit_dashboard' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => false,
                'translator' => true,
            ],
            'update_plugins' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'delete_plugins' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'install_plugins' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'update_themes' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'install_themes' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'update_core' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'list_users' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'remove_users' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'add_users' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'promote_users' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'edit_theme_options' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'delete_themes' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'export' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'edit_comment' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'approve_comment' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'unapprove_comment' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'reply_comment' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'quick_edit_comment' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'spam_comment' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'unspam_comment' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'trash_comment' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'untrash_comment' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'delete_comment' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'edit_permalink' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => true,
                'translator' => true,
            ],

            // Woody Caps
            'woody_admin_tools' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'woody_instagram' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'woody_pages' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'woody_process_drupal_import' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'woody_process_csv_edit' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'woody_hawwwai' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'woody_process_convert_to_geojson' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'woody_topic' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'woody_restrictions' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'woody_seo' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'woody_settings' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'woody_menus' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],

            // Taxonomies
            'Configurer les thématiques' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Editer les thématiques' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Supprimer les thématiques' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Assigner les thématiques' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'Configurer les lieux' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Editer les lieux' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Supprimer les lieux' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Assigner les lieux' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'Configurer les circonstances' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Editer les circonstances' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Supprimer les circonstances' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Assigner les circonstances' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => true,
                'translator' => true,
                'mediatheque' => false,
            ],
            'Configurer les cibles' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
                'mediatheque' => false,
            ],
            'Editer les cibles' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
                'mediatheque' => false,
            ],
            'Supprimer les cibles' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
                'mediatheque' => false,
            ],
            'Assigner les cibles' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'Configurer les catégories de médias' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Editer les catégories de médias' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Supprimer les catégories de médias' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Assigner les catégories de médias' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => true,
                'translator' => false,
            ],
            'Configurer les hashtags' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Editer les hashtags' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Supprimer les hashtags' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Assigner les hashtags' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Configurer les types de publications' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Editer les types de publications' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Supprimer les types de publications' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            "Configurer les catégories d'expression" => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            "Editer les catégories d'expression" => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            "Supprimer les catégories d'expression" => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            "Assigner les catégories d'expression" => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Configurer les catégories de profil' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Editer les catégories de profil' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Supprimer les catégories de profil' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Assigner les catégories de profil' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],

            // Duplicate
            'copy_posts' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => true,
                'contributor' => true,
                'translator' => false,
            ],

            // Query Monitor
            'view_query_monitor' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],

            // Members
            'restrict_content' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'list_roles' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'create_roles' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'delete_roles' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'edit_roles' => [
                'administrator' => true,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],

            // Custom
            'delete_site' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'customize' => [
                'administrator' => false,
                'editor' => false,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],

            // Redirection
            'manage_redirection' => [
                'administrator' => true,
                'editor' => true,
                'redactor' => false,
                'contributor' => false,
                'translator' => false,
            ],
        ];

        $capabilities = apply_filters('woody_capabilities', $capabilities);

        foreach ($capabilities as $capability => $roles) {
            foreach ($roles as $role => $boolean) {
                $current_role = get_role($role);
                if(!empty($current_role)) {
                    if ($boolean) {
                        $current_role->add_cap($capability);
                    } else {
                        $current_role->remove_cap($capability);
                    }
                }
            }
        }
    }

    public function betterSearchReplaceCapability()
    {
        return 'activate_plugins';
    }
}
