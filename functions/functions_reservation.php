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
    $barbers = getBarbers();

    $finalDates = [];
    $b = 0;
    while (($barberID < 1 && $b < count($barbers)) || ($barberID > 0 && $b < 1)) :
        $currentDate = (new DateTime())->modify("-1 day");

        if ($barberID > 0) {
            $barber = get_user_by("ID", $barberID);
        } else {
            $barber = $barbers[$b];
        }
        // "barberID: " . $barber->ID . "<br>";
        // "barber: " . ($barber->first_name) . "<br>";

        $worktime = get_field("worktime", "user_" . $barber->ID);
        $lunchtime = get_field("lunchtime", "user_" . $barber->ID);
        $weekendWork = get_field('weekend_work', "user_" . $barber->ID) ?? ['saturday' => false, 'sunday' => false];


        $args = [
            'post_type' => 'appointment',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ];
        $dateCond = [
            "relation" => "OR",
            [
                'key' => 'appointment_datetime_from',
                'value' => date("Y-m-d") . " 00:00:00",
                'compare' => '>=',
                'type' => 'DATETIME',
            ],
            [
                'key' => 'appointment_datetime_to',
                'value' => date("Y-m-d") . " 00:00:00",
                'compare' => '>=',
                'type' => 'DATETIME',
            ]
        ];

        $args['meta_query'] = [
            'relation' => 'AND',
            [
                'key' => 'appointment_barber',
                'value' => $barber->ID,
                'compare' => '='
            ],
            $dateCond,
        ];

        $reservations = new WP_Query($args);

        $obsadeneArr = [];
        if ($reservations->have_posts()) :
            while ($reservations->have_posts()) : $reservations->the_post();
                $datetime = get_field("appointment_datetime", get_the_ID());

                $startAt = DateTime::createFromFormat("Y-m-d H:i:s", $datetime['from']);
                $endAt = DateTime::createFromFormat("Y-m-d H:i:s", $datetime['to']);

                if ($startAt->format('Y-m-d') !== $endAt->format('Y-m-d')) {
                    $currentDay = clone $startAt;
                    $endOfDay = clone $currentDay;
                    $endOfDay->setTime(23, 59, 59);
                    $startOfEndDay = clone $endAt;
                    $startOfEndDay->setTime(0, 0, 0);

                    // First day
                    $obsadeneArr[$currentDay->format("Y-m-d")][] = [
                        'start' => $startAt->format("H:i"),
                        'end' => $endOfDay->format("H:i")
                    ];

                    // Intermediate days
                    $currentDay->add(new DateInterval('P1D'))->setTime(0, 0, 0);
                    while ($currentDay < $startOfEndDay) {
                        $obsadeneArr[$currentDay->format("Y-m-d")][] = [
                            'start' => '00:00',
                            'end' => '23:59'
                        ];
                        $currentDay->add(new DateInterval('P1D'));
                    }

                    // Last day
                    $obsadeneArr[$endAt->format("Y-m-d")][] = [
                        'start' => '00:00',
                        'end' => $endAt->format("H:i")
                    ];
                } else {
                    $obsadeneArr[$startAt->format("Y-m-d")][] = [
                        'start' => $startAt->format("H:i"),
                        'end' => $endAt->format("H:i")
                    ];
                }

            endwhile;
            wp_reset_postdata();
        endif;

        //var_dump($obsadeneArr);

        for ($i = 0; $i < 60; $i++) :
            $date = $currentDate->modify("+1 day");
            $dateFormat = $currentDate->format("Y-m-d");

            //Saturday work
            if (!($weekendWork['saturday'] ?? false) && (int)$date->format("N") === 6) {
                $finalDates[$currentDate->format("Y")][$currentDate->format("n")][$dateFormat] = ['apps' => [], 'isAvailable' => 0];
                continue;
            };

            //Sunday work
            if (!($weekendWork['sunday'] ?? false) && (int)$date->format("N") === 7) {
                $finalDates[$currentDate->format("Y")][$currentDate->format("n")][$dateFormat] = ['apps' => [], 'isAvailable' => 0];
                continue;
            };

            $currentDate = $date;
            //echo "currentDate: " . $date->format("d.m.Y") . "<br>";
            $obsadene = $obsadeneArr[$dateFormat] ?? false;

            //work start is currentTime
            $currentTime = DateTime::createFromFormat('Y-m-d H:i', $dateFormat . " " . $worktime['start'])->modify("-30 minutes");
            $workEnd = DateTime::createFromFormat('Y-m-d H:i', $dateFormat . " " . $worktime['end']);
            $workEndWhile = DateTime::createFromFormat('Y-m-d H:i', $dateFormat . " " . $worktime['end'])->modify("-" . $serviceDuration . " minutes")->format("H:i");
            $lunchStart = DateTime::createFromFormat('Y-m-d H:i', $dateFormat . " " . $lunchtime['start'])->format("H:i");
            $lunchEnd = DateTime::createFromFormat('Y-m-d H:i', $dateFormat . " " . $lunchtime['end'])->format("H:i");

            //echo "Work from: " . $worktime['start'] . " to " . $worktime['end'] . "<br>";
            //echo "Lunch from: " . $lunchStart . " to " . $lunchEnd . "<br>";

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

                        $canEnd = $currentEnd <= $terminStart || $currentStart >= $terminEnd;
                        //echo "$currentEnd <= $terminStart " . " || " . " $currentStart >= $terminEnd <br>";

                        if (!(/*$canStart &&*/ $canEnd)) {
                            $ok = false;
                            break;
                        }
                    }
                }

                $timeToAdd = [];
                $termin = $finalDates[$currentDate->format("Y")][$currentDate->format("n")][$dateFormat]['apps'][$currentStart] ?? [];

                // termin is available
                if ($ok) {
                    $timeToAdd['isAvailable'] = 1;
                    $timeToAdd['barbers'] = [$barber->ID];
                }
                // termin is not available && is not set
                else if (empty($termin)) {
                    $timeToAdd['isAvailable'] = 0;
                    $timeToAdd['barbers'] = [];
                }

                if(!empty($timeToAdd)) {
                    if(!empty($termin) && !empty($termin['barbers'])) {
                        $timeToAdd['barbers'] = array_merge($timeToAdd['barbers'], $termin['barbers']);
                    }
                    $finalDates[$currentDate->format("Y")][$currentDate->format("n")][$dateFormat]['apps'][$currentStart] = $timeToAdd;
                }

                if($finalDates[$currentDate->format("Y")][$currentDate->format("n")][$dateFormat]['isAvailable'] != 1 && $ok) {
                    $finalDates[$currentDate->format("Y")][$currentDate->format("n")][$dateFormat]['isAvailable'] = 1;
                }
            endwhile;

            if(!isset($finalDates[$currentDate->format("Y")][$currentDate->format("n")][$dateFormat]['isAvailable'])) {
                $finalDates[$currentDate->format("Y")][$currentDate->format("n")][$dateFormat]['isAvailable'] = 0;
            }
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
    $barbers = isset($_POST['barbers']) ? js_json_decode($_POST['barbers']) : false;
    if (empty($barbers)) {
        wp_send_json([
            'message' => "Nebol vybraný barber."
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

    $cancelToken = getRandomString(24);
    update_post_meta($postID, "cancel_token", $cancelToken);

    //Pick one barber
    $barbers = array_values($barbers);
    $barberID = $barbers[array_rand($barbers)];

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
    if (!empty($receiver)) reservation_notification($receiver, "new", $postID, false);

    if($reservationsEmail = get_field("reservations_email", "options")) {
        reservation_notification($reservationsEmail, "new", $postID, true);
    }

    wp_send_json(["id" => $postID, 'service' => $service], 200);
}
