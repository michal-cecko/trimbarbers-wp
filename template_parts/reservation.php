<?php


$barbers = getBarbers();

$args = [
    'post_type' => 'service',
    'post_status' => 'publish',
    'posts_per_page' => -1,
];
$services = new WP_Query($args);

?>

<div id="reservation" :class="isModalVisible ? 'active' : ''">
    <div class="container">
        <div class="reservation-wrapper">
            <button id="openReservationBtn" class="btn btn-primary btn-solid" :class="!isModalVisible ? 'active' : ''"
                    @click="isModalVisible = true">Rezervácia
            </button>
            <div class="reservation-container" :class="isModalVisible ? 'active' : ''">
                <div class="header" :class="step > 1 ? 'hasBackButton' : ''">
                    <div class="header-text">
                        <div class="back-button" @click="changeStep(step - 1, true)">
                            <?= svgIcon(icon_path(false) . "/icon-arrow.svg") ?>
                        </div>
                        <div class="texts">
                            <span v-if="step === 1">Výber barbera</span>
                            <span v-else-if="step === 2">Výber služby</span>
                            <span v-else-if="step === 3">Výber dátumu</span>
                            <span v-else-if="step === 4">Výber času</span>
                            <span v-else>Kontaktné údaje</span>
                        </div>
                    </div>
                    <div class="close" @click="isModalVisible = false">
                        <?= svgIcon(icon_path(false) . "/icon-close.svg") ?>
                    </div>
                </div>
                <div class="content-container">
                    <div class="content choose-barber">
                        <div class="barber" @click="chooseBarber(-1, 'Nezáleží', '')"
                             :class="barber.id === -1 ? 'chosen' : ''">
                            <div class="img-container any">
                                <?= svgIcon(icon_path(false) . "/icon-random.svg") ?>
                            </div>
                            <div class="name">Nezáleží</div>
                        </div>
                        <?php foreach ($barbers as $barber) :
                            $id = "user_" . $barber->ID;
                            $photo = get_field("profile_image", $id)
                            ?>
                            <div class="barber" :class="barber.id === <?= $barber->ID ?> ? 'chosen' : ''"
                                 @click="chooseBarber(<?= $barber->ID ?>, '<?= $barber->first_name ?>', '<?= $photo ?>')">
                                <div class="img-container">
                                    <?php if ($photo) : ?>
                                        <img src="<?= $photo ?>" alt="<?= $barber->first_name ?>">
                                    <?php else : ?>
                                        <?= svgIcon(icon_path(false) . "/icon-question_mark.svg") ?>
                                    <?php endif ?>
                                </div>
                                <div class="name"><?= $barber->first_name ?></div>
                            </div>
                        <?php endforeach ?>
                    </div>
                    <div class="content choose-service">
                        <?php if ($services->have_posts()) : ?>
                            <?php while ($services->have_posts()) :
                                $services->the_post();
                                $price = get_field("serv-price");
                                $duration = get_field("serv-duration");
                                ?>
                                <div class="service" :class="service.id === <?= get_the_ID() ?> ? 'chosen' : ''"
                                     @click="chooseService(<?= get_the_ID() ?>, '<?= get_the_title() ?>', <?= $price ?>)">
                                    <div class="name"><?= get_the_title() ?></div>
                                    <div class="duration"><?= $duration ?>min</div>
                                    <div class="price"><?= $price ?>€</div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata(); ?>
                        <?php endif ?>
                    </div>
                    <div class="content choose-date position-relative">
                        <div class="date-picker" :class="Object.keys(availableDates).length ? 'shown' : ''">
                            <template v-for="(monthArray, year, index) in availableDates">
                                <div v-for="(dates, month, index) in monthArray" class="dates-container">
                                    <h3 class="month-name" v-html="getMonthName(month)"></h3>
                                    <div class="date-grid">
                                        <template v-for="(appointments, availableDate, index) in dates">
                                            <div class="date"
                                                 :class="[availableDate === date ? 'chosen' : '', appointments['isAvailable'] === 0 ? 'notAvailable' : '']"
                                                 @click="chooseDate(availableDate)">
                                                <div class="number" v-html="moment(availableDate).format('D')"></div>
                                                <div class="name"
                                                     v-html="getDayName(moment(availableDate).format('d'))"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="empty-date-picker" :class="Object.keys(availableDates).length ? '' : 'shown'">
                            <lord-icon
                                    src="<?= get_template_directory_uri() ?>/assets/icons/icon-loader.json"
                                    trigger="loop"
                                    stroke="60"
                                    colors="primary:#1f1f1f,secondary:#1f1f1f">
                            </lord-icon>
                        </div>
                    </div>
                    <div class="content choose-time position-relative">
                        <div class="time-picker" v-if="Object.keys(timeOptions).length">
                            <div v-for="(timeArray, availableTime, index) in timeOptions" class="time-container"
                                 :class="[!empty(chosenTime.time) && availableTime === chosenTime.time ? 'chosen' : '', (timeArray.isAvailable === 0 ? 'unavailable' : '')]"
                                 @click="chooseTime(availableTime)">
                                <div class="time" :class="getTimeClass(availableTime)"
                                     v-html="moment('2022-03-21T' + availableTime + ':00').format('H:mm')"></div>
                            </div>
                        </div>
                    </div>
                    <div class="content choose-contact position-relative">
                        <div class="form-input" :class="hasError('name') ? 'error' : ''">
                            <div class="form-label">Meno a priezvisko</div>
                            <input type="text" v-model="customer.name">
                        </div>
                        <div class="form-input" :class="hasError('email') ? 'error' : ''">
                            <div class="form-label">Email</div>
                            <input type="text" v-model="customer.email">
                        </div>
                        <div class="form-input" :class="hasError('phone') ? 'error' : ''">
                            <div class="form-label">Telefón</div>
                            <input type="text" v-model="customer.phone">
                        </div>
                        <div class="form-input">
                            <div class="form-label">Poznámka</div>
                            <input type="text" v-model="customer.note">
                        </div>
                        <label for="saveCustomerToCookies" class="form-checkbox label-right">
                            <input type="checkbox" v-model="saveCustomerToCookies" id="saveCustomerToCookies">
                            <span class="checkbox">
                                <?= svgIcon(icon_path(false) . "/icon-check.svg") ?>
                            </span>
                            <span class="form-label">Uložiť údaje</span>
                        </label>
                    </div>
                </div>
                <div id="current-reservation"
                     :class="[(isVisibleOrder ? 'visible' : ''), (step === 5 ? 'bigger' : '')]">
                    <div class="header" @click="headerToggler()">
                        <span class="header-text">Vaša rezervácia</span>
                        <span class="toggle-button" :class="step !== 5 ? 'active' : ''">
                            <?= svgIcon(icon_path(false) . "/icon-arrow.svg") ?>
                        </span>
                    </div>
                    <div class="order" v-if="barber.id !== null">
                        <div class="img-container" v-if="barber.id > 0 && barber.photo">
                            <img :src="barber.photo" alt="">
                        </div>
                        <div class="img-container" v-else-if="barber.id === -1">
                            <?= svgIcon(icon_path(false) . "/icon-random.svg") ?>
                        </div>
                        <div class="img-container" v-else>
                            <?= svgIcon(icon_path(false) . "/icon-question_mark.svg") ?>
                        </div>
                        <div class="info">
                            <div class="name-price-container">
                                <div class="name" v-html="barber.name"></div>
                                <div v-show="service.id !== null" class="price" v-html="service.price + '€'"></div>
                            </div>
                            <div v-show="service.id !== null" class="service" v-html="service.name"></div>
                            <div v-show="date !== null" class="date"
                                 v-html="formatSelectedDatetime()"></div>
                        </div>
                    </div>
                    <div class="contact-data" v-if="step === 5">
                        <div class="label" v-if="!hasError(customer.name)">Meno:</div>
                        <div class="value" v-if="!hasError(customer.name)" v-html="customer.name"></div>
                        <div class="label" v-if="!hasError(customer.email)">Email:</div>
                        <div class="value" v-if="!hasError(customer.email)" v-html="customer.email"></div>
                        <div class="label" v-if="!hasError(customer.phone)">Telefón:</div>
                        <div class="value" v-if="!hasError(customer.phone)" v-html="customer.phone"></div>
                    </div>
                    <div class="button-container">
                        <button class="btn btn-primary btn-no-scale btn-solid next-step"
                                :class="!canContinue(2) ? 'btn-disabled' : ''" :disabled="!canContinue(2)"
                                v-if="step === 1" @click="changeStep(2)">Vybrať službu
                        </button>
                        <button class="btn btn-primary btn-no-scale btn-solid next-step"
                                :class="!canContinue(3) ? 'btn-disabled' : ''" :disabled="!canContinue(3)"
                                v-else-if="step === 2" @click="changeStep(3)">Vybrať dátum
                        </button>
                        <button class="btn btn-primary btn-no-scale btn-solid next-step"
                                :class="!canContinue(4) ? 'btn-disabled' : ''" :disabled="!canContinue(4)"
                                v-else-if="step === 3" @click="changeStep(4)">Vybrať čas
                        </button>
                        <button class="btn btn-primary btn-no-scale btn-solid next-step"
                                :class="!canContinue(4) ? 'btn-disabled' : ''" :disabled="!canContinue(5)"
                                v-else-if="step === 4" @click="changeStep(5)">Kontaktné údaje
                        </button>
                        <button class="btn btn-primary btn-no-scale btn-solid next-step" v-else
                                @click="makeReservation()">
                            Rezervovať
                        </button>
                    </div>
                </div>
                <div class="sending-overlay" :class="[(sending ? 'sending' : ''), (sent ? 'sent' : '')]">
                    <lord-icon
                            src="<?= get_template_directory_uri() ?>/assets/icons/icon-loader.json"
                            trigger="loop"
                            stroke="60"
                            class="loader"
                            colors="primary:#1f1f1f,secondary:#1f1f1f">
                    </lord-icon>
                    <lord-icon
                            src="<?= get_template_directory_uri() ?>/assets/icons/icon-check.json"
                            trigger="click"
                            stroke="100"
                            class="check"
                            colors="primary:#348165,secondary:#348165">
                    </lord-icon>
                </div>
            </div>
        </div>
    </div>
</div>