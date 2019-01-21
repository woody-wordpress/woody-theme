<?php

$context = [];
$context = apply_filters('inc_footer_override', $context);
Timber::render('inclusions/inc_footer.twig', $context);
