<?php

// CREATING CUSTOM POST TYPES

function create_post_types()
{


    /*
     * SLUŽBY / SERVICES ---- START
     */

    $labels = array(
        'name' => __('Služby', 'barbers'),
        'singular_name' => __('Služba', 'barbers'),
        'add_new' => __('Pridať novú službu', 'barbers'),
        'add_new_item' => __('Pridať novú službu', 'barbers'),
        'edit_item' => __('Upraviť službu', 'barbers'),
        'new_item' => __('Nová služba', 'barbers'),
        'view_item' => __('Otvoriť službu', 'barbers'),
        'search_items' => __('Hľadať službu', 'barbers'),
        'not_found' => __('Služba nebolo nájdená', 'barbers'),
        'not_found_in_trash' => __('Služba nebola nájdená v koši', 'barbers')
    );

    $supports = array(
        'title',
    );

    $args = array(
        'labels' => $labels,
        'supports' => $supports,
        'public' => TRUE,
        'has_archive' => FALSE,
        'show_in_rest' => FALSE,
        'taxonomy' => [],
        'menu_icon' => 'dashicons-admin-tools',
        'rewrite' => ['slug' => 'service'],
    );

    register_post_type('service', $args);


    // Add custom columns
    function custom_services_columns($columns)
    {
        unset($columns['date']);
        $columns['description'] = 'Popis';
        $columns['price'] = 'Cena';
        $columns['date'] = 'Dátum';
        return $columns;
    }

    add_filter('manage_service_posts_columns', 'custom_services_columns');

    // Populate custom columns
    function custom_services_column_data($column, $post_id)
    {
        switch ($column) {
            case 'price':
                echo get_field('serv-price', $post_id) . "€";
                break;
            case 'description':
                echo get_field('serv-description', $post_id);
                break;
        }
    }

    add_action('manage_service_posts_custom_column', 'custom_services_column_data', 10, 2);


    /*
     * SLUZBY / SERVICES ---- END
     */


    //-----------------------------------------------------------------------------------------


    /*
     * TERMÍNY / APPOINTMENTS ---- START
     */

    $labels = array(
        'name' => __('Termíny', 'barbers'),
        'singular_name' => __('Termín', 'barbers'),
        'add_new' => __('Pridať nový termín', 'barbers'),
        'add_new_item' => __('Pridať nový termín', 'barbers'),
        'edit_item' => __('Upraviť termín', 'barbers'),
        'new_item' => __('Nový termín', 'barbers'),
        'view_item' => __('Otvoriť termín', 'barbers'),
        'search_items' => __('Hľadať termín', 'barbers'),
        'not_found' => __('Termín nebol nájdený', 'barbers'),
        'not_found_in_trash' => __('Termín nebol nájdený v koši', 'barbers')
    );

    $supports = [];

    $args = array(
        'labels' => $labels,
        'supports' => $supports,
        'public' => TRUE,
        'has_archive' => FALSE,
        'show_in_rest' => TRUE,
        'taxonomy' => [],
        'menu_icon' => 'dashicons-calendar-alt',
        'rewrite' => ['slug' => 'appointment'],
    );

    register_post_type('appointment', $args);
    register_post_meta('appointment', 'cancel_token', array(
        'type' => 'string',
        'single' => true,
        'show_in_rest' => true,
    ));
    register_post_meta('appointment', 'has_been_reminded', array(
        'type' => 'boolean',
        'single' => true,
        'show_in_rest' => true,
    ));

    //
    add_action('load-edit.php', function () {
        $screen = get_current_screen();
        // Only edit post screen:
        if ('edit-appointment' === $screen->id) {
            add_action('all_admin_notices', function () {
                ob_start();
                include(get_template_directory() . "/template_parts/admin/calendar.php");
                echo ob_get_clean();
            });
        }
    });


    /*
     * TERMÍNY / APPOINTMENTS ---- END
     */


    //-----------------------------------------------------------------------------------------


    /*
     * ZÁKAZNÍCI / CUSTOMERS ---- START
     */

    $labels = array(
        'name' => __('Zákazníci', 'barbers'),
        'singular_name' => __('Zákazník', 'barbers'),
        'add_new' => __('Pridať nového zákazníka', 'barbers'),
        'add_new_item' => __('Pridať nového zákazníka', 'barbers'),
        'edit_item' => __('Upraviť zákazníka', 'barbers'),
        'new_item' => __('Nový zákazník', 'barbers'),
        'view_item' => __('Otvoriť zákazníka', 'barbers'),
        'search_items' => __('Hľadať zákazníka', 'barbers'),
        'not_found' => __('Zákazník nebol nájdený', 'barbers'),
        'not_found_in_trash' => __('Zákazník nebol nájdený v koši', 'barbers')
    );

    $supports = [];

    $args = array(
        'labels' => $labels,
        'supports' => $supports,
        'public' => TRUE,
        'has_archive' => FALSE,
        'show_in_rest' => FALSE,
        'taxonomy' => [],
        'menu_icon' => 'dashicons-admin-users',
        'rewrite' => ['slug' => 'customer'],
    );

    register_post_type('customer', $args);

    // Add custom columns
    function custom_customers_columns($columns)
    {
        unset($columns['title']);
        unset($columns['date']);
        $columns['name'] = 'Meno a priezvisko';
        $columns['email'] = 'Email';
        $columns['phone'] = 'Telefón';
        $columns['last_appointment'] = 'Dátum posledného termínu';
        return $columns;
    }

    add_filter('manage_customer_posts_columns', 'custom_customers_columns');

    // Populate custom columns
    function custom_customers_column_data($column, $post_id)
    {
        switch ($column) {
            case 'name':
                $name = get_field('cust-name', $post_id);
                echo !empty($name) ? "<a style='font-weight: bold' href='" . get_edit_post_link($post_id) . "'>" . $name . "</a>" : "Bez mena a priezviska.";
                break;
            case 'email':
                $email = get_field('cust-email', $post_id);
                echo !empty($email) ? $email : "-";
                break;
            case 'phone':
                $phone = get_field('cust-phone', $post_id);
                echo !empty($phone) ? $phone : "-";
                break;
            case 'last_appointment':
                $date = get_field('cust-last_appointment', $post_id);
                echo !empty($date) ? $date : "-";
                break;
        }
    }

    add_action('manage_customer_posts_custom_column', 'custom_customers_column_data', 10, 2);


    function hide_title_slug_permalink_for_customer_post_type() {
        global $post;
        if ( 'customer' === $post->post_type ) {
            ?>
            <style type="text/css">
                #titlediv,
                #edit-slug-box,
                #sample-permalink,
                #slugdiv,
                #post-body-content {
                    display: none !important;
                }
            </style>
            <?php
        }
    }
    add_action( 'edit_form_after_title', 'hide_title_slug_permalink_for_customer_post_type' );



    add_filter( 'posts_join', 'customer_search_join' );
    function customer_search_join( $join ) {
        global $pagenow, $wpdb;

        // Only apply filter when performing a search on edit page of the "customer" post type.
        if ( is_admin() && 'edit.php' === $pagenow && 'customer' === $_GET['post_type'] && ! empty( $_GET['s'] ) ) {
            $join .= 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
        }
        return $join;
    }

    add_filter( 'posts_where', 'customer_search_where' );
    function customer_search_where( $where ) {
        global $pagenow, $wpdb;

        // Only apply filter when performing a search on edit page of the "customer" post type.
        if ( is_admin() && 'edit.php' === $pagenow && 'customer' === $_GET['post_type'] && ! empty( $_GET['s'] ) ) {
            $where = preg_replace(
                "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
                "(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)", $where );
            $where .= " GROUP BY {$wpdb->posts}.id"; // Solves duplicated results
        }
        return $where;
    }


    /*
     * ZÁKAZNÍCI / CUSTOMERS ---- END
     */


    /**
     * REGISTER SUBMITTED FORMS META
     */
    /*$args = [
        'type' => 'string',
    ];
    register_post_meta('form', 'submitted_forms', $args);*/


    /**
     * REGISTER META BOX.
     */
    /*add_action( 'add_meta_boxes', 'sw_register_submitted_forms_meta_box' );
    function sw_register_submitted_forms_meta_box() {
        add_meta_box( 'sw-submitted_forms', __( 'Odoslané formuláre', 'barbers' ), 'sw_print_submitted_forms', 'form' );
    }
    function sw_print_submitted_forms( $submittedForm ) {
        get_template_part("template_parts/admin/submitted_form-rows", "", compact("rows"));
    }*/

    /*
     * FORMULÁRE ---- END
     */


    //-----------------------------------------------------------------------------------------
}

add_action('init', 'create_post_types');



add_action("admin_init", "import_custs");

function import_custs() {

    if(!isset($_GET['lol'])) return;

    $args = array(
        'post_type' => 'appointment',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_key' => 'appointment_datetime_from',
        'orderby' => 'meta_value',
        'order' => 'DESC',
    );

    $appointment_query = new WP_Query( $args );

    while ( $appointment_query->have_posts() ) {
        $appointment_query->the_post();

        // Get the appointment_customer_email field value
        $customer_email = get_field( 'appointment_customer_email' );
        if(empty($customer_email)) continue;

        $customer_name = get_field( 'appointment_customer_name' );
        $customer_phone = get_field( 'appointment_customer_phone' );
        $customer_date = date("Y-m-d H:i:s", strtotime(get_field( 'appointment_datetime_from' )));

        // Check if a customer with this email already exists
        $existing_customer_query = new WP_Query( array(
            'post_type' => 'customer',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'cust-email',
                    'value' => $customer_email,
                    'compare' => '=',
                ),
            ),
        ) );

        // If no existing customer, create a new one
        if ( ! $existing_customer_query->have_posts() ) {
            $new_customer_post = array(
                'post_type' => 'customer',
                'post_status' => 'publish',
            );

            $new_customer_id = wp_insert_post( $new_customer_post );

            update_field("cust-email", $customer_email, $new_customer_id);
            update_field("cust-name", $customer_name, $new_customer_id);
            update_field("cust-phone", $customer_phone, $new_customer_id);
            update_field("cust-last_appointment", $customer_date, $new_customer_id);
        }

        // Reset post data
        wp_reset_postdata();
    }
}