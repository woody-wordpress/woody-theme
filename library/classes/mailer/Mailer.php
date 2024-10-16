<?php

namespace Woody\WoodyTheme\library\classes\mailer;

class Mailer
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        if(!empty(WOODY_SMTP_HOSTNAME) && !empty(WOODY_SMTP_USERNAME) && !empty(WOODY_SMTP_PASSWORD) && !empty(WOODY_SMTP_SENDER) && !empty(WOODY_SMTP_SENDER_NAME)) {
            add_action('phpmailer_init', [$this, 'phpmailerInit']);
            add_filter('wp_mail_from', fn ($email) => WOODY_SMTP_SENDER);
            add_filter('wp_mail_from_name', fn ($name) => WOODY_SMTP_SENDER_NAME);
        }
    }

    public function phpmailerInit($phpmailer)
    {
        $phpmailer->Host = WOODY_SMTP_HOSTNAME; // for example, smtp.mailtrap.io
        $phpmailer->Port = WOODY_SMTP_PORT; // set the appropriate port: 465, 2525, etc.
        $phpmailer->Username = WOODY_SMTP_USERNAME; // your SMTP username
        $phpmailer->Password = WOODY_SMTP_PASSWORD; // your SMTP password
        $phpmailer->SMTPAuth = true;
        $phpmailer->SMTPSecure = WOODY_SMTP_SECURE; // preferable but optional
        $phpmailer->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
        $phpmailer->IsSMTP();
    }
}
