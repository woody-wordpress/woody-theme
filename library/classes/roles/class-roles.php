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
        add_action('woody_theme_update', [$this, 'addRolesOnPluginActivation'], 2);
    }

    public function addRolesOnPluginActivation()
    {
        $capabilities = [
            'activate_plugins' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
            ],
            'delete_others_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
            ],
            'delete_others_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
            ],
            'delete_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
            ],
            'delete_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
            ],
            'delete_private_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
            ],
            'delete_private_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
            ],
            'delete_published_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
            ],
            'delete_published_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'edit_dashboard' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'edit_others_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'edit_others_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'edit_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'edit_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'edit_private_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'edit_private_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'edit_published_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'edit_published_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'edit_theme_options' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
            ],
            'export' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
            ],
            'import' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
            ],
            'list_users' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
            ],
            'manage_categories' => [
                'administrator' => false,
                'editor' => false,
                'contributor' => false,
            ],
            'manage_links' => [
                'administrator' => false,
                'editor' => false,
                'contributor' => false,
            ],
            'manage_options' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
            ],
            'moderate_comments' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
            ],
            'promote_users' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
            ],
            'publish_pages' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'publish_posts' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
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
            ],
            'read' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'remove_users' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
            ],
            'switch_themes' => [
                'administrator' => true,
                'editor' => false,
                'contributor' => false,
            ],
            'upload_files' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'customize' => [
                'administrator' => false,
                'editor' => false,
                'contributor' => false,
            ],
            'delete_site' => [
                'administrator' => false,
                'editor' => false,
                'contributor' => false,
            ],
            'Configurer les thématiques' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
            ],
            'Editer les thématiques' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
            ],
            'Supprimer les thématiques' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
            ],
            'Assigner les thématiques' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'Configurer les lieux' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
            ],
            'Editer les lieux' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
            ],
            'Supprimer les lieux' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
            ],
            'Assigner les lieux' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'Configurer les saisons' => [
                'administrator' => false,
                'editor' => false,
                'contributor' => false,
            ],
            'Editer les saisons' => [
                'administrator' => false,
                'editor' => false,
                'contributor' => false,
            ],
            'Supprimer les saisons' => [
                'administrator' => false,
                'editor' => false,
                'contributor' => false,
            ],
            'Assigner les saisons' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
            ],
            'Configurer les catégories de médias' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
            ],
            'Editer les catégories de médias' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
            ],
            'Supprimer les catégories de médias' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
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
            ],
            'Editer les hashtags' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
            ],
            'Supprimer les hashtags' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => false,
            ],
            'Assigner les hashtags' => [
                'administrator' => true,
                'editor' => true,
                'contributor' => true,
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
