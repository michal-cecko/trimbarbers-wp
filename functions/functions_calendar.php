<?php

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
    $notify = isset($_POST['notify']) && $_POST['notify'] !== "false";
    $appointment = js_json_decode($_POST['appointment']);
    $type = $appointment['type'] ?? false;
    if (!$type) {
        wp_send_json([
            'message' => "Typ termínu nebol nastavený."
        ], 403);
    }

    $args = [
        'post_status' => 'publish',
        'post_type' => 'appointment',
    ];
    $postID = wp_insert_post($args);

    $cancelToken = getRandomString(12);
    update_post_meta($postID, "cancel_token", $cancelToken);

    update_appointment_fields($appointment, $barberID, $postID, $type);

    $service = null;
    if ($type !== "free") {
        $service = get_the_title($appointment['serviceID']);
        if ($notify) {
            $receiver = $appointment['customer']['email'];
            if (!empty($receiver)) reservation_notification($receiver, "new", $postID);
        }
    }

    wp_send_json(["id" => $postID, 'service' => $service], 200);
}

add_action('wp_ajax_edit_appointment', 'edit_appointment');
function edit_appointment()
{
    $barberID = $_POST['barberID'] ?? false;
    if (!$barberID) {
        wp_send_json([
            'message' => "ID Barbera nebolo nastavené."
        ], 403);
    }

    $appointment = js_json_decode($_POST['appointment']);
    $notify = isset($_POST['notify']) && $_POST['notify'] !== "false";

    $type = $appointment['type'] ?? false;
    if (!$type) {
        wp_send_json([
            'message' => "Typ termínu nebol nastavený."
        ], 403);
    }

    $id = $_POST['id'] ?? false;
    if (!$id) {
        wp_send_json([
            'message' => "ID termínu nebolo nastavené."
        ], 403);
    }

    update_appointment_fields($appointment, $barberID, $id, $type);

    $service = null;
    if ($type !== "free") {
        $service = get_the_title($appointment['serviceID']);
        if ($notify) {
            $receiver = get_field('appointment_customer_email', $id);
            if (!empty($receiver)) reservation_notification($receiver, "update", $id);
        }
    }

    wp_send_json(["id" => $id, 'service' => $service], 200);
}

function update_appointment_fields($data, $barber, $post_id, $type)
{
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

    if ($type == "appointment") {
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
function remove_appointment()
{
    $id = $_POST['id'] ?? false;
    checkAppointment($id);
    $notify = isset($_POST['notify']) && $_POST['notify'] !== "false";
    cancel_appointment($id, $notify);
    wp_send_json([], 200);
}

function cancel_appointment($id, $notify = false)
{
    $type = get_field("appointment_type", $id);
    if ($type !== "free" && $notify) {
        $receiver = get_field('appointment_customer_email', $id);
        if (!empty($receiver)) reservation_notification($receiver, "cancel", $id);
    }
    return wp_delete_post($id, true);
}


add_action('wp_ajax_move_appointment', 'move_appointment');

function move_appointment()
{
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

function checkAppointment($id)
{
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
    if ($dateRange === "timeGridWeek") {
        $startEndOfWeek = getStartAndEndDateOfWeek($datetime->getTimestamp());
        $dateToFetchFrom = $startEndOfWeek['start'] . " 00:00:00";
        $dateToFetchTo = $startEndOfWeek['end'] . " 23:59:59";
    } //Day
    else {
        $dateToFetchFrom = $datetime->format("Y-m-d") . " 00:00:00";
        $dateToFetchTo = $datetime->format("Y-m-d") . " 23:59:59";
    }

    //Query the appointments
    $args = [
        'post_type' => 'appointment',
        'post_status' => 'published',
        'posts_per_page' => -1,
    ];
    $dateCondition = [
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
    ];
    if ($barberID > 0) {
        $metaQuery = [
            "relation" => "AND",
            [
                'key' => 'appointment_barber',
                'value' => $barberID,
                'compare' => '='
            ],
            $dateCondition
        ];
    } else {
        $metaQuery = $dateCondition;
    }
    $args['meta_query'] = $metaQuery;

    $appointments = new WP_Query($args);

    //Process the appoints to return
    $return = [];

    if (!$appointments->have_posts()) wp_send_json(["appointments" => []], 200);

    $fetchedBarbers = [];

    while ($appointments->have_posts()):
        $appointments->the_post();
        $id = get_the_ID();
        $type = get_field("appointment_type");
        $barberID = get_field("appointment_barber");

        if (!isset($fetchedBarbers[$barberID])) {
            $barber = get_user_by("ID", $barberID);
            $fetchedBarbers[$barber->ID] = $barber;
        } else {
            $barber = $fetchedBarbers[$barberID];
        }

        if (!$barber) continue;

        if ($type === "free") {
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


add_action('rest_api_init', 'register_rest_routes', 1);

function register_rest_routes()
{
    register_rest_route('api/v1', '/cancel-reservation/', array(
        'methods' => 'GET',
        'callback' => 'cancel_reservation_handler',
        'args' => array(
            't' => array(
                'required' => true,
                'type' => 'string',
            ),
            'i' => array(
                'required' => true,
                'type' => 'number',
            ),
        ),
    ));

    register_rest_route('api/v1', '/notify-customers/', array(
        'methods' => 'GET',
        'callback' => 'notify_customers',
        'args' => array(
            't' => array(
                'required' => true,
                'type' => 'string',
            ),
        ),
    ));
}

function cancel_reservation_handler($request)
{
    $token = $request->get_param('t');
    $id = $request->get_param('i');
    if (get_post_meta($id, "cancel_token", true) === $token) {
        cancel_appointment($id, true);
    }
    wp_redirect(home_url(). "?c=1");
    exit();
}

function notify_customers($request)
{
    ini_set("max_execution_time", 999999);
    $token = $request->get_param('t');
    if ($token === "***REMOVED-NOTIFY-TOKEN***") {
        $date = (new DateTime())->modify("+1 day");
        $args = [
            'post_type' => 'appointment',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                "relation" => "AND",
                [
                    'key' => 'appointment_datetime_from',
                    'value' => $date->format("Y-m-d H:i:s"),
                    'compare' => '<='
                ],
                [
                    'key' => 'appointment_type',
                    'value' => "appointment",
                    'compare' => '='
                ],
            ],
        ];
        $reservations = new WP_Query($args);
        if ($reservations->have_posts()) {
            while ($reservations->have_posts()) {
                $reservations->the_post();
                $id = get_the_ID();

                if(in_array(get_post_meta($id, 'has_been_reminded', true), [true, 1])) continue;

                $receiver = get_field("appointment_customer_email", $id);
                if (!empty($receiver)) reservation_notification($receiver, "notification", $id);
                update_post_meta($id, "has_been_reminded", true);
            }
            wp_reset_postdata();
        }
    }
    exit();
}

