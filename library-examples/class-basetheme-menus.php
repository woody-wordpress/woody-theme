<?php

class Basetheme_menu {

    public function execute() {
        $this->register_hooks();
    }

    protected function register_hooks() {
        add_action ('wp_update_nav_menu', 'emw_create_hierarchy_from_menu', 10, 2);
    }

}

function emw_create_hierarchy_from_menu($menu_id, $menu_data = NULL) {
    if ($menu_id != 13)  // You should update this integer to the id of the menu you want to keep in sync
        return;
    if ($menu_data !== NULL) // If $menu_date !== NULL, this means the action was fired in nav-menu.php, BEFORE the menu items have been updated, and we should ignore it.
        return;
    $menu_details = get_term_by('id', $menu_id, 'nav_menu');
    if ($items = wp_get_nav_menu_items ($menu_details->term_id)) {
        // Create an index of menu item IDs, so we can find parents easily
        foreach ($items as $key => $item)
            $item_index[$item->ID] = $key;
        // Loop through each menu item
        foreach ($items as $item)
            // Only proceed if we're dealing with a page
            if ($item->object == 'page') {
                // Get the details of the page
                $post = get_post($item->object_id, ARRAY_A);
                if ($item->menu_item_parent != 0)
                    // This is not top-level menu items, so we need to find the parent page
                    if ($items[$item_index[$item->menu_item_parent]]->object != 'page') {
                        // The parent isn't a page. Queue an error message.
                        global $messages;
                        $messages[] = '<div id="message" class="error"><p>' . sprintf( __("The parent of <strong>%1s</strong> is <strong>%2s</strong>, which is not a page, which means that this part of the menu cannot sync with your page hierarchy.", ETTD), $item->title, $items[$item_index[$item->menu_item_parent]]->title) . '</p></div>';
                        $new_post['post_parent'] = new WP_Error;
                    } else
                        // Get the new parent page from the index
                        $new_post['post_parent'] = $items[$item_index[$item->menu_item_parent]]->object_id;
                else
                    $new_post['post_parent'] = 0; // Top-level menu item, so the new parent page is 0
                if (!is_wp_error ($new_post['post_parent'])) {
                    $new_post['ID'] = $post['ID'];
                    $new_post['menu_order'] = $item->menu_order;
                    if ($new_post['menu_order'] !== $post['menu_order'] || $new_post['post_parent'] !== $post['post_parent'])
                        // Only update the page if something has changed
                        wp_update_post ($new_post);
                }
            }
    }
}
