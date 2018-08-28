<?

add_action('admin_bar_menu', 'cah_news_toolbar_link', 999);
function cah_news_toolbar_link($wp_admin_bar) {
    // Add link to edit post
    if (is_page('news-post') && isset($_GET['postID'])) {
        $postID = $_GET['postID'];
        $link = get_edit_post_link($postID);

        $wp_admin_bar->add_node( array(
            'id'		=> 'cah-news-edit-link',
            'title'     => 'Edit News Post',
            'href'      => $link,
        ) );

        // Remove old 'Edit page' and 'Copy to a new draft' links
        $edit_node = $wp_admin_bar->get_node( 'edit' );
        $draft_node = $wp_admin_bar->get_node( 'new_draft' );
        if( $edit_node ) {
            $wp_admin_bar->remove_node( 'edit' );
        }
        if( $draft_node ) {
            $wp_admin_bar->remove_node( 'new_draft' );
        }

    }

}



?>