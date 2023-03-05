<?php

// OPTIONS PAGES

if (function_exists('acf_add_options_page')) {

    acf_add_options_page(
        array(
            'page_title' => 'Nastavenia stránky',
            'menu_title' => 'Nastavenia stránky',
            'menu_slug' => 'theme-general-settings',
            'capability' => 'edit_posts',
            'redirect' => FALSE
        ));
}