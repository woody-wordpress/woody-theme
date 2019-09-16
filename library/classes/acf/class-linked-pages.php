<?php
/**
 * @author Jérémy Legendre
 * @copyright Raccourci Agency 2019
 *
 * ACF LinkedPages
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_ACF_LinkedPages
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {

        add_action('init', [$this, 'registerTaxonomy'] );
        add_action('woody_theme_update', [$this, 'setPostTaxonomyTerm']);
        add_action('acf/load_field/key=field_5d7f57f2b21f7', [$this, 'getAvailablePages'], 10, 1);
        add_action('save_post', [$this, 'setLinkBetweenPages'], 100, 1);
    }

    public function registerTaxonomy()
    {
        register_taxonomy(
            'prepare_onspot',
            'page',
            array(
                'label' => 'Type préparation / sur place',
                'labels' => [],
                'hierarchical' => false,
                'show_ui' => false,
                'show_in_menu' => false
            )
        );

        wp_insert_term('prepare', 'prepare_onspot', array('slug' => 'prepare'));
        wp_insert_term('spot', 'prepare_onspot', array('slug' => 'prepare'));
    }

    public function setPostTaxonomyTerm()
    {
        $args = array(
            'post_type' => 'page',
            'post_status' => array(
                'publish',
                'draft'
            ),
            'posts_per_page' => -1
        );

        $result = new \WP_Query($args);

        foreach($result->posts as $post){
            $type = get_field('field_5d47d14bdf764', $post->ID) ? 'prepare' : 'spot' ;
            $term = get_term_by('slug', $type, 'prepare_onspot');
            wp_set_post_terms( $post->ID, $term->slug, 'prepare_onspot' );
        }
    }

    public function getAvailablePages($field)
    {
        $type = get_field('field_5d47d14bdf764') ? 'spot' : 'prepare' ;
        $field['taxonomy'] = ["prepare_onspot:".$type];

        return $field;
    }

    public function setLinkBetweenPages($post_id)
    {
        $type = get_field('field_5d47d14bdf764', $post_id) ;
        $type_value = $type ? 'prepare' : 'spot';
        $term = get_term_by('slug', $type_value, 'prepare_onspot');
        wp_set_post_terms( $post_id, $term->term_id, 'prepare_onspot' );

        if(!wp_is_post_revision( $post_id )){
            $opposite = false;
            if(!empty($type)){
                // set linked page opposite (if current is preparation post, other must be on spot page )
                $opposite = $type == true ? false : true ;
            }

            $linked_post = get_field('field_5d7f57f2b21f7', $post_id);
            $linked_post_related = $linked_post ? get_field('field_5d7f57f2b21f7', $linked_post->ID) : false ;
            if($linked_post && !$linked_post_related ){
                update_field('field_5d47d14bdf764', $opposite, $linked_post->ID);
                update_field('field_5d7f57f2b21f7', get_post($post_id), $linked_post->ID);

                $type_value = $opposite ? 'prepare' : 'spot';
                $term = get_term_by('slug', $type_value, 'prepare_onspot');
                wp_set_post_terms( $linked_post->ID, $term, 'prepare_onspot' );
            }
        }else{

        }
    }
}
