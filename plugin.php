<?php

/**
 * Plugin Name: WordPress REST API Functions
 * Description: Bring functions file into REST API.
 * Author: Fredrik Forsmo
 * Author URI: https://frozzare.com
 * Version: 1.0.0
 * Plugin URI: https://github.com/wpup/functions
 * Textdomain: wp-functions
 * Domain Path: /languages/
 */

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Bootstrap plugin.
 */
add_action( 'plugins_loaded', function () {
    new WPUP\Functions\REST_API;
} );
