<?php
/* 
 * Author:      Articled.io
 * Author URI:  https://articled.io/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!function_exists('add_action')) die(); 
require_once(plugin_dir_path(__FILE__).'../articled-options.php');




/**
 * Adds app to options
 */
if (isset($_POST['articled_activate_app']) &&
    isset($_POST['nonce'])  &&
    wp_verify_nonce($_POST['nonce'], 'articled_activate_app')
){

    $apiPublicKey = articled_apiPublicKey();
    $appshortCode = sanitize_text_field($_POST['appShortCode']);
    $appName = sanitize_name($_POST['appName']);
    $appPages = $_POST['articledAppPages'];
    $appPosts = $_POST['articledAppPosts'];
    
    //validating
    if ($appPages !== 'on' && $appPosts !== 'on') {
        articled_message('At least one field must be selected!', 'error');
    } else {

        $activeAppData = '';

        if ($appPosts == 'on') {
            $activeAppData = '0';
        }
        if ($appPages == 'on') {
            $activeAppData .= '1';
        }

        update_option('articled_active_app', $appshortCode);
        update_option('articled_active_app_name', $appName);
        update_option('articled_active_app_data', $activeAppData);

        articled_message('Successfully activated app!', 'success');

    }
    
}




/**
 * Removes app from options
 */
if (isset($_POST['articled_deactivate_app']) &&
    isset($_POST['nonce'])  &&
    wp_verify_nonce($_POST['nonce'], 'articled_deactivate_app')
){

    update_option('articled_active_app', false);
    update_option('articled_active_app_name', false);
    update_option('articled_active_app_data', false);

    articled_message('Successfully deactivated app!', 'success');

}




/**
 * Updates options of app
 */
if (isset($_POST['articled_update_app']) &&
    isset($_POST['nonce'])  &&
    wp_verify_nonce($_POST['nonce'], 'articled_update_app')
){
    
    $appPages = $_POST['articledAppPages'];
    $appPosts = $_POST['articledAppPosts'];

    if ($appPages !== 'on' && $appPosts !== 'on') {

        articled_message('At least one field must be selected!', 'error');

    } else {

        $activeAppData = '';

        $activeAppData = ($appPosts == 'on') ? '0' : '';
        $activeAppData .= ($appPages == 'on') ? '1' : '';
        
        update_option('articled_active_app_data', $activeAppData);
        
        articled_message('Successfully updated app!', 'success');

    }
    
}





