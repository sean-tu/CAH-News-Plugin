<?php

/*
 *
 * Plugin Name: CAH News Plugin
 * Description: News aggregation and distribution for UCF College of Arts and Humanities department sites
 * Author: Sean Reedy
 *
 */

define('CAH_NEWS_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Exclude current post from query
function cah_news_exclude_current_post($query) {
    global $post_ID; 
    if (isset($post_ID) && is_numeric($post_ID)) {
        $query->set('post__not_in', array($post_ID)); 
    }
}

// Show posts related to current news post
function cah_news_related_posts2($post_ID) {
    switch_to_blog(1); 
    $post_categories = wp_get_post_categories($post_ID);
    if (count($post_categories) > 1) {
        add_action('pre_get_posts', 'cah_news_exclude_current_post');
        echo '<h3>Related Posts</h3>'; 
        $cat_array = '';
        foreach($post_categories as $c) {
            $cat_array .= (string)$c . ','; 
        }
        $cat_array = substr($cat_array, 0, -1); 
        $sc_string = sprintf('[cah-news limit=3 per_page=3 cat=%s]', $cat_array); 
        do_shortcode($sc_string); 
    }
    restore_current_blog(); 
}

// Returns a REST API query URL of news posts
function cah_news_query($params, $embed=true) {
    $base_url = 'http://wordpress.cah.ucf.edu/wp-json/wp/v2/news?'; 
    $query = ''; 
    foreach($params as $key => $value) {
        if (is_array($value)) {
            $value = implode(',', $value); 
        }
        if ($value != '') {
            $query .= sprintf('%s=%s&', $key, $value); 
        }
    }
    if ($embed) {
        $query .= '_embed';
    }
    return $base_url . $query;
}

// Retrieve thumbnail media from post JSON
function cah_news_get_thumbnail($post) {
    $media = $post->_links->{'wp:featuredmedia'}; 
    if ($media->embeddable == true) {
        $img_href = $media->href; 
    }

}

// Display links to posts with same categories as current news post
function cah_news_related_posts($post_ID) {
    switch_to_blog(1);
    $cats = wp_get_post_categories($post_ID); 
    restore_current_blog(); 

    $request_url = cah_news_query(array(
        'dept' => get_option('cah_news_display_dept2'), 
        'categories' => $cats,
        'per_page' => 4, 
        'exclude' => $post_ID, 
    ));
    // echo $request_url; 
    $response = wp_remote_get($request_url, array('timeout'=>20)); 

    if (is_wp_error($response)) {
        echo 'Error showing related posts';
        echo $response->get_error_message();
        return; 
    }

    $posts = json_decode(wp_remote_retrieve_body($response)); 

    echo '<h4>Related Posts</h4>';
    echo '<ul class="list-group list-group-flush">';
    foreach($posts as $post) {
        $post_url = esc_url(add_query_arg(array('postID' => $post->id), get_home_url(null, 'news-post'))); 
        echo sprintf('<a href="%s"><li class="list-group-item list-group-item-action">%s</li></a>', $post_url, $post->title->rendered);
    }
    echo '</ul>'; 
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
            '<br>');    
    echo '<br>';
    restore_current_blog(); 
}

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

function query_news($deptIDs, $args)
{
    $per_page = $args['per_page']; 
    $cat = $args['cat']; 
    $paged = get_query_var('paged', 1); 

    if (get_current_blog_id() !== 1)
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
    if (!empty($cat)) {
        $args['category__in'] = $cat; 
    }
    
    if (!empty($args['exclude'])) {
        $args['post__not_in'] = array($args['exclude']); 
        // $args['ignore_sticky_posts'] = true; 
    }
    return new WP_Query($args);
}

function display_news($news_query, $current_blog) {
    cah_news_before(); 
    echo "<div class='ucf-news modern'>";
    if ($news_query->have_posts()) : while ($news_query->have_posts()) : $news_query->the_post(); 
        $post_url = esc_url(add_query_arg(array('postID' => get_the_ID()), get_home_url($current_blog, 'news-post'))); 
    ?>
        <div class="ucf-news-item p-0">
        <a href="<?= $post_url ?>" class='p-3'>
            <? if ($img = get_the_post_thumbnail_url()): ?>
                <div class="ucf-news-thumbnail-image-cah mr-3"
                     style="background-image:url('<?= $img ?>'">
                </div>
            <? endif; ?>
            <div class="ucf-news-item-content">
                <div class="ucf-news-item-details">
                    <h5 class='ucf-news-item-title'><? the_title(); ?></h5>
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

function cah_news_post($id) {
    $post_url = esc_url(add_query_arg(array('postID' => $id), get_home_url($current_blog, 'news-post'))); 
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
   <?
}

// Display pagination navigation 
function cah_news_pagination($query) {
    $max_pages = $query->max_num_pages;
    $current_page = max(get_query_var('paged'), 1);

    $page_nums = array();

    $width = 3;
    if ($current_page > 1) {
        $page_nums[] = 1;
        for($i=$current_page-$width; $i<$current_page; $i++) {
            if ($i > 1) {
                $page_nums[] = $i;
            }
        }
    }

    $page_nums[] = $current_page;

    if ($current_page < $max_pages) {
        for($i=$current_page+1; $i<$current_page+$width; $i++) {
            if ($i < $max_pages) {
                $page_nums[] = $i;
            }
        }

        if ($page_nums[-1] !== $max_pages) {
            $page_nums[] = $max_pages;
        }

    }

    ?>
    <nav aria-label="News posts">
        <ul class="pagination pagination-lg">
            <li class="page-item"><? previous_posts_link( '&laquo; Previous page', $max_pages) ?></li>

            <?
            $prev = $page_nums[0];
            foreach($page_nums as $page) {
                // divider
                if ($page - $prev > 1) {
                    echo sprintf('<li class="page-item disabled"><a tabindex="-1" class="page-link disabled">...</a></li>');
                }
                $link = get_pagenum_link($page);
                $active = $page == $current_page ? 'active' : '';
                echo sprintf('<li class="page-item %s"><a href="%s" class="page-link">%s</a></li>', $active, $link, $page);
                $prev = $page;
            }
            ?>


            <li class="page-item"><? next_posts_link( 'Next page &raquo;', $max_pages) ?></li>

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
require_once CAH_NEWS_PLUGIN_PATH . 'includes/cah-news-shortcode.php';

// Included admin files
if (is_admin()) {
    require_once CAH_NEWS_PLUGIN_PATH . 'includes/cah-news-options.php';
}


?>