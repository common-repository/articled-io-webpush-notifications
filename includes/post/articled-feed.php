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
 * Creates a feed with the latest posts
 */
if (isset($_POST['articled_create_feed']) &&
    isset($_POST['nonce'])  &&
    wp_verify_nonce($_POST['nonce'], 'articled_create_feed')
){

    $args = array( 'numberposts' => '15' );
    $recent_posts = wp_get_recent_posts( $args );
    
    $posthtml = '<html><head>'
    . '<meta name="articled-feed" />'
    . '<meta name="robots" content="noindex">'
    . '<title>Articled.io - Article Feed</title>'
    . '</head><body><h1 class="ac-h1">Articled.io Auto Generated Feed</h1><div class="articled-feed-container">'
    . '<h3 class="ac-h3">Articles will appear here once you publish or edit an article</h3>'
    . '</div></body></html>';

    $sw = fopen(ABSPATH . 'articled-post-feed.html', 'w');
    fwrite($sw, $posthtml);

    update_option('articled_feedURL', 'articled-post-feed.html');

    articled_message('Successfully created feed at: <strong>' .  get_site_url() . '/articled-post-feed.html</strong>', 'success');
        
    
}

/*
if (array_key_exists('articled_create_feed', $_POST)) {


}
*/



/**
 * Deletes created feed
 */
if (isset($_POST['articled_delete_feed']) &&
    isset($_POST['nonce'])  &&
    wp_verify_nonce($_POST['nonce'], 'articled_delete_feed')
){

    $feedURL = articled_feedURL();

    unlink(ABSPATH . $feedURL);
    
    update_option('articled_feedURL', false);
    
    articled_message('Successfully deleted feed!', 'success');

}

?>