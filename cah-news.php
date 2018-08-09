<?php

/*
 *
 * Plugin Name: CAH News Plugin
 * Description: News aggregation and distribution for UCF College of Arts and Humanities department sites
 * Author: Sean Reedy
 *
 */

define('CAH_NEWS_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Add class to prev/next page links
function posts_link_attributes() {
    return 'class="page-link"';
}

// Filter excerpt length
function cah_news_excerpt_length($length) {
    return 20; 
}

// Change 'Read more' string of excerpt 
function cah_news_excerpt_more($more) {
    return '...'; 
}

// Add filters to modify display of news posts 
function cah_news_before() {
    add_filter('next_posts_link_attributes', 'posts_link_attributes');
    add_filter('previous_posts_link_attributes', 'posts_link_attributes');

    add_filter('excerpt_more', 'cah_news_excerpt_more'); 
    add_filter('excerpt_length', 'cah_news_excerpt_length'); 
}

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

function get_displayed_departments() {
    $displayDept = get_option('cah_news_display_dept');
    return $displayDept;
}

function query_news($deptIDs)
{
    $per_page = 5;
    $paged = max(get_query_var('paged'), 1); 


    switch_to_blog(1);

    $args = array(
        'post_type' => 'news',
        'tax_query' => array(
            array(
                'taxonomy' => 'dept',
                'field' => 'term_id',
                'terms' => $deptIDs,
            )
        ),
        'posts_per_page' => $per_page,
        'paged' => $paged,
        's' => get_search_query(), 
    );

    return new WP_Query($args);
}

function display_news($news_query, $current_blog) {
    cah_news_before(); 
    echo "<div class='ucf-news modern'>";
    if ($news_query->have_posts()) : while ($news_query->have_posts()) : $news_query->the_post(); 
        $post_url = esc_url(add_query_arg(array('postID' => get_the_ID()), get_home_url($current_blog, 'news-post'))); 
    ?>
        <div class="ucf-news-item">
        <a href="<?= $post_url ?>">
            <? if ($img = get_the_post_thumbnail_url()): ?>
                <div class="ucf-news-thumbnail-image-cah mr-3"
                     style="background-image:url('<?= $img ?>'">
                </div>
            <? endif; ?>
            <div class="ucf-news-item-content">
                <div class="ucf-news-item-details">
                    <h4 class="ucf-news-item-title"><? the_title(); ?></h4>
                    <p class="ucf-news-item-excerpt">
                        <span class="meta text-muted"><? the_date() ?> - </span>
                        <?
                        the_excerpt();
                        wp_reset_postdata();
                        ?>
                    </p>
                </div>
            </div>
        </a>
        </div>
    <?php endwhile; endif; ?>

    </div>

   <?
}

// Display pagination navigation 
function cah_news_pagination($query) {
    ?>
    <nav aria-label="News posts">
        <ul class="pagination pagination-lg">
            <li class="page-item"><? previous_posts_link( '&laquo; Previous page', $query->max_num_pages) ?></li>
            <li class="page-item"><? next_posts_link( 'Next page &raquo;', $query->max_num_pages) ?></li>
        </ul>
    </nav>
    <?
}

function get_departments() {
    switch_to_blog(1);
    $depts = get_terms([
        'taxonomy'   => 'dept',
        'hide_empty' => false,
        ]);
    restore_current_blog();
    return $depts;
}

// Returns news posts that do not have assigned Department taxonomy
function get_uncategorized_news() {
    $args = array(
        'post_type' => 'news',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'dept',
                'operator' => 'NOT EXISTS',
            ),
        ),
    );
    return get_posts($args);
}

// Apply department taxonomy to currently uncategorized news posts
function bulk_apply_dept_tax($deptName) {
    global $msg;
    $appliedCount = 0;
    $news_posts = get_uncategorized_news();

    $deptSlug = sanitize_title($deptName);
    $msg .= 'Using slug ' . $deptSlug . ".<br>";
    $term = get_term_by('slug', $deptSlug, 'dept');
    if ($term == false) {
        $new_term = wp_insert_term($deptName, 'dept', array('slug' => $deptSlug));
        if (is_wp_error($new_term)) {
            $msg .= "Could not create taxonomy term for new department.<br>";
            return 0;
        } else {
            $msg .= "Created dept " . $deptName . " (" . $deptSlug . ")<br>";
            $deptID = $new_term['term_id'];
        }
    }
    else {
        $deptID = $term->term_id;
    }

    foreach ($news_posts as $post) {
        $set_ret = wp_set_object_terms($post->ID, $deptID, 'dept', true);

        if ( is_wp_error($set_ret) ) {
            $msg .= 'Error applying taxonomy ' . $deptID . '. ';
        } else {
            // Success! The post's categories were set.
            $appliedCount++;
        }
    }

    return $appliedCount;
}


// Included files 
// require_once CAH_NEWS_PLUGIN_PATH . 'includes/cah-news-table.php';
require_once CAH_NEWS_PLUGIN_PATH . 'includes/cah-news-options.php';
require_once CAH_NEWS_PLUGIN_PATH . 'includes/cah-news-shortcode.php';


?>