<?php

namespace Woody\WoodyTheme\library\classes\apirender;

class Bridge
{
    // private $pluginHawwwai;

    // // Construct
    // public function setPlugin($plugin_hawwwai_kernel)
    // {
    //     $this->pluginHawwwai = $plugin_hawwwai_kernel;
    // }

    // /**
    //  *
    //  * Nom : getApiRenderTemplate
    //  * Auteur : Benoit Bouchaud
    //  * Return : Retourne un DOM html compilé par l'apirender en fonction d'une langue et d'un ID de configuration
    //  * @param    conf_id - Un nombre correspondant à une configuration de playlist
    //  * @return   generatedHtml - Un bout de DOM Html
    //  *
    //  */
    // public function getApiRenderTemplate($confId)
    // {
    //     $lang = $this->getCurrentLang();
    //     $generatedHtml = false;

    //     if (!empty($this->pluginHawwwai)) {
    //         $hawwwaiPlaylistModule = $this->pluginHawwwai->getModule('wp_hawwwai_playlist');
    //         if (!empty($hawwwaiPlaylistModule)) {
    //             $generatedHtml = $hawwwaiPlaylistModule->getPlaylistsManager()->renderPlaylist($confId, $lang);
    //         }
    //     }

    //     return $generatedHtml;
    // }

    // private function getCurrentLang()
    // {
    //     $active_lang = PLL_DEFAULT_LANG;

    //     // Polylang
    //     if (function_exists('pll_current_language')) {
    //         $active_lang = pll_current_language();
    //     }

    //     return $active_lang;
    // }
}

// Execute Class
// $apirenderBridge = new WoodyTheme_ApirenderBridge();
// if (is_plugin_active('hawwwai-plugin/hawwwai.php')) {
//     $apirenderBridge->setPlugin($plugin_hawwwai_kernel);
// }
