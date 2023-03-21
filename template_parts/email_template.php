<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0;">
    <meta name="format-detection" content="telephone=no"/>
    <style>
        /* Reset styles */
        body {
            margin: 0;
            padding: 0;
            min-width: 100%;
            width: 100% !important;
            height: 100% !important;
        }

        body, table, td, div, p, a {
            -webkit-font-smoothing: antialiased;
            text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
            line-height: 100%;
        }

        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            border-collapse: collapse !important;
            border-spacing: 0;
        }

        img {
            border: 0;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        #outlook a {
            padding: 0;
        }

        .ReadMsgBody {
            width: 100%;
        }

        .ExternalClass {
            width: 100%;
        }

        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {
            line-height: 100%;
        }

        /* Rounded corners for advanced mail clients only */
        @media all and (min-width: 560px) {
            .container {
                border-radius: 8px;
                -webkit-border-radius: 8px;
                -moz-border-radius: 8px;
                -khtml-border-radius: 8px;
            }
        }

        /* Set color for auto links (addresses, dates, etc.) */
        a, a:hover {
            color: #127DB3;
        }

        .footer a, .footer a:hover {
            color: #999999;
        }

    </style>

    <!-- MESSAGE SUBJECT -->
    <title>Get this responsive email template</title>

</head>

<!-- BODY -->
<!-- Set message background color (twice) and text color (twice) -->
<body topmargin="0" rightmargin="0" bottommargin="0" leftmargin="0" marginwidth="0" marginheight="0" width="100%"
      style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; width: 100%; height: 100%; -webkit-font-smoothing: antialiased; text-size-adjust: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; line-height: 100%;
	background-color: #F0F0F0;
	color: #000000;"
      bgcolor="#F0F0F0"
      text="#000000">

<!-- SECTION / BACKGROUND -->
<!-- Set message background color one again -->
<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0"
       style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; width: 100%;" class="background">
    <tr>
        <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0;"
            bgcolor="#F0F0F0">

            <!-- WRAPPER -->
            <!-- Set wrapper width (twice) -->
            <table border="0" cellpadding="0" cellspacing="0" align="center"
                   width="560" style="border-collapse: collapse; border-spacing: 0; padding: 0; width: inherit;
	max-width: 560px;" class="wrapper">

                <tr>
                    <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%;
			padding-top: 20px;
			padding-bottom: 20px;">

                        <!-- PREHEADER -->
                        <!-- Set text color to background color -->
                            <!-- LOGO -->
                            <!-- Image text color should be opposite to background color. Set your url, image src, alt and title. Alt text should fit the image size. Real image size should be x2. URL format: http://domain.com/?utm_source={{Campaign-Source}}&utm_medium=email&utm_content=logo&utm_campaign={{Campaign-Name}} -->
                            <a target="_blank" style="text-decoration: none;"
                               href="https://trimbarbers.sk/"><img border="0" vspace="0" hspace="0"
                                                                   src="https://www.trimbarbers.sk/wp-content/uploads/2023/03/Logo.png"
                                                                   width="160"
                                                                   alt="Logo" title="Logo" style="
				color: #000000;
				font-size: 10px; margin: 0; padding: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: block;"/></a>

                    </td>
                </tr>

                <!-- End of WRAPPER -->
            </table>

            <!-- WRAPPER / CONTEINER -->
            <!-- Set conteiner background color -->
            <table border="0" cellpadding="0" cellspacing="0" align="center"
                   bgcolor="#FFFFFF"
                   width="560" style="border-collapse: collapse; border-spacing: 0; padding: 0; width: inherit;
	max-width: 560px;" class="container">

                <!-- HEADER -->
                <!-- Set text color and font family ("sans-serif" or "Georgia, serif") -->
                <?php
                	$data = $args['data'];
                	$type = $args['type'];
                    $service = $data['service'];
                    $servicePrice = get_field("serv-price", $service->ID);
                    $serviceTitle = get_the_title($service->ID);
                    $id = $data['id'];

                    if($type === "new") {
                        $title = "Potvrdenie o záväznej rezervácii";
                        $subtitle = $serviceTitle . " (" . $servicePrice. "€)";
                    } else if($type === "update") {
                        $title = "Zmena rezervácie";
                    } else if($type === "notification") {
                        $title = "Pripomienka rezervácie";
                    } else { //cancel
                        $title = "Zrušenie rezervácie";
                    }
                ?>
                <tr>
                    <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 24px; font-weight: bold; line-height: 130%;
			padding-top: 25px;
			color: #000000;
			font-family: sans-serif;" class="header">
                        <?= $title ?>
                    </td>
                </tr>

                <?php if (isset($subtitle)) : ?>
                    <!-- SUBHEADER -->
                    <!-- Set text color and font family ("sans-serif" or "Georgia, serif") -->
                    <tr>
                        <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-bottom: 3px; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 18px; font-weight: 300; line-height: 150%;
			padding-top: 5px;
			color: #000000;
			font-family: sans-serif;" class="subheader">
                            <?= $subtitle ?>
                        </td>
                    </tr>
                <?php endif ?>

                <!-- HERO IMAGE -->
                <!-- Image text color should be opposite to background color. Set your url, image src, alt and title. Alt text should fit the image size. Real image size should be x2 (wrapper x2). Do not set height for flexible images (including "auto"). URL format: http://domain.com/?utm_source={{Campaign-Source}}&utm_medium=email&utm_content={{Ìmage-Name}}&utm_campaign={{Campaign-Name}} -->
                <!--                <tr>
                                    <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0;
                            padding-top: 20px;" class="hero"><a target="_blank" style="text-decoration: none;"
                                                                href="https://github.com/konsav/email-templates/"><img border="0" vspace="0" hspace="0"
                                                                                                                       src="https://raw.githubusercontent.com/konsav/email-templates/master/images/hero-wide.png"
                                                                                                                       alt="Please enable images to view this content" title="Hero Image"
                                                                                                                       width="560" style="
                            width: 100%;
                            max-width: 560px;
                            color: #000000; font-size: 13px; margin: 0; padding: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: block;"/></a></td>
                                </tr>-->


                <?php
                $date = new DateTime(get_field('appointment_datetime_from', $id));
                $customer = get_field('appointment_customer', $id);
                $note = get_field('appointment_note', $id);
                $barber = $data['barber'];

                if($type === "new") { ob_start(); ?>

                    Dobrý deň, ďakujeme za Vašu rezervíciu a tešíme sa na Vašu návštevu.<br><br>

                    <b>DETAILY VAŠEJ REZERVÁCIE</b><br>
                    <b>Dátum a čas</b>: <?= $date->format("j.n.Y - H:i") ?><br>
                    <b>Miesto</b>: A. Bernoláka 8316/48A, Žilina<br>
                    <b>Služba</b>: <?= $serviceTitle . " (" . $servicePrice. "€)" ?><br>
                    <b>Barber</b>: <?= $barber->display_name ?><br>

                    <br>

                    <b>KONTAKTNÉ ÚDAJE</b><br>
                    <b>Meno a priezvisko</b>: <?= $customer['name'] ?><br>
                    <b>Telefón</b>: <?= $customer['phone'] ?><br>
                    <b>Email</b>: <?= $customer['email'] ?><br>

                    <?php if (!empty($note)) : ?>
                        <br><b>POZNÁMKA</b><br>
                        <?= $note ?>
                    <?php endif ?>

                    <?php $content = ob_get_clean();
                } else if($type === "update") { ob_start(); ?>

                    Dobrý deň, Vaša rezervácia bola upravená.<br><br>
                    <b>DETAILY VAŠEJ REZERVÁCIE</b><br>
                    <b>Dátum a čas</b>: <?= $date->format("j.n.Y - H:i") ?><br>
                    <b>Miesto</b>: A. Bernoláka 8316/48A, Žilina<br>
                    <b>Služba</b>: <?= $serviceTitle . " (" . $servicePrice. "€)" ?><br>
                    <b>Barber</b>: <?= $barber->display_name ?><br>

                    <br>

                    <b>KONTAKTNÉ ÚDAJE</b><br>
                    <b>Meno a priezvisko</b>: <?= $customer['name'] ?><br>
                    <b>Telefón</b>: <?= $customer['phone'] ?><br>
                    <b>Email</b>: <?= $customer['email'] ?><br>

                    <?php $content = ob_get_clean();
                } else if($type === "notification") { ob_start(); ?>

                    Dobrý deň, pripomíname Vám, že sa blíži dátum Vašej rezervácie.<br><br>
                    <b>DETAILY VAŠEJ REZERVÁCIE</b><br>
                    <b>Dátum a čas</b>: <?= $date->format("j.n.Y - H:i") ?><br>
                    <b>Miesto</b>: A. Bernoláka 8316/48A, Žilina<br>
                    <b>Služba</b>: <?= $serviceTitle . " (" . $servicePrice. "€)" ?><br>
                    <b>Barber</b>: <?= $barber->display_name ?><br>

                    <?php $content = ob_get_clean();
                }
                //cancel
                else { ob_start(); ?>

                    Dobrý deň, je nám to ľúto, ale Vaša rezervácia bola zrušená.<br><br>

                    <b>DETAILY VAŠEJ REZERVÁCIE</b><br>
                    <b>Dátum a čas</b>: <?= $date->format("j.n.Y - H:i") ?><br>
                    <b>Miesto</b>: A. Bernoláka 8316/48A, Žilina<br>
                    <b>Služba</b>: <?= $serviceTitle . " (" . $servicePrice. "€)" ?><br>
                    <b>Barber</b>: <?= $barber->display_name ?><br>

                    <?php $content = ob_get_clean();
                } ?>


                <!-- PARAGRAPH -->
                <!-- Set text color and font family ("sans-serif" or "Georgia, serif"). Duplicate all text styles in links, including line-height -->
                <tr>
                    <td align="left" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
			padding-top: 25px;
			color: #000000;
			font-family: sans-serif;" class="paragraph">
                        <?= $content ?>
                    </td>
                </tr>

                <?php if (in_array($type, ['new', 'update', 'notification'])) : ?>

                    <?php
                    $id = $data['id'];
                    $token = get_post_meta($id, 'cancel_token', true);
                    $cancelLink = false;
                    if(!empty($token)) {
                        $cancelLink = "https://trimbarbers.sk/wp-json/api/v1/cancel-reservation?t=$token&i=$id";
                    }
                    ?>
                    <?php if ($cancelLink) : ?>

                        <!-- LINE -->
                        <!-- Set line color -->
                        <tr>
                            <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%;
			padding-top: 25px;" class="line">
                                <hr
                                        color="#E0E0E0" align="center" width="100%" size="1" noshade
                                        style="margin: 0; padding: 0;"/>
                            </td>
                        </tr>

                        <!-- PARAGRAPH -->
                        <!-- Set text color and font family ("sans-serif" or "Georgia, serif"). Duplicate all text styles in links, including line-height -->
                        <tr>
                            <td align="left" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-top: 10px; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
			padding-top: 25px;color: #000000;font-family: sans-serif;" class="paragraph">
                                Ak chcete zrušiť Vašu rezerváciu, možete tak urobiť pomocou tlačidla nižšie.
                            </td>
                        </tr>

                        <!-- BUTTON -->
                        <!-- Set button background color at TD, link/text color at A and TD, font family ("sans-serif" or "Georgia, serif") at TD. For verification codes add "letter-spacing: 5px;". Link format: http://domain.com/?utm_source={{Campaign-Source}}&utm_medium=email&utm_content={{Button-Name}}&utm_campaign={{Campaign-Name}} -->
                        <tr>
                            <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%;
			padding-top: 25px;
			padding-bottom: 5px;" class="button"><a
                                        href="<?= $cancelLink ?>" target="_blank" style="text-decoration: none;">
                                    <table border="0" cellpadding="0" cellspacing="0" align="center"
                                           style="max-width: 240px; min-width: 120px; border-collapse: collapse; border-spacing: 0; padding: 0;">
                                        <tr>
                                            <td align="center" valign="middle"
                                                style="padding: 12px 24px; margin: 0; text-decoration: none; border-collapse: collapse; border-spacing: 0; border-radius: 4px; -webkit-border-radius: 4px; -moz-border-radius: 4px; -khtml-border-radius: 4px;"
                                                bgcolor="#E9703E">
                                                <a target="_blank" style="text-decoration: none;
					color: #FFFFFF; font-family: sans-serif; font-size: 17px; font-weight: 400; line-height: 120%;"
                                                   href="<?= $cancelLink ?>">
                                                    Zrušiť termín
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </a>
                            </td>
                        </tr>
                    <?php endif ?>
                <?php endif ?>

                <!-- LINE -->
                <!-- Set line color -->
                <!--                <tr>
                                    <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%;
                            padding-top: 25px;" class="line"><hr
                                            color="#E0E0E0" align="center" width="100%" size="1" noshade style="margin: 0; padding: 0;" />
                                    </td>
                                </tr>-->

                <!-- LIST -->
                <!--                <tr>
                                    <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%;" class="list-item"><table align="center" border="0" cellspacing="0" cellpadding="0" style="width: inherit; margin: 0; padding: 0; border-collapse: collapse; border-spacing: 0;">

                                            &lt;!&ndash; LIST ITEM &ndash;&gt;
                                            <tr>

                                                &lt;!&ndash; LIST ITEM IMAGE &ndash;&gt;
                                                &lt;!&ndash; Image text color should be opposite to background color. Set your url, image src, alt and title. Alt text should fit the image size. Real image size should be x2 &ndash;&gt;
                                                <td align="left" valign="top" style="border-collapse: collapse; border-spacing: 0;
                                    padding-top: 30px;
                                    padding-right: 20px;"><img
                                                        border="0" vspace="0" hspace="0" style="padding: 0; margin: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: block;
                                    color: #000000;"
                                                        src="https://raw.githubusercontent.com/konsav/email-templates/master/images/list-item.png"
                                                        alt="H" title="Highly compatible"
                                                        width="50" height="50"></td>

                                                &lt;!&ndash; LIST ITEM TEXT &ndash;&gt;
                                                &lt;!&ndash; Set text color and font family ("sans-serif" or "Georgia, serif"). Duplicate all text styles in links, including line-height &ndash;&gt;
                                                <td align="left" valign="top" style="font-size: 17px; font-weight: 400; line-height: 160%; border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0;
                                    padding-top: 25px;
                                    color: #000000;
                                    font-family: sans-serif;" class="paragraph">
                                                    <b style="color: #333333;">Highly compatible</b><br/>
                                                    Tested on the most popular email clients for web, desktop and mobile. Checklist included.
                                                </td>

                                            </tr>

                                            &lt;!&ndash; LIST ITEM &ndash;&gt;
                                            <tr>

                                                &lt;!&ndash; LIST ITEM IMAGE &ndash;&gt;
                                                &lt;!&ndash; Image text color should be opposite to background color. Set your url, image src, alt and title. Alt text should fit the image size. Real image size should be x2 &ndash;&gt;
                                                <td align="left" valign="top" style="border-collapse: collapse; border-spacing: 0;
                                    padding-top: 30px;
                                    padding-right: 20px;"><img
                                                        border="0" vspace="0" hspace="0" style="padding: 0; margin: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: block;
                                    color: #000000;"
                                                        src="https://raw.githubusercontent.com/konsav/email-templates/master/images/list-item.png"
                                                        alt="D" title="Designer friendly"
                                                        width="50" height="50"></td>

                                                &lt;!&ndash; LIST ITEM TEXT &ndash;&gt;
                                                &lt;!&ndash; Set text color and font family ("sans-serif" or "Georgia, serif"). Duplicate all text styles in links, including line-height &ndash;&gt;
                                                <td align="left" valign="top" style="font-size: 17px; font-weight: 400; line-height: 160%; border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0;
                                    padding-top: 25px;
                                    color: #000000;
                                    font-family: sans-serif;" class="paragraph">
                                                    <b style="color: #333333;">Designer friendly</b><br/>
                                                    Sketch app resource file and a&nbsp;bunch of&nbsp;social media icons are&nbsp;also included in&nbsp;GitHub repository.
                                                </td>

                                            </tr>

                                        </table></td>
                                </tr>-->

                <!-- LINE -->
                <!-- Set line color -->
                <tr>
                    <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%;
			padding-top: 25px;" class="line">
                        <hr
                                color="#E0E0E0" align="center" width="100%" size="1" noshade
                                style="margin: 0; padding: 0;"/>
                    </td>
                </tr>

                <!-- PARAGRAPH -->
                <!-- Set text color and font family ("sans-serif" or "Georgia, serif"). Duplicate all text styles in links, including line-height -->
                <tr>
                    <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 17px; font-weight: 400; line-height: 160%;
			padding-top: 20px;
			padding-bottom: 25px;
			color: #000000;
			font-family: sans-serif;" class="paragraph">
                        Máte nejaké otázky? <a href="mailto:info@trimbarbers.sk" target="_blank"
                                        style="color: #127DB3; font-family: sans-serif; font-size: 17px; font-weight: 400; line-height: 160%;">info@trimbarbers.sk</a>
                    </td>
                </tr>

                <!-- End of WRAPPER -->
            </table>

            <!-- WRAPPER -->
            <!-- Set wrapper width (twice) -->
            <table border="0" cellpadding="0" cellspacing="0" align="center"
                   width="560" style="border-collapse: collapse; border-spacing: 0; padding: 0; width: inherit;
	max-width: 560px;" class="wrapper">

                <!-- SOCIAL NETWORKS -->
                <!-- Image text color should be opposite to background color. Set your url, image src, alt and title. Alt text should fit the image size. Real image size should be x2 -->
                <tr>
                    <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%;
			padding-top: 25px;" class="social-icons">
                        <table
                                width="256" border="0" cellpadding="0" cellspacing="0" align="center"
                                style="border-collapse: collapse; border-spacing: 0; padding: 0;">
                            <tr>

                                <?php

                                $fb = get_field("socials_facebook", "options");
                                $ig = get_field("socials_instagram", "options");

                                ?>

                                <!-- WEB -->
                                <td align="center" valign="middle"
                                    style="margin: 0; padding: 0; padding-left: 10px; padding-right: 10px; border-collapse: collapse; border-spacing: 0;">
                                    <a target="_blank" href="https://trimbarbers.sk/" style="text-decoration: none;">
                                        <img border="0" vspace="0" hspace="0" width="44" height="44" style="padding: 0; margin: 0; outline: none;
                                        text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: inline-block; color: #000000;"
                                             src="https://www.trimbarbers.sk/wp-content/uploads/2023/03/web.png">
                                    </a>
                                </td>

                                <?php if ($fb) : ?>
                                    <!-- FACEBOOK -->
                                    <td align="center" valign="middle"
                                        style="margin: 0; padding: 0; padding-left: 10px; padding-right: 10px; border-collapse: collapse; border-spacing: 0;">
                                        <a target="_blank" href="<?= $fb ?>" style="text-decoration: none;">
                                            <img border="0" vspace="0" hspace="0" width="44" height="44" style="padding: 0; margin: 0; outline: none;
                                        text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: inline-block; color: #000000;"
                                                 src="https://www.trimbarbers.sk/wp-content/uploads/2023/03/facebook.png">
                                        </a>
                                    </td>
                                <?php endif ?>

                                <?php if ($ig) : ?>
                                    <!-- INSTAGRAM -->
                                    <td align="center" valign="middle"
                                        style="margin: 0; padding: 0; padding-left: 10px; padding-right: 10px; border-collapse: collapse; border-spacing: 0;">
                                        <a target="_blank" href="<?= $ig ?>"
                                           style="text-decoration: none;">
                                            <img border="0" vspace="0" hspace="0" style="padding: 0; margin: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: none; display: inline-block;
					color: #000000;" alt="I" title="Instagram"
                                                 width="44" height="44"
                                                 src="https://www.trimbarbers.sk/wp-content/uploads/2023/03/instagram.png">
                                        </a>
                                    </td>
                                <?php endif ?>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- FOOTER -->
                <!-- Set text color and font family ("sans-serif" or "Georgia, serif"). Duplicate all text styles in links, including line-height -->
                <tr>
                    <td align="center" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 6.25%; padding-right: 6.25%; width: 87.5%; font-size: 13px; font-weight: 400; line-height: 150%;
			padding-top: 20px;
			padding-bottom: 20px;
			color: #999999;
			font-family: sans-serif;" class="footer">
                        Tento email Vám bol odoslaný z webu <a href="https://trimbarbers.sk/" target="_blank"
                                                               style="text-decoration: underline; color: #999999; font-family: sans-serif; font-size: 13px; font-weight: 400; line-height: 150%;">www.trimbarbers.sk</a>.
                    </td>
                </tr>

                <!-- End of WRAPPER -->
            </table>

            <!-- End of SECTION / BACKGROUND -->
        </td>
    </tr>
</table>

</body>
</html>