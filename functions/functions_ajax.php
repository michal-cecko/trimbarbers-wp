<?php
//AJAX

add_action( 'wp_ajax_delete_form', 'delete_submitted_form' );
add_action( 'wp_ajax_nopriv_delete_form', 'delete_submitted_form' );

function delete_submitted_form() {
    wp_send_json([], 200);
}
