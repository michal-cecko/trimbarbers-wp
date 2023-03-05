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
        if(strpos($html, "<svgsvg") !== false) $html = str_replace("<svgsvg", "<svg", $html);
    }

    return $html;
}

function printMenu($location) {
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

function image_path($uri = true) {
    return ($uri ? get_template_directory_uri() : get_template_directory()) . "/assets/images";
}

function icon_path($uri = true) {
    return ($uri ? get_template_directory_uri() : get_template_directory()) . "/assets/icons";
}

function script_path($uri = true) {
    return ($uri ? get_template_directory_uri() : get_template_directory()) . "/assets/js";
}

function favicon_path($uri = true) {
    return ($uri ? get_template_directory_uri() : get_template_directory()) . "/assets/favicon";
}