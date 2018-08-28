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

// Add metabox to show news post link
function cah_news_metaboxes() {
    add_meta_box(
        'cah_news_link',
        'News Post Link',
        'cah_news_link_metabox',
        'news',
        'side',
        'high'
    );
}


?>

