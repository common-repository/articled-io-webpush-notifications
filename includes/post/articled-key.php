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
 * Makes two requests to the articled.io server to retrieve account data
 *  (public user dir, apps)
 * 
 * Saves data from articled.io server as options
 */
if (isset($_POST['articled_add_apiPublicKey']) &&
    isset($_POST['nonce'])  &&
    wp_verify_nonce($_POST['nonce'], 'articled_add_apiPublicKey')
){

    $articledServiceWorker = $_POST['articledServiceWorker'];

    $articledServiceWorkerUrl = sanitize_file_name($_POST['articledServiceWorkerUrl']);
    $apiPublicKey = sanitize_key($_POST['apiPublicKey']);
    
    //missing input
    if (empty($apiPublicKey)) {
        articled_message('API Public Key is empty', 'error');
    }
    
    //validating
    else if (strlen($apiPublicKey) !== 64) {
        articled_message('Invalid API Key', 'error');
    }
    else if ($articledServiceWorker !== 'new' && $articledServiceWorker !== 'old') {
        articled_message('Invalid service worker option', 'error');
    }
    
    //missing input
    else if (empty($articledServiceWorkerUrl) && $articledServiceWorker == 'old') {
        articled_message('Please enter the location of your service worker file', 'error');
    }

    else {
        
        //get user information
        $response = wp_remote_post( 'https://articled.io/api/user/public', array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => array( 'apiPublicKey' => $apiPublicKey ),
            'cookies' => array()
            )
        );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            articled_message($error_message, 'error');
         } else {
            
            $articled_user = json_decode(articled_esc($response['body']), true);
            $articled_user_status = $articled_user['status']; 

            if ($articled_user_status) {
                
                //get app information
                $response2 = wp_remote_post ('https://webpush.articled.io/api/app/public', array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'body' => array( 'apiPublicKey' => $apiPublicKey ),
                    'cookies' => array()
                    ));

                if (is_wp_error($response2)) {
                    $error_message = $response->get_error_message();
                    articled_message($error_message, 'error');
                } else {

                    $articled_apps = json_decode(articled_esc($response2['body']), true);
                    
                   
                    $swtxt = 'self.addEventListener("push",function(event){var data={};if(event.data){data=event.data.json();}var title=data.title||"Untitled";var message=data.message||"Empty";var tag=data.tag||null;var icon=data.icon||null;var url=data.url;var image=data.image;event.waitUntil(self.registration.showNotification(title,{body:message,tag:tag,icon:icon,image:image,data:url}));});self.addEventListener("notificationclick",function(event){var url=event.notification.data;if(url){clients.openWindow(url);}else{return;}});self.addEventListener("activate",function(event){event.waitUntil(self.clients.claim());});';
                    $swSuccess = false;

                    //setup service worker
                    if ($articledServiceWorker == 'new') {
                        
                        //write new service worker
                        $sw = fopen(ABSPATH . 'service-worker.js', 'w');
                        fwrite($sw, $swtxt);
                        
                        update_option('articled_serviceWorker', 'new-service-worker');
                        $swSuccess = true;
                        
                    } else {

                        //read current service worker
                        $csw = file_get_contents(ABSPATH . $articledServiceWorkerUrl);
                        
                        //verify file contents
                        if ($csw !== false) {
                                
                            //check if it doesn't have articled-worker
                            if (strpos($csw, 'articled-worker.js') == false) {

                                //import articled-worker to sw
                                $csw .= ' importScripts("articled-worker.js");';
                                
                                //create articled-worker
                                $sw = fopen(ABSPATH . 'articled-worker.js', 'w');
                                fwrite($sw, $swtxt);

                                //create service-worker
                                $sw = fopen(ABSPATH . 'service-worker.js', 'w');
                                fwrite($sw, $csw);
                                
                            }
                       
                            //update options
                            update_option('articled_serviceWorker', $articledServiceWorkerUrl);
                            $swSuccess = true;
                            
                            
                        }

                    }

                    //service worker installed or not found
                    if ($swSuccess) {

                        update_option('articled_apiPublicKey', $apiPublicKey);
                        update_option('articled_userDir', $articled_user['userDir']);
                        update_option('articled_apps', $articled_apps['apps']);
                        articled_message('Authentication successful!', 'success');
                        
                    } else {
                        articled_message('The service worker <strong>' . $articledServiceWorkerUrl . '</strong> was not found in your website!', 'error');
                    }

                }

            } else {
                articled_message("Authentication failed! <br><br>Is the API key correct?", 'error');
            }

        }
    }
}




/**
 * Removes all saved options
 * 
 * Deletes or restores the service worker to it's original state (if a previous
 *  service worker was used instead of creating a new one)
 */
if (isset($_POST['articled_remove_apiPublicKey']) &&
    isset($_POST['nonce'])  &&
    wp_verify_nonce($_POST['nonce'], 'articled_remove_apiPublicKey')
){

    $sw = articled_serviceWorker();

    //delete all options
    update_option('articled_apiPublicKey', false);
    update_option('articled_userDir', false);
    update_option('articled_apps', false);
    update_option('articled_active_app', false);
    update_option('articled_active_name', false);
    update_option('articled_active_app_data', false);
    update_option('articled_message_type', false);
    update_option('articled_message', false);
    update_option('articled_serviceWorker', false);
    update_option('articled_feedURL', false);

    if ($sw == 'new-service-worker') {
            unlink(ABSPATH . 'service-worker.js');
    } else {
        
            //delete articled-worker.js
            unlink(ABSPATH . 'articled-worker.js');
            
            //read current service worker
            $csw = file_get_contents(ABSPATH . $sw);
            
            //remove articled-worker import from service worker
            $rcsw = str_replace(' importScripts("articled-worker.js");', '', $csw);
            
            //update service worker
            $fsw = fopen(ABSPATH . $sw, 'w');
            fwrite($fsw, $rcsw);
    }

    articled_message('Successfully uninstalled the Articled.io Widget and removed settings', 'success');

}