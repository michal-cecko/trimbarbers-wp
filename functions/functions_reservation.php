<?php

add_action('wp_ajax_get_available_dates', 'get_available_dates');
add_action('wp_ajax_nopriv_get_available_dates', 'get_available_dates');

function get_available_dates()
{
    $barberID = $_GET['barberID'] ?? false;
    $serviceID = $_GET['serviceID'] ?? false;

    if ($barberID === false) {
        wp_send_json([
            'message' => "Nebol zadaný barber."
        ], 403);
    }
    if ($serviceID === false) {
        wp_send_json([
            'message' => "Nebola zadaná služba."
        ], 403);
    }
    $serviceDuration = get_field("serv-duration", $serviceID);
    if ($barberID < 1) {
        $barbers = get_users([
            'role' => 'barber',
        ]);
    }

    $args = [
        'post_type' => 'appointment',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ];
    $dateCond = [
        "relation" => "OR",
        [
            'key' => 'appointment_datetime_from',
            'value' => date("Ymd") . " 00:00:00",
            'compare' => '<=',
            'type' => 'DATETIME',
        ],
        [
            'key' => 'appointment_datetime_to',
            'value' => date("Ymd") . " 00:00:00",
            'compare' => '<=',
            'type' => 'DATETIME',
        ]
    ];
    if ($barberID > 0) {
        $args['meta_query'] = [
            'relation' => 'AND',
            [
                'key' => 'appointment_barber',
                'value' => $barberID,
                'compare' => '='
            ],
            $dateCond,
        ];
    } else {
        $args['meta_query'] = $dateCond;
    }
    $reservations = new WP_Query($args);

    $currentDate = (new DateTime())->modify("-1 day");
    $b = 0;
    $obsadeneArr = [];
    if ($reservations->have_posts()) :
        while ($reservations->have_posts()) : $reservations->the_post();
            $datetime = get_field("appointment_datetime", get_the_ID());

            $start = DateTime::createFromFormat("Y-m-d H:i:s", $datetime['from']);
            $end = DateTime::createFromFormat("Y-m-d H:i:s", $datetime['to']);

            $obsadeneArr[$start->format("Y-m-d")][] = ['start' => $start->format("H:i"), "end" => $end->format("H:i")];
        endwhile;
        wp_reset_postdata();
    endif;

    $finalDates = [];
    while (($barberID < 1 && $b < count($barbers)) || ($barberID > 0 && $b < 1)) :

        if ($barberID > 0) {
            $barber = get_user_by("ID", $barberID);
        } else {
            $barber = $barbers[$b];
        }
        $worktime = get_field("worktime", "user_" . $barber->ID);
        $lunchtime = get_field("lunchtime", "user_" . $barber->ID);

        for ($i = 0; $i < 60; $i++) :
            $date = $currentDate->modify("+1 day");
            if (in_array($date->format("N"), [6, 7])) continue;
            $currentDate = $date;
            //echo "currentDate: " . $date->format("d.m.Y") . "<br>";
            $dateFormat = $currentDate->format("Y-m-d");

            $obsadene = $obsadeneArr[$dateFormat] ?? false;

            //work start is currentTime
            $currentTime = DateTime::createFromFormat('Y-m-d H:i', $dateFormat . " " . $worktime['start'])->modify("-30 minutes");
            $workEnd = DateTime::createFromFormat('Y-m-d H:i', $dateFormat . " " . $worktime['end']);
            $workEndWhile = DateTime::createFromFormat('Y-m-d H:i', $dateFormat . " " . $worktime['end'])->modify("-" . $serviceDuration . " minutes")->format("H:i");
            $lunchStart = DateTime::createFromFormat('Y-m-d H:i', $dateFormat . " " . $lunchtime['start'])->format("H:i");
            $lunchEnd = DateTime::createFromFormat('Y-m-d H:i', $dateFormat . " " . $lunchtime['end'])->format("H:i");

            //echo "Work from: " . $worktime['start'] . " to " . $worktime['end'] . "<br>";
            //echo "Lunch from: " . $lunchStart . " to " . $lunchEnd . "<br>";
            //print_r($obsadene);
            //echo "<br>";

            //echo "while: " . $currentTime->format("H:i") . " <= " . $workEndWhile;
            while ($currentTime->format("H:i") < $workEndWhile) :
                $currentTime = $currentTime->modify("+30 minutes");
                $currentStart = $currentTime->format("H:i");
                $endTime = new DateTime($currentTime->format("H:i"));
                $currentEnd = $endTime->modify("+" . $serviceDuration . " minutes")->format("H:i");
                //echo "termin: from" . $currentStart . " to $currentEnd<br>";

                $canEnd = $currentEnd <= $lunchStart || $currentStart >= $lunchEnd;
                if (!$canEnd) {
                    //echo "skipping cause of lunch<br>";
                    continue;
                }

                $ok = true;
                if (!empty($obsadene)) {
                    foreach ($obsadene as $time) {
                        $terminStart = $time['start'];
                        $terminEnd = $time['end'];

                        $canStart = $currentStart <= $terminEnd;
                        //echo "$currentStart <= $terminEnd <br>";
                        $canEnd = $currentEnd <= $terminStart || $currentStart >= $terminEnd;
                        //echo "$currentEnd <= $terminStart " . " || " . " $currentStart >= $terminEnd <br>";

                        if (!($canStart && $canEnd)) {
                            $ok = false;
                            break;
                        }
                    }
                }
                if ($ok) {
                    //echo "is ok<br>";
                    $finalDates[$currentDate->format("n")][$dateFormat][$currentStart] = 1;
                } else {
                    //echo "is NOT ok<br>";
                    $finalDates[$currentDate->format("n")][$dateFormat][$currentStart] = 0;
                }
            endwhile;
            //var_dump($finalDates[$currentDate->format("Y-m-d")]);
        endfor;

        $b++;
    endwhile;

    wp_send_json([
        'dates' => $finalDates
    ], 200);
}


add_action('wp_ajax_make_reservation', 'make_reservation');
add_action('wp_ajax_nopriv_make_reservation', 'make_reservation');

function make_reservation()
{
    $barberID = $_POST['barber'] ?? false;
    if (!$barberID) {
        wp_send_json([
            'message' => "ID Barbera nebolo nastavené."
        ], 403);
    }

    $date = $_POST['date'] ?? false;
    if (!$date) {
        wp_send_json([
            'message' => "Dátum nebol nastavený."
        ], 403);
    }

    $time = $_POST['time'] ?? false;
    if (!$time) {
        wp_send_json([
            'message' => "Čas nebol nastavený."
        ], 403);
    }

    $service = $_POST['service'] ?? false;
    if (!$service) {
        wp_send_json([
            'message' => "Služba nebola nastavená."
        ], 403);
    }

    $customer = js_json_decode($_POST['customer']);
    $servDuration = get_field("serv-duration", $service);
    $datetimeStart = DateTime::createFromFormat("Y-m-d H:i", $date . " " . $time);
    $datetimeEnd = DateTime::createFromFormat("Y-m-d H:i", $date . " " . $time)->modify("+" . $servDuration . "minutes");

    $args = [
        'post_status' => 'publish',
        'post_type' => 'appointment',
    ];
    $postID = wp_insert_post($args);

    $cancelToken = getRandomString(12);
    update_post_meta($postID, "cancel_token", $cancelToken);

    update_appointment_fields([
        'customer' => $customer,
        'datetime' => [
            'start' => $datetimeStart->format("Y-m-d H:i:s"),
            'end' => $datetimeEnd->format("Y-m-d H:i:s"),
        ],
        'serviceID' => $service,
        'note' => $customer['note'],
    ], $barberID, $postID, "appointment");

    $receiver = $customer['email'];
    if (!empty($receiver)) reservation_notification($receiver, "new", $postID);

    wp_send_json(["id" => $postID, 'service' => $service], 200);
}
