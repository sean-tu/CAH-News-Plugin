<?

// Apply department taxonomy to currently uncategorized news posts
function bulk_apply_dept_tax($deptName)
{
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
        }
        else {
            $msg .= "Created dept " . $deptName . " (" . $deptSlug . ")<br>";
            $deptID = $new_term['term_id'];
        }
    }
    else {
        $deptID = $term->term_id;
    }

    foreach ($news_posts as $post) {
        $set_ret = wp_set_object_terms($post->ID, $deptID, 'dept', true);

        if (is_wp_error($set_ret)) {
            $msg .= 'Error applying taxonomy ' . $deptID . '. ';
        }
        else {
        // Success! The post's categories were set.
            $appliedCount++;
        }
    }

    return $appliedCount;
}

?>