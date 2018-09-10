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
        add_action('woody_update', [$this, 'addRolesOnPluginActivation'], 2);
    }

    public function addRolesOnPluginActivation()
    {
        // Administrator
        $role = get_role('administrator');

        $role->add_cap('Configurer les thématiques');
        $role->add_cap('Editer les thématiques');
        $role->add_cap('Supprimer les thématiques');
        $role->add_cap('Assigner les thématiques');

        $role->add_cap('Configurer les lieux');
        $role->add_cap('Editer les lieux');
        $role->add_cap('Supprimer les lieux');
        $role->add_cap('Assigner les lieux');

        $role->add_cap('Assigner les saisons');

        $role->add_cap('Configurer les catégories de médias');
        $role->add_cap('Editer les catégories de médias');
        $role->add_cap('Supprimer les catégories de médias');
        $role->add_cap('Assigner les catégories de médias');

        $role->add_cap('Configurer les hashtags');
        $role->add_cap('Editer les hashtags');
        $role->add_cap('Supprimer les hashtags');
        $role->add_cap('Assigner les hashtags');

        // Editor
        $role = get_role('editor');

        $role->add_cap('Configurer les thématiques');
        $role->add_cap('Editer les thématiques');
        $role->add_cap('Supprimer les thématiques');
        $role->add_cap('Assigner les thématiques');

        $role->add_cap('Configurer les lieux');
        $role->add_cap('Editer les lieux');
        $role->add_cap('Supprimer les lieux');
        $role->add_cap('Assigner les lieux');

        $role->add_cap('Assigner les saisons');

        $role->add_cap('Configurer les catégories de médias');
        $role->add_cap('Editer les catégories de médias');
        $role->add_cap('Supprimer les catégories de médias');
        $role->add_cap('Assigner les catégories de médias');

        $role->add_cap('Configurer les hashtags');
        $role->add_cap('Editer les hashtags');
        $role->add_cap('Supprimer les hashtags');
        $role->add_cap('Assigner les hashtags');

        // Contributor
        $role = get_role('contributor');

        $role->remove_cap('Configurer les thématiques');
        $role->remove_cap('Editer les thématiques');
        $role->remove_cap('Supprimer les thématiques');
        $role->add_cap('Assigner les thématiques');

        $role->remove_cap('Configurer les lieux');
        $role->remove_cap('Editer les lieux');
        $role->remove_cap('Supprimer les lieux');
        $role->add_cap('Assigner les lieux');

        $role->add_cap('Assigner les saisons');

        $role->remove_cap('Configurer les catégories de médias');
        $role->remove_cap('Editer les catégories de médias');
        $role->remove_cap('Supprimer les catégories de médias');
        $role->add_cap('Assigner les catégories de médias');

        $role->remove_cap('Configurer les hashtags');
        $role->remove_cap('Editer les hashtags');
        $role->remove_cap('Supprimer les hashtags');
        $role->add_cap('Assigner les hashtags');
    }
}
