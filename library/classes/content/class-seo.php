<?php

/**
 * Woody SEO
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

use WoodyProcess\Tools\WoodyTheme_WoodyProcessTools;

class WoodyTheme_Seo
{
    public function __construct()
    {
        $this->registerHooks();

        if (defined('WP_CLI') && \WP_CLI) {
            $this->registerCommands();
        }
    }

    protected function registerHooks()
    {
        add_filter('woody_seo_transform_pattern', [$this, 'woodySeoTransformPattern'], 10, 1);
        add_action('admin_menu', [$this, 'generateMenu'], 10);
        add_action('woody_migrate_yoast_post_meta', [$this, 'migrateYoastPostMeta'], 10, 1);
        add_action('woody_migrate_yoast_primary_tags', [$this, 'migrateYoastPrimaryTags'], 10, 2);
    }

    public function woodySeoTransformPattern($string)
    {
        $tools = new WoodyTheme_WoodyProcessTools;
        $string = $tools->replacePattern($string, get_the_ID());
        return $string;
    }

    public function generateMenu()
    {
        acf_add_options_page([
            'page_title' => 'Paramètres Woody SEO',
            'menu_title' => 'Woody SEO',
            'menu_slug' => 'woodyseo_settings',
            'capability'    => 'edit_pages',
            'icon_url'      => 'dashicons-awards',
            'position'      => 50
        ]);
    }

    // **************************** //
    // Migration YOAST => WOODY SEO //
    // **************************** //

    public function registerCommands()
    {
        \WP_CLI::add_command('woody:migrate_yoast', [$this, 'migrateYoast']);
    }

    public function migrateYoast()
    {
        // On récupère toutes les pages du site et les taxonomies associées
        $query = $this->getAllPages();
        $posts = (!empty($query->posts)) ? $query->posts : [];
        if (empty($posts)) {
            output_warning('SORRY, WEBSITE SEEMS TO BE EMPTY OF PAGES');
        }
        $taxonomies = getPageTaxonomies();

        // Nombre de pages dans le site
        output_log('## ' . $query->post_count . ' PAGES TO UPDATE');

        // On migre l'id de suivi google
        output_log('## ' . 'UPDATING SITE METADATA');
        $this->migrateGoogleVerifCode();

        // On migre les meta (title, desc, og, twitter) de chaque page
        output_log('## ' . 'UPDATING PAGES METADATA');
        foreach ($posts as $post) {
            do_action('woody_async_add', 'woody_migrate_yoast_post_meta', $post, 'post_' . $post->ID);
            // $this->migrateYoastPostMeta($post);
        }

        // On migre les tags primary

        output_log('## ' . 'UPDATING PRIMARY TAGS');
        foreach ($posts as $post) {
            do_action('woody_async_add', 'woody_migrate_yoast_primary_tags', ['post' => $post, 'taxonomies' =>$taxonomies], 'post_' . $post->ID);
            // $this->migrateYoastPrimaryTags($post, $taxonomies);
        }
    }

    private function getAllPages()
    {
        $args = [
            'post_type' => 'page',
            'post_status' => 'any',
            'nopaging' => true
        ];

        $query = new WP_Query($args);

        return $query;
    }

    public function migrateGoogleVerifCode()
    {
        // Option à mettre à jour => field_5e16e3c18c1c3

        $yoast = get_option('wpseo');
        if (empty($yoast)) {
            output_warning('YOAST OPTION NOT FOUND');
        } else {
            $google_verif_code = (!empty($yoast['googleverify'])) ? $yoast['googleverify'] : '';

            if (empty($google_verif_code)) {
                output_warning('GOOGLE\'S VERIFICATION CODE IS EMPTY');
            } else {
                $meta = '<meta name="google-site-verification" content="' . $google_verif_code . '" />';
                $update = update_field('woody_custom_meta', $meta, 'option');
                if ($update == true) {
                    output_success('WOODY CUSTOM META UPDATED');
                } else {
                    output_warning('AN ERROR WAS OCCURED WHEN UPDATING OPTION OR THE OPTION IS ALREADY SET');
                }
            }
        }
    }

    public function migrateYoastPostMeta($post)
    {
        // On stocke les metas yoast et le nom du champ de remplacement
        $yoast_data[$post->ID] = [
                'meta_title' => [
                    'value' => get_post_meta($post->ID, '_yoast_wpseo_title', true),
                    'target' => 'woodyseo_meta_title'
                ],
                'meta_desc' => [
                    'value' => get_post_meta($post->ID, '_yoast_wpseo_metadesc', true),
                    'target' => 'woodyseo_meta_description'
                ],

                'og_title' => [
                    'value' => get_post_meta($post->ID, '_yoast_wpseo_opengraph-title', true),
                    'target' => 'woodyseo_fb_title'
                ],

                'og_desc' => [
                    'value' => get_post_meta($post->ID, '_yoast_wpseo_opengraph-description', true),
                    'target' => 'woodyseo_fb_description'
                ],

                'twitter_title' => [
                    'value' => get_post_meta($post->ID, '_yoast_wpseo_twitter-title', true),
                    'target' => 'woodyseo_twitter_title'
                ],

                'twitter_desc' => [
                    'value' => get_post_meta($post->ID, '_yoast_wpseo_twitter-description', true),
                    'target' => 'woodyseo_twitter_description'
                    ]
                ];

        // On récupère l'id de l'image si on trouve une url pour les images opengraph ou twitter
        $og_image = attachment_url_to_postid(get_post_meta($post->ID, '_yoast_wpseo_opengraph-image', true));
        $twitter_image = attachment_url_to_postid(get_post_meta($post->ID, '_yoast_wpseo_twitter-image', true));

        $yoast_data[$post->ID]['og_image'] = [
                'value' => (!empty($og_image)) ? $og_image : '',
                'target' => 'woodyseo_fb_image'
            ];
        $yoast_data[$post->ID]['twitter_image'] = [
                'value' => (!empty($twitter_image)) ? $og_image : '',
                'target' => 'woodyseo_twitter_image'
            ];

        // Servira pour le log final : post mis à jour ou non
        $post_updated = false;

        foreach ($yoast_data[$post->ID] as $metadata_key => $metadata) {
            if (!empty($metadata['value'])) {
                // Pour chaque valeur de champ Yoast, on met à jour le champ acf correspondant
                $metadata['value'] = $this->yoastPatternToWoodyOnes($metadata['value']);
                $update = update_field($metadata['target'], $metadata['value'], $post->ID);

                if ($update == true) {
                    // On log le nom de la meta mise à jour
                    output_success($metadata_key . ' sucessfully updated');
                    $post_updated = true;
                } else {
                    // On log la non mise à jour du champ car valeur identique à Yoast
                    output_warning('The value of ' . $metadata['target'] . ' is already set as ' . $metadata['value']);
                    $post_updated = false;
                }
            }
        }

        // On log le résultat de la mise à jour du post
        if ($post_updated == true) {
            output_success('POST UPDATED');
        } else {
            output_warning('NOTHING TO WRITE');
        }
    }

    public function migrateYoastPrimaryTags($params)
    {
        $post = $params['post'];
        $taxonomies = $params['taxonomies'];

        // On récupère le champ ACF à mettre à jour
        $field = get_field('tags_primary', $post->ID);

        // Pour chaque taxonomie de la page, on vérifie s'il existe un tag primary
        foreach ($taxonomies as $tax_key => $tax) {
            $primary_term = get_post_meta($post->ID, '_yoast_wpseo_primary_' . $tax->name, true);
            $field['primary_' . $tax->name ] = (!empty($primary_term)) ? $primary_term: '';
        }

        if (!empty($field)) {
            $update = update_field('field_5d7bada38eedf', $field, $post->ID);
        }

        // On log le résultat de la mise à jour
        if ($update == true) {
            output_success('POST '. $post->ID .' UPDATED');
        } else {
            output_warning($post->ID . ' : NOTHING TO WRITE');
        }
    }

    public function yoastPatternToWoodyOnes($string)
    {
        $search = ['%%sep%%', '%%sitename%%', '%%title%%', '%%cf_page_teaser_desc%%'];
        $replace = ['|', '%site_name%', '%post_title%', '%teaser_desc%'];
        $string = str_replace($search, $replace, $string);
        return $string;
    }
}
