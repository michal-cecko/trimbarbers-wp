<?php


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

    register_rest_route('api/v1', '/generate-ics/', array(
        'methods' => 'GET',
        'callback' => 'generate_ics',
    ));
}

function cancel_reservation_handler($request)
{
    $token = $request->get_param('t');
    $id = $request->get_param('i');
    if (get_post_meta($id, "cancel_token", true) === $token) {
        cancel_appointment($id, "customer", true);
    }
    wp_redirect(home_url(). "?c=1");
    exit();
}

function generate_ics($request)
{
    require_once get_template_directory() . '/classes/ICS.php';

    $start = $request->get_param('s') ?? false;
    $end = $request->get_param('e') ?? false;
    $name = $request->get_param('n') ?? false;
    $location = "A. Bernoláka 8316/48A, 010 01 Žilina";
    $hashOK = $request->get_param("h") === '***REMOVED-ICS-HASH***';

    if(!$start || !$name || !$end || !$hashOK) {
        wp_send_json_error("Neboli definované potrebné dáta");
    }

    $ics = new ICS($start, $end, $name, "", $location);
    $ics->show();
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
                [
                    'relation' => 'OR',
                    [
                        'key' => 'has_been_reminded',
                        'compare' => 'NOT EXISTS'
                    ],
                    [
                        'key' => 'has_been_reminded',
                        'value' => '',
                        'compare' => '='
                    ],
                    [
                        'key' => 'has_been_reminded',
                        'value' => false,
                        'compare' => '='
                    ],
                    [
                        'key' => 'has_been_reminded',
                        'value' => 0,
                        'compare' => '='
                    ]
                ]
            ],
        ];
        $reservations = new WP_Query($args);
        if ($reservations->have_posts()) {
            while ($reservations->have_posts()) {
                $reservations->the_post();
                $id = get_the_ID();

                $receiver = get_field("appointment_customer_email", $id);
                if (!empty($receiver)) reservation_notification($receiver, "notification", $id);
                update_post_meta($id, "has_been_reminded", true);
            }
            wp_reset_postdata();
        }
    }
    exit();
}