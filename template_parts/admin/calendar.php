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
$services = new WP_Query($args)
?>

<div id="calendarContainer">
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
                    <form id="createAppointmentForm">
                        <div class="mb-3">
                            <label for="type">Typ</label>
                            <select v-model="appointment.type" class="form-control" id="type">
                                <option value="free">Voľno</option>
                                <option value="appointment" selected>Termín</option>
                            </select>
                        </div>
                        <div class="mb-3">
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
                        <div class="mb-3">
                            <label for="name" class="form-label">Meno a priezvisko</label>
                            <input v-model="appointment.customer.name" type="text" class="form-control" id="name"
                                   name="name">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input v-model="appointment.customer.email" type="email" class="form-control" id="email"
                                   name="email">
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Telefón</label>
                            <input v-model="appointment.customer.phone" type="tel" class="form-control" id="phone"
                                   name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="note" class="form-label">Poznámka</label>
                            <input v-model="appointment.note" type="tel" class="form-control" id="note" name="note">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                    <button type="button" class="btn btn-primary" @click="createAppointment()">Pridať</button>
                </div>
            </div>
        </div>
    </div>
</div>