<?php

/**
 * Template
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Template_Sitemap
{
    protected $twig_tpl = '';
    protected $context = [];
    protected $mode = '';

    public function __construct()
    {
        $this->initContext();
    }

    protected function registerHooks()
    { }

    public function render()
    {
        if (!empty($this->twig_tpl) && !empty($this->context)) {
            switch ($this->mode) {
                case 'index':
                case 'list':
                    header('Content-Type: text/xml; charset=UTF-8');
                    break;
                case 'xsl':
                    header('Content-Type: text/xsl; charset=UTF-8');
                    break;
            }
            Timber::render($this->twig_tpl, $this->context);
        }
    }

    private function initContext()
    {
        $this->context = Timber::get_context();
        $this->mode = get_query_var('sitemap');
        switch ($this->mode) {
            case 'index':
                $query = $this->getPosts();
                if (!empty($query)) {
                    $this->twig_tpl = 'sitemap/sitemapindex.xml.twig';
                    for ($i = 1; $i <= $query->max_num_pages; $i++) {
                        $this->context['sitemaps'][] = [
                            'loc' => 'sitemap-' . $i . '.xml',
                            'lastmod' => date('c', time()),
                        ];
                    }
                }
                break;
            case 'list':
                $query = $this->getPosts();
                if (!empty($query)) {
                    $this->twig_tpl = 'sitemap/sitemap.xml.twig';
                    if (!empty($query->posts)) {
                        foreach ($query->posts as $post) {
                            $this->context['urls'][] = [
                                'loc' => get_permalink($post),
                                'lastmod' => get_the_modified_date('c', $post),
                                'images' => $this->getImagesFromPost($post)
                            ];
                        }
                    }
                }
                break;
            case 'xsl':
                $this->twig_tpl = 'sitemap/sitemap.xsl.twig';
                break;
        }

        /* Restore original Post Data */
        wp_reset_postdata();
    }

    private function getPosts()
    {
        $polylang = get_option('polylang');
        if ($polylang['force_lang'] == 3 && !empty($polylang['domains'])) {
            // Si le site est en multi domaines, on cree un sitemap par langue
            $languages = pll_current_language();
        } else {
            // Si le site est alias, on fusionne toutes les pages dans le même sitemap
            $languages = pll_languages_list();

            // Si la langue n'est pas active on n'ajoute pas les pages au sitemap
            $woody_lang_enable = get_option('woody_lang_enable', []);
            foreach ($languages as $key => $slug) {
                if (!in_array($slug, $woody_lang_enable)) {
                    unset($languages[$key]);
                }
            }
        }

        $query = new \WP_Query([
            'post_type' => ['page', 'touristic_sheet'],
            'orderby' => 'menu_order',
            'order'   => 'DESC',
            'lang' => $languages,
            'posts_per_page' => 50,
            'paged' => (get_query_var('page')) ? get_query_var('page') : 1
        ]);

        if ($query->have_posts()) {
            return $query;
        }
    }

    private function getImagesFromPost($post)
    {
        $images = [];

        if ($post->post_type == 'page') {

            $fields = [
                'field_5b0e5ddfd4b1b', // Visuel et Accroche
                'field_5b44ba5c74495', // En-tête
                'field_5b169fab55db0', // Personnalise : mise en avant
                'field_5ba8ef5bce474', // Personnalise : menu
            ];

            foreach ($fields as $field) {
                $img = get_field($field, $post->ID);
                $this->extractImg($images, $img);
            }

            $sections = get_field('field_5afd2c6916ecb', $post->ID);
            if (!empty($sections)) {
                foreach ($sections as $section) {
                    if (!empty($section['background_img'])) {
                        $this->extractImg($images, $section['background_img']);
                    }

                    if (!empty($section['icon_img'])) {
                        $this->extractImg($images, $section['icon_img']);
                    }

                    if (!empty($section['section_content'])) {
                        foreach ($section['section_content'] as $section_content) {
                            if (!empty($section_content['background_img'])) {
                                $this->extractImg($images, $section_content['background_img']);
                            }

                            if (!empty($section_content['icon_img'])) {
                                $this->extractImg($images, $section_content['icon_img']);
                            }

                            switch ($section_content['acf_fc_layout']) {
                                case 'gallery':
                                    if (!empty($section_content['gallery_items'])) {
                                        foreach ($section_content['gallery_items'] as $gallery_item) {
                                            $this->extractImg($images, $gallery_item);
                                        }
                                    }
                                    break;

                                case 'interactive_gallery':
                                    if (!empty($section_content['interactive_gallery'])) {
                                        foreach ($section_content['interactive_gallery_items'] as $gallery_item) {
                                            $this->extractImg($images, $gallery_item);
                                        }
                                    }
                                    break;

                                case 'manual_focus':
                                    if (!empty($section_content['content_selection'])) {
                                        foreach ($section_content['content_selection'] as $content_selection) {
                                            if (!empty($content_selection['custom_content']) && !empty($content_selection['custom_content']['img'])) {
                                                $this->extractImg($images, $content_selection['custom_content']['img']);
                                            }
                                        }
                                    }
                                    break;

                                case 'socialwall':
                                    if (!empty($section_content['socialwall_manual'])) {
                                        foreach ($section_content['socialwall_manual'] as $socialwall_manual) {
                                            $this->extractImg($images, $socialwall_manual);
                                        }
                                    }
                                    break;

                                default:
                                    if (!empty($section_content['img'])) {
                                        $this->extractImg($images, $section_content['img']);
                                    }
                                    break;
                            }
                        }
                    }
                }
            }
        }

        return $images;
    }

    private function extractImg(&$images, $img)
    {
        if (!empty($img) && !empty($img['url'])) {
            $images[$img['url']] = [
                'loc' => $img['url'],
                'title' => $img['title'],
                'caption' => trim($img['alt'] . ' ' . $img['caption'] . ' ' . $img['description'])
            ];
        }
    }
}
