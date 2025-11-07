<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class User_Manager {

    public function __construct() {
        // Αρχικοποίηση των λειτουργιών, αν χρειάζεται
    }

    public function get_users() {
        return get_users();
    }

    public function assign_albums_to_user( $user_id, $album_ids ) {
        if ( ! is_array( $album_ids ) ) {
            $album_ids = array();
        }
    
        // Ενημερώνουμε το user_meta
        update_user_meta( $user_id, '_assigned_albums', $album_ids );
    
        // Διαγράφουμε παλιές αναθέσεις από τα albums
        $all_albums = get_posts( array(
            'post_type' => 'event_album',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));
    
        foreach ( $all_albums as $album ) {
            $assigned_users = get_post_meta( $album->ID, '_assigned_users', true );
            if ( ! is_array( $assigned_users ) ) {
                $assigned_users = array();
            }
    
            if ( in_array( $user_id, $assigned_users ) && ! in_array( $album->ID, $album_ids ) ) {
                $assigned_users = array_diff( $assigned_users, array( $user_id ) );
                update_post_meta( $album->ID, '_assigned_users', $assigned_users );
            }
        }
    
        // Προσθέτουμε τον χρήστη στα νέα albums
        foreach ( $album_ids as $album_id ) {
            $assigned_users = get_post_meta( $album_id, '_assigned_users', true );
            if ( ! is_array( $assigned_users ) ) {
                $assigned_users = array();
            }
            if ( ! in_array( $user_id, $assigned_users ) ) {
                $assigned_users[] = $user_id;
                update_post_meta( $album_id, '_assigned_users', $assigned_users );
            }
        }
    }
    
    
    

    public function remove_album_from_user( $user_id, $album_id ) {
        $assigned_users = get_post_meta( $album_id, '_assigned_users', true );
        if ( is_array( $assigned_users ) && in_array( $user_id, $assigned_users ) ) {
            $assigned_users = array_diff( $assigned_users, array( $user_id ) );
            update_post_meta( $album_id, '_assigned_users', $assigned_users );
        }
    }

    public function get_user_assigned_albums( $user_id ) {
        $album_ids = get_user_meta( $user_id, '_assigned_albums', true );
    
        if ( ! is_array( $album_ids ) || empty( $album_ids ) ) {
            error_log('No albums assigned for user ID ' . $user_id);
            return array();
        }
    
        // Ανάκτηση των albums
        $albums = get_posts(array(
            'post_type' => 'event_album',
            'post_status' => 'publish',
            'post__in' => $album_ids,
            'orderby' => 'post_title',
            'posts_per_page' => -1,
            'order' => 'ASC',
        ));
    
        error_log('Retrieved albums for user ID ' . $user_id . ': ' . print_r($albums, true));
        return $albums;
    }
    
    
    
    
    
}
