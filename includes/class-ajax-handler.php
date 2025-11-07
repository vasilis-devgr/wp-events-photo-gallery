<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Ajax_Handler {

    public function __construct() {
        add_action( 'wp_ajax_load_more_photos', array( $this, 'load_more_photos' ) );
        add_action( 'wp_ajax_nopriv_load_more_photos', array( $this, 'load_more_photos' ) );
        add_action( 'wp_ajax_load_more_photos_frontend', array( $this, 'load_more_photos_frontend' ) );
        add_action( 'wp_ajax_nopriv_load_more_photos_frontend', array( $this, 'load_more_photos_frontend' ) );
        add_action( 'wp_ajax_add_to_wishlist', array( $this, 'add_to_wishlist' ) );
        add_action( 'wp_ajax_nopriv_add_to_wishlist', array( $this, 'add_to_wishlist' ) );
        add_action( 'wp_ajax_epa_add_photos_to_album', array( $this, 'add_photos_to_album' ) );
        add_action( 'wp_ajax_epa_delete_photo', array( $this, 'delete_photo' ) ); 
        add_action( 'wp_ajax_assign_albums_to_user', array( $this, 'assign_albums_to_user' ) );
        add_action( 'wp_ajax_load_album_photos', array( $this, 'load_album_photos' ) );
        add_action( 'wp_ajax_nopriv_load_album_photos', array( $this, 'load_album_photos' ) );
        add_action( 'wp_ajax_get_assigned_albums', array( $this, 'get_assigned_albums' ) );
        add_action( 'wp_ajax_remove_from_wishlist', array( $this, 'remove_from_wishlist' ) );
        add_action( 'wp_ajax_load_more_from_wishlist', array( $this, 'load_more_from_wishlist' ) );
        add_action( 'wp_ajax_epa_search_album_images', array( $this, 'epa_search_album_images' ) );
        add_action( 'wp_ajax_epa_save_user_collections', array( $this, 'epa_save_user_collections' ) );
        add_action( 'wp_ajax_epa_search_existing_collection', array( $this, 'epa_search_existing_collection' ) );
        add_action( 'wp_ajax_epa_get_all_collection_lists', array( $this, 'epa_get_all_collection_lists' ) );
        add_action( 'wp_ajax_epa_add_to_cart_single_product_page', array( $this, 'epa_add_to_cart_single_product_page' ) );
        add_action( 'wp_ajax_epa_edit_user_collections', array( $this, 'epa_edit_user_collections' ) );
        add_action( 'wp_ajax_epa_remove_user_collections', array( $this, 'epa_remove_user_collections' ) );
        add_action( 'wp_ajax_search_user_album_photos', array( $this, 'epa_search_user_album_photos' ) );
        add_action( 'wp_ajax_search_user_collections', array( $this, 'epa_search_user_collections' ) );
    }

    public function remove_from_wishlist() {
        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce provided' );
            exit;
        }
    
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Not logged in' );
            exit;
        }
    
        $user_id  = get_current_user_id();
        $photo_id = isset( $_POST['photo_id'] ) ? intval( $_POST['photo_id'] ) : 0;
        $collection_id = isset( $_POST['collection_id'] ) ? intval( $_POST['collection_id'] ) : 0;
    
        if ( ! $photo_id ) {
            wp_send_json_error( 'Invalid photo ID' );
            exit;
        }
    
        $wishlist = get_user_meta( $user_id, '_epa_wishlist', true );

        if ( is_array( $wishlist ) ) {

            foreach ( $wishlist as $key => $item ) {
                if ( isset( $item->collection_id ) && isset( $item->photo_id ) ) {
                    if ( $item->collection_id == $collection_id && $item->photo_id == $photo_id ) {
                        unset( $wishlist[$key] );
                    }
                }
            }
            update_user_meta( $user_id, '_epa_wishlist', $wishlist );
            wp_send_json_success( 'Photo removed from your Wishlist.' );
        } else {
            wp_send_json_error( 'Wishlist not found.' );
        }
    }

    public function load_more_from_wishlist(){
        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce provided' );
            exit;
        }
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Not logged in' );
            exit;
        }
        $user_id = get_current_user_id();
        $all_collection_photos = get_user_meta( $user_id, '_epa_wishlist', true );
        $posts_per_page =   get_option( 'epa_number_of_images', 10 )  ;
        $page = isset($_POST['page']) ? $_POST['page']: 1;
        $collection_id = isset($_POST['collection_id']) ? intval($_POST['collection_id']): 0;
        $type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';

        if($type == 'grid view'){
            $class = 'epa-wishlist-list';
        }else{
            $class = 'epa-wishlist-grid';
        }
        
        if ( ! is_array( $all_collection_photos ) || empty( $all_collection_photos ) ) {
            return '<p>You have no photos in your collection.</p>';
        }
        foreach($all_collection_photos as $all_collection_photo){
            if($all_collection_photo->collection_id == $collection_id){
                $collection[] = $all_collection_photo->photo_id;
            }
        }

        if ( empty( $collection ) ) {
            return '<p>No photos found in your collection.</p>';
        }

        $args = array(
            'post_type'      => 'attachment',
            'paged'           => $page,
            'post__in'       => $collection,
            'posts_per_page' => $posts_per_page,
            'orderby'        => 'post__in', // Maintain the wishlist order
        );
        $photos = get_posts( $args );
    
        if ( empty( $photos ) ) {
            return '<p>No photos found in your wishlist.</p>';
        }

        ob_start();
        foreach ( $photos as $photo ) : ?>
            <div class="epa-wishlist-item <?php echo $class;?>">
                <?php
                $thumbnail_url = wp_get_attachment_image_url( $photo->ID, 'medium' );
                $file_name = basename( get_attached_file( $photo->ID ) ); // Λήψη ονόματος αρχείου
                $watermark_img = get_post_meta($photo->ID,'watermark_img', true);
                ?>
                <a href="<?php echo esc_url($watermark_img); ?>" data-fancybox="gallery" data-caption="<?php echo esc_html( pathinfo( $file_name, PATHINFO_FILENAME ) ); ?>">
                    <img src="<?php echo esc_url( $watermark_img ); ?>" data-full-url="<?php echo esc_url( wp_get_attachment_url( $photo->ID ) ); ?>" alt="Wishlist Image">
                </a>
                <p class="epa-photo-filename"><?php echo esc_html( pathinfo( $file_name, PATHINFO_FILENAME ) ); ?></p>
                <span class="epa-remove-wishlist-icon" data-photo-id="<?php echo esc_attr( $photo->ID ); ?>">
                    <i class="fas fa-times"></i> Remove
                </span>
            </div>
        <?php endforeach; 
        $content = ob_get_clean();

        $no_more_images = count($photos) < $posts_per_page ? 0 : 1;
        wp_send_json_success( array( 'content' => $content, 'no_more_images' => $no_more_images ) );

    }
    


    public function get_assigned_albums() {
        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce' );
            exit;
        }
    
        $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
    
        if ( ! $user_id ) {
            wp_send_json_error( 'Invalid user ID' );
            exit;
        }
    
        $user_manager = new User_Manager();
        $assigned_albums = $user_manager->get_user_assigned_albums( $user_id );
    
        $album_manager = new Album_Manager();
        $all_albums = $album_manager->get_albums();
    
        ob_start();
        foreach ( $all_albums as $album ) {
            $checked = ( in_array( $album->ID, array_column( $assigned_albums, 'ID' ) ) ) ? 'checked' : '';
            echo '<label><input type="checkbox" name="album_ids[]" value="' . esc_attr( $album->ID ) . '" ' . $checked . '> ' . esc_html( $album->post_title ) . '</label><br>';
        }
        $output = ob_get_clean();
        wp_send_json_success( $output );
    }

    public function assign_albums_to_user() {
        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce' );
            exit;
        }
    
        $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
        $album_ids = isset( $_POST['album_ids'] ) ? array_map( 'intval', $_POST['album_ids'] ) : array();
    
        if ( empty( $user_id ) ) {
            wp_send_json_error( 'Invalid user ID' );
            exit;
        }
    
        $user_manager = new User_Manager();
        $user_manager->assign_albums_to_user( $user_id, $album_ids );
    
        wp_send_json_success( 'Albums successfully assigned to user.' );
    }
    
    
    
    
    
    
    public function refresh_all_assignments() {
        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce' );
            exit;
        }
    
        $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
    
        if ( ! $user_id ) {
            wp_send_json_error( 'Invalid user ID' );
            exit;
        }
    
        $user_manager = new User_Manager();
        $album_manager = new Album_Manager();
    
        $assigned_users = $user_manager->get_user_assigned_albums( $user_id );
        $assigned_albums = $album_manager->get_albums();
    
        ob_start();
        foreach ( $assigned_users as $user ) {
            echo '<li>' . esc_html( $user->display_name ) . '</li>';
        }
        $users_html = ob_get_clean();
    
        ob_start();
        foreach ( $assigned_albums as $album ) {
            echo '<li>' . esc_html( $album->post_title ) . '</li>';
        }
        $albums_html = ob_get_clean();
    
        wp_send_json_success( array(
            'users_html'  => $users_html,
            'albums_html' => $albums_html,
        ));
    }
       
    
    
    

    public function load_more_photos() {
        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce provided' );
            exit;
        }

        $album_id = isset( $_POST['album_id'] ) ? intval( $_POST['album_id'] ) : 0;
        $page = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
        $posts_per_page = get_option( 'epa_number_of_images', 12 );

        if ( ! $album_id ) {
            wp_send_json_error( 'Invalid album ID' );
            exit;
        }

        $photo_manager = new Photo_Manager();
        $photos = $photo_manager->get_album_photos( $album_id, $page, $posts_per_page );

        if ( empty( $photos ) ) {
            wp_send_json_error( 'No photos found for this album.' );
            exit;
        }

        ob_start();
        foreach ( $photos as $photo ) {
            $thumbnail_url = wp_get_attachment_image_url( $photo->ID, 'full' );
            $full_url      = wp_get_attachment_url( $photo->ID );
            ?>
            <div class="epa-photo-item">
                <img src="<?php echo esc_url( $thumbnail_url ); ?>" data-full-url="<?php echo esc_url( $full_url ); ?>" alt="">
                <span class="epa-wishlist-icon" data-photo-id="<?php echo esc_attr( $photo->ID ); ?>">
                    <i class="far fa-heart"></i>
                </span>
                <button class="epa-delete-photo" data-photo-id="<?php echo esc_attr( $photo->ID ); ?>">X</button> <!-- Νέο κουμπί διαγραφής -->
            </div>
            <?php
        }
        $content = ob_get_clean();
        $no_more_images = count($photos) < $posts_per_page ? 0 : 1;
        wp_send_json_success( array( 'content' => $content, 'no_more_images' => $no_more_images ) );
    }

    public function load_more_photos_frontend() {
        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce provided' );
            exit;
        }

        $album_id = isset( $_POST['album_id'] ) ? intval( $_POST['album_id'] ) : 0;
        $page = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
        $posts_per_page = get_option( 'epa_number_of_images', 12 );
        $type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';

        if($type == 'grid view'){
            $class = 'epa-photo-list';
        }else{
            $class = 'epa-photo-grid';
        }

        if ( ! $album_id ) {
            wp_send_json_error( 'Invalid album ID' );
            exit;
        }

        $photo_manager = new Photo_Manager();
        $photos = $photo_manager->get_album_photos( $album_id, $page, $posts_per_page );

        if ( empty( $photos ) ) {
            wp_send_json_error( 'No photos found for this album.' );
            exit;
        }

        $user_id = get_current_user_id(); 
        $wishlist = get_user_meta( $user_id, '_epa_wishlist', true );

        ob_start();
        foreach ( $photos as $photo ) {
            $thumbnail_url = wp_get_attachment_image_url( $photo->ID, 'medium' );
            $full_url      = wp_get_attachment_url( $photo->ID );
            $file_name = basename( get_attached_file( $photo->ID ) );
            $watermark_img = get_post_meta($photo->ID,'watermark_img', true);
            $is_in_wishlist = false;
            if (is_array($wishlist)) {
                foreach ($wishlist as $item) {
                    if (isset($item->photo_id) && $item->photo_id == $photo->ID) {
                        $is_in_wishlist = true;
                        break;
                    }
                }
            }
            ?>
            <div class="epa-photo-item <?php echo $class;?>">
                <a href="<?php echo esc_url($watermark_img); ?>" data-fancybox="gallery" data-caption="<?php echo esc_html( pathinfo( $file_name, PATHINFO_FILENAME ) ); ?>">
                    <img src="<?php echo esc_url( $watermark_img ); ?>" data-full-url="<?php echo esc_url( $full_url ); ?>" alt="">
                </a>
                <p class="epa-photo-filename"><?php echo esc_html( pathinfo( $file_name, PATHINFO_FILENAME ) ); ?></p>
                <?php 
                if($is_in_wishlist){ ?>
                    <span class="epa-wishlist-icon-already">
                        <i class="fas fa-heart"></i>
                    </span>
                <?php }else{ ?>
                    <span class="epa-wishlist-icon" data-photo-id="<?php echo esc_attr( $photo->ID ); ?>">
                        <i class="far fa-heart"></i>
                        <p class="save-collection">Save to collection</p>
                    </span>
                    <span>
                        <input type="checkbox" name="my-photo-checkbox" value="<?php echo esc_attr( $photo->ID ); ?>" />
                    </span>
                <?php }
                ?>
            </div>
            <?php
        }
        $content = ob_get_clean();
        $no_more_images = count($photos) < $posts_per_page ? 0 : 1;
        wp_send_json_success( array( 'content' => $content, 'no_more_images' => $no_more_images ) );
    }

    public function add_photos_to_album() {
        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce' );
            exit;
        }

        $album_id = isset( $_POST['album_id'] ) ? intval( $_POST['album_id'] ) : 0;
        $image_ids = isset( $_POST['image_ids'] ) ? $_POST['image_ids'] : array();

        if ( ! $album_id || empty( $image_ids ) || ! is_array( $image_ids ) ) {
            wp_send_json_error( 'Invalid album or image IDs' );
            exit;
        }

        $photo_manager = new Photo_Manager();

        foreach ( $image_ids as $image_id ) {
            wp_update_post( array(
                'ID' => intval( $image_id ),
                'post_parent' => $album_id,
            ));

            $check_watermark = get_post_meta($image_id,'watermark_img', true);
            if (empty($check_watermark)) {
                $photo_manager->watermark_on_image_upload( $image_id );
            }

        }
        wp_send_json_success( 'Photos added successfully to album.' );
    }

    public function delete_photo() {
        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce' );
            exit;
        }

        $photo_id = isset( $_POST['photo_id'] ) ? intval( $_POST['photo_id'] ) : 0;

        if ( ! $photo_id ) {
            wp_send_json_error( 'Invalid photo ID' );
            exit;
        }

        $result = wp_update_post ( array(
            'ID' => intval( $photo_id ),
            'post_parent' => 0,
        ));

        $args = array(
            'meta_key'     => '_epa_wishlist',
            'meta_compare' => 'EXISTS', 
            'number'       => -1,  
        );
        $user_query = new WP_User_Query($args);
        if (!empty($user_query->get_results())) {
            $users = $user_query->get_results();
            foreach ($users as $user) {
                $user_wishlist = get_user_meta($user->ID, '_epa_wishlist', true);
                if (!empty($user_wishlist)) {
                    foreach ($user_wishlist as $key => $item) {
                        if (isset($item->photo_id) && $item->photo_id == $photo_id) {
                            unset($user_wishlist[$key]); 
                        }
                    }

                    update_user_meta($user->ID, '_epa_wishlist', $user_wishlist);
                }
            }
        }

        if (is_wp_error($result)) {
            wp_send_json_error( 'Failed to delete photo.' );
        }elseif ($result == 0) {
            wp_send_json_error( 'Failed to delete photo.' );
        }else {
            wp_send_json_success( 'Photo deleted successfully.' );
        }
    }

    public function add_to_wishlist() {
        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce provided' );
            exit;
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Not logged in' );
            exit;
        }

        $user_id  = get_current_user_id();
        $collection_data = isset($_POST['collection_data']) ? json_decode(wp_unslash($_POST['collection_data'])) : [];

        if ( empty($collection_data) || !is_array($collection_data) ) {
            wp_send_json_error( 'Invalid collection data.' );
            exit;
        }

        $wishlist = get_user_meta( $user_id, '_epa_wishlist', true );
        if ( ! is_array( $wishlist ) ) {
            $wishlist = array();
        }
        
        foreach ( $collection_data as $new_item ) {
            $exists = false;
            foreach ( $wishlist as $existing_item ) {
                if (
                    isset($existing_item->{"collection_id"}, $new_item->{"collection_id"}) &&
                    isset($existing_item->photo_id, $new_item->photo_id) &&
                    $existing_item->{"collection_id"} == $new_item->{"collection_id"} &&
                    $existing_item->photo_id == $new_item->photo_id
                ) {
                    $exists = true;
                    break;
                }
            }
            if ( ! $exists ) {
                $wishlist[] = $new_item;
            }
        }
        update_user_meta( $user_id, '_epa_wishlist', $wishlist );
        wp_send_json_success( 'Wishlist updated successfully.' );
        exit;
    }


    public function epa_search_album_images() {
        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce provided' );
            exit;
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Not logged in' );
            exit;
        }

        $album_id = isset( $_POST['album_id'] ) ? intval( $_POST['album_id'] ) : 0;
        $search_query = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
        $posts_per_page = get_option( 'epa_number_of_images', 10 )  ;
        $page = isset($_POST['page']) ? $_POST['page']: 1;
        $type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
        $order = isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : 'DESC';

        if($type == 'grid view'){
            $class = 'epa-photo-list';
        }else{
            $class = 'epa-photo-grid';
        }

        if ( ! $album_id ) {
            wp_send_json_error( 'Invalid album ID' );
            exit;
        }

        $args = array(
            'post_type'      => 'attachment',
            'posts_per_page' => $posts_per_page,
            'post_status'    => 'inherit',
            'post_parent'    => $album_id, 
            'order'          => $order,
            'paged'          => $page,
            's'              => $search_query, 
        );
        $photos = get_posts( $args );
        $no_more_images = count($photos) < $posts_per_page ? 0 : 1;

        if ( empty( $photos ) ) {
            wp_send_json_error( array( 'content' => 'No photos found for this album.', 'no_more_images' => $no_more_images ) );

            exit;
        }

        $user_id = get_current_user_id(); 
        $wishlist = get_user_meta( $user_id, '_epa_wishlist', true );

        ob_start();
        foreach ( $photos as $photo ) {
            $thumbnail_url = wp_get_attachment_image_url( $photo->ID, 'medium' );
            $full_url      = wp_get_attachment_url( $photo->ID );
            $file_name = basename( get_attached_file( $photo->ID ) );
            $watermark_img = get_post_meta($photo->ID,'watermark_img', true);
            $is_in_wishlist = false;
            if (is_array($wishlist)) {
                foreach ($wishlist as $item) {
                    if (isset($item->photo_id) && $item->photo_id == $photo->ID) {
                        $is_in_wishlist = true;
                        break;
                    }
                }
            }
            ?>
            <div class="epa-photo-item <?php echo $class;?>">
                <a href="<?php echo esc_url($watermark_img); ?>" data-fancybox="gallery" data-caption="<?php echo esc_html( pathinfo( $file_name, PATHINFO_FILENAME ) ); ?>">
                    <img src="<?php echo esc_url( $watermark_img ); ?>" data-full-url="<?php echo esc_url( $full_url ); ?>" alt="">
                </a>
                <p class="epa-photo-filename"><?php echo esc_html( pathinfo( $file_name, PATHINFO_FILENAME ) ); ?></p>
                <?php 
                if($is_in_wishlist){ ?>
                    <span class="epa-wishlist-icon-already">
                        <i class="fas fa-heart"></i>
                    </span>
                <?php }else{ ?>
                    <span class="epa-wishlist-icon" data-photo-id="<?php echo esc_attr( $photo->ID ); ?>">
                        <i class="far fa-heart"></i>
                        <p class="save-collection">Save to collection</p>
                    </span>
                    <span>
                        <input type="checkbox" name="my-photo-checkbox" value="<?php echo esc_attr( $photo->ID ); ?>" />
                    </span>
                <?php }
                ?>
            </div>
            <?php
        }
        $content = ob_get_clean();
        wp_send_json_success( array( 'content' => $content, 'no_more_images' => $no_more_images ) );


    }

    public function epa_save_user_collections() {
        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce provided' );
            exit;
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Not logged in' );
            exit;
        }

        $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
        $thumbnail_id = isset( $_POST['thumbnail_id'] ) ? intval( $_POST['thumbnail_id'] ) : 0;
        $collection_name = isset( $_POST['collection_name'] ) ? sanitize_text_field( $_POST['collection_name'] ) : '';
        if ( ! $collection_name ) {
            wp_send_json_error( 'Please add the collection name' );
            exit;
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'user_collections';
        $name_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE name = %s AND user_id = %d",
            $collection_name,
            $user_id
        ) );

        if ( $name_exists == 0 ) {

            $inserted = $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'name' => $collection_name, 
                    'thumbnail_id' => $thumbnail_id,
                    'created_at' => current_time('mysql'), 
                ),
                array(
                    '%s',  
                    '%s' ,
                    '%s' ,
                    '%s',
                )
            );
            if ( $inserted ) {
                wp_send_json_success( 'Collection inserted successfully!.' );
            } else{
                wp_send_json_success( 'Failed to insert the collection!.' );
            }
        }else{
            wp_send_json_error( 'Collection name already exists!.' );
        }
    }

    public function epa_search_existing_collection(){
        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce provided' );
            exit;
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Not logged in' );
            exit;
        }

        $search_query = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';

        global $wpdb;

        $table_name = $wpdb->prefix . 'user_collections';

        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name WHERE name LIKE %s", '%' . $wpdb->esc_like($search_query) . '%')
        );

        if (empty($results)) {
            wp_send_json_error('No existing collection found');
        }

        ob_start();
        foreach($results as $result){ ?>
            <div class="epa-collection-item">
                <img decoding="async" src="<?php echo EPA_PLUGIN_URL.'assets/collection-icon.png';?>" alt="Thumbnail">
                <span data-id="<?php echo $result->id;?>"><?php echo $result->name;?></span>
            </div>
        <?php }
        $content = ob_get_clean();
        wp_send_json_success( array( 'content' => $content ) );

    }

    public function epa_get_all_collection_lists(){
        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce provided' );
            exit;
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Not logged in' );
            exit;
        }

        $user_id = get_current_user_id(); 

        global $wpdb;

        $table_name = $wpdb->prefix . 'user_collections';

        $all_collections = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d",
                $user_id
            )
        );

        if (empty($all_collections)) {
            wp_send_json_error('No existing collection found');
        }

        ob_start();
        foreach ( $all_collections as $all_collection ) {
            $thumbnail_id = isset($all_collection->thumbnail_id) ? $all_collection->thumbnail_id : 0;
            if($thumbnail_id > 0){
                $thumbnail_url = get_post_meta($thumbnail_id,'watermark_img', true);
            }else{
                $thumbnail_url =  EPA_PLUGIN_URL.'assets/collection-icon.png';
            }

            ?>
           <div class="epa-collection-item">
                <img src="<?php echo $thumbnail_url;?>" alt="Thumbnail">
                <span data-id="<?php echo $all_collection->id; ?>"><?php echo $all_collection->name;?></span>
            </div>
            <?php
        }
        $content = ob_get_clean();
        wp_send_json_success( array( 'content' => $content ) );

    }


    public function epa_add_to_cart_single_product_page(){
        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce provided' );
            exit;
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Not logged in' );
            exit;
        }

        $product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
        $event_type = isset( $_POST['event_type'] ) ? sanitize_text_field( $_POST['event_type'] ) : '';
        $collection_id = isset( $_POST['collection_id'] ) ? intval( $_POST['collection_id'] ) : 0;
        $photo_id = isset( $_POST['photo_id'] ) ? intval( $_POST['photo_id'] ) : 0;

        if ( ! $product_id ) {
            wp_send_json_error( 'Invalid product ID' );
            exit;
        }

        if ( ! $event_type ) {
            wp_send_json_error( 'Invalid event type' );
            exit;
        }

        if ( ! $collection_id ) {
            wp_send_json_error( 'Invalid collection id' );
            exit;
        }
        if($event_type == 'album'){
            $product_url = get_the_permalink($product_id).'?collection_id='.$collection_id;
            wp_send_json_success( array( 'url' =>  $product_url) );
        }

        if($event_type == 'cup'){
            $product_url = get_the_permalink($product_id).'?photo_id='.$photo_id;
            wp_send_json_success( array( 'url' =>  $product_url) );
        }

        if($event_type == 't-shirt'){
            $product_url = get_the_permalink($product_id).'?photo_id='.$photo_id;
            wp_send_json_success( array( 'url' =>  $product_url) );
        }

    }
    public function epa_edit_user_collections(){
        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce' );
            exit;
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Not logged in' );
            exit;
        }
        $collection_id = isset( $_POST['collection_id'] ) ? intval( $_POST['collection_id'] ) : 0;
        $collection_name = isset( $_POST['collection_name'] ) ? sanitize_text_field ( $_POST['collection_name'] ) : '';
        
        if ( ! $collection_id ) {
            wp_send_json_error( 'Invalid collection ID' );
            exit;
        }

        if ( ! $collection_name ) {
            wp_send_json_error( 'Collection name not found!' );
            exit;
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'user_collections';
        $user_id = get_current_user_id(); 

        $name_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE name = %s AND user_id = %d",
            $collection_name,
            $user_id
        ) );

        if ( $name_exists == 0 ) {
            $updated = $wpdb->update(
                $table_name,
                [ 'name' => $collection_name ],   
                [ 'id' => $collection_id ], 
                [ '%s' ],                  
                [ '%d' ]                   
            );

            if ($updated !== false) {
                wp_send_json_success('Collection updated successfully.');
            }else{
                wp_send_json_error('Failed to update the collection.');
            }
        }else{
            wp_send_json_error('Collection name already exists!.');
        }

    }

    public function epa_remove_user_collections(){

        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce' );
            exit;
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Not logged in' );
            exit;
        }

        $collection_id = isset( $_POST['collection_id'] ) ? intval( $_POST['collection_id'] ) : 0;

        if ( ! $collection_id ) {
            wp_send_json_error( 'Invalid collection ID' );
            exit;
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'user_collections';
        $user_id = get_current_user_id(); 

        $all_collections = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d AND user_id = %d",
                $collection_id,
                $user_id
            )
        );

        if (empty($all_collections)) {
            wp_send_json_error('No existing collection found');
        }
        $wishlist = get_user_meta( $user_id, '_epa_wishlist', true );
        
        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table_name WHERE id = %d AND user_id = %d",
                $collection_id,
                $user_id
            )
        );  

        if ($deleted !== false) {

            if(!empty($wishlist)){
                $filtered_array = array_filter($wishlist, function($item) use ($collection_id) {
                    return $item->collection_id != $collection_id;
                });
                update_user_meta($user_id, '_epa_wishlist', $filtered_array);
            }

            wp_send_json_success('Collection deleted successfully.');

        }else{
            wp_send_json_error('Failed to delete the collection.');
        }

    }

    public function get_attachment_count_by_parent_id($parent_id) {

        $args = [
            'post_type'      => 'attachment',
            'post_parent'    => $parent_id,
            'posts_per_page' => -1, 
            'fields'         => 'ids', 
            'post_status'    => 'inherit',
        ];

        $query = new WP_Query($args);

        return $query->found_posts;
    }

    public function epa_search_user_album_photos(){

        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce' );
            exit;
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Not logged in' );
            exit;
        }

        $search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';

        $user_id = get_current_user_id(); 

        $album_ids = get_user_meta( $user_id, '_assigned_albums', true );
        if ( ! $album_ids || ! is_array( $album_ids ) ) {
            wp_send_json_error( 'No albums found.' );
            exit;
        }

        $args = [
            'post_type'       => 'event_album',
            'post_status'     => 'publish',
            'posts_per_page'  => -1, 
            'post__in'        => $album_ids, 
            's'               => $search, 
        ];

        $albums = get_posts( $args );
        ob_start();
        if (!empty($albums)) {
            foreach ( $albums as $album ) : 
                $album_url = home_url( '/my-account/my-photos/' );
                $album_url = add_query_arg( 'album_id', $album->ID, $album_url );

                $attachment_count = $this->get_attachment_count_by_parent_id($album->ID);
                $thumbnail_url = wp_get_attachment_image_src(get_post_thumbnail_id($album->ID), 'thumbnail');
                if(!empty($thumbnail_url)){
                    $thumbnail_url = $thumbnail_url[0];
                }else{
                    $thumbnail_url = EPA_PLUGIN_URL . 'assets/folder-icon.png';
                }
                echo '<div class="epa-album-item">';
                echo '<a href="' . esc_url($album_url) . '">';
                echo '<img src="' . $thumbnail_url . '" alt="' . esc_attr( $album->post_title ) . '">';
                echo '<div class="epa-myphotos-content">';
                echo '<h3>' . esc_html( $album->post_title ) . '</h3>';
                echo '<span>( '.$attachment_count.' photos)</span>';
                echo '</div>';
                echo '</a>';
                echo '</div>';
            endforeach;
        } else {
            echo '<p>No albums found.</p>';
        }

        $content = ob_get_clean();
        wp_send_json_success( array( 'content' => $content ) );
    }

    public function epa_search_user_collections(){

        if ( ! check_ajax_referer( 'epa_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid nonce' );
            exit;
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Not logged in' );
            exit;
        }

        $search = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
        $user_id = get_current_user_id(); 

        global $wpdb;

        $table_name = $wpdb->prefix . 'user_collections';

        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE name LIKE %s AND user_id = %d",
            '%' . $wpdb->esc_like($search) . '%',
            $user_id
        );
        
        $all_collections = $wpdb->get_results($query);

        if(empty($all_collections)){
            wp_send_json_error( 'No collection found!' );
        }

        ob_start();
        foreach ( $all_collections as $all_collection ) : 
            $count_collection_photos = 10;
            $thumbnail_url = wp_get_attachment_image_src($all_collection->thumbnail_id, 'thumbnail');
            if(!empty($thumbnail_url)){
                $thumbnail_url = $thumbnail_url[0];
            }else{
                $thumbnail_url = EPA_PLUGIN_URL . 'assets/folder-icon.png';
            }

            $collection_url = home_url( '/my-account/my-collections/' );
            $collection_url = add_query_arg( 'collection_id', $all_collection->id, $collection_url );

            echo '<div class="epa-collection-album-item">';
            echo '<a href="' . esc_url( $collection_url ) . '">';
            echo '<img src=" '. $thumbnail_url .'" alt="' . esc_attr( $all_collection->name ) . '">';
            echo '<div class="epa-collection-content">';
            echo '<h3>' . esc_html( $all_collection->name ) . '</h3>';
            echo '<span>( '.$count_collection_photos.' photos)</span>';
            echo '</div>';
            echo '</a>';
            echo '<div class="my-photos-action"><span class="epa-edit-collection" data-collection-name="' .esc_attr( $all_collection->name ). '" data-collection-id="' .esc_attr( $all_collection->id ). '" data-collection-id="1"><i class="fas fa-pencil-alt"></i>Rename</span>';
            echo '<span class="epa-remove-collection" data-collection-id="' .esc_attr( $all_collection->id ). '" data-collection-id="1"><i class="fas fa-trash-alt"></i>Delete</span></div>';
            echo '</div>';
        endforeach;
        $content = ob_get_clean();
        wp_send_json_success( array( 'content' => $content ) );
    }
}
