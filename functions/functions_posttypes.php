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
        'show_in_rest' => TRUE,
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
    register_post_meta( 'appointment', 'cancel_token', array(
        'type' => 'string',
        'single' => true,
        'show_in_rest' => true,
    ) );
    register_post_meta( 'appointment', 'has_been_reminded', array(
        'type' => 'boolean',
        'single' => true,
        'show_in_rest' => true,
    ) );

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