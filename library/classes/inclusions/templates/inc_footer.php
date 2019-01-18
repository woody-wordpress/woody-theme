<?php

$context = [];
$context = apply_filters('inc_footer_override', $context);
$return = Timber::compile('inclusions/inc_footer.twig', $context);
print $return;
