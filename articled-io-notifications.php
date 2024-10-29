<?php
/**
 * Plugin Name: Articled.io Webpush Notifications
 * Plugin URI:  https://articled.io/integrations/wordpress/
 * Description: Targeted webpush notifications plugin for the Articled.io widget.
 * Version:     1.0.4
 * Author:      Articled.io
 * Author URI:  https://articled.io/
 * Requires at least: 3.0.0
 * Tested up to: 5.2.2
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

 if (!defined('ABSPATH')) {
     exit;
 }

 if (!current_user_can('administrator')) {
     exit;
 }

 require_once(plugin_dir_path(__FILE__).'/includes/articled-options.php');


 /**
  * Registers the plugin menu page in the admin panel.
  */
 function articled_menu() {
    add_menu_page('Articled.io Webpush Notifications', 
    'Articled.io', 
    'manage_options', 
    'articled-menu', 
    'articled_menu_page', 
     plugin_dir_url(__FILE__).'/assets/img/logo.png', 
    200);
}    

add_action('admin_menu', 'articled_menu');

//register widget script
wp_register_script('articled-widget-script', false);






/**
 * Main menu method, loads the plugin html and code accordingly.
 */
function articled_menu_page() {
    
    //message handler
    articled_message_handler();

    //load html
    require_once(plugin_dir_path(__FILE__).'/includes/articled-html.php');
    
    //load api key code
    require_once(plugin_dir_path(__FILE__).'/includes/post/articled-key.php');
   
    //load assets
    wp_enqueue_style('articled-io-notifications', plugin_dir_url(__FILE__).'/assets/css/style.css');
    wp_enqueue_script('articled-io-notifications', plugin_dir_url(__FILE__).'/assets/js/ui.js');

            
    if (articled_apiPublicKey()) {
        
        //app
        require_once(plugin_dir_path(__FILE__).'/includes/post/articled-app.php');

        //update apps
        require_once(plugin_dir_path(__FILE__).'/includes/post/articled-apps.php');
        
        //create/remove feed
        require_once(plugin_dir_path(__FILE__).'/includes/post/articled-feed.php');
    
    }

 }

 


 /**
  * Adds an action hook to wordpress that checks database options for an active app,
  * If an app is found, it loads the widget javascipt to the html depending,
  * on the app settings.
  */

 function articled_widget_action() {

    if (get_option('articled_active_app')) { 
        
        $appName = get_option('articled_active_app_name');
        $userDir = get_option('articled_userDir');
        $appData = get_option('articled_active_app_data');
        
        $postType = get_post_type();
        $cont = false;

        if ($postType == 'post' && strpos($appData, '0') !== false) {
            $cont = true;
        }
        if ($postType == 'page' && strpos($appData, '1') !== false) {
            $cont = true;
        }

        if ($cont) {

            wp_enqueue_script('articled-widget-script');
            wp_add_inline_script('articled-widget-script', 
            '<script>' .
            '(function(a,r,t,i,c,l) {' .
            'i=r.getElementsByTagName("head")[0],' .
            'c=r.createElement("script"),' . 
            'l=r.createElement("link");' . 
            'c.type="text/javascript";c.src=t+a+".js";' . 
            'c.async=true;l.type="text/css";' . 
            'l.rel="stylesheet";l.href=t+a+".css";' . 
            'i.appendChild(l);i.appendChild(c);' . 
            '})("articled",document,"https://articled.io/widget/' . $userDir . '/' . $appName . '/");' . 
            '</script>' );

        }

     }

}
add_action('wp_footer', 'articled_widget_action');




/**
 * Adds an action hook to wordpress that creates an html file with the
 * latest 15 posts every time a post is updated or created.
 */
function articled_feed_action() {
    
    $feedURL = get_option('articled_feedURL', false);

    if ($feedURL !== false) {

        $args = array( 'numberposts' => '15' );
        $recent_posts = wp_get_recent_posts( $args );
        
        $posthtml = '<html><head>'
        . '<meta name="articled-feed" />'
        . '<meta name="robots" content="noindex">'
        . '<title>Articled.io - Article Feed</title>'
        . '</head><body><h1 class="ac-h1">Articled.io Auto Generated Feed</h1><div class="articled-feed-container">';

        foreach($recent_posts as $recent){
            
            $pstatus = $recent['post_status'];

            if ($pstatus == 'publish') {

                $pid = $recent['ID'];
                $authorid = $recent['post_author'];
                $rmore = get_extended(get_post_field('post_content',$pid));

                $ptitle = $recent['post_title'];
                $pimage = wp_get_attachment_url(get_post_thumbnail_id($pid)); 
                $psummary = $recent['post_excerpt'];
                $pcategory = get_the_category($pid)[0]->name; 
                $pauthor = get_author_name($authorid);
                $pdate = date('d F, Y', strtotime($recent['post_date']));
                $purl = get_permalink($recent['ID']);
                
                $posthtml .= '<div class="ac-article">'
                . '<p class="ac-title">' . $ptitle . '</p>';

                if ($pimage !== false) {
                    $posthtml .= '<img class="ac-img" src="' . $pimage . '">';
                }

                if (strlen($psummary) > 0) {
                    $posthtml .= '<p class="ac-summary">' . $psummary . '</p>';
                } else {
                    $posthtml .= '<p class="ac-summary">' . wp_strip_all_tags($rmore['main']) . '</p>';
                }

                $posthtml .= '<div class="ac-sources"><span class="ac-category">' . $pcategory . '</span>'
                . '<span class="ac-author">' . $pauthor . '</span></div>'
                . '<p class="ac-date">' . $pdate . '</p>'
                . '<a class="ac-url" href="' . $purl . '">Article Link</a></div>';

            }

        }

        $posthtml .= '</div></body></html>';

        $sw = fopen(ABSPATH . $feedURL, 'w');
        fwrite($sw, $posthtml);

    }

}

add_action('transition_post_status', 'articled_feed_action');




/**
 * Adds widget script short code for all apps 
 * Short code naming syntax is [Articled_$appName].
 */
foreach(get_option('articled_apps', false) as $app) {
    
    $userDir = get_option('articled_userDir', false);
    $appName = $app['name'];
    
    add_shortcode ('Articled_' . $appName . '', function() use ($userDir, $appName) {
        
        wp_enqueue_script('articled-widget-script');
        wp_add_inline_script('articled-widget-script', 
        '<script>' .
        '(function(a,r,t,i,c,l) {' .
        'i=r.getElementsByTagName("head")[0],' .
        'c=r.createElement("script"),' . 
        'l=r.createElement("link");' . 
        'c.type="text/javascript";c.src=t+a+".js";' . 
        'c.async=true;l.type="text/css";' . 
        'l.rel="stylesheet";l.href=t+a+".css";' . 
        'i.appendChild(l);i.appendChild(c);' . 
        '})("articled",document,"https://articled.io/widget/' . $userDir . '/' . $appName . '/");' . 
        '</script>' );

    });
}

 ?>