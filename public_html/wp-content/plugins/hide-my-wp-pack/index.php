<?php
/*
  Plugin Name: WP Ghost - Advanced Pack
  Plugin URI: https://hidemywpghost.com
  Author: Hide My WP Ghost
  Description: Advanced security features for WP Ghost plugin
  Version: 1.4.3
  Author URI: https://hidemywpghost.com
  Network: true
  Requires at least: 5.3
  Tested up to: 6.8
  Requires PHP: 7.0
 */

if (defined('ABSPATH') && !defined('HMW_VERSION') ) {

    //Set current plugin version
    define('HMWPP_VERSION', '1.4.3');
    define('HMWP_VERSION_MIN', '5.0.00');

    //Set the plugin basename
    define('HMWPP_BASENAME',  plugin_basename(__FILE__));

    //important to check the PHP version
    try {

        //Call config files
        include dirname(__FILE__) . '/config/config.php';

        //import main classes
        include_once _HMWPP_CLASSES_DIR_ . 'ObjController.php';

        if(class_exists('HMWPP_Classes_ObjController')) {
            //Load Exception, Error and Tools class
	        HMWPP_Classes_ObjController::getClass('HMWPP_Classes_Tools');
	        HMWPP_Classes_ObjController::getClass('HMWPP_Classes_Error');

            //Load Front Controller
            HMWPP_Classes_ObjController::getClass('HMWPP_Classes_FrontController');

            //if the disable signal is on, return
            //don't run cron hooks and update if there are installs
            if (defined('HMWPP_DISABLE') && HMWPP_DISABLE) {
                return;
            }elseif (!is_multisite() && defined('WP_INSTALLING') && WP_INSTALLING) {
                return;
            }elseif (is_multisite() && defined('WP_INSTALLING_NETWORK') && WP_INSTALLING_NETWORK) {
                return;
            }elseif (defined('WP_UNINSTALL_PLUGIN') && WP_UNINSTALL_PLUGIN <> ''){
                return;
            }

            //don't load brute force and events on cron jobs
            if(!defined('DOING_CRON') || !DOING_CRON){
                if (HMWPP_Classes_Tools::getOption('hmwp_templogin') ) {
                    HMWPP_Classes_ObjController::getClass('HMWPP_Controllers_Templogin');
                }
                if (HMWPP_Classes_Tools::getOption('hmwp_uniquelogin') ) {
                    HMWPP_Classes_ObjController::getClass('HMWPP_Controllers_Uniquelogin');
                }
                if (HMWPP_Classes_Tools::getOption('hmwp_2falogin') ) {
                    HMWPP_Classes_ObjController::getClass('HMWPP_Controllers_Twofactor');
                }
            }

            //Request the plugin update when a new version is released
            if (!defined('WP_AUTO_UPDATE_HMWP') || WP_AUTO_UPDATE_HMWP) {
                require dirname(__FILE__) . '/update.php';
            }

        }

    } catch ( Exception $e ) {

    }

}
