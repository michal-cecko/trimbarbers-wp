<?php

// CUSTOM MENU FROM WP MENU

function wp_get_menu_array($menu_name)
{
    $locations = get_nav_menu_locations();
    if (!empty($locations[$menu_name])) {
        $menu = wp_get_nav_menu_object($locations[$menu_name]);
        $array_menu = wp_get_nav_menu_items($menu, array('order' => 'DESC'));
        $menu = array();
        foreach ($array_menu as $m) {
            //print_r($m);
            if (empty($m->menu_item_parent)) {
                $menu[$m->ID] = array();
                $menu[$m->ID]['ID'] = $m->ID;
                $menu[$m->ID]['title'] = $m->title;
                $menu[$m->ID]['url'] = $m->url;
                $menu[$m->ID]['children'] = array();
                $menu[$m->ID]['class'] = $m->current == TRUE ? 'active' : '';
            }
        }
        $submenu = array();
        foreach ($array_menu as $m) {
            if ($m->menu_item_parent) {
                $submenu[$m->ID] = array();
                $submenu[$m->ID]['ID'] = $m->ID;
                $submenu[$m->ID]['title'] = $m->title;
                $submenu[$m->ID]['url'] = $m->url;
                $submenu[$m->ID]['class'] = $m->current == TRUE ? 'active' : '';
                if ($m->current == TRUE) {
                    $menu[$m->menu_item_parent]['class'] = 'active';
                }
                $menu[$m->menu_item_parent]['children'][$m->ID] = $submenu[$m->ID];
            }
        }

        return $menu;
    }

    return FALSE;
}

add_filter('wp_get_nav_menu_items', 'prefix_nav_menu_classes', 10, 3);

function prefix_nav_menu_classes($items, $menu, $args)
{
    _wp_menu_item_classes_by_context($items);

    return $items;
}

function svgIcon($path, array $attributes = [])
{
    ob_start();
    include $path;
    $html = ob_get_clean();

    if ($svgTagEndPosition = strpos($html, "<svg") !== false) {
        $attrHtml = "svg";
        foreach ($attributes as $attr => $value) {
            $attrHtml .= " " . $attr . "='" . implode(" ", $value) . "' ";
        }
        $html = substr_replace($html, $attrHtml, $svgTagEndPosition, 0);
        if (strpos($html, "<svgsvg") !== false) $html = str_replace("<svgsvg", "<svg", $html);
    }

    return $html;
}

function printMenu($location)
{
    $menu = wp_get_menu_array("footer-links");
    if ($menu) {
        foreach ($menu as $key => $item) { ?>
            <li>
                <a class="<?= $item['class'] ?>" href="<?= esc_url($item["url"]); ?>">
                    <?= esc_attr($item['title']); ?>
                </a>
            </li>
        <?php }
    } else {
        echo "<!-- Menu nebolo nájdené  -->";
    }
}


// ASSETS PATHS

function image_path($uri = true)
{
    return ($uri ? get_template_directory_uri() : get_template_directory()) . "/assets/images";
}

function icon_path($uri = true)
{
    return ($uri ? get_template_directory_uri() : get_template_directory()) . "/assets/icons";
}

function script_path($uri = true)
{
    return ($uri ? get_template_directory_uri() : get_template_directory()) . "/assets/js";
}

function favicon_path($uri = true)
{
    return ($uri ? get_template_directory_uri() : get_template_directory()) . "/assets/favicon";
}


function js_json_decode($json)
{
    return json_decode(str_replace("\\", "", $json), true);
}


function getStartAndEndDateOfWeek($timestamp)
{
    $currentDayOfWeek = date('N', $timestamp); // Get current day of the week (1 = Monday, 7 = Sunday)
    $weekStartDate = date('Y-m-d', strtotime("-" . ($currentDayOfWeek - 1) . " days", $timestamp)); // Calculate the start date of the current week
    $weekEndDate = date('Y-m-d', strtotime("+" . (7 - $currentDayOfWeek) . " days", $timestamp)); // Calculate the end date of the current week

    return ["start" => $weekStartDate, "end" => $weekEndDate];
}


function getRandomString($length)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }

    return $randomString;
}

function getMonthName($num)
{
    if ($num == 1) return "Január";
    if ($num == 2) return "Február";
    if ($num == 3) return "Marec";
    if ($num == 4) return "Apríl";
    if ($num == 5) return "Máj";
    if ($num == 6) return "Jún";
    if ($num == 7) return "Júl";
    if ($num == 8) return "August";
    if ($num == 9) return "September";
    if ($num == 10) return "Október";
    if ($num == 11) return "November";
    return "December";
}

function getDayName($num)
{
    if ($num == 1) return "Pondelok";
    if ($num == 2) return "Utorok";
    if ($num == 3) return "Streda";
    if ($num == 4) return "Štvrtok";
    if ($num == 5) return "Piatok";
    if ($num == 6) return "Sobota";
    return "Nedeľa";
}

function getShortDayName($num)
{
    return substr(getDayName($num), 0, 3);
}

function showNotification($text, $status = "success")
{
    ob_start(); ?>
    <div class="notificaiton">
        <?= svgIcon(icon_path(false) . "/icon-check.svg") ?>
        <span><?= $text ?></span>
    </div>
    <?php return ob_get_clean();
}

function getBarbers($idsOnly = false)
{
    $args = [
        'role' => 'barber',
        'meta_query' => [
            "relation" => "AND",
            [
                'key' => 'worktime_start',
                'compare' => 'EXISTS',
            ],
            [
                'key' => 'worktime_end',
                'compare' => 'EXISTS',
            ],
            [
                'key' => 'lunchtime_start',
                'compare' => 'EXISTS',
            ],
            [
                'key' => 'lunchtime_end',
                'compare' => 'EXISTS',
            ],
        ],
    ];
    if ($idsOnly) $args['fields'] = "ID";
    return get_users($args);
}

function getCurrentUserRole()
{
    $current_user = wp_get_current_user();
    if (!empty($current_user->roles)) {
        return $current_user->roles[0];
    }
    return null;
}