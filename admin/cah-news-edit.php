<?php

add_action('add_meta_boxes', 'cah_news_metaboxes');

// Display metabox with direct links to child sites where post appears
function cah_news_link_metabox() {
    global $post;
    $links = cah_news_get_post_links($post->ID);
    echo '<div class="container">';
    echo implode(',', $links);
    echo '</div>';
}

// Get direct links to child sites where post appears
function cah_news_get_post_links($id) {
    $terms = wp_get_post_terms($id, 'dept');
    $links = [];
    foreach($terms as $term) {
        $blog_id = cah_news_get_blog_id($term->term_id);
        $post_url = add_query_arg('postID', $id, get_home_url($blog_id, 'news-post'));
        $links[] = sprintf('<a href="%s">%s</a>', $post_url, $term->name);
    }
    return $links;
}

?>

