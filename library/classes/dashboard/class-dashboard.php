<?php
/**
 * Admin Dashboard
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Dashboard
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('wp_dashboard_setup', [$this, 'dashboardWidgets']);
    }

    /**
     * Benoit Bouchaud
     * On retire les boxes inutiles du dashboard
     */
    public function dashboardWidgets()
    {
        wp_add_dashboard_widget(
            'raccourci-family', // Widget slug.
            'Raccourci Family', // Title.
            [$this, 'raccourciFamilyWidget'] // Display function.
        );

        wp_add_dashboard_widget(
            'woody-lestudio', // Widget slug.
            'Le Studio', // Title.
            [$this, 'leStudioWidget'] // Display function.
        );
    }

    public function raccourciFamilyWidget()
    {
        $vars = [
            'class' => 'raccourci-family',
            'cover' => get_template_directory_uri() . '/library/classes/dashboard/assets/cover-raccourci-family.jpg',
            'title' => 'Groupe Facebook · 628 membres',
            'description' => 'Un lieu d&#039;échange de partage et de bonnes idées entre tous les membres de la Raccourci Family !',
            'button_link' => 'https://www.facebook.com/plugins/group/join/popup/?group_id=355097798174987&amp;source=email_campaign_plugin',
            'button_icon' => 'facebook',
            'button_text' => 'Rejoindre ce groupe',
        ];

        print $this->getWidgetTpl($vars);
    }

    public function leStudioWidget()
    {
        $vars = [
            'class' => 'lestudio',
            'cover' => get_template_directory_uri() . '/library/classes/dashboard/assets/cover-lestudio.jpg',
            'title' => 'Le Studio',
            'description' => 'Piloter tous vos produits Raccourci depuis le Studio (TourismSystem, Nurtik, Fairguest, Mobitour, Tickets, ...)',
            'button_link' => 'https://studio.raccourci.fr',
            'button_icon' => 'screenoptions',
            'button_text' => 'Accéder au Studio',
        ];

        print $this->getWidgetTpl($vars);
    }

    private function getWidgetTpl($vars, $tpl_name = 'cover-link')
    {
        $data = file_get_contents(__DIR__ . '/widgets/' . $tpl_name . '.html');
        foreach ($vars as $key => $val) {
            $data = str_replace('{{ ' . $key . ' }}', $val, $data);
        }

        return $data;
    }
}
