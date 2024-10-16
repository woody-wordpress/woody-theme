<?php
/**
 * acfPrivateGroups
 *
 * @package SubWoodyTheme
 * @since SubWoodyTheme 1.0.0
 */

namespace Woody\WoodyTheme\library\classes\acf;

use Symfony\Component\Finder\Finder;

class PrivateGroups
{
    public function __construct()
    {
        add_filter('acf/settings/load_json', array($this,'acfJsonLoad'));
        add_filter('woody_acf_save_paths', array($this,'acfJsonSave'));
    }

    /**
     * Register ACF Json load directory
     *
     * @since 1.0.0
     */
    public function acfJsonLoad($paths)
    {
        if (!in_array(get_stylesheet_directory() . '/acf-json', $paths)) {
            $paths[] = get_stylesheet_directory() . '/acf-json';
        }

        return $paths;
    }

    /**
     * Register ACF Json Save directory
     *
     * @since 1.0.0
     */
    public function acfJsonSave($groups)
    {
        $acf_json_path = get_stylesheet_directory() . '/acf-json';
        if (file_exists($acf_json_path)) {
            $finder = new Finder();
            $finder->files()->in($acf_json_path)->name('*.json');
            foreach ($finder as $file) {
                $filename = str_replace('.json', '', $file->getRelativePathname());
                $groups[$filename] = $acf_json_path;
            }
        }

        return $groups;
    }
}
