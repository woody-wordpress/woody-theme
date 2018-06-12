<?php
/**
 * The page template file
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 * @package HawwwaiTheme
 * @since HawwwaiTheme 1.0.0
 */

$context = Timber::get_context();

// Creating Timber object to access twig keys
$context['post'] = new TimberPost();

$context['woody_components'] = Woody::getTwigsPaths();

// rcd(get_class_methods(TimberPost), true);
/** ****************************
 * Displaying the page's heading
 **************************** **/
$page_heading = [];
$page_heading['content'] = get_field('field_5b052bbab3867');
$page_heading['media_type'] = get_field('field_5b0e5cc3d4b1a');

if ($page_heading['media_type'] == 'img') {
    $page_heading['media'] = get_field('field_5b0e5ddfd4b1b');
} else {
    $page_heading['media'] = get_field('field_5b0e5df0d4b1c');
}

$page_heading['title_as_h1'] = get_field('field_5b0e54ebfa657');
$page_heading['classes'] = get_field('field_5b0e5ef78f6be');

$page_heading_tpl = get_field('field_5b052d70ea19b');

$context['page_heading'] = Timber::compile($context['woody_components'][$page_heading_tpl], $page_heading);

/** ************************
 * Displaying the sections
 ************************ **/
// Create a empty array to fill with rendered twig components and get the sections of the page
$context['sections'] = [];
$sections = $context['post']->get_field('section');

// Foreach section, fill vars to display in the woody's components
foreach ($sections as $key => $section) {
    $classes_array = [];
    $display= [];
    // Send $section to section's header tpl
    $the_header = Timber::compile($context['woody_components']['section-section_header-tpl_1'], $section);

    // Send $section to section's footer tpl
    $the_footer = Timber::compile($context['woody_components']['section-section_footer-tpl_1'], $section);

    // Creating data for display options => set the container classes
    $classes_array = [];
    $display['gridContainer'] = (empty($section['display_fullwidth'])) ? 'grid-container' : '';

    if (!empty($section['background_img'])) {
        $display['background_img'] = $section['background_img'];
        $classes_array[] = 'isRel';
    }

    if (!empty($section['background_color'])) {
        $classes_array[] = $section['background_color'];
    }
    if (!empty($section['background_img_opacity'])) {
        $classes_array[] = $section['background_img_opacity'];
    }
    if (!empty($section['section_paddings']['section_padding_top'])) {
        $classes_array[] = $section['section_paddings']['section_padding_top'];
    }
    if (!empty($section['section_paddings']['section_padding_bottom'])) {
        $classes_array[] = $section['section_paddings']['section_padding_bottom'];
    }
    if (!empty($section['section_margins']['section_margin_top'])) {
        $classes_array[] = $section['section_margins']['section_margin_top'];
    }
    if (!empty($section['section_margins']['section_margin_bottom'])) {
        $classes_array[] = $section['section_margins']['section_margin_bottom'];
    }

    // Implode classes
    $display['classes'] = implode(' ', $classes_array);

    // Render the section layout with rendered woody's components
    $components = [];

    // Get every section_content's layouts in the post
    if (!empty($section['section_content'])) {
        foreach ($section['section_content'] as $key => $layout) {

            // if ($layout['acf_fc_layout'] == 'files') {
            //     rcd($layout, true);
            // }

            // If the layout is a manual content selection, we create the $items array to push it into woody tpls
            if ($layout['acf_fc_layout'] == 'manual_focus') {
                $the_items = [];
                foreach ($layout['content_selection'] as $key => $item_wrapper) {
                    // Selected content is custom
                    if ($item_wrapper['content_selection_type'] == 'custom_content' && !empty($item_wrapper['custom_content'])) {
                        $item = $item_wrapper['custom_content'];
                        $the_items['items'][$key]['title'] = (!empty($item['title'])) ? $item['title'] : '';
                        $the_items['items'][$key]['pretitle'] = (!empty($item['pretitle'])) ? $item['pretitle'] : '';
                        $the_items['items'][$key]['subtitle'] = (!empty($item['subtitle'])) ? $item['subtitle'] : '';
                        $the_items['items'][$key]['icon'] = (!empty($item['icon'])) ? $item['icon'] : '';
                        $the_items['items'][$key]['description'] = (!empty($item['description'])) ? $item['description'] : '';
                        $the_items['items'][$key]['link']['url'] = (!empty($item['link']['url'])) ? $item['link']['url'] : '';
                        $the_items['items'][$key]['link']['title'] = (!empty($item['link']['title'])) ? $item['link']['title'] : '';
                        $the_items['items'][$key]['link']['target'] = (!empty($item['link']['target'])) ? $item['link']['target'] : '';

                        // Get the choice of the media
                        if ($item['media_type'] == 'img' && !empty($item['img'])) {
                            $the_items['items'][$key]['img'] = $item['img'];
                        } elseif ($item['media_type'] == 'movie' && !empty($item['movie'])) {
                            $the_items['items'][$key]['movie'] = $item['movie'];
                        }

                        // Selected content is an existing post
                    } elseif ($item_wrapper['content_selection_type'] == 'existing_content' && !empty($item_wrapper['existing_content'])) {
                        $item = $item_wrapper['existing_content'];

                        if (!empty($item['content_selection'])) {
                            $the_items['items'][$key]['link']['url'] = $item['content_selection']->get_path();
                            $the_items['items'][$key]['link']['title'] = (!empty($item['link_label'])) ? $item['link_label'] : '';
                            // Search for focus fields and, if empty, search for post field
                            if (!empty($item['content_selection']->get_field('focus_title'))) {
                                $the_items['items'][$key]['title'] = $item['content_selection']->get_field('focus_title');
                            } elseif (!empty($item['content_selection']->get_field('title'))) {
                                $the_items['items'][$key]['title'] = $item['content_selection']->get_field('title');
                            }

                            if (in_array('pretitle', $item['display_elements'])) {
                                if (!empty($item['content_selection']->get_field('focus_pretitle'))) {
                                    $the_items['items'][$key]['pretitle'] = $item['content_selection']->get_field('focus_pretitle');
                                } elseif (!empty($item['content_selection']->get_field('pretitle'))) {
                                    $the_items['items'][$key]['pretitle'] = $item['content_selection']->get_field('pretitle');
                                }
                            }

                            if (in_array('subtitle', $item['display_elements'])) {
                                if (!empty($item['content_selection']->get_field('focus_subtitle'))) {
                                    $the_items['items'][$key]['subtitle'] = $item['content_selection']->get_field('focus_subtitle');
                                } elseif (!empty($item['content_selection']->get_field('subtitle'))) {
                                    $the_items['items'][$key]['subtitle'] = $item['content_selection']->get_field('subtitle');
                                }
                            }

                            if (in_array('icon', $item['display_elements'])) {
                                if (!empty($item['content_selection']->get_field('focus_icon'))) {
                                    $the_items['items'][$key]['icon'] = $item['content_selection']->get_field('focus_icon');
                                } elseif (!empty($item['content_selection']->get_field('icon'))) {
                                    $the_items['items'][$key]['icon'] = $item['content_selection']->get_field('icon');
                                }
                            }

                            if (in_array('description', $item['display_elements'])) {
                                if (!empty($item['content_selection']->get_field('focus_description'))) {
                                    $the_items['items'][$key]['description'] = $item['content_selection']->get_field('focus_description');
                                } elseif (!empty($item['content_selection']->get_field('description'))) {
                                    $the_items['items'][$key]['description'] = $item['content_selection']->get_field('description');
                                }
                            }

                            if (!empty($item['content_selection']->get_field('focus_img'))) {
                                $the_items['items'][$key]['img'] = $item['content_selection']->get_field('focus_img');
                            }
                        }
                    }
                }
                // rcd($the_items, true);

                $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $the_items);
            } else {
                $components['items'][] = Timber::compile($context['woody_components'][$layout['woody_tpl']], $layout);
            }
        }

        if (!empty($section['woody_tpl'])) {
            $the_layout = Timber::compile($context['woody_components'][$section['woody_tpl']], $components);
        }
    }

    // Fill $the_section var with rendered woody's components to fill $context['the_sections']
    $the_section = [
        'header' => $the_header,
        'footer' => $the_footer,
        'layout' => $the_layout,
        'display' => $display,
    ];

    $context['the_sections'][] = Timber::compile($context['woody_components']['section-section_full-tpl_1'], $the_section);
}

// Render the $context in page.twig
Timber::render('page.twig', $context);
