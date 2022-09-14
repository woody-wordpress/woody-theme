<?php

/**
 * Images
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */
use Symfony\Component\Finder\Finder;

class WoodyTheme_Images
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        // Enable Media Replace Plugin
        //TODO: Activate when available
        //add_action('enable-media-replace-upload-done', [$this, 'mediaReplaced'], 10, 3);

        // Filters
    }

    /**
    * Remplacement de média avec le plugin Enable Media Replace
    * Lorsqu'une image est remplacée, on supprime toutes les thumbs générées et on vide la cache du endpoint de création des thumbs
    * @param   $target_url : Url de la nouvelle image
    * @param   $source_url : Url de l'ancienne image
    * @param   $attachment_id : identifiant de l'attachment modifié
    * @return  woody_flush_varnish
    */
    //TODO : Deactivated
    // public function mediaReplaced($target_url, $source_url, $attachment_id)
    // {
    //     //TODO : Requete trop lourde, trouver une autre solution pour update l chaine $source_url dans la BDD
    //     global $wpdb;
    //     $metas = $wpdb->get_results('SELECT post_id, meta_key, meta_value FROM wp_postmeta WHERE meta_key LIKE "%_text" OR meta_key LIKE "%_link"');
    //     if (!empty($metas)) {
    //         foreach ($metas as $meta) {
    //             if (strpos('_', $meta->meta_key) === 0) {
    //                 continue;
    //             }

    //             if (strpos($meta->meta_value, $source_url) !== false) {
    //                 update_post_meta($meta->post_id, $meta->meta_key, str_replace($source_url, $target_url, maybe_unserialize($meta->meta_value)));
    //                 clean_post_cache($meta->post_id);
    //                 do_action('woody_flush_varnish', $meta->post_id);
    //             }
    //         }
    //     }


    //     if (wp_attachment_is_image($attachment_id)) {
    //         $attachment_metadata = maybe_unserialize(wp_get_attachment_metadata($attachment_id));

    //         if (!empty($attachment_metadata['file'])) {
    //             $path_arr = explode('/', $attachment_metadata['file']);
    //             $date = sprintf('%s/%s', $path_arr[0], $path_arr[1]);
    //             $path_parts = pathinfo($path_arr[2]);
    //             $name = $path_parts['filename'];
    //             $extension = $path_parts['extension'];
    //             $pattern = sprintf('/^%s-([0-9]*)x([0-9]*).%s/', $name, $extension);

    //             $finder = new Finder();
    //             $finder->files()->in(sprintf('%s/%s/thumbs', WP_UPLOAD_DIR, $date));

    //             if (!empty($finder)) {
    //                 foreach ($finder as $file) {
    //                     preg_match($pattern, $file->getRelativePathname(), $matches);
    //                     if (!empty($matches)) {
    //                         unlink($file->getRealPath());
    //                         output_log('Deleted file %s/thumbs/%s ', $date, $file->getRelativePathname());
    //                     }
    //                 }
    //             }

    //             // Flush Varnish by xkey WP_SITE_KEY_$attachment_id (/wp-json/woody/crop/$attachment_id/{ratios})
    //             do_action('woody_flush_varnish', $attachment_id);
    //         }
    //     }
    // }
}
