<?php
const WP_VERSIONING = FALSE;
$version = wp_get_theme()->get("Version") ?? "1.0.0";
DEFINE("VERSION", !WP_VERSIONING ? time() : $version);

// THEME SETUP

add_action('after_setup_theme', 'theme_setup');
function theme_setup(): void
{
    //Theme support
    add_theme_support('menus');
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));
    add_theme_support('custom-logo', array('class' => 'custom-logo'));
    remove_theme_support("core-block-patterns");
}

// THEME CLEANUP
add_action('init', 'theme_cleanup', 9999);
function theme_cleanup(): void
{
    //Disable emojis
    /*remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );*/

    // Remove the REST API endpoint.
    remove_action('rest_api_init', 'wp_oembed_register_route');

    // Remove the REST API lines from the HTML Header
    remove_action('wp_head', 'rest_output_link_wp_head', 10);

    // Remove oEmbed discovery links.
    remove_action('wp_head', 'wp_oembed_add_discovery_links');

    // Remove oEmbed-specific JavaScript from the front-end and back-end.
    remove_action('wp_head', 'wp_oembed_add_host_js');

    //wp_head cleanup
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'index_rel_link');
    remove_action('wp_head', 'wp_generator');
    remove_action('do_feed_rdf', 'do_feed_rdf', 10, 1);
    remove_action('do_feed_rss', 'do_feed_rss', 10, 1);
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'parent_post_rel_link', 10, 0);
    remove_action('wp_head', 'start_post_rel_link', 10, 0);
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
    remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
    remove_action('wp_head', 'noindex', 1);
    remove_action('wp_head', 'rel_canonical');

    // Remove WordPress version from RSS feeds
    add_filter('the_generator', '__return_false');

    // Remove WordPress.org Dns-prefetch.
    remove_action('wp_head', 'wp_resource_hints', 2);

    // Remove type and id attributes
    add_filter('style_loader_tag', 'html5_script_style_tags');
    add_filter('script_loader_tag', 'html5_script_style_tags');
    function html5_script_style_tags($tag)
    {
        $tag = preg_replace('~\s+type=["\'][^"\']++["\']~', '', $tag);
        $tag = preg_replace('~\s+id=["\'][^"\']++["\']~', '', $tag);

        return $tag;
    }

    //TURN OFF COMMENTS
    //Redirect any user trying to access comments page
    global $pagenow;
    if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url());
        exit;
    }

    //Hide wpadminbar from FE
    add_filter('show_admin_bar', '__return_false');

    // Disable support for comments and trackbacks in post types
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }

    //Close comments on the front-end
    add_filter('comments_open', '__return_false', 20, 2);
    add_filter('pings_open', '__return_false', 20, 2);

    // Hide existing comments
    add_filter('comments_array', '__return_empty_array', 10, 2);

    // Remove comments page in menu
    add_action('admin_menu', function () {
        remove_menu_page('edit-comments.php');
    });

    // Remove comments links from admin bar
    add_action('init', function () {
        if (is_admin_bar_showing()) {
            remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }
    });

    //TURN OFF POSTS
    add_action('admin_menu', function () {
        remove_menu_page('edit.php');
    });
    add_action('admin_bar_menu', 'remove_default_post_type_menu_bar', 999);
    function remove_default_post_type_menu_bar($wp_admin_bar)
    {
        $wp_admin_bar->remove_node('new-post');
    }
}

// ENQUEUE / DEQUEUE SCRIPTS

const COMPONENT_PREFIX = "comp-";

function enqueue_component($name, $defaultPHPVars = [], ...$moreVals)
{
    $handle = COMPONENT_PREFIX . $name;
    wp_register_script($handle, get_template_directory_uri() . "/dist/js/components/" . $name . ".js", false, VERSION, true);
    wp_enqueue_script($handle);

    $vars = array_merge($moreVals, $defaultPHPVars);
    if (empty($vars)) return;
    wp_localize_script($handle, 'PHPVars', $vars);
}

add_filter('script_loader_tag', 'add_type_attribute', 50, 3);
function add_type_attribute($tag, $handle, $src)
{
    // if not your script, do nothing and return original $tag
    if (!str_contains($handle, COMPONENT_PREFIX)) {
        return $tag;
    }

    // load component as module.
    return '<script type="module" src="' . esc_url($src) . '"></script>';
}

add_action('wp_enqueue_scripts', 'enqueue_custom_scripts_links', 10);
function enqueue_custom_scripts_links(): void
{
    $defaultPHPVars = [
        'homeUrl' => get_home_url(),
        'ajaxUrl' => admin_url('admin-ajax.php')
    ];

    //JS + TS + VUE

    //DEFAULTS
    wp_enqueue_script('vue-js', 'https://cdn.jsdelivr.net/npm/vue/dist/vue.min.js');
    wp_enqueue_script('axios-js', 'https://cdnjs.cloudflare.com/ajax/libs/axios/1.2.4/axios.min.js', 'vue-js');
    wp_enqueue_script('moment-js', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js');
    wp_enqueue_script('moment-js-locale', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/sk.min.js');
    wp_enqueue_style('swiper-css', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/9.0.5/swiper-bundle.css');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js');
    wp_enqueue_script('lordicon-js', 'https://cdn.lordicon.com/libs/mssddfmo/lord-icon-2.1.0.js');
    wp_enqueue_script('aos-js', 'https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js');
    wp_enqueue_script('lazyload-js', script_path() . '/libs/lazyload.min.js');

    wp_register_script(
        "general-js",
        get_template_directory_uri() . '/dist/js/general.js',
        false,
        VERSION,
        TRUE
    );
    wp_enqueue_script('general-js');


    //COMPONENTS

    enqueue_component("commons", $defaultPHPVars);
    enqueue_component("header");
    enqueue_component("reservation");


    //STYLES

    //Remove global inline styles
    wp_dequeue_style('global-styles');

    if (!WP_VERSIONING) {
        $mainCSSPath = get_template_directory_uri() . '/dist/css/main.css';
    } else {
        $mainCSSPath = get_template_directory_uri() . '/dist/css/main.min.css';
    }

    wp_register_style('main-css', $mainCSSPath, FALSE, VERSION);
    wp_enqueue_style('main-css');
}

function admin_enqueue_scripts()
{
    $user = wp_get_current_user();
    $defaultPHPVars = [
        'homeUrl' => get_home_url(),
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ajax-nonce')
    ];

    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css', FALSE, time());
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js');
    wp_enqueue_script('vue-js', 'https://cdn.jsdelivr.net/npm/vue/dist/vue.min.js');
    wp_enqueue_script('moment-js', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js');
    wp_enqueue_script('moment-js-locale', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/sk.min.js');
    wp_enqueue_style('admin-css', get_template_directory_uri() . '/dist/css/admin/admin.css', FALSE, time());
    wp_enqueue_style('calendar-css', get_template_directory_uri() . '/dist/css/admin/calendar.css', FALSE, time());
    wp_enqueue_script('lordicon-js', 'https://cdn.lordicon.com/libs/mssddfmo/lord-icon-2.1.0.js');

    if( in_array('barber', $user->roles) || in_array('together-barber', $user->roles) ){
        wp_enqueue_style('barbers-css', get_template_directory_uri() . '/dist/css/admin/barber_role.css', FALSE, time());
    }

    wp_register_script(
        "admin-js",
        get_template_directory_uri() . '/dist/js/admin.js',
        false,
        time(),
        TRUE
    );
    wp_enqueue_script('admin-js');
    wp_localize_script('admin-js', 'PHPVars', $defaultPHPVars);

    //COMPONENTS
    enqueue_component("ajax", $defaultPHPVars);

    //FullCalendar (vendored locally — no CDN)
    wp_enqueue_script('fullcalendar-js', script_path() . '/libs/fullcalendar/index.global.min.js', [], '6.1.4', true);
    wp_enqueue_script('fullcalendar-sk', script_path() . '/libs/fullcalendar/locales/sk.global.js', ['fullcalendar-js'], '6.1.4', true);

    //CALENDAR WITH SK LOCALE
    enqueue_component("admin/calendar");
}

add_action('admin_enqueue_scripts', 'admin_enqueue_scripts', 10);