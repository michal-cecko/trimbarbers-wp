<?php

$barbers = get_users([
    'role' => 'barber',
]);

?>

<div id="reservation">
    <div class="container">
        <div class="reservation-wrapper">
            <button id="openReservationBtn" class="btn btn-primary btn-solid">Rezervácia</button>
            <div class="reservation-container">
                <div class="header">
                    <div class="close">
                        <?= svgIcon(icon_path(false) . "/icon-close.svg") ?>
                    </div>
                    <div class="header-text">
                        <span>Výber barbera</span>
                    </div>
                </div>
                <div class="content choose-barber">
                    <div class="barber" @click="chooseBarber(-1)">
                        <div class="any-icon">
                            <?= svgIcon(icon_path(false) . "/icon-random.svg") ?>
                        </div>
                    </div>
                    <?php foreach ($barbers as $barber) : $id = "user_" . $barber->ID; ?>
                        <div class="barber" :class="barber === <?= $barber->ID ?> ? 'chosen' : ''" @click="chooseBarber(<?= $barber->ID ?>)">
                            <div class="img-container">
                                <?php if ($photo = get_field("profile_image", $id)) : ?>
                                    <img src="<?= $photo ?>" alt="">
                                <?php else : ?>
                                    <img src="<?= icon_path() . "/icon-question_mark.svg" ?>" alt="">
                                <?php endif ?>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
    </div>
    <div id="current-reservation">
        <div class="header">
            <span>Vaša rezervácia</span>
        </div>
        <span class="toggle-button">
            <?= svgIcon(icon_path(false) . "/icon-close.svg") ?>
        </span>
    </div>
</div>