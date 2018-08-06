<?

add_shortcode('cah-news', 'cah_news_shortcode');
function cah_news_shortcode($atts) {
    $displayDept = get_option('cah_news_display_dept2');
    if (empty($displayDept)) {
        // If option not set, fall back to show news from all departments 
        echo "Displaying all news."; 
        $displayDept = array_column(get_departments(), 'term_id'); 
    }

    $a = shortcode_atts(array(
        'dept' => array(''),
        'limit' => -1,
        'paged' => true,
    ), $atts);
    
    $query = query_news($displayDept);  // switches to main blog 1 and performs query
    display_news($query);               // displays news posts
    restore_current_blog();
    cah_news_pagination($query); 
}

?>