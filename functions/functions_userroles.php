<?php

// Remove default roles except admin
function remove_existing_roles() {
    $roles = wp_roles()->get_names();
    foreach ($roles as $role => $name) {
        if ($role !== 'administrator') {
            remove_role($role);
        }
    }
}
add_action('init', 'remove_existing_roles');


// add barber role
function add_barber_role() {
    $admin_caps = get_role('administrator')->capabilities;
    add_role('barber', 'Barber', $admin_caps);
}
add_action('init', 'add_barber_role');