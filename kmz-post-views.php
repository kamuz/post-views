<?php

/*
Plugin Name: KMZ Page Views
Description: Simple plugin for count post views
Plugin URI: https://wpdev.pp.ua
Author: Vladimir Kamuz
Author URI: https://wpdev.pp.ua
Text Domain: kmz-post-views
Version: 1.0.0
*/

register_activation_hook(__FILE__, 'kmz_post_create_field');

function kmz_post_create_field(){
    global $wpdb;

    $query = "ALTER TABLE $wpdb->posts ADD post_views INT NOT NULL DEFAULT '0'";
    $wpdb->query($query);
}