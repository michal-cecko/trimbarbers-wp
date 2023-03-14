<?php
//AJAX

add_action('wp_ajax_make_appointment', 'make_appointment');
add_action('wp_ajax_nopriv_make_appointment', 'make_appointment');

function make_appointment()
{
    $barberID = $_POST['barberID'] ?? false;
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

    $post_id = insert_appointment($appointment, $barberID);

    $service = null;
    if($type !== "free") $service = get_the_title($appointment['serviceID']);

    wp_send_json(["id" => $post_id, 'service' => $service], 200);
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
    $datetimeFrom = new DateTime($data['datetime']['start']);
    $datetimeTo = new DateTime($data['datetime']['end']);

    update_field('appointment_datetime', [
        'from' => $datetimeFrom->format('Y-m-d H:i:s'),
        'to' => $datetimeTo->format('Y-m-d H:i:s'),
    ], $post_id);

    if($type == "appointment") {
        //Service
        update_field('appointment_service', $data['serviceID'], $post_id);

        //Customer
        update_field('appointment_customer', [
            'name' => $data['customer']['name'],
            'email' => $data['customer']['email'],
            'phone' => $data['customer']['phone'],
        ], $post_id);
    }

    //Note
    update_field('appointment_note', $data['note'], $post_id);

    return $post_id;
}

add_action('wp_ajax_remove_appointment', 'remove_appointment');

function remove_appointment() {
    $id = $_POST['id'] ?? false;
    checkAppointment($id);

    $status = !empty(wp_delete_post($id, true)) ? 200 : 403;

    wp_send_json([], $status);
}




add_action('wp_ajax_move_appointment', 'move_appointment');

function move_appointment() {
    $id = $_POST['id'] ?? false;
    $newStart = $_POST['newStart'] ?? false;
    $newEnd = $_POST['newEnd'] ?? false;

    checkAppointment($id);
    if (empty($newEnd) || empty($newStart)) {
        wp_send_json([
            'message' => "Dátum termínu nebol nastavený."
        ], 403);
    }

    $status = update_field('appointment_datetime', [
        'from' => $newStart,
        'to' => $newEnd,
    ], $id);

    wp_send_json([], $status);
}

function checkAppointment($id) {
    if (!$id) {
        wp_send_json([
            'message' => "ID termínu nebolo nastavené."
        ], 403);
    }

    if (get_post_type($id) !== 'appointment') {
        wp_send_json([
            'message' => "Nepovolená akcia."
        ], 403);
    }
}

add_action('wp_ajax_get_appointments', 'get_appointments');
add_action('wp_ajax_nopriv_get_appointments', 'get_appointments');

function get_appointments()
{
    $barberID = $_GET['barberID'] ?? false;
    $timestamp = $_GET['timestamp'] ?? false;
    $dateRange = $_GET['dateRange'] ?? false;
    if (empty($barberID) || empty($timestamp) || empty($dateRange)) {
        wp_send_json([
            'message' => "Niektorá z potrebných premenných nebola nastavená."
        ], 403);
    }

    $datetime = (new DateTime())->setTimestamp(floor($timestamp / 1000));
    $datetime->modify("+1 hour");

    //Week
    if($dateRange === "week") {
        $startEndOfWeek = getStartAndEndDateOfWeek($datetime->getTimestamp());
        $dateToFetchFrom = $startEndOfWeek['start'] . " 00:00:00";
        $dateToFetchTo = $startEndOfWeek['end'] . " 23:59:59";
    }
    //Day
    else {
        $dateToFetchFrom = $datetime->format("Y-m-d") . " 00:00:00";
        $dateToFetchTo = $datetime->format("Y-m-d") . " 23:59:59";
    }

    //Query the appointments
    $args = [
        'post_type' => 'appointment',
        'post_status' => 'published',
        'posts_per_page' => -1,
        'meta_query' => [
            "relation" => "OR",
            [
                'key' => 'appointment_datetime_from',
                'value' => [$dateToFetchFrom, $dateToFetchTo],
                'compare' => 'BETWEEN',
                'type' => 'DATETIME'
            ],
            [
                'key' => 'appointment_datetime_to',
                'value' => [$dateToFetchFrom, $dateToFetchTo],
                'compare' => 'BETWEEN',
                'type' => 'DATETIME'
            ]
        ]
    ];
    $appointments = new WP_Query($args);

    //Process the appoints to return
    $return = [];

    if(!$appointments->have_posts()) wp_send_json(["appointments" => []], 200);

    while($appointments->have_posts()):
        $appointments->the_post();
        $id = get_the_ID();
        $type = get_field("appointment_type");
        $barber = get_user_by("ID", get_field("appointment_barber"));

        if(!$barber) continue;

        if($type === "free") {
            $return[$id] = [
                'type' => $type,
                'barber' => $barber->display_name,
                'barberID' => $barber->ID,
                'note' => get_field("appointment_note"),
                'datetime' => get_field("appointment_datetime")
            ];
        } else {
            $service = get_field("appointment_service");
            $return[$id] = [
                'type' => $type,
                'barber' => $barber->display_name,
                'barberID' => $barber->ID,
                'serviceID' => $service->ID,
                'service' => get_the_title($service),
                'datetime' => get_field("appointment_datetime"),
                'customer' => get_field("appointment_customer"),
                'note' => get_field("appointment_note"),
            ];
        }
    endwhile;

    //Send JSON
    wp_send_json(["appointments" => $return], 200);
}
