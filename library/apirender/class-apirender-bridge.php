<?php

class HawwwaiTheme_ApirenderBridge
{
    // Construct
    public function __construct()
    {
    }

    /**
     *
     * Nom : getApiRenderTemplate
     * Auteur : Benoit Bouchaud
     * Return : Retourne un DOM html compilé par l'apirender en fonction d'une langue et d'un ID de configuration
     * @param    conf_id - Un nombre correspondant à une configuration de playlist
     * @return   generatedHtml - Un bout de DOM Html
     *
     */
    public function getApiRenderTemplate($conf_id)
    {
        $lang = get_locale();
        $generatedHtml = false;
        if (is_plugin_active('hawwwai')) {
            $hawwwaiPlaylistModule = $plugin_hawwwai_kernel->getModule('playlist');
            if (!empty($hawwwaiPlaylistModule)) {
                $generatedHtml = $hawwwaiPlaylistModule->renderPlaylist($confId, $lang);
            }
        }

        return $generatedHtml;
    }
}

// Execute Class
$apirenderBridge = new HawwwaiTheme_ApirenderBridge();
