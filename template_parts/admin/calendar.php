<style>
    #wpbody-content .wrap {
        display: none;
    }
</style>

<?php
$args = [
    'post_type' => 'service',
    'post_status' => 'publish',
    'posts_per_page' => -1
];
$services = new WP_Query($args);

$colors = [];
$durations = [];
if ($services->have_posts()) :
    while ($services->have_posts()) :
        $services->the_post();
        $colors[get_the_ID()] = get_field("admin_color");
        $durations[get_the_ID()] = get_field("serv-duration");
    endwhile;
    wp_reset_query();
endif;

$currentUser = wp_get_current_user();
$currentUserRole = getCurrentUserRole();

$barbers = getBarbers();
$barbersFinal = [];
foreach ($barbers as $barber) {
    $barbersFinal[$barber->ID] = [
        'id' => $barber->ID,
        'name' => $barber->first_name,
        'profileImage' => get_field("profile_image", "user_" . $barber->ID),
    ];
}

?>

<div id="calendarContainer">
    <div id="services-colors" style="display:none;"
         data-colors="<?= htmlspecialchars(json_encode($colors), ENT_QUOTES, 'UTF-8'); ?>"></div>
    <div id="services-durations" style="display:none;"
         data-durations="<?= htmlspecialchars(json_encode($durations), ENT_QUOTES, 'UTF-8'); ?>"></div>
    <div id="logged-user" style="display:none;" data-id="<?= $currentUser->ID ?>"
         data-name="<?= $currentUser->first_name ?>" data-role="<?= $currentUserRole ?>"></div>
    <div id="barbers" style="display:none;"
         data-barbers="<?= htmlspecialchars(json_encode($barbersFinal), ENT_QUOTES, 'UTF-8') ?>"></div>
    <div class="header-wrapper mb-3 d-flex align-items-center justify-content-between flex-wrap">
        <?php if ($currentUserRole !== "barber") : ?>
            <div class="barbers-toggler">
                <div class="barber" @click="changeCurrentBarberView(-1)"
                     :class="chosenBarberOnView === -1 ? 'active' : ''">
                    <div class="img">
                        <?= svgIcon(icon_path(false) . "/icon-all_barbers.svg") ?>
                    </div>
                    <span>Všetci</span>
                </div>
                <?php foreach ($barbersFinal as $barber) : ?>
                    <div class="barber" @click="changeCurrentBarberView(<?= $barber['id'] ?>)"
                         :class="chosenBarberOnView === <?= $barber['id'] ?> ? 'active' : ''">
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
        <?php else : ?>
            <div></div>
        <?php endif ?>
        <div class="buttons-wrapper ml-auto d-flex align-items-center flex-wrap">
            <button type="button" class="btn btn-primary" @click="createModal.show()">Pridať termín</button>
        </div>
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
                        <div class="part col-12" v-show="chosenBarberOnView === -1 || loggedInBarber.role === 'administrator'"
                             :class="appointment.type === 'appointment' ? 'col-md-6' : ''">
                            <div class="field-container mb-3">
                                <label for="type">Barber</label>
                                <select v-model="chosenBarberInForms" class="form-control" id="type">
                                    <option v-for="barber in barbers" :value="barber.id" v-html="barber.name"></option>
                                </select>
                            </div>
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
                        <div class="part col-12" :class="appointment.type === 'appointment' ? 'col-md-6' : ''">
                            <div class="field-container mb-3">
                                <label for="start" class="form-label">Začiatok</label>
                                <input v-model="appointment.datetime.start" onfocus="this.showPicker()" step="1800"
                                       type="datetime-local" class="form-control" id="start" name="start">
                            </div>
                        </div>
                        <div class="part col-12" v-show="appointment.type === 'appointment'"
                             :class="appointment.type === 'appointment' ? 'col-md-6' : ''">
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
                        <div class="part col-12" :class="appointment.type === 'appointment' ? 'col-md-6' : ''">
                            <div class="field-container mb-3">
                                <label for="end" class="form-label">Koniec</label>
                                <input v-model="appointment.datetime.end" onfocus="this.showPicker()" step="1800"
                                       type="datetime-local" class="form-control" id="end" name="end">
                            </div>
                        </div>
                    </div>
                    <div class="row divided-row" v-if="appointment.type === 'appointment'">
                        <div class="heading-part col-12">
                            <h3>Zákazník</h3>
                        </div>
                        <div class="part col-12">
                            <div class="field-container mb-3">
                                <label for="name" class="form-label">Vybrať z databázy</label>
                                <div class="searchable-select" :class="shownOptions ? 'show' : ''" v-click-outside="hideOptions">
                                    <input type="text" class="searchbar" v-model="customerSearchQuery" @input="debouncedFetchCustomers" @click="shownOptions = true">
                                    <div class="options">
                                        <div class="option" v-if="Object.keys(customers).length > 0" v-for="customer in customers" @click="chooseCustomer(customer)" v-html="formatCustomerOption(customer)"></div>
                                        <div class="nothing" v-if="Object.keys(customers).length === 0" v-html="'Žiadne výsledky.'"></div>
                                    </div>
                                </div>
                            </div>
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
                    <button type="button" class="btn btn-primary" v-html="buttonLoader ? 'Pridávam...' : 'Pridať'"
                            @click="createAppointment()"></button>
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
                        <div class="part col-12" v-show="chosenBarberOnView === -1 || loggedInBarber.role === 'administrator'"
                             :class="appointment.type === 'appointment' ? 'col-md-6' : ''">
                            <div class="field-container mb-3">
                                <label for="type">Barber</label>
                                <select v-model="chosenBarberInForms" class="form-control" id="type">
                                    <option v-for="barber in barbers" :value="barber.id" v-html="barber.name"></option>
                                </select>
                            </div>
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
                        <div class="part col-12" :class="appointment.type === 'appointment' ? 'col-md-6' : ''">
                            <div class="field-container mb-3">
                                <label for="start" class="form-label">Začiatok</label>
                                <input v-model="appointment.datetime.start" onfocus="this.showPicker()" step="1800"
                                       type="datetime-local" class="form-control" id="start" name="start">
                            </div>
                        </div>
                        <div class="part col-12" :class="appointment.type === 'appointment' ? 'col-md-6' : ''">
                            <div class="field-container mb-3">
                                <label for="end" class="form-label">Koniec</label>
                                <input v-model="appointment.datetime.end" onfocus="this.showPicker()" step="1800"
                                       type="datetime-local" class="form-control" id="end" name="end">
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
                    <button type="button" class="btn btn-primary btn-loader"
                            v-html="buttonLoader ? 'Upravujem...' : 'Upraviť'"
                            @click="editAppointment()">
                    </button>
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
                <div class="modal-footer" v-if="appointmentToDelete">
                    <div class="field-container me-auto"
                         v-if="appointmentToDelete.extendedProps.type === 'appointment'">
                        <input v-model="notify" type="checkbox" class="form-control" id="notify" name="notify">
                        <label for="notify">Odoslať notifikáciu?</label>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                    <button type="button" class="btn btn-danger" v-html="buttonLoader ? 'Vymazávam...' : 'Vymazať'"
                            @click="removeAppointment()">
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>