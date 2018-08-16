<?php

use Symfony\Component\Finder\Finder;

function humanDays($number)
{
    if ($number % 7 === 0) {
        $week_number = $number / 7;
        if ($week_number > 1) {
            $human_string = $week_number . ' semaines';
        } else {
            $human_string = $week_number . ' semaine';
        }
    } else {
        if ($number > 1) {
            $human_string = $number . ' jours';
        } else {
            $human_string = $number . ' jour';
        }
    }

    return $human_string;
}

function getWoodyIcons()
{
    $the_icons = [];

    $core_icons = woodyIconsFolder(get_template_directory() . '/src/icons/icons_set_01');
    $site_icons = woodyIconsFolder(get_stylesheet_directory() . '/src/icons');

    $the_icons = array_merge($core_icons, $site_icons);

    return $the_icons;
}

function woodyIconsFolder($folder)
{
    $icons_finder = new Finder();
    $icons_finder->files()->name('*.svg')->in($folder);
    foreach ($icons_finder as $key => $icon) {
        $icon_name = str_replace('.svg', '', $icon->getRelativePathname());
        $icon_class = 'wicon-' . $icon_name;
        $icon_human_name = str_replace('-', ' ', $icon_name);
        $icon_human_name = substr($icon_human_name, 4);
        $icon_human_name = ucfirst($icon_human_name);
        $the_icons[$icon_class] = $icon_human_name;
    }

    return $the_icons;
}
