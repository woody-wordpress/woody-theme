<?php

/**
 * Woody
 * @author Léo POIROUX
 * @copyright Raccourci Agency 2024
 */

function woody_addon_asset_path($addon, $filename){
        $manifest = [];
        $manifest_path = WP_DIST_DIR . '/addons/' . $addon .'/rev-manifest.json';
        if (file_exists($manifest_path)) {
            $manifest = json_decode(file_get_contents($manifest_path), true, 512, JSON_THROW_ON_ERROR);

            if (!empty($manifest[$filename])) {
                $filename = $manifest[$filename];
            }
        }

        return WP_DIST_URL . '/addons/' . $addon .'/' . $filename;
    }

function woody_addon_asset_content($addon, $filename)
    {
        $manifest = [];
        $manifest_path = WP_DIST_DIR . '/addons/' . $addon .'/rev-manifest.json';
        if (file_exists($manifest_path)) {
            $manifest = json_decode(file_get_contents($manifest_path), true, 512, JSON_THROW_ON_ERROR);

            if (!empty($manifest[$filename])) {
                $filename = $manifest[$filename];
            }
        }

        return file_get_contents(WP_DIST_DIR . '/addons/' . $addon .'/' . $filename);
    }
