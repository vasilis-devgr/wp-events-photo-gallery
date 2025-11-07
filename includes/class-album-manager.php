<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Album_Manager {

    public function __construct() {
        add_action( 'init', array( $this, 'register_album_post_type' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_album_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_album_meta' ), 10, 2 );
    }

    // Εγγραφή Custom Post Type για Albums
    public function register_album_post_type() {
        $labels = array(
            'name'               => 'Albums',
            'singular_name'      => 'Album',
            'menu_name'          => 'Albums',
            'name_admin_bar'     => 'Album',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Album',
            'new_item'           => 'New Album',
            'edit_item'          => 'Edit Album',
            'view_item'          => 'View Album',
            'all_items'          => 'All Albums',
            'search_items'       => 'Search Albums',
            'parent_item_colon'  => 'Parent Albums:',
            'not_found'          => 'No albums found.',
            'not_found_in_trash' => 'No albums found in Trash.'
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'exclude_from_search' => true,
            'show_in_nav_menus'   => true,
            'has_archive'        => false,
            'show_in_menu'       => 'events-photo-album',
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'event_album' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'supports'           => array( 'title', 'editor', 'thumbnail' ),
        );

        register_post_type( 'event_album', $args );
    }

    // Προσθήκη Meta Box για αντιστοίχιση χρηστών
    public function add_album_meta_boxes() {
        /*add_meta_box(
            'album_users',
            'Assign Users',
            array( $this, 'render_album_users_meta_box' ),
            'event_album',
            'normal',
            'high'
        );*/
    }

   /* public function render_album_users_meta_box( $post ) {
        $assigned_users = get_post_meta( $post->ID, '_assigned_users', true );
        $users = get_users();

        echo '<select name="assigned_users[]" multiple style="width:100%;height:200px;">';
        foreach ( $users as $user ) {
            $selected = ( is_array( $assigned_users ) && in_array( $user->ID, $assigned_users ) ) ? 'selected' : '';
            echo '<option value="' . esc_attr( $user->ID ) . '" ' . $selected . '>' . esc_html( $user->display_name ) . ' (' . esc_html( $user->user_email ) . ')</option>';
        }
        echo '</select>';
    }*/

public function save_album_meta( $post_id, $post ) {
    if ( $post->post_type != 'event_album' ) {
        return;
    }
}


    // Μέθοδοι για δημιουργία, μετονομασία, διαγραφή albums μέσω του Admin UI
    public function create_album( $album_name ) {
        $album = array(
            'post_title'  => sanitize_text_field( $album_name ),
            'post_status' => 'publish',
            'post_type'   => 'event_album',
        );
        return wp_insert_post( $album );
    }

    public function rename_album( $album_id, $new_name ) {
        wp_update_post( array(
            'ID'         => $album_id,
            'post_title' => sanitize_text_field( $new_name ),
        ));
    }

    public function delete_album( $album_id ) {
        wp_delete_post( $album_id, true );
    }

    public function get_albums() {
        $args = array(
            'post_type'      => 'event_album',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        );
        return get_posts( $args );
    }

    public function get_user_albums( $user_id ) {
        $album_ids = get_user_meta( $user_id, '_assigned_albums', true );
    
        if ( ! is_array( $album_ids ) || empty( $album_ids ) ) {
            return array(); // Επιστρέφουμε κενό array αν δεν υπάρχουν albums
        }
    
        // Ανάκτηση albums βάσει IDs
        $args = array(
            'post_type' => 'event_album',
            'post_status' => 'publish',
            'post__in' => $album_ids,
            'orderby' => 'post_title',
            'posts_per_page' => -1,
            'order' => 'ASC',
        );
    
        return get_posts( $args );
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
}
