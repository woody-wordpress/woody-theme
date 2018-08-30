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
        add_action('admin_init', [$this, 'addRolesOnPluginActivation']);
    }

    public function addRolesOnPluginActivation()
    {
        add_role('woody_admin', 'Woody Admin', array());
        add_role('woody_editor', 'Woody Editeur', array());
    }
}
