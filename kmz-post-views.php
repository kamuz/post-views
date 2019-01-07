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

include(dirname(__FILE__) . '/kmz-functions.php');

/**
 * Add new field in the DB table wp_posts
 */

register_activation_hook(__FILE__, 'kmz_post_create_field');

function kmz_post_create_field(){
    global $wpdb;
    if( !kmz_has_field($wpdb->posts, 'post_views') ){
        $query = "ALTER TABLE $wpdb->posts ADD post_views INT NOT NULL DEFAULT '0'";
        $wpdb->query($query);
    }
}

/**
 * Output posts views after content on Blog pages
 */

add_filter('the_content', 'kmz_post_views');

function kmz_post_views($content){
    if(is_page()) return $content;
    global $post;
    $views = $post->post_views;
    return $content . "<p><b>Views:</b> " . $views . "</p>";
}

/**
 * Add view and save it in the DB after go to single post
 */

add_action('wp_head', 'kmz_add_view');

function kmz_add_view(){
    if(!is_single()) return;
    global $post, $wpdb;

    $id = $post->ID;
    $views = $post->post_views += 1;
    $wpdb->update(
        $wpdb->posts,
        array('post_views' => $views),
        array('ID' => $id)
    );
}