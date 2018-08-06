<?php

if (isset($_POST['setDept']) && !empty($_POST['setDept'])) {
    $setDept = $_POST['setDept'];
    bulk_apply_dept_tax($setDept);
}

function apply_dept_tax($postID, $deptSlug) {
    if (!has_term('', 'department')) {

        if ($deptSlug == '') {
            $deptSlug = get_bloginfo('name');
        }

        wp_set_object_terms($postID, $deptSlug, 'department', true);
    }
}

function bulk_apply_dept_tax($deptSlug) {
    $args = array(
        'post_type' => 'news',
        array(
            'taxonomy' => 'dept',
            'operator' => 'NOT EXISTS',
        ),
    );
    $query = WP_QUERY($args);
    while ($query->have_posts()) {
        $query->the_post();
        apply_dept_tax(get_the_ID(), $deptSlug);
    }
}

?>
