<?

// Show links to other sites where post appears
function cah_news_appears_on($post_ID, $exclude=[]) {
    $links = cah_news_get_post_links($post_ID, $exclude);
    if ($links) {
        echo '<span class="text-muted">Appears on </span> ' . implode(',', $links);
    }
}


// Display post's categories and tags
function cah_news_categories_tags($post_ID) {
    switch_to_blog(1);
    $post_categories = wp_get_post_categories($post_ID);
    $categories_count = count($post_categories);
    if ($categories_count > 0) {
        echo "<span class='text-muted'>Posted in </span>";
        foreach($post_categories as $c){
            $categories_count--;
            if ($cat = get_category($c)) {
                echo $cat->name;
                if ($categories_count > 0) echo ', ';
            }
        }
        echo '<br>';
    }
    echo get_the_tag_list(
        '<span class="text-muted">Tags:</span> ',
        ', ',
        '<br>'
    );
    restore_current_blog();
}


// Display links to posts with same categories as current news post
function cah_news_related_posts($post_ID) {
    switch_to_blog(1);
    $cats = wp_get_post_categories($post_ID);
    restore_current_blog();

    $posts = cah_news_query(array(
        'dept' => get_option('cah_news_display_dept2'),
        'categories' => $cats,
        'per_page' => 4,
        'exclude' => $post_ID,
    ));

    if ($posts) {
        echo '<h4>Related Posts</h4>';
        echo '<ul class="list-group list-group-flush">';
        foreach($posts as $post) {
            $post_url = esc_url(add_query_arg(array('postID' => $post->id), get_home_url(null, 'news-post')));
            echo sprintf('<a href="%s"><li class="list-group-item list-group-item-action">%s</li></a>', $post_url, $post->title->rendered);
        }
        echo '</ul>';
    }
}

// Return to referrer
function referral($content='') {
    $ref_string = '<a href="%s" class="btn btn-outline-primary btn-sm my-4">%s</a>';
    if (isset($_SERVER['HTTP_REFERER'])) {
        $ref_url = $_SERVER['HTTP_REFERER'];
        if (!preg_match('/news-post/', $ref_url)) {
            $ref = sprintf($ref_string, $_SERVER['HTTP_REFERER'], '&laquo; Back to news');
            return $content . $ref;
        }
    }
    $ref = sprintf($ref_string, get_home_url(null, get_option('cah_news_set_news_page', 'news')), 'More news');

    return $content . $ref;
}
