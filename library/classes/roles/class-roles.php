<?php
/**
 * Roles
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Roles
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('woody_theme_update', [$this, 'addRoles']);
        add_action('woody_theme_update', [$this, 'addCapabilities']);
    }

    public function addRoles()
    {
        add_role('administrator', 'Administrateur');
        add_role('editor', 'Éditeur');
        add_role('contributor', 'Contributeur');
        add_role('translator', 'Traducteur');
    }

    public function addCapabilities()
    {
        $capabilities = [
            'activate_plugins' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'delete_others_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'delete_others_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => false,
            ],
            'delete_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'delete_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => false,
            ],
            'delete_private_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'delete_private_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => false,
            ],
            'delete_published_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'delete_published_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => false,
            ],
            'edit_dashboard' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'edit_others_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'edit_others_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'edit_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'edit_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'edit_private_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'edit_private_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'edit_published_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'edit_published_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'edit_theme_options' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'export' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'import' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'list_users' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'manage_categories' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'manage_links' => [
                'administrator' => false,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'manage_options' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'moderate_comments' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'promote_users' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'publish_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'publish_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'read_private_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'read_private_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'read' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'remove_users' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'switch_themes' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'upload_files' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'customize' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'delete_site' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'wpseo_bulk_edit' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'wpseo_edit_advanced_metadata' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'wpseo_manage_options' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'copy_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'view_query_monitor' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Configurer les thématiques' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'Editer les thématiques' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'Supprimer les thématiques' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'Assigner les thématiques' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'Configurer les lieux' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'Editer les lieux' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'Supprimer les lieux' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'Assigner les lieux' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'Configurer les saisons' => [
                'administrator' => false,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Editer les saisons' => [
                'administrator' => false,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Supprimer les saisons' => [
                'administrator' => false,
                'editor' => false,
                'contributor' => false,
                'translator' => false,
            ],
            'Assigner les saisons' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => true,
            ],
            'Configurer les catégories de médias' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'Editer les catégories de médias' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'Supprimer les catégories de médias' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'Assigner les catégories de médias' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'Configurer les hashtags' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'Editer les hashtags' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'Supprimer les hashtags' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
                'translator' => false,
            ],
            'Assigner les hashtags' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
                'translator' => false,
            ],
        ];

        foreach ($capabilities as $capability => $roles) {
            foreach ($roles as $role => $boolean) {
                $current_role = get_role($role);
                if ($boolean) {
                    $current_role->add_cap($capability);
                } else {
                    $current_role->remove_cap($capability);
                }
            }
        }
    }
}
