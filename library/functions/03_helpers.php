<?php

use WoodyProcess\Tools\WoodyTheme_WoodyGetters;
use WoodyProcess\Tools\WoodyTheme_WoodyCompilers;

// ***************************************************************************************//
// Get previews - Retournent des tableaux de données compatibles avec les templates Woody //
// ***************************************************************************************//

function getCustomPreview($item, $wrapper = null)
{
    $getter = new WoodyProcess\Tools\WoodyTheme_WoodyGetters;
    return $getter->getCustomPreview($item, $wrapper);
}

function getPagePreview($wrapper, $item, $clickable = true)
{
    $getter = new WoodyProcess\Tools\WoodyTheme_WoodyGetters;
    return $getter->getPagePreview($wrapper, $item, $clickable);
}

function getTouristicSheetPreview($wrapper = null, $item)
{
    $getter = new WoodyProcess\Tools\WoodyTheme_WoodyGetters;
    return $getter->getTouristicSheetPreview($wrapper, $item);
}
