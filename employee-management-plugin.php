<?php
/*
Plugin Name: CRS Employee Management Plugin
Plugin URI: https://github.com/crswebb/crs-employee-management-plugin
Description: A plugin for managing employees
Version: 1.0.0
Requires at least: 6.0
Requires PHP: 7.4
Author: Stefan Bergfeldt
Author URI: https://crswebb.se/
License: MIT
License URI: https://opensource.org/licenses/MIT
Text Domain: crs-employee-management-plugin
Domain Path: /languages
*/

defined('ABSPATH') || exit;

// Plugin version constant, used for asset cache-busting and the migration.
define('CRS_EMPLOYEE_MANAGEMENT_VERSION', '1.0.0');

function crs_employee_management_enqueue_scripts_and_styles()
{
    wp_enqueue_style(
        'custom-admin-styles',
        plugins_url('/css/admin-styles.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'css/admin-styles.css')
    );
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'crs_employee_management_enqueue_scripts_and_styles');

function crs_employee_management_enqueue_styles()
{
    wp_enqueue_style(
        'my-plugin-styles',
        plugins_url('/css/plugin-styles.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'css/plugin-styles.css')
    );
}
add_action('wp_enqueue_scripts', 'crs_employee_management_enqueue_styles');

// Translations load automatically for WordPress.org-hosted plugins (and from
// the bundled /languages folder via the Domain Path header on WordPress 6.7+),
// so no load_plugin_textdomain() call is needed.

function crs_register_employee_post_type()
{

    $labels = array(
        'name' => __('Employees', 'crs-employee-management-plugin'),
        'singular_name' => __('Employee', 'crs-employee-management-plugin'),
        'add_new' => __('Add New', 'crs-employee-management-plugin'),
        'add_new_item' => __('Add New Employee', 'crs-employee-management-plugin'),
        'edit_item' => __('Edit Employee', 'crs-employee-management-plugin'),
        'new_item' => __('New Employee', 'crs-employee-management-plugin'),
        'view_item' => __('View Employee', 'crs-employee-management-plugin'),
        'search_items' => __('Search Employees', 'crs-employee-management-plugin'),
        'not_found' => __('No Employees found', 'crs-employee-management-plugin'),
        'not_found_in_trash' => __('No Employees found in Trash', 'crs-employee-management-plugin'),
        'parent_item_colon' => __('Parent Employee:', 'crs-employee-management-plugin'),
        'menu_name' => __('Employees', 'crs-employee-management-plugin'),
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => false,
        'description' => __('Employees', 'crs-employee-management-plugin'),
        'taxonomies' => array('crs_employee_category'),
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

    register_post_type('crs_employee', $args);
}

add_action('init', 'crs_register_employee_post_type');


// Add custom fields for employee
function crs_employee_add_custom_fields()
{
    add_meta_box('employee_fields', __('Employee Fields', 'crs-employee-management-plugin'), 'crs_employee_fields_callback', 'crs_employee', 'normal', 'high');
}
add_action('add_meta_boxes', 'crs_employee_add_custom_fields');

// Add a custom meta field for sorting order
function crs_add_employee_sorting_order_meta()
{
    add_meta_box(
        'employee_sorting_order_meta',
        __('Employee Sorting Order', 'crs-employee-management-plugin'),
        'crs_render_employee_sorting_order_meta_box',
        'crs_employee',
        // Custom post type for employees
        'side',
        // Meta box position
        'default' // Meta box priority
    );
}

function crs_render_employee_sorting_order_meta_box($post)
{
    // Nonce field so the save handler can verify the request came from this form.
    wp_nonce_field(basename(__FILE__), 'employee_sorting_order_nonce');
    // Render the meta box HTML with an input field for sorting order
    $sorting_order = get_post_meta($post->ID, 'crs_employee_sorting_order', true);
    echo '<input type="number" name="employee_sorting_order" value="' . esc_attr($sorting_order) . '">';
}

add_action('add_meta_boxes', 'crs_add_employee_sorting_order_meta');

// Save the sorting order when updating employee posts
function crs_save_employee_sorting_order_meta($post_id)
{
    // Bail during autosave: no meta to persist and the nonce is not present.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Verify the nonce emitted by the meta box render callback.
    if (!isset($_POST['employee_sorting_order_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['employee_sorting_order_nonce'])), basename(__FILE__))) {
        return;
    }

    // Ensure the current user is allowed to edit this post.
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['employee_sorting_order'])) {
        $sorting_order = sanitize_text_field(wp_unslash($_POST['employee_sorting_order']));
        update_post_meta($post_id, 'crs_employee_sorting_order', $sorting_order);
    }
}

add_action('save_post_crs_employee', 'crs_save_employee_sorting_order_meta');

// Modify the query to retrieve sorted employees
function crs_modify_employee_query($query)
{
    // Only touch the main front-end query for the employee post type.
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->get('post_type') === 'crs_employee') {
        $query->set('meta_key', 'crs_employee_sorting_order');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'ASC');
    }
}

add_action('pre_get_posts', 'crs_modify_employee_query');

function crs_employee_fields_callback($post)
{
    wp_nonce_field(basename(__FILE__), 'employee_fields_nonce');
    $employee_email = get_post_meta($post->ID, 'crs_employee_email', true);
    $employee_phone = get_post_meta($post->ID, 'crs_employee_phone', true);
    $employee_description = get_post_meta($post->ID, 'crs_employee_description', true);
    $employee_title = get_post_meta($post->ID, 'crs_employee_title', true);

    ?>
    <div>
        <label for="employee_title"><?php esc_html_e('Title:', 'crs-employee-management-plugin'); ?></label>
        <input type="text" id="employee_title" name="employee_title" value="<?php echo esc_attr($employee_title); ?>">
    </div>
    <div>
        <label for="employee_email"><?php esc_html_e('E-mail:', 'crs-employee-management-plugin'); ?></label>
        <input type="email" id="employee_email" name="employee_email" value="<?php echo esc_attr($employee_email); ?>">
    </div>
    <div>
        <label for="employee_phone"><?php esc_html_e('Phone:', 'crs-employee-management-plugin'); ?></label>
        <input type="text" id="employee_phone" name="employee_phone" value="<?php echo esc_attr($employee_phone); ?>">
    </div>
    <div>
        <label for="employee_description"><?php esc_html_e('Short description', 'crs-employee-management-plugin'); ?></label>
        <textarea id="employee_description" name="employee_description"><?php echo esc_textarea($employee_description); ?></textarea>
    </div>
    <?php
}

// Save custom fields data
function crs_employee_save_custom_fields($post_id)
{
    // Bail during autosave: no meta to persist and the nonce is not present.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Verify the nonce emitted by the meta box render callback.
    if (!isset($_POST['employee_fields_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['employee_fields_nonce'])), basename(__FILE__))) {
        return;
    }

    // Ensure the current user is allowed to edit this post.
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['employee_email'])) {
        update_post_meta($post_id, 'crs_employee_email', sanitize_text_field(wp_unslash($_POST['employee_email'])));
    }
    if (isset($_POST['employee_phone'])) {
        update_post_meta($post_id, 'crs_employee_phone', sanitize_text_field(wp_unslash($_POST['employee_phone'])));
    }
    if (isset($_POST['employee_description'])) {
        update_post_meta($post_id, 'crs_employee_description', sanitize_textarea_field(wp_unslash($_POST['employee_description'])));
    }
    if (isset($_POST['employee_title'])) {
        update_post_meta($post_id, 'crs_employee_title', sanitize_text_field(wp_unslash($_POST['employee_title'])));
    }
}
add_action('save_post_crs_employee', 'crs_employee_save_custom_fields');

function crs_register_employee_taxonomy()
{

    $labels = array(
        'name' => __('Employee Categories', 'crs-employee-management-plugin'),
        'singular_name' => __('Employee Category', 'crs-employee-management-plugin'),
        'search_items' => __('Search Employee Categories', 'crs-employee-management-plugin'),
        'all_items' => __('All Employee Categories', 'crs-employee-management-plugin'),
        'parent_item' => __('Parent Employee Category', 'crs-employee-management-plugin'),
        'parent_item_colon' => __('Parent Employee Category:', 'crs-employee-management-plugin'),
        'edit_item' => __('Edit Employee Category', 'crs-employee-management-plugin'),
        'update_item' => __('Update Employee Category', 'crs-employee-management-plugin'),
        'add_new_item' => __('Add New Employee Category', 'crs-employee-management-plugin'),
        'new_item_name' => __('New Employee Category Name', 'crs-employee-management-plugin'),
        'menu_name' => __('Employee Categories', 'crs-employee-management-plugin'),
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

    register_taxonomy('crs_employee_category', array('crs_employee'), $args);
}
add_action('init', 'crs_register_employee_taxonomy', 0);

/**
 * One-time, idempotent data migration.
 *
 * Renames the legacy post type, taxonomy and meta keys to their crs_ prefixed
 * equivalents so existing content survives the rename. Keyed on the
 * 'crs_employee_db_version' option so it runs at most once per version bump.
 * Runs on admin_init, after the post type and taxonomy are registered on init.
 */
function crs_employee_run_migration()
{
    // Current data schema version. Bump this if a future migration is needed.
    $target_version = '1';

    // Already migrated to (or past) this version: nothing to do.
    if (get_option('crs_employee_db_version') === $target_version) {
        return;
    }

    global $wpdb;

    // Rename the post type on existing posts: 'employee' -> 'crs_employee'.
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-time schema migration; a direct UPDATE is required to rename legacy rows and caching does not apply.
    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->posts} SET post_type = %s WHERE post_type = %s",
            'crs_employee',
            'employee'
        )
    );

    // Rename the taxonomy on existing term relationships:
    // 'employee_category' -> 'crs_employee_category'.
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-time schema migration; a direct UPDATE is required to rename legacy rows and caching does not apply.
    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->term_taxonomy} SET taxonomy = %s WHERE taxonomy = %s",
            'crs_employee_category',
            'employee_category'
        )
    );

    // Rename each meta key: 'employee_*' -> 'crs_employee_*'.
    $meta_key_map = array(
        'employee_email' => 'crs_employee_email',
        'employee_phone' => 'crs_employee_phone',
        'employee_description' => 'crs_employee_description',
        'employee_title' => 'crs_employee_title',
        'employee_sorting_order' => 'crs_employee_sorting_order',
    );
    foreach ($meta_key_map as $old_key => $new_key) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-time schema migration; a direct UPDATE is required to rename legacy meta keys and caching does not apply.
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->postmeta} SET meta_key = %s WHERE meta_key = %s",
                $new_key,
                $old_key
            )
        );
    }

    // Rewrite rules reference the post type/taxonomy slugs, so flush once.
    flush_rewrite_rules();

    // Record that the migration has run so it never repeats.
    update_option('crs_employee_db_version', $target_version);
}
add_action('admin_init', 'crs_employee_run_migration');

function crs_display_all_employees()
{
    ob_start();
    $args = array(
        'post_type' => 'crs_employee',
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
add_shortcode('crs_all_employees', 'crs_display_all_employees');
// Legacy alias kept so existing page/post content keeps working.
add_shortcode('all_employees', 'crs_display_all_employees');

function crs_display_category_employees($atts)
{
    ob_start();
    $args = array(
        'post_type' => 'crs_employee',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'crs_employee_category',
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

add_shortcode('crs_category_employees', 'crs_display_category_employees');
// Legacy alias kept so existing page/post content keeps working.
add_shortcode('category_employees', 'crs_display_category_employees');

function crs_display_single_employee($atts)
{
    ob_start();
    $args = array(
        'post_type' => 'crs_employee',
        'p' => $atts['id']
    );
    $employee = new WP_Query($args);
    if ($employee->have_posts()) {
        while ($employee->have_posts()) {
            $employee->the_post();
            echo '<h2>' . esc_html( get_the_title() ) . '</h2>';
            echo '<div>' . wp_kses_post( apply_filters( 'the_content', get_the_content() ) ) . '</div>';
        }
        wp_reset_postdata();
    }
    return ob_get_clean();
}
add_shortcode('crs_single_employee', 'crs_display_single_employee');
// Legacy alias kept so existing page/post content keeps working.
add_shortcode('single_employee', 'crs_display_single_employee');
