 <?php
/* 
 * Author:      Articled.io
 * Author URI:  https://articled.io/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!function_exists('add_action')) die(); 
require_once(plugin_dir_path(__FILE__).'/articled-options.php'); ?>




<div class="wrap">
    <h1>Articled.io - Webpush Notifications</h1>
</div>

<div class="wrap">
    <div class="metabox-holder">

        <?php
            if (!articled_apiPublicKey()) { ?>

            <div class="meta-box-sortables ui-sortable">
                <div class="postbox articled-box">
                    
                <h2 class="articled-title"><span>
                        Plugin Settings
                    </span></h2>

                    <div class="inside">
                        <form method="post" action="">
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <label for="apiPublicKey">API Public Key</label>
                                        </th>
                                        <td>
                                            <input type="text" 
                                                        class="large-text" 
                                                        name="apiPublicKey" 
                                                        id="apiPublicKey" 
                                                        placeholder="">
                                                <p class="description">The public key of your Articled.io account</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="apiPublicKey">Service Worker</label>
                                        </th>
                                        <td>
                                        <fieldset>
                                            
                                            <div class="articled-container">
                                                <input type="radio" name="articledServiceWorker" id="serviceWorkerOld" value="old" checked="checked">
                                                <span>I have a service worker installed at: <strong><?php echo get_site_url(); ?>/</strong></span>
                                                <input type="text" name="articledServiceWorkerUrl" id="articledServiceWorkerUrl" placeholder="service-worker.js">    
                                            </div>
                                            
                                            <div class="articled-container">
                                                <input type="radio" name="articledServiceWorker" id="serviceWorkerNew" value="new">
                                                <span>Install a service worker for me</span>    
                                            </div>
                                        
                                        </fieldset>
                                        <p class="description"><a href="https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API" style="font-style: normal;" target="_blank">What is a Service Worker?</a>
                                        </p>

                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('articled_add_apiPublicKey'); ?>">
                            <input style="margin-bottom: 10px; margin-top: 50px;" type="submit" 
                                            name="articled_add_apiPublicKey" 
                                            id="articled_add_apiPublicKey" 
                                            class="button button-primary" 
                                            value="Start Plugin">
                        </form>
                    </div>
                </div>
            </div>

        <?php } else { ?>
            
            <div class="meta-box-sortables ui-sortable">
                <div class="postbox articled-box">    
                    <h2 class="articled-title">
                        <span>
                            Apps
                        </span>
                    </h2>
                    <div class="inside">
                    
                        <?php

                            $apps = articled_apps();

                            if (count($apps) == 0) {
                                ?> 
                                <p>It seems you have not created an app in the Articled.io Dashboard yet. <br>Create an app in order to continue any further.</p>
                                <?php
                            } else {
                                ?> 
                                <p>Public information of your Articled.io apps created in the dashboard.</p>
                                <?php
                                foreach($apps as $app) {
                                    
                                    $appName = $app['name'];
                                    $appShortCode = '[Articled_' . $appName . ']';
                                    $appActive = (articled_active_app() == $appShortCode);
                                    $appActiveData = articled_active_app_data();
                                    $userDir = articled_userDir();

                                    ?>
                                        <div class="articled-app">
                                            <form action="" method="post">
                                                <table class="form-table">
                                                    <tbody>
                                                    <tr>
                                                            <th class="articled-first-list" scope="row">
                                                                <label><strong>App Name:</strong></label>
                                                            </th>
                                                            <td class="articled-first-list"><label><?php echo $appName; ?></label></td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">
                                                                <label><strong>Short Code:</strong></label>
                                                            </th>
                                                            <td><label><?php echo $appShortCode; ?></label></td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">
                                                                <label><strong>Active In:</strong></label>
                                                            </th>
                                                            <td>
                                                                <label><input type="checkbox" <?php echo (strpos($appActiveData, '0') !== false) ? "checked" : ""; ?> name="articledAppPosts" id="">Posts</label></td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">
                                                                <label></label>
                                                            </th>
                                                            <td>
                                                                <label><input type="checkbox" <?php echo (strpos($appActiveData, '1') !== false) ? "checked" : ""; ?> name="articledAppPages" id="">Pages</label></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <input type="text" name="appShortCode" style="display: none;" value="<?php echo $appShortCode; ?>">
                                                <input type="text" name="appName" style="display: none;" value="<?php echo $appName; ?>">
                                                
                                                <?php $appKword = $appActive ? 'Deactivate' : 'Activate'; ?>
                                                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('articled_' . ($appActive ? 'deactivate' : 'activate') . '_app'); ?>">
                                                <input type="submit" 
                                                    name="articled_<?php echo strtolower($appKword); ?>_app" 
                                                    id="articled_<?php echo $appKword . '_' . $appName; ?>_app" 
                                                    class="button button-<?php echo $appActive ? 'secondary' : 'primary'; ?>" 
                                                    value="<?php echo $appKword; ?>">  

                                                <?php if ($appActive) { ?>
                                                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('articled_update_app'); ?>">
                                                    <input type="submit" 
                                                    name="articled_update_app" 
                                                    class="button button-secondary" 
                                                    value="Update">  

                                                <?php } ?>

                                            </form>
                                        </div>
                                    <?php

                                }
                            }

                        ?>
                        
                    </div>
                </div>
            </div>

            <div class="meta-box-sortables ui-sortable">
                <div class="postbox articled-box">    
                    <h2 class="articled-title">
                        <span>
                        Feed Creator
                        </span>
                    </h2>
                    <div class="inside" style="display: none;">
                    <p>Creates a feed that updates everytime you create or edit a post. It can be used as a feed source when creating an app in the Articled.io Dashboard.<br> <i> The feed will not be indexed by search engines.</i></p>
                        
                    <?php if (articled_feedURL() == false) { ?>
                        <form action="" method="post">
                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('articled_create_feed'); ?>">
                            <input type="submit" 
                                    name="articled_create_feed" 
                                    id="articled_create_feed" 
                                    class="button button-secondary" 
                                    value="Create Feed">    
                        </form>
                    <?php } else { ?>
                        <form action="" method="post">
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <label>Feed URL: </label>
                                        </th>
                                        <td>
                                            <a taget="_blank" href="<?php echo  get_site_url() . '/articled-post-feed.html' ?>"><?php echo  get_site_url() . '/articled-post-feed.html' ?></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('articled_delete_feed'); ?>">
                            <input type="submit" 
                                    name="articled_delete_feed" 
                                    id="articled_delete_feed" 
                                    class="button button-secondary" 
                                    value="Delete Feed">    
                        </form>
                    <?php } ?>

                    </div>
                </div>
            </div>
            
            <div class="meta-box-sortables ui-sortable">
                <div class="postbox articled-box">    
                    <h2 class="articled-title">
                        <span>
                            Update Apps
                        </span>
                    </h2>
                    <div class="inside" style="display: none;">
                    <p>Synchronizes the current saved Apps in the Wordpress database with the Articled.io Dashboard.</p>

                    <form method="post" action="">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('articled_update_apps'); ?>">
                        <input type="submit" 
                                name="articled_update_apps" 
                                id="articled_update_apps" 
                                class="button button-secondary" 
                                value="Update Apps">
                     </form>
                    </div>
                </div>
            </div>

            <div class="meta-box-sortables ui-sortable">
                <div class="postbox articled-box">    
                    <h2 class="articled-title">
                        <span>
                            Delete Settings
                        </span>
                    </h2>
                    <div class="inside" style="display: none;">
                    <p>Deletes the current plugin settings including: Account API Key, widget installations, feed, and service worker.</p>

                    <form method="post" action="">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('articled_remove_apiPublicKey'); ?>">
                        <input type="submit" 
                                name="articled_remove_apiPublicKey" 
                                id="articled_remove_apiPublicKey" 
                                class="button button-secondary" 
                                value="Delete">
                     </form>
                    </div>
                </div>
            </div>
        
        <?php } ?>

    </div>
</div>
<hr>
<div class="wrap">
    <span>Copyright © Articled.io</span>
    <a style="margin: 0px 10px;" target="blank" href="https://articled.io/integrations/wordpress.html">Plugin Guide</a>
    <a style="margin: 0px 10px;" target="blank" href="https://articled.io/dashboard">Dashboard</a>
</div>