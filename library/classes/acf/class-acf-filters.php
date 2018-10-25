<?php
/**
 * ACF Short Link
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_ACF_Filters
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('timber/twig', [$this, 'addToTwig']);
    }

    public function addToTwig($twig)
    {
        //$twig->addExtension(new Twig_Extension_StringLoader());
        $twig->addFilter(new Twig_SimpleFilter('phone_click', [$this, 'phoneClick_Filter']));
        return $twig;
    }

    public function phoneClick_Filter($text)
    {
        return substr($text, 0, -2) . '<span class="hidden-number">▒▒</span>';
    }
}
