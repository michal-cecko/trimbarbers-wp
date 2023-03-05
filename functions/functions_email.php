<?php
    add_action( 'wp_ajax_send_form_email', 'contact_form' );
    add_action( 'wp_ajax_nopriv_send_form_email', 'contact_form' );

    function getEmailTemplate($data)
    {
        ob_start(); ?>
        <!DOCTYPE html>
        <html lang="en" xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <meta name="x-apple-disable-message-reformatting">
            <title></title>
            <!--[if mso]>
            <noscript>
            <xml>
                <o:OfficeDocumentSettings>
                    <o:PixelsPerInch>96</o:PixelsPerInch>
                </o:OfficeDocumentSettings>
            </xml>
            </noscript>
            <![endif]-->
            <style>
                table, td, div, h1, p {
                    font-family: Arial, sans-serif;
                }
            </style>
        </head>
        <body style="margin:0;padding:0;background: white;">
        <table role="presentation"
               style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;">
            <tr>
                <td align="center" style="padding:0;">
                    <table role="presentation"
                           style="width:602px;border-collapse:collapse;border:0px solid #cccccc;border-spacing:0;text-align:left;">
                        <tr>
                            <td align="center" style="padding:40px 0 30px 0;background:#fff;">
                                <!-- LOGO GOES HERE -->
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:36px 30px 42px 30px;background: #F3F3F4;">
                                <h1 style="font-size:24px;text-align: center; margin:0 0 20px 0;font-family:Arial,sans-serif;"><?= $data['heading']; ?></h1>
                                <div style="display: flex;width: 100%;">
                                    <table role="presentation"
                                           style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
                                        <tr>
                                            <td style="padding:0 0 0 0;color:#153643;">
                                                <div
                                                    style="margin:0 0 12px 0;font-size:16px;line-height:20px;font-family:Arial,sans-serif;"><?= $data['content']; ?></div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 30px 42px 30px;background: #F3F3F4;">
                                <p style="margin:0 0 12px 0;font-size:13px;line-height:20px;font-family:Arial,sans-serif;"><?= $data['footer']; ?></p>
                                <p style="text-align: center"><a style="text-decoration: underline; color: black;" href="https://trimbarbers.sk/">www.trimbarbers.sk</a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        </body>
        </html>
        <?php
        $email = ob_get_clean();
        return $email;
    }


function contact_form()
    {
        if ( isset( $_POST[ 'form_data' ] ) ) {

            /*if ( ! isset( $_POST['form_data']['gdpr_acceptance'] ) ) {
                echo json_encode( array( 'type' => 'fail', 'message' => 'Musíte súhlasiť s podmienkami o ochrane osobných údajov.' ) );
                wp_die();
            }*/

            // sanitize form values
            $name  = sanitize_text_field( $_POST[ 'form_data' ][ 'name' ] );
            $email = sanitize_text_field( $_POST[ 'form_data' ][ 'email' ] );
            $phone = sanitize_text_field( $_POST[ 'form_data' ][ 'phone' ] );
            $text = sanitize_text_field( $_POST[ 'form_data' ][ 'message' ] );
            $doucovatel = sanitize_text_field( $_POST[ 'form_data' ][ 'douc_email' ] );
            $doucovatel_meno = sanitize_text_field( $_POST[ 'form_data' ][ 'douc_name' ] );
            $reciever = get_field( "contact_email", "options" );
            if(empty($doucovatel))$doucovatel = $reciever;

            $message = '<div>';

            $message .= '<h3 style="margin-bottom: 5px;">Kontaktný formulár:</h3>';
            $message .= '<h4 style="margin-bottom: 5px; text-decoration: underline;">Údaje o zákazníkovi:</h4>';
            $message .= '<p>Meno a priezvisko: ' . $name . '</p>';
            $message .= '<p>Email: ' . $email . '</p>';
            $message .= '<p>Telefón: ' . $phone . '</p>';
            if($text){
                $message .= '<p>Správa: ' . $text . '</p>';
            }
            $message .= '</div>';

            $data = ['heading' => 'Gratulujeme! Váš profil zaujal nového zákazníka.', 'content' => $message, 'footer' => 'Pripomíname, že je Vašou povinnosťou odpovedať v čo najkratšom čase. '];
            $email_temp = getEmailTemplate($data);

            $subject = 'Nová správa od zákazníka - ' . $name;
            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $name . ' <' . $email . '>',
                'Reply-to: ' . $email,
                'Cc: ' . $reciever,
            );


            /* Email to customer */
            $message_customer = '<div>';
            $message_customer .= '<p>Tešíme sa na naše spoločné hodiny a úspešnú spoluprácu. Váš doučovateľ ' . $doucovatel_meno . ' Vám odpovie čoskoro.</p>';
            $message_customer .= '<p>Ďakujeme<br>Tím swslovakia.eu</p>';
            $data_customer = ['heading' => 'Ďakujeme, že sa chcete s nami vzdelávať', 'content' => $message_customer, 'footer' => 'Pokiaľ by ste nedostali odpoveď do 24 hodín, kontaktujte nás prosím prostredníctvom e-mailovej adresy info@swslovakia.eu alebo telefónneho čísla <a href="tel:+421911597608">+421911597608.</a>'];
            $email_temp_customer = getEmailTemplate($data_customer);
            $subject_customer = 'Ďakujeme za Váš záujem | swslovakia.eu';
            $headers_customer = array(
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $reciever . ' <' . $reciever . '>',
                'Reply-to: ' . $reciever
            );


            if ( wp_mail( $email, $subject_customer, $email_temp_customer, $headers_customer ) &&  wp_mail( $doucovatel, $subject, $email_temp, $headers )) {
                echo json_encode( array( 'type' => 'success', 'message' => 'Ďakujeme, formulár bol úspešne odoslaný. Odpovieme Vám čoskoro.' ) );
                wp_die();
            } else {
                echo json_encode(
                    array(
                        'type'    => 'fail',
                        'message' => 'Formulár sa nepodarilo odoslať. Skúste to neskôr.'
                    ) );
                wp_die();
            }
        } else {
            echo json_encode( array( 'type' => 'fail', 'message' => 'Formulár sa nepodarilo odoslať. Skúste to neskôr prosím.' ) );
            wp_die();
        }
    }