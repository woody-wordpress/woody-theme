<?php

class WoodyTheme_cleanDataBases
{
    public function __construct()
    {
        $this->registerHooks();
        $this->total = 0;
        $this->count = 0;
        $this->deleted = 0;
    }

    public function registerHooks()
    {
        \WP_CLI::add_command('woody:clean_db', [$this, 'cleanDB'], 10, 1);
    }

    public function cleanDB($args, $assoc_args)
    {
        if (empty($assoc_args['post_id'])) {
            global $wpdb;
            $pages = $wpdb->get_results(sprintf('SELECT ID FROM %sposts WHERE post_type="page"', $wpdb->prefix));
            output_h1(is_countable($pages) ? 'Nombre de page à parcourir : ' . count($pages) : 'Aucune page trouvée');
            $this->total = is_countable($pages) ? count($pages) : 0;
            foreach ($pages as $page) {
                ++$this->count;
                $this->deleted = 0;
                output_h2(sprintf('%s/%s : Traitment de la page %s', $this->count, $this->total, $page->ID));
                $this->getMetaDuplicate($page->ID);
                output_success($this->deleted . ' metadata supprimées pour la page ' . $page->ID);
            }
        } else {
            $this->getMetaDuplicate($assoc_args['post_id']);
            output_h1('Mise à jour de la page "' . html_entity_decode(get_the_title($assoc_args['post_id'])) . '" ('. $assoc_args['post_id'] .')');
            clean_post_cache($assoc_args['post_id']);
            output_success($this->deleted . ' metadata supprimées pour la page ' . $assoc_args['post_id']);
        }
    }

    private function getMetaDuplicate($page_id)
    {
        global $wpdb;
        $sql = sprintf('SELECT post_id, meta_key, COUNT(*) FROM %spostmeta WHERE post_id="%s" GROUP BY post_id, meta_key HAVING COUNT(*) > 1', $wpdb->prefix, $page_id);
        $results = $wpdb->get_results($sql);
        if (!empty($results)) {
            foreach ($results as $result) {
                output_h3(sprintf('%s/%s : %s occurences de %s pour la page %s', $this->count, $this->total, $result->{'COUNT(*)'}, $result->meta_key, $page_id));
                $this->deleteDuplicated($result->meta_key, $page_id);
            }
        }
    }

    private function deleteDuplicated($meta_key, $post_id)
    {
        global $wpdb;
        $sql = sprintf('SELECT meta_id FROM %spostmeta WHERE meta_key="%s" AND post_id="%s" ORDER BY meta_id ASC', $wpdb->prefix, $meta_key, $post_id);
        $results = $wpdb->get_results($sql);
        $deleted = [];
        foreach ($results as $key => $result) {
            if ($key === array_key_first($results)) {
                output_log('Keep ' . $result->meta_id);
            } else {
                $wpdb->delete('wp_postmeta', ['meta_id' => $result->meta_id]);
                ++$this->deleted;
                $deleted[] = $result->meta_id;
            }
        }
        output_log('Deleted ' . count($deleted) . ' elements');
    }
}
