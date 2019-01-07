<?php

/**
 * Check if has specific field in the specific table
 */

function kmz_has_field($table, $column){
    global $wpdb;

    $fields = $wpdb->get_results("SHOW fields FROM $table", ARRAY_A);

    foreach($fields as $field){
        if($field['Field'] == $column){
            return true;
        }
    }
    return false;
}