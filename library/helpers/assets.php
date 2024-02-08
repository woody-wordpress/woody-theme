<?php

/**
 * Woody
 * @author Léo POIROUX
 * @copyright Raccourci Agency 2022
 */

function woody_addon_asset_path($addon, $filename)
{
    return apply_filters('woody_addon_asset_path', $addon, $filename);
}

function woody_addon_asset_content($addon, $filename)
{
    return apply_filters('woody_addon_asset_content', $addon, $filename);
}
