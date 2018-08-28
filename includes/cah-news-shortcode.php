<?

add_shortcode('cah-news', 'cah_news_shortcode');

function cah_news_shortcode_new($atts) {    
    $atts = shortcode_atts(array(
        'dept' => array(),
        'limit' => -1,
        'per_page' => 5,
        'view' => 'full',
        'cat'  => array(),
        'exclude' => array(), 
    ), $atts);

    cah_news_before(); 
    
    if ($atts['view'] == 'preview') {
        echo '<h2>In the News</h2>'; 
        cah_news_get_news(3, false);
    }

    if ($atts['view'] == 'full') {
        cah_news_search();
        cah_news_get_news(); 
    }

  
}

function cah_news_shortcode($atts) {    
    $atts = shortcode_atts(array(
        'dept' => array(),
        'limit' => -1,
        'per_page' => 5,
        'view' => 'full',
        'cat'  => array(),
        'exclude' => array(), 
    ), $atts);
    
    if ($atts['view'] == 'preview') {
        $atts['limit'] = 3;
        $atts['per_page'] = 3; 
        
        echo '<h2>In the News</h2>'; 
    }

    if ($atts['view'] == 'full') {
        cah_news_search();
    }

    // Department(s) to display 
    $displayDept = !empty($atts['dept']) ? $atts['dept'] : get_option('cah_news_display_dept2'); 
    if (empty($displayDept)) {
        // If option not set, fall back to show news from all departments 
        echo "Displaying all news."; 
        $displayDept = array_column(get_departments(), 'term_id'); 
    }

    $current_blog = get_current_blog_id(); 
    $query = query_news($displayDept, $atts);          // switches to main blog 1 and performs query
    display_news($query, $current_blog);         // displays news posts
    if ($current_blog != 1) {
        switch_to_blog($current_blog);
    }
    // restore_current_blog();

    if ($atts['limit'] == -1) {
        cah_news_pagination($query->max_num_pages);
    }
    if ($atts['view'] == 'preview') {
        $news_page = get_option('cah_news_set_news_page', 'news'); 
        echo sprintf('<a class="btn btn-primary btn-sm" href="%s">More News</a><br>', home_url($news_page)); 
    }
}

?>