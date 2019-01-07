<?php

include(dirname(__FILE__) . '/kmz-functions.php');

if( !defined('WP_UNINSTALL_PLUGIN') ) exit;

global $wpdb;

if( kmz_has_field($wpdb->posts, 'post_views') ){
    $query = "ALTER TABLE $wpdb->posts DROP post_views";
    $wpdb->query($query);
}