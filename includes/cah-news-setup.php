<?php 

// Load scripts and styles
function cah_news_enqueue_assets() {   
    wp_enqueue_script( 'cah_news_lazy_load', plugins_url('src/js/lazy_load.js', dirname(__FILE__)), array(), '1.0' , true );
    wp_enqueue_style( 'cah_news_stylesheet', plugins_url('static/cah-news.css', dirname(__FILE__)), array(), '1.0' , 'all' );
}
add_action('wp_enqueue_scripts', 'cah_news_enqueue_assets');

// Register custom taxonomy to classify department origin
add_action( 'init', 'create_dept_tax' );
function create_dept_tax() {
    $labels = array(
        'name'                           => 'Departments',
        'singular_name'                  => 'Department',
        'search_items'                   => 'Search Departments',
        'all_items'                      => 'All Departments',
        'edit_item'                      => 'Edit Department',
        'update_item'                    => 'Update Department',
        'add_new_item'                   => 'Add New Department',
        'new_item_name'                  => 'New Department Name',
        'menu_name'                      => 'Department',
        'view_item'                      => 'View Department',
        'popular_items'                  => 'Popular Department',
        'separate_items_with_commas'     => 'Separate departments with commas',
        'add_or_remove_items'            => 'Add or remove departments',
        'choose_from_most_used'          => 'Choose from the most used departments',
        'not_found'                      => 'No departments found'
    );

    register_taxonomy(
        'dept',
        'news',
        array(
            'label'         => __('Department'),
            'hierarchical'  => true, // must be true for post_categories_meta_box
            'labels'        => $labels,
            'public'        => true, 
            'show_in_rest'  => true,
            'show_in_menu'  => false,
            'description'   => 'Taxonomy to classify department of CAH to which news item belongs.',
//            'meta_box_cb' => 'post_tags_meta_box',
            'meta_box_cb'   => 'post_categories_meta_box',
        )
    );

    // Associate 'Department' taxonomy with 'News' CPT
    register_taxonomy_for_object_type('news', 'dept');
}


// Get Blog ID associated with a department taxonomy 
function cah_news_get_blog_id($dept_id) {

    $id = get_term_meta($dept_id, 'blog_id', true); 
    if (!$id) {
        $blogs = []; 
        foreach(get_sites() as $site) {
            $blog_name = $site.__get('blogname');
            $blog_id = $site->blog_id; 
            $blogs[$blog_name] = $blog_id; 
        }

        $dept_name = get_term($dept_id, 'dept'); 
        if ($blogs[$dept_name]) {
            return $blogs[$dept_name]; 
        }
        return -1; 

    } 
    else {
        return $id; 
    }

}

?>
