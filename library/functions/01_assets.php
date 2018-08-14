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
    $svg_icons = get_template_directory() . '/src/icons/svg';

    $icon_finder = new Finder();
    $icon_finder->files()->name('*.svg')->in($svg_icons);
    foreach ($icon_finder as $key => $icon) {
        $icon_name = str_replace('.svg', '', $icon->getRelativePathname());
        $icon_class = 'wicon-' . $icon_name;
        $icon_human_name = str_replace('-', ' ', $icon_name);
        $icon_human_name = substr($icon_human_name, 4);
        $icon_human_name = ucfirst($icon_human_name);
        $the_icons[$icon_class] = $icon_human_name;
    }

    return $the_icons;
}
