<?php
//AJAX

add_action('wp_ajax_make_appointment', 'make_appointment');
add_action('wp_ajax_nopriv_make_appointment', 'make_appointment');

function make_appointment()
{
    $barberID = $_POST['barber_id'] ?? false;
    if (!$barberID) {
        wp_send_json([
            'message' => "ID Barbera nebolo nastavené."
        ], 403);
    }

    $appointment = js_json_decode($_POST['appointment']);
    $type = $appointment['type'] ?? false;
    if (!$type) {
        wp_send_json([
            'message' => "Typ termínu nebol nastavený."
        ], 403);
    }

    insert_appointment($appointment, $barberID);

    wp_send_json([], 200);
}

function insert_appointment($data, $barber) {
    $appointment = [
        'post_status'   => 'publish',
        'post_type'     => 'appointment',
    ];

    $post_id = wp_insert_post($appointment);
    $type = $data['type'];

    // Must-update fields: Type + Barber
    update_field('appointment_type', $type, $post_id);
    update_field('appointment_barber', $barber, $post_id);

    // Must-update datetime
    $datetimeFrom = new DateTime($data['datetime']['from']);
    $datetimeTo = new DateTime($data['datetime']['to']);
    update_field('appointment_datetime_from', $datetimeFrom->format('Y-m-d H:i:s'), $post_id);
    update_field('appointment_datetime_to', $datetimeTo->format('Y-m-d H:i:s'), $post_id);

    if($type == "appointment") {
        //Service
        update_field('appointment_service', $data['serviceID'], $post_id);

        //Customer
        update_field('appointment_customer_name', $data['customer']['name'], $post_id);
        update_field('appointment_customer_email', $data['customer']['email'], $post_id);
        update_field('appointment_customer_phone', $data['customer']['phone'], $post_id);

        //Note
        update_field('appointment_note', $data['customer']['phone'], $post_id);
    }
}



add_action('wp_ajax_get_appointments', 'get_appointments');
add_action('wp_ajax_nopriv_get_appointments', 'get_appointments');

function get_appointments()
{
    $barberID = $_POST['barber_id'] ?? false;
    if (!$barberID) {
        wp_send_json([
            'message' => "ID Barbera nebolo nastavené."
        ], 403);
    }

    $datetime = $_POST['datetime'] ?? false;
    if (!$datetime) {
        wp_send_json([
            'message' => "Nebol zadaný datetime."
        ], 403);
    }

    $datetime = new DateTime($datetime);
    var_dump($datetime->format("W"));

    wp_send_json([], 200);
}
