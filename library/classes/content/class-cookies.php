<?php

/**
 * Cookies
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Cookies
{
    public function __construct()
    {
        $this->registerHooks();
    }

    private function registerHooks()
    {
        add_action('wp_ajax_get_cookie_banner', [$this, 'getCookieOptions']);
        add_action('wp_ajax_nopriv_get_cookie_banner', [$this, 'getCookieOptions']);
    }

    public function getCookieOptions()
    {
        $return = [];
        $template = "parts/cookies.twig";

        $lang = !empty(pll_current_language()) ? pll_current_language() : "fr";
        $infos = $this->getInfosByLang($lang);

        $data = [
            "message"       => !empty($infos['message'])     ? $infos['message']     : "",
            "dismiss"       => !empty($infos['dismiss'])     ? $infos['dismiss']     : "",
            "allow"         => !empty($infos['allow'])       ? $infos['allow']       : "",
            "deny"          => !empty($infos['deny'])        ? $infos['deny']        : "",
            "link"          => !empty($infos['link'])        ? $infos['link']        : "",
            "href"          => !empty($infos['href'])        ? $infos['href']        : "",
            "policy"        => !empty($infos['policy'])      ? $infos['policy']      : "",
            "personalize"   => !empty($infos['personalize']) ? $infos['personalize'] : ""
        ];

        $max = !empty(get_option("options_cookie_activate")) && is_numeric(get_option("options_cookie_activate")) ? get_option("options_cookie_activate") : 0 ;
        for ($i = 0 ; $i < $max ; $i++) {
            $data['options'][$i] = [];
            $data['options'][$i]['label'] = !empty(get_option('options_cookie_activate_'.$i.'_label')) ? get_option('options_cookie_activate_'.$i.'_label') : '';
            $data['options'][$i]['description'] = !empty(get_option('options_cookie_activate_'.$i.'_description')) ? get_option('options_cookie_activate_'.$i.'_description') : '';
        }

        $return['banner'] = \Timber::compile($template, $data);

        $template = "parts/cookie_parameters.twig";

        $return['parameters'] = \Timber::compile($template, $data);

        if (!is_null($return)) {
            wp_send_json($return);
            exit;
        } else {
            wp_send_json($return);
            die();
        }
    }

    private function getInfosByLang($lang)
    {
        $return = [];

        switch ($lang) {
            case 'en':
                $return['message'] = "This website activates cookies by default for audience measurement and anonymous features.";
                $return['dismiss'] = 'Got it!';
                $return['allow'] = "Allow cookies";
                $return['personalize'] = 'Customize';
                $return['deny'] = 'Decline';
                $return['link'] = 'Learn more';
                $return['policy'] = 'Cookies Policy';
            break;
            case 'nl':
                $return['message'] = "Deze website maakt gebruik van anonieme functionele en analytische cookies.";
                $return['dismiss'] = 'Oké, ik begrijp het!';
                $return['allow'] = "Cookies accepteren";
                $return['personalize'] = 'Pas  aan';
                $return['deny'] = 'Cookies weigeren';
                $return['link'] = 'Meer informatie';
                $return['policy'] = 'Cookiebeleid';
            break;
            case 'de':
                $return['message'] = "Diese Website nutzt für ihre Funktion und Analyse anoynm Cookies.";
                $return['dismiss'] = 'Okay, ich verstehe!';
                $return['allow'] = "Cookies akzeptieren";
                $return['personalize'] = 'Anpassen';
                $return['deny'] = 'Keine Cookies';
                $return['link'] = 'Mehr erfahren';
                $return['policy'] = 'Richtlinien für Cookies';
            break;
            case 'es':
                $return['message'] = "Esta página web activa las cookies de forma predeterminada para la medición de la audiencia y las funciones anónimas.";
                $return['dismiss'] = '¡Bien, lo entiendo!';
                $return['allow'] = "Acepto cookies";
                $return['personalize'] = 'Personalizar';
                $return['deny'] = 'No acepto cookies';
                $return['link'] = 'Más información';
                $return['policy'] = 'Política sobre Cookies';
            break;
            case 'it':
                $return['message'] = "Questo sito web attiva di default i cookies per la misurazione dell'audience e le funzioni anonime.";
                $return['dismiss'] = 'Ok, ho capito!';
                $return['allow'] = "Accetto i cookies";
                $return['personalize'] = 'Personalizzare';
                $return['deny'] = 'Non voglio i cookies';
                $return['link'] = 'Per saperne di più';
                $return['policy'] = 'Politica sui cookies';
            break;
            case 'ja':
                $return['message'] = "このWebサイトは、デフォルトでオーディエンス測定および匿名機能のためにCookieをアクティブにします。";
                $return['dismiss'] = 'わかりました';
                $return['allow'] = "クッキーを受け入れる";
                $return['personalize'] = 'カスタマイズする';
                $return['deny'] = 'クッキーを受け入れない';
                $return['link'] = 'もっと知りたい';
                $return['policy'] = 'クッキーポリシー';
            break;
            case 'fr':
            default:
                $return['message'] = "Ce site web active par défaut des cookies de mesure d'audience et pour des fonctionnalités anonymes.";
                $return['dismiss'] = 'OK je comprends !';
                $return['allow'] = "J'accepte les cookies";
                $return['personalize'] = 'Personnaliser';
                $return['deny'] = 'Je ne veux pas de cookies';
                $return['link'] = 'En savoir plus';
                $return['policy'] = 'Règles sur les cookies';
            break;
        }

        $return['link'] = !empty(get_option('options_cookie_link')) ? get_option('options_cookie_link') : $return['link'] ;
        $return['href'] = !empty(get_option('options_cookie_link_label')) ? get_option('options_cookie_link_label') : "https://www.cnil.fr/fr/site-web-cookies-et-autres-traceurs" ;

        return $return;
    }
}
