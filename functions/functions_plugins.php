<?php

add_action('admin_notices', 'madelo_starterpack_check_updates');

function madelo_starterpack_check_updates() {
    if(isset($_GET['update_madelo']) && $_GET['update_madelo'] == 1) {
        update_plugin('madelo_starterpack');
    }
}

// BASE -- COPY TO OTHER WEBSITES

function update_plugin($plugin_slug): bool
{
    $pluginLocation = ABSPATH . 'wp-content/plugins/' . $plugin_slug . '/' . $plugin_slug . '.php';

    if (!file_exists($pluginLocation)) {
        echo(notice("Plugin doesn't exist in wp-content/plugins!"));
    }

    // Set the URL of your update server
    $update_server_url = 'https://tvojweb.online/plugins';

// Get the current version of your plugin
    $plugin_data = get_plugin_data($pluginLocation);

// Get the version number
    $current_version = $plugin_data['Version'];

// Set the timeout for the HTTP request
    $timeout = 10;

// Create the URL to check for updates
    $update_url = $update_server_url . '?slug=' . $plugin_slug . '&version=' . $current_version;

// Make the HTTP request to check for updates
    $response = wp_remote_get($update_url, array('timeout' => $timeout));
    $status = $response['response']['code'] ?? false;
    // Check for errors
    if (is_wp_error($response) || $status != 200) {
        // Handle the error
        if (is_wp_error($response)) {
            echo(notice('Error checking for updates: ' . $response->get_error_message()));
            return false;
        }
        echo(notice('Error checking for updates: (#' . $status . ') ' . $response['response']['message']));
        return false;

    } else {
        // Parse the response
        $response_body = wp_remote_retrieve_body($response);
        $body_arr = json_decode($response_body);

        // Check if an update is available
        if (empty($body_arr)) {
            echo(notice('Response was empty. Contact support.'));
            return false;
        }
        if ($body_arr->code !== 200) {
            echo(notice('(#' . $body_arr->code . ') ' . $body_arr->message));
            return false;
        }
        if (!empty($body_arr->new_version)) {
            // Download the updated version of the plugin
            $update_file = wp_remote_get($body_arr->package_url, array('timeout' => $timeout));

            // Check for errors
            if (is_wp_error($update_file)) {
                // Handle the error
                echo(notice('Error downloading update: ' . $update_file->get_error_message()));
                return false;
            } else {
                // Install the updated version of the plugin
                $plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;
                $plugin_file = $plugin_dir . '/' . $plugin_slug . '.php';
                $update_file_path = $plugin_dir . '/' . $plugin_slug . '-update.zip';

                // Save the update file to disk
                file_put_contents($update_file_path, $update_file['body']);

                // Unzip the update file
                $zip = new ZipArchive();
                if ($zip->open($update_file_path) === true) {
                    // Remove the old plugin directory

                    if (is_dir($plugin_dir)) {
                        rrmdir($plugin_dir);
                    }

                    // Extract the update file to the new plugin directory
                    $zip->extractTo(WP_PLUGIN_DIR);
                    $zip->close();

                    // Deactivate the plugin
                    deactivate_plugins($plugin_file);

                    // Reactivate the plugin
                    activate_plugin($plugin_file);

                    echo(notice('Plugin updated successfully!', 'success'));
                    return true;
                } else {
                    // Handle the error
                    echo(notice('Error unzipping update'));
                    return false;
                }
            }
        }

        echo(notice('Plugin is up to date'));
        return false;
    }
}

// Recursive function to remove directory and its contents
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                    rrmdir($dir. DIRECTORY_SEPARATOR .$object);
                else
                    unlink($dir. DIRECTORY_SEPARATOR .$object);
            }
        }
        rmdir($dir);
    }
}

function notice($text, $type = "error") {
    return '<div class="notice notice-' . $type . ' is-dismissible"><p>' . $text . '</p></div>';
}