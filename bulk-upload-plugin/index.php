<?php
/*
Plugin Name: Bulk Plugin Uploader
Description: Allows users to upload and install multiple WordPress plugins at once.
Version: 1.0
Author: Naimur Rahman
Author URI: https://facebook.com/naimur444
*/

// Add a menu item under "Plugins" in the WordPress admin
function bulk_plugin_uploader_menu() {
    add_plugins_page(
        'Bulk Plugin Uploader',
        'Bulk Plugin Uploader',
        'manage_options',
        'bulk-plugin-uploader',
        'bulk_plugin_uploader_page'
    );
}
add_action('admin_menu', 'bulk_plugin_uploader_menu');

// Display the bulk plugin uploader page
function bulk_plugin_uploader_page() {
    if (isset($_POST['bulk_plugin_upload_submit'])) {
        $plugins = $_FILES['bulk_plugin_upload_files'];

        // Loop through the uploaded files
        for ($i = 0; $i < count($plugins['name']); $i++) {
            $plugin_file = $plugins['tmp_name'][$i];

            // Check if the uploaded file is a valid plugin
            if (bulk_validate_plugin($plugin_file)) {
                // Install the plugin
                $plugin = bulk_plugin_upload_install($plugin_file);
                if (is_wp_error($plugin)) {
                    echo '<div class="bulk-plugin-uploader-message error">Error installing plugin: ' . $plugins['name'][$i] . '</div>';
                } else {
                    echo '<div class="bulk-plugin-uploader-message success">Plugin installed: ' . $plugins['name'][$i] . '</div>';
                }
            } else {
                echo '<div class="bulk-plugin-uploader-message error">Invalid plugin file: ' . $plugins['name'][$i] . '</div>';
            }
        }
    }

    // Display the upload form
    ?>
    <div class="wrap">
        <h1>Bulk Plugin Uploader</h1>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="bulk_plugin_upload_files[]" multiple>
            <p class="submit"><input type="submit" name="bulk_plugin_upload_submit" class="button button-primary" value="Upload and Install"></p>
        </form>
    </div>
    <?php
}

// Validate the uploaded plugin file
function bulk_validate_plugin($plugin_file) {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    $result = validate_file($plugin_file);
    return is_wp_error($result) ? false : true;
}

// Install the plugin
function bulk_plugin_upload_install($plugin_file) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

    $upgrader = new Plugin_Upgrader();
    $result = $upgrader->install($plugin_file);

    if (is_wp_error($result)) {
        return $result;
    } else {
        return true;
    }
}
