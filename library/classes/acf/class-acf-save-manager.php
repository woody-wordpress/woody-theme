<?php
/**
 * https://support.advancedcustomfields.com/forums/topic/multiple-save-locations-for-json/
 */

class WoodyTheme_ACF_Save_Manager
{
    // $groups is an array of field group key => path pairs
    // these will be set later
    private array $groups = [];

    // this variable will store the current group key
    // that is being saved so that we can retrieve it later
    private $current_group_being_saved;

    public function __construct()
    {
        // this init action will set up the save paths
        add_action('admin_init', array($this, 'admin_init'));

        // this action is called by ACF before saving a field group
        // the priority is set to 1 so that it runs before the internal ACF action
        add_action('acf/update_field_group', array($this, 'update_field_group'), 1, 1);
    }

    public function admin_init()
    {
        $this->groups = [];
        $this->groups = apply_filters('woody_acf_save_paths', $this->groups);
    }

    public function update_field_group($group)
    {
        // the purpose of this function is to see if we want to
        // change the location where this group is saved
        // and if we to to add a filter to alter the save path
        // first check to see if this is one of our groups
        $this->current_group_being_saved = isset($this->groups[$group['key']]) ? $group['key'] : 'default';

        // store the group key and add action
        add_action('acf/settings/save_json', array($this, 'override_json_location'), 9999);

        // don't forget to return the groups
        return $group;
    }

    public function override_json_location($path)
    {
        // alter the path based on group being saved and
        // our save locations
        $path = $this->groups[$this->current_group_being_saved];

        return $path;
    }
}
