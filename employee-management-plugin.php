<?php
/*
Plugin Name: CRS Employee Management Plugin
Plugin URI: https://github.com/crswebb/crs-employee-management-plugin
Description: A plugin for managing employees
Version: 1.0
Requires at least: 6.0
Requires PHP: 7.4
Author: Stefan Bergfeldt
Author URI: https://crswebb.se/
License: MIT
License URI: https://opensource.org/licenses/MIT
Text Domain: crs-employee-management
Domain Path: /languages
*/

add_theme_support('post-thumbnails');
function crs_employee_management_enqueue_scripts_and_styles()
{
    wp_enqueue_style('custom-admin-styles', plugins_url('/css/admin-styles.css', __FILE__));
    wp_enqueue_media();

    wp_enqueue_script('my-plugin-script', plugins_url('/js/my-plugin-script.js', __FILE__), array('jquery'), '1.0', true);
    // Localize the JavaScript file with necessary values
    wp_localize_script(
        'my-plugin-script',
        'myPluginData',
        array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
        )
    );
}
add_action('admin_enqueue_scripts', 'crs_employee_management_enqueue_scripts_and_styles');

function crs_employee_management_enqueue_styles()
{
    wp_enqueue_style('my-plugin-styles', plugins_url('/css/plugin-styles.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'crs_employee_management_enqueue_styles');

function crs_employee_management_load_textdomain() {
    load_plugin_textdomain( 'crs-employee-management', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}

add_action( 'plugins_loaded', 'crs_employee_management_load_textdomain' );

function register_employee_post_type()
{

    $labels = array(
        'name' => __('Employees', 'crs-employee-management'),
        'singular_name' => __('Employee', 'crs-employee-management'),
        'add_new' => __('Add New', 'crs-employee-management'),
        'add_new_item' => __('Add New Employee', 'crs-employee-management'),
        'edit_item' => __('Edit Employee', 'crs-employee-management'),
        'new_item' => __('New Employee', 'crs-employee-management'),
        'view_item' => __('View Employee', 'crs-employee-management'),
        'search_items' => __('Search Employees', 'crs-employee-management'),
        'not_found' => __('No Employees found', 'crs-employee-management'),
        'not_found_in_trash' => __('No Employees found in Trash', 'crs-employee-management'),
        'parent_item_colon' => __('Parent Employee:', 'crs-employee-management'),
        'menu_name' => __('Employees', 'crs-employee-management'),
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => false,
        'description' => __('Employees', 'crs-employee-management'),
        'taxonomies' => array('employee_category'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-businessman',
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'supports' => array('title', 'thumbnail'),
    );

    register_post_type('employee', $args);
}

add_action('init', 'register_employee_post_type');


// Add custom fields for employee
function employee_add_custom_fields()
{
    add_meta_box('employee_fields', __('Employee Fields', 'crs-employee-management'), 'employee_fields_callback', 'employee', 'normal', 'high');
}
add_action('add_meta_boxes', 'employee_add_custom_fields');

// Add a custom meta field for sorting order
function add_employee_sorting_order_meta()
{
    add_meta_box(
        'employee_sorting_order_meta',
        __('Employee Sorting Order', 'crs-employee-management'),
        'render_employee_sorting_order_meta_box',
        'employee',
        // Custom post type for employees
        'side',
        // Meta box position
        'default' // Meta box priority
    );
}

function render_employee_sorting_order_meta_box($post)
{
    // Render the meta box HTML with an input field for sorting order
    $sorting_order = get_post_meta($post->ID, 'employee_sorting_order', true);
    echo '<input type="number" name="employee_sorting_order" value="' . esc_attr($sorting_order) . '">';
}

add_action('add_meta_boxes', 'add_employee_sorting_order_meta');

// Save the sorting order when updating employee posts
function save_employee_sorting_order_meta($post_id)
{
    if (isset($_POST['employee_sorting_order'])) {
        $sorting_order = sanitize_text_field($_POST['employee_sorting_order']);
        update_post_meta($post_id, 'employee_sorting_order', $sorting_order);
    }
}

add_action('save_post_employee', 'save_employee_sorting_order_meta');

// Modify the query to retrieve sorted employees
function modify_employee_query($query)
{
    if ($query->get('post_type') === 'employee') {
        $query->set('meta_key', 'employee_sorting_order');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'ASC');
    }
}

add_action('pre_get_posts', 'modify_employee_query');

function employee_fields_callback($post)
{
    wp_nonce_field(basename(__FILE__), 'employee_fields_nonce');
    $employee_email = get_post_meta($post->ID, 'employee_email', true);
    $employee_phone = get_post_meta($post->ID, 'employee_phone', true);
    $employee_description = get_post_meta($post->ID, 'employee_description', true);
    $employee_title = get_post_meta($post->ID, 'employee_title', true);

    ?>
    <div>
        <label for="employee_title"><?php _e('Title:', 'crs-employee-management'); ?></label>
        <input type="text" id="employee_title" name="employee_title" value="<?php echo esc_attr($employee_title); ?>">
    </div>
    <div>
        <label for="employee_email"><?php _e('E-mail:', 'crs-employee-management'); ?></label>
        <input type="email" id="employee_email" name="employee_email" value="<?php echo esc_attr($employee_email); ?>">
    </div>
    <div>
        <label for="employee_phone"><?php _e('Phone:', 'crs-employee-management'); ?></label>
        <input type="text" id="employee_phone" name="employee_phone" value="<?php echo esc_attr($employee_phone); ?>">
    </div>
    <div>
        <label for="employee_description"><?php _e('Short description', 'crs-employee-management'); ?></label>
        <textarea id="employee_description" name="employee_description"><?php echo esc_textarea($employee_description); ?></textarea>
    </div>
    <?php
}

// Save custom fields data
function employee_save_custom_fields($post_id)
{
    if (isset($_POST['employee_email'])) {
        update_post_meta($post_id, 'employee_email', sanitize_text_field($_POST['employee_email']));
    }
    if (isset($_POST['employee_phone'])) {
        update_post_meta($post_id, 'employee_phone', sanitize_text_field($_POST['employee_phone']));
    }
    if (isset($_POST['employee_description'])) {
        update_post_meta($post_id, 'employee_description', sanitize_textarea_field($_POST['employee_description']));
    }
    if (isset($_POST['employee_title'])) {
        update_post_meta($post_id, 'employee_title', sanitize_text_field($_POST['employee_title']));
    }
}
add_action('save_post_employee', 'employee_save_custom_fields');

function register_employee_taxonomy()
{

    $labels = array(
        'name' => __('Employee Categories', 'crs-employee-management'),
        'singular_name' => __('Employee Category', 'crs-employee-management'),
        'search_items' => __('Search Employee Categories', 'crs-employee-management'),
        'all_items' => __('All Employee Categories', 'crs-employee-management'),
        'parent_item' => __('Parent Employee Category', 'crs-employee-management'),
        'parent_item_colon' => __('Parent Employee Category:', 'crs-employee-management'),
        'edit_item' => __('Edit Employee Category', 'crs-employee-management'),
        'update_item' => __('Update Employee Category', 'crs-employee-management'),
        'add_new_item' => __('Add New Employee Category', 'crs-employee-management'),
        'new_item_name' => __('New Employee Category Name', 'crs-employee-management'),
        'menu_name' => __('Employee Categories', 'crs-employee-management'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'show_in_nav_menus' => true,
        'show_admin_column' => true,
        'hierarchical' => true,
        'show_tagcloud' => true,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'employee-category'),
    );

    register_taxonomy('employee_category', array('employee'), $args);
}
add_action('init', 'register_employee_taxonomy', 0);

function display_all_employees()
{
    ob_start();
    $args = array(
        'post_type' => 'employee',
        'posts_per_page' => -1
    );
    $employees = new WP_Query($args);
    if ($employees->have_posts()) {
        echo '<section class="employees">';
        while ($employees->have_posts()) {
            $employees->the_post();
            $template_path = plugin_dir_path(__FILE__) . 'employee-template.php';
            include($template_path);
        }
        echo '</section>';
        wp_reset_postdata();
    }
    return ob_get_clean();
}
add_shortcode('all_employees', 'display_all_employees');

function display_category_employees($atts)
{
    ob_start();
    $args = array(
        'post_type' => 'employee',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'employee_category',
                'field' => 'slug',
                'terms' => $atts['category']
            )
        )
    );
    $employees = new WP_Query($args);
    if ($employees->have_posts()) {
        echo '<section class="employees">';
        while ($employees->have_posts()) {
            $employees->the_post();
            $template_path = plugin_dir_path(__FILE__) . 'employee-template.php';
            include($template_path);
        }
        echo '</section>';
        wp_reset_postdata();
    }
    return ob_get_clean();
}

add_shortcode('category_employees', 'display_category_employees');

function display_single_employee($atts)
{
    ob_start();
    $args = array(
        'post_type' => 'employee',
        'p' => $atts['id']
    );
    $employee = new WP_Query($args);
    if ($employee->have_posts()) {
        while ($employee->have_posts()) {
            $employee->the_post();
            echo '<h2>' . get_the_title() . '</h2>';
            echo '<div>' . get_the_content() . '</div>';
        }
        wp_reset_postdata();
    }
    return ob_get_clean();
}
add_shortcode('single_employee', 'display_single_employee');
?>