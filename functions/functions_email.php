<?php
add_action('wp_ajax_send_form_email', 'contact_form');
add_action('wp_ajax_nopriv_send_form_email', 'contact_form');

function getEmailTemplate($emailType, $data)
{
    ob_start();
    get_template_part("template_parts/email_template", "", ['type' => $emailType, 'data' => $data]);
    return ob_get_clean();
}


function reservation_notification($to, $type, $id, $sendingToBarber = false)
{
    $service = get_field("appointment_service", $id);
    $barber = get_field("appointment_barber", $id);
    $serviceTitle = get_the_title($service->ID);

    $message = getEmailTemplate($type, [
        'barber' => $barber,
        'service' => $service,
        "id" => $id,
        "sendingToBarber" => $sendingToBarber
    ]);

    if ($type === "new") {
        $subject = 'Nová rezervácia | ' . $serviceTitle;
    } else if ($type === "update") {
        $subject = 'Úprava vašej rezervácie | ' . $serviceTitle;
    } else if ($type === "notification") {
        $subject = 'Pripomienka rezervácie | ' . $serviceTitle;
    } else { // cancel
        $subject = 'Zrušenie termínu | ' . $serviceTitle;
    }

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: trimbarbers.sk <' . $to . '>',
        'Reply-to: info@trimbarbers.sk'
    ];

    return wp_mail($to, $subject, $message, $headers);
}


function set_profile_picture($phpmailer)
{
    $profile_picture_url = 'https://www.trimbarbers.sk/wp-content/uploads/2023/03/logomail.png';
    $phpmailer->AddCustomHeader('X-Profile-Picture: ' . $profile_picture_url);
}

add_action('phpmailer_init', 'set_profile_picture');