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
        $template = "parts/cookies.twig";
        $infos = $this->getInfosByLang(pll_current_language());

        $data = [
            'options'   => [],
            "message"   => $infos['message'],
            "dismiss"   => $infos['dismiss'],
            "allow"     => $infos['allow'],
            "deny"      => $infos['deny'],
            "link"      => $infos['link'],
            "href"      => $infos['href'],
            "policy"    => $infos['policy']
        ];

        $max = !empty(get_option("options_cookie_activate")) ? get_option("options_cookie_activate") : 0 ;
        for ($i = 0 ; $i < $max ; $i++ ) {
            $data['options'][$i]['label'] = !empty(get_option('options_cookie_activate_'.$i.'_label')) ? get_option('options_cookie_activate_'.$i.'_label') : '';
            $data['options'][$i]['description'] = !empty(get_option('options_cookie_activate_'.$i.'_description')) ? get_option('options_cookie_activate_'.$i.'_description') : '';
        }

        $return = \Timber::compile($template, $data);

        if (!is_null($return)) {
            wp_send_json($return);
        } else {
            header("HTTP/1.0 400 Bad Request");
            die();
        }
        exit;
    }

    private function getInfosByLang($lang)
    {
        switch($lang)
        {
            case 'en':
                $infos['message'] = "This website activates cookies by default for audience measurement and anonymous features.";
                $infos['dismiss'] = 'Got it!';
                $infos['allow'] = "Allow cookies";
                $infos['deny'] = 'Decline';
                $infos['link'] = 'Learn more';
                $infos['policy'] = 'Cookies Policy';
            break;
            case 'nl':
                $infos['message'] = "Deze website maakt gebruik van anonieme functionele en analytische cookies.";
                $infos['dismiss'] = 'Oké, ik begrijp het!';
                $infos['allow'] = "Cookies accepteren";
                $infos['deny'] = 'Cookies weigeren';
                $infos['link'] = 'Meer informatie';
                $infos['policy'] = 'Cookiebeleid';
            break;
            case 'de':
                $infos['message'] = "Diese Website nutzt für ihre Funktion und Analyse anoynm Cookies.";
                $infos['dismiss'] = 'Okay, ich verstehe!';
                $infos['allow'] = "Cookies akzeptieren";
                $infos['deny'] = 'Keine Cookies';
                $infos['link'] = 'Mehr erfahren';
                $infos['policy'] = 'Richtlinien für Cookies';
            break;
            case 'es':
                $infos['message'] = "Esta página web activa las cookies de forma predeterminada para la medición de la audiencia y las funciones anónimas.";
                $infos['dismiss'] = '¡Bien, lo entiendo!';
                $infos['allow'] = "Acepto cookies";
                $infos['deny'] = 'No acepto cookies';
                $infos['link'] = 'Más información';
                $infos['policy'] = 'Política sobre Cookies';
            break;
            case 'it':
                $infos['message'] = "Questo sito web attiva di default i cookies per la misurazione dell'audience e le funzioni anonime.";
                $infos['dismiss'] = 'Ok, ho capito!';
                $infos['allow'] = "Accetto i cookies";
                $infos['deny'] = 'Non voglio i cookies';
                $infos['link'] = 'Per saperne di più';
                $infos['policy'] = 'Politica sui cookies';
            break;
            case 'ja':
                $infos['message'] = "このWebサイトは、デフォルトでオーディエンス測定および匿名機能のためにCookieをアクティブにします。";
                $infos['dismiss'] = 'わかりました';
                $infos['allow'] = "クッキーを受け入れる";
                $infos['deny'] = 'クッキーを受け入れない';
                $infos['link'] = 'もっと知りたい';
                $infos['policy'] = 'クッキーポリシー';
            break;
            case 'fr':
            default:
                $infos['message'] = "Ce site web active par défaut des cookies de mesure d'audience et pour des fonctionnalités anonymes.";
                $infos['dismiss'] = 'OK je comprends !';
                $infos['allow'] = "J'accepte les cookies";
                $infos['deny'] = 'Je ne veux pas de cookies';
                $infos['link'] = 'En savoir plus';
                $infos['policy'] = 'Règles sur les cookies';
            break;
        }

        return $infos;
    }
}
