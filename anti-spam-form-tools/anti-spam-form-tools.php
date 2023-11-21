<?php
/*
Plugin Name: Anti-Spam Form Tools
Description: Adds a honeypot field to all Contact Form 7 forms to help prevent spam.
Plugin URI: https://github.com/Matthewpco/WP-Plugin-Anti-Spam-Form-Tools
Version: 1.2.0
Author: Gary Matthew Payne
Author URI: https://wpwebdevelopment.com/
License: GPL2
*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Register a setting for the checkbox
add_action('admin_init', 'asft_honeypot_register_settings');

function asft_honeypot_register_settings() {
    register_setting('asft_honeypot', 'asft_honeypot_enabled');
}

// Add the honeypot field to all CF7 forms
add_filter('wpcf7_form_elements', 'asft_honeypot_add_field');

function asft_honeypot_add_field($form) {

    if (get_option('asft_honeypot_enabled')) {
        $form .= '<input type="hidden" name="honeypot" value="" />';
    }

    return $form;
}


// Handle the honeypot field
add_action('wpcf7_before_send_mail', 'asft_honeypot_handle_field');

function asft_honeypot_handle_field($contact_form) {
    // Check if option is turned on
    if (get_option('asft_honeypot_enabled')) {
        // Check if honeypot field is filled out
        if (!empty($_POST['honeypot'])) {
            // Add a filter to clear the message if spam and prevent sending
            add_filter('wpcf7_mail_components', 'asft_honeypot_block_spam');
        }
    }
}

function asft_honeypot_block_spam($components) {
    // Set the recipient email address to an empty string
    $components['recipient'] = '';

    return $components;
}

// Register a settings page
add_action('admin_menu', 'asft_honeypot_settings_page');

function asft_honeypot_settings_page() {
    add_options_page(
        'Anti-Spam Form Tools', // page title
        'Anti-Spam Form Tools', // menu title
        'manage_options', // capability
        'anti-spam-form-tools', // menu slug
        'asft_honeypot_settings_page_content' // callback function
    );
}

function asft_honeypot_settings_page_content() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Get the value of the checkbox
    $is_enabled = get_option('asft_honeypot_enabled');

    ?>

    
    <h1>Anti-Spam Form Tools Settings</h1>

    <form method="post" action="options.php">
        <?php
        // Add settings page content here
        settings_fields('asft_honeypot'); // This should match with the parameter of register_setting
        ?>
        <label for="asft_honeypot_enabled">
            <input name="asft_honeypot_enabled" id="asft_honeypot_enabled" type="checkbox" value="1" class="code" <?php checked(1, $is_enabled); ?> />
                Enable Honeypot
        </label>
        <?php submit_button(); ?>
    </form>

    <?php
}

