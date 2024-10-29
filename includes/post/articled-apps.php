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
 * Makes a request to the articled.io server to retrieve the public widget directory
 * 
 * Updates options with data from server  
 */
if (isset($_POST['articled_update_apps']) &&
    isset($_POST['nonce'])  &&
    wp_verify_nonce($_POST['nonce'], 'articled_update_apps')
){

    $apiPublicKey = articled_apiPublicKey();

    //get app information
    $response = wp_remote_post ('https://webpush.articled.io/api/app/public', array(
        'method' => 'POST',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(),
        'body' => array( 'apiPublicKey' => $apiPublicKey ),
        'cookies' => array()
        ));

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        articled_message($error_message, 'error');
    } else {

        $articled_apps = json_decode(articled_esc($response['body']), true);
        
        update_option('articled_apps', $articled_apps['apps']);
        
        articled_message('Apps updated successfully!', 'success');

    }

}


?>