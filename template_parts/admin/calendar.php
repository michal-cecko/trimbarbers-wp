<style>
    #wpbody-content .wrap {
        display: none;
    }
</style>

<?php
$args = [
    'post_type' => 'service',
    'post_status' => 'published',
    'posts_per_page' => -1
];
$services = new WP_Query($args);

$colors = [];
if ($services->have_posts()) :
    while ($services->have_posts()) :
        $services->the_post();
        $colors[get_the_ID()] = get_field("admin_color");
    endwhile;
    wp_reset_query();
endif;

$currentUser = wp_get_current_user();
if(!current_user_can('barber')) {

}


$barbers = get_users([
    'role' => 'barber',
]);
$barbersFinal = [];
foreach ($barbers as $barber) {
    $barbersFinal[$barber->ID] = [
        'id' => $barber->ID,
        'name' => $barber->display_name,
        'profileImage' => get_field("profile_image", "user_" . $barber->ID),
    ];
}

?>

<div id="calendarContainer">
    <div id="services-colors" style="display:none;"
         data-colors="<?= htmlspecialchars(json_encode($colors), ENT_QUOTES, 'UTF-8'); ?>"></div>
    <div id="logged-user" style="display:none;" data-id="<?= $currentUser->ID ?>"
         data-name="<?= $currentUser->display_name ?>" data-role="<?= $currentUser->roles[0] ?>"></div>
    <div id="barbers" style="display:none;"
         data-barbers="<?= htmlspecialchars(json_encode($barbersFinal), ENT_QUOTES, 'UTF-8') ?>"></div>
    <div class="barbers-toggler">
        <div class="barber" @click="changeCurrentBarberView(-1)" :class="chosenBarber === -1 ? 'active' : ''">
            <div class="img">
                <?= svgIcon(icon_path(false) . "/icon-all_barbers.svg") ?>
            </div>
            <span>Všetci</span>
        </div>
        <?php foreach ($barbersFinal as $barber) : ?>
            <div class="barber" @click="changeCurrentBarberView(<?= $barber['id'] ?>)"
                 :class="chosenBarber === <?= $barber['id'] ?> ? 'active' : ''">
                <div class="img">
                    <?php if (!empty($barber['profileImage'])) : ?>
                        <img src="<?= $barber['profileImage'] ?>" alt="Fotka">
                    <?php else : ?>
                        <?= svgIcon(icon_path(false) . "/icon-question_mark_admin.svg") ?>
                    <?php endif ?>
                </div>
                <span><?= $barber['name'] ?></span>
            </div>
        <?php endforeach ?>
    </div>
    <div id="calendar"></div>
    <div class="modal fade" id="createAppointmentModal" tabindex="-1" aria-labelledby="createAppointmentModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createEventModalLabel">Pridať termín</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row divided-row">
                        <div class="heading-part col-12">
                            <h3>Rezervácia</h3>
                        </div>
                        <div class="part col-12" :class="appointment.type === 'appointment' ? 'col-md-6' : ''">
                            <div class="field-container mb-3">
                                <label for="type">Typ</label>
                                <select v-model="appointment.type" class="form-control" id="type">
                                    <option value="free">Voľno</option>
                                    <option value="appointment" selected>Termín</option>
                                </select>
                            </div>
                        </div>
                        <div class="part col-md-6 col-12" v-show="appointment.type === 'appointment'">
                            <div class="field-container mb-3">
                                <label for="service">Služba</label>
                                <select v-model="appointment.serviceID" class="form-control" id="service">
                                    <option value="" selected>Vyberte službu</option>
                                    <?php if ($services->have_posts()) : ?>
                                        <?php while ($services->have_posts()) : $services->the_post() ?>
                                            <option value="<?= get_the_ID() ?>"><?= get_the_title() ?></option>
                                        <?php endwhile ?>
                                        <?php wp_reset_query(); endif ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row divided-row" v-if="appointment.type === 'appointment'">
                        <div class="heading-part col-12">
                            <h3>Zákazník</h3>
                        </div>
                        <div class="part col-md-4 col-12">
                            <div class="field-container mb-3">
                                <label for="name" class="form-label">Meno a priezvisko</label>
                                <input v-model="appointment.customer.name" type="text" class="form-control" id="name"
                                       name="name">
                            </div>
                        </div>
                        <div class="part col-md-4 col-12">
                            <div class="field-container mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input v-model="appointment.customer.email" type="email" class="form-control" id="email"
                                       name="email">
                            </div>
                        </div>
                        <div class="part col-md-4 col-12">
                            <div class="field-container mb-3">
                                <label for="phone" class="form-label">Telefón</label>
                                <input v-model="appointment.customer.phone" type="tel" class="form-control" id="phone"
                                       name="phone">
                            </div>
                        </div>
                    </div>
                    <div class="row divided-row">
                        <div class="heading-part col-12">
                            <h3>Ostatné</h3>
                        </div>
                        <div class="part col-12">
                            <div class="field-container mb-3">
                                <label for="note" class="form-label">Poznámka</label>
                                <input v-model="appointment.note" type="tel" class="form-control" id="note" name="note">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="field-container me-auto" v-if="appointment.type === 'appointment'">
                        <input v-model="notify" type="checkbox" class="form-control" id="notify" name="notify">
                        <label for="notify">Odoslať notifikáciu?</label>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                    <button type="button" class="btn btn-primary" @click="createAppointment()">Pridať</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editAppointmentModal" tabindex="-1" aria-labelledby="editAppointmentModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAppointmentModalLabel">Upraviť termín</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row divided-row">
                        <div class="heading-part col-12">
                            <h3>Rezervácia</h3>
                        </div>
                        <div class="part col-12" :class="appointment.type === 'appointment' ? 'col-md-6' : ''">
                            <div class="field-container mb-3">
                                <label for="type">Typ</label>
                                <select v-model="appointment.type" class="form-control" id="type">
                                    <option value="free">Voľno</option>
                                    <option value="appointment" selected>Termín</option>
                                </select>
                            </div>
                        </div>
                        <div class="part col-md-6 col-12" v-show="appointment.type === 'appointment'">
                            <div class="field-container mb-3">
                                <label for="service">Služba</label>
                                <select v-model="appointment.serviceID" class="form-control" id="service">
                                    <option value="" selected>Vyberte službu</option>
                                    <?php if ($services->have_posts()) : ?>
                                        <?php while ($services->have_posts()) : $services->the_post() ?>
                                            <option value="<?= get_the_ID() ?>"><?= get_the_title() ?></option>
                                        <?php endwhile ?>
                                        <?php wp_reset_query(); endif ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row divided-row" v-if="appointment.type === 'appointment'">
                        <div class="heading-part col-12">
                            <h3>Zákazník</h3>
                        </div>
                        <div class="part col-md-4 col-12">
                            <div class="field-container mb-3">
                                <label for="name" class="form-label">Meno a priezvisko</label>
                                <input v-model="appointment.customer.name" type="text" class="form-control" id="name"
                                       name="name">
                            </div>
                        </div>
                        <div class="part col-md-4 col-12">
                            <div class="field-container mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input v-model="appointment.customer.email" type="email" class="form-control" id="email"
                                       name="email">
                            </div>
                        </div>
                        <div class="part col-md-4 col-12">
                            <div class="field-container mb-3">
                                <label for="phone" class="form-label">Telefón</label>
                                <input v-model="appointment.customer.phone" type="tel" class="form-control" id="phone"
                                       name="phone">
                            </div>
                        </div>
                    </div>
                    <div class="row divided-row">
                        <div class="heading-part col-12">
                            <h3>Ostatné</h3>
                        </div>
                        <div class="part col-12">
                            <div class="field-container mb-3">
                                <label for="note" class="form-label">Poznámka</label>
                                <input v-model="appointment.note" type="tel" class="form-control" id="note" name="note">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="field-container me-auto" v-if="appointment.type === 'appointment'">
                        <input v-model="notify" type="checkbox" class="form-control" id="notify" name="notify">
                        <label for="notify">Odoslať notifikáciu?</label>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                    <button type="button" class="btn btn-primary" @click="editAppointment()">Upraviť</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="deleteAppointmentModal" tabindex="-1" aria-labelledby="deleteAppointmentModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAppointmentModalLabel">Zmazať termín</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <p>Naozaj chcete vymazať tento termín?</p>
                            <div class="appointmentToDelete" v-if="appointmentToDelete">
                                <div class="time"
                                     v-html="moment(appointmentToDelete.start).format('HH:mm') + ' - ' + moment(appointmentToDelete.end).format('HH:mm')"></div>
                                <div class="title" v-html="appointmentToDelete.title"></div>
                                <div class="service" v-if="appointmentToDelete.extendedProps.service"
                                     v-html="appointmentToDelete.extendedProps.service"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="field-container me-auto" v-if="appointment.type === 'appointment'">
                        <input v-model="notify" type="checkbox" class="form-control" id="notify" name="notify">
                        <label for="notify">Odoslať notifikáciu?</label>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                    <button type="button" class="btn btn-danger" @click="removeAppointment()">Vymazať</button>
                </div>
            </div>
        </div>
    </div>

</div>