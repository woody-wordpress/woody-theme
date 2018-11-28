<?php
require_once get_template_directory() . '/library/templates/template.php';
require_once get_template_directory() . '/library/templates/template-touristic_sheet.php';

$template = new WoodyTheme_Template_TouristicSheet();
$template->render();
