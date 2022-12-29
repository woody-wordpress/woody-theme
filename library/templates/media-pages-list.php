<?php

/**
 * Woody Theme
 * @authorBenoit BOUCHAUD
 * @copyright Raccourci Agency 2022
 */

if (! defined('ABSPATH')) {
    exit;
}
 // Exit if accessed directly
?>

<header class="woody-mediapageslist-header woody-sitemap">
    <h1>
        Liste des pages utilisant l'image "<?php echo (empty($att_metadata['image_meta']['title'])) ? get_the_title($att_id) : $att_metadata['image_meta']['title'] ?>"
        <span>Made with ♥ by Raccourci Agency</span>
    </h1>
</header>
<div class="woody-mediapageslist-container">
    <section class="woody-mediapageslist-file">
        <img src="<?php echo wp_get_attachment_image_url($att_id, 'ratio_square_small') ?>"
            width="200" height="200" />
    </section>
    <section class="woody-mediapageslist-table">
        <?php
            if (!empty($results)) {
                echo '<table>';
                echo '<thead>';
                echo '<tr>';
                echo '<th>Titre de la page</th>';
                echo '<th>Type de contenu</th>';
                echo '<th>Langue</th>';
                echo '<th>Post ID</th>';
                echo '<th>Editer la page</td>';
                echo '<th>Voir la page</td>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                foreach ($results as $result) {
                    echo '<tr>';
                    echo '<td>' . $result->post_title . '</td>';
                    echo '<td>' . $result->post_type . '</td>';
                    echo '<td>' . pll_get_post_language($result->post_id) . '</td>';
                    echo '<td>' . $result->post_id . '</td>';
                    echo '<td><a href="'. get_edit_post_link($result->post_id) . '" target="_blank">Editer</a></td>';
                    echo '<td><a href="' . woody_get_permalink($result->post_id) . '" target="_blank">Voir la page</a></td>';
                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';
            } else {
                echo "<h3>Cette image n'est utilisée dans aucune page</h3>";
            }
?>
    </section>
</div>
