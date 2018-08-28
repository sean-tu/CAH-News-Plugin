<?php

// Custom admin columns in news posts display
add_filter('manage_news_posts_columns', 'cah_news_set_columns');
function cah_news_set_columns($columns) {
$columns['department'] = 'Department';
return $columns;
}

add_action('manage_news_posts_custom_column', 'cah_news_custom_column', 10, 2);
function cah_news_custom_column($column, $post_id) {
switch($column) {
case 'department':
$depts = get_the_term_list($post_id, 'dept', '', ',', '');
if (is_string($depts))
echo $depts;
else
break;
}
}

// Add option to filter posts in admin list
function cah_news_admin_filter_posts_select() {
if (!isset($_GET['post_type'])) {
return;
}
if ($_GET['post_type'] = 'news') {
$field = 'ADMIN_FILTER_FIELD_VALUE';
echo sprintf('<select name="%s">', $field);
    echo '<option value="">All Departments</option>';
    $current = $_GET[$field];
    foreach(get_departments() as $dept) {
    $selected = $dept->term_id == $current ? 'selected=selected' : '';
    echo sprintf('<option value="%d" %s>%s</option>', $dept->term_id, $selected, $dept->name);
    }
    echo '</select>';

}
}
add_action('restrict_manage_posts', 'cah_news_admin_filter_posts_select');
add_filter('parse_query', 'cah_news_admin_filter_posts');

function cah_news_admin_filter_posts($query) {
if (!is_admin() || !isset($_GET['post_type'])) {
return $query;
}
global $pagenow;
$field = 'ADMIN_FILTER_FIELD_VALUE';
if ($_GET['post_type'] == 'news' && $pagenow == 'edit.php' && isset($_GET[$field]) && $_GET[$field] != '') {
$query->set('tax_query', array(
array(
'taxonomy' => 'dept',
'field' => 'term_id',
'terms' => $_GET[$field],
)
));
}
return $query;
}

?>