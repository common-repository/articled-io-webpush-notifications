<?php
/* 
 * Author:      Articled.io
 * Author URI:  https://articled.io/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
 
 if (!function_exists('add_action')) die(); 

/**
 * Wordpress options getters & success/error message handler. 
 * Used accross the plugin.
 * 
 * Current options saved in database:
 * 
 *  articled_apiPublicKey       -> Public API key of the Articled.io account
 *  articled_userDir            -> Public directory in the Articled.io server that hosts the widget for the user
 *  articled_apps               -> Public information of the user created Articled.io apps
 *  articled_active_app         -> Short code of the active/installed app
 *  articled_active_app_data    -> Whether app is installed in posts/pages or both [0 = posts, 1 = pages]
 *  articled_active_app_name    -> Name of the active app
 * 
 * 
 *  articled_message_type       -> Message type [success, error]
 *  articled_message            -> Message
 *  articled_serviceWorker      -> Service worker data ['new-service-worker' or 'old-service-worker-name']
 * 
 *  articled_feedURL            -> URL of the created feed || false
 */

//option getters
function articled_apiPublicKey() {
    return get_option('articled_apiPublicKey', false);
}

function articled_userDir() {
    return get_option('articled_userDir', false);
}

function articled_apps() {
    return get_option('articled_apps', false);
}

function articled_active_app() {
    return get_option('articled_active_app', false);
}

function articled_active_app_name() {
    return get_option('articled_active_name', false);
}

function articled_active_app_data() {
    return get_option('articled_active_app_data', '');
}

function articled_serviceWorker() {
    return get_option('articled_serviceWorker', '');
}

function articled_feedURL() {
    return get_option('articled_feedURL', '');
}


//message handler
function articled_message($message, $type) {
    update_option('articled_message', $message);
    update_option('articled_message_type', $type);
    ?> <meta http-equiv="refresh" content="0"> <?php
}

function articled_message_handler() {

    $message = get_option('articled_message', false);
    if ($message) {
        $type = get_option('articled_message_type', false);
        if ($type == 'success') {
            articled_success_message($message);
        } else {
            articled_error_message($message);
        }
        update_option('articled_message', false);
        update_option('articled_message_type', false);
    } 

}


//error/success message
function articled_success_message($message) {
    ?>
        <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
            <?php echo "<p>" . $message . "</p>"; ?>
        </div>
    <?php

 }

 function articled_error_message($message) {
    ?>
        <div id="setting-error-settings_updated" class="error settings-error notice is-dismissible">
            <?php echo "<p>" . $message . "</p>"; ?>
        </div>
    <?php
 }


 function articled_esc($text) {
    $safe_text = wp_check_invalid_utf8( $text );
    $safe_text = _wp_specialchars($text, ENT_NOQUOTES);
    return apply_filters( 'esc_html', $safe_text, $text );
 }

?>