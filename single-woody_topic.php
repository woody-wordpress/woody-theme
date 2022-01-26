<?php

require_once get_template_directory() . '/library/templates/template.php';
require_once get_template_directory() . '/library/templates/template-topic.php';

$template = new WoodyTheme_Template_Topic();
$template->render();
