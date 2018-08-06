<?
// WP List Table implementation to dispaly department taxonomy items

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
class DepartmentsTable extends WP_List_Table {
    public function __construct() {
        parent::__construct(array(
                'singular'  =>'department',
                'plural'    => 'departments',
                'ajax'      => false,
        ));
    }

    function get_columns() {
        $columns = array(
                'cb'        => '<input type="checkbox" />',
                'deptName'  => 'Department',
                'deptID'    => 'ID',
                'deptSlug'  => 'Slug',
                'postCount' => 'Post count',
        );
        return $columns;
    }

    function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $this->get_department_data();
    }

    // Populate table with department information 
    function get_department_data() {
        $depts = get_departments();
        $deptData = array();
        foreach($depts as $dept) {
            $deptData[] = array(
                    'deptName'  => $dept->name,
                    'deptID'    => $dept->term_id,
                    'deptSlug'  => $dept->slug,
                    'postCount' => $dept->count, 
            );
        }
        return $deptData; 
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'deptID':
            case 'deptName':
            case 'deptSlug':
            case 'postCount':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
        }
    }

    // Checkbox column 
    function column_cb($item) {
        $optionName = 'cah_news_display_dept2';
        $optionValue = get_option($optionName); 
        return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" %3$s />',
			$optionName,  
            $item['deptID'],
            // checked('0', get_option($optionName, $item['deptID']))
            in_array($item['deptID'], $optionValue) ? 'checked' : ''
		);
    }

    // Add text content around table 
    function extra_tablenav($which) {
        if ($which == 'top') {
            echo '<h3>CAH Departments to display</h3>'; 
        }
        if ($which == 'bottom') {
            echo 'Current value: ' . get_option('cah_news_display_dept2') . '<br>'; 
            echo 'Current blog: ' . get_current_blog_id(); 
        }
    }
}

// Get the departments table and output the HTML 
function get_departments_table() {
    $deptTable = new DepartmentsTable(); 
    $deptTable->prepare_items();
    $deptTable->display(); 
}

?> 