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
        $twig->addFilter(new Twig_SimpleFilter('phone_click', [$this, 'phoneClick']));
        $twig->addFilter(new Twig_SimpleFilter('humanize_filesize', [$this, 'humanizeFilesize']));
        $twig->addFilter(new Twig_SimpleFilter('dump', [$this, 'dump']));
        return $twig;
    }

    public function phoneClick($text)
    {
        return substr($text, 0, -2) . '<span class="hidden-number">▒▒</span>';
    }

    public function humanizeFilesize($bytes, $decimals = 0)
    {
        $factor = floor((strlen($bytes) - 1) / 3);
        if ($factor > 0) {
            $sz = 'KMGT';
        }
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor - 1] . 'B';
    }

    public function dump($text)
    {
        return rcd($text);
    }
}
