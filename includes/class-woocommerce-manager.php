<?php

class Woocommerce_Manager {

    public function __construct() {
        add_action('woocommerce_product_options_general_product_data', [$this, 'epa_add_event_photo_gallery_checkbox']);
        add_action('woocommerce_process_product_meta', [$this, 'epa_save_event_photo_gallery_meta']);
        add_action('woocommerce_before_add_to_cart_button', [$this, 'epa_display_collection_data']);
        add_filter('woocommerce_add_cart_item_data', [$this, 'add_custom_product_meta_to_cart'], 10, 2);
        add_filter('woocommerce_get_item_data', [$this, 'display_custom_meta_in_cart'], 10, 2);
        add_action('woocommerce_add_order_item_meta', [$this, 'display_custom_meta_on_order'], 10, 3);
        add_filter('woocommerce_account_menu_items', [$this, 'epa_add_my_photos_tab']);
    }

    public function epa_add_event_photo_gallery_checkbox() {
        global $post;

        woocommerce_wp_checkbox([
            'id' => '_event_photo_gallery', 
            'label' => __('Event Photo Gallery', 'woocommerce'),
            'description' => __('Check this box to enable the event photo gallery option.', 'woocommerce'),
            'desc_tip' => true,
        ]);

        woocommerce_wp_select([
            'id' => '_event_photo_gallery_type',
            'label' => __('Select Event Type', 'woocommerce'),
            'options' => [
                'album'    => __('Album', 'woocommerce'),
                't-shirt'  => __('T-Shirt', 'woocommerce'),
                'cup'      => __('Cup', 'woocommerce'),
            ],
            'description' => __('Select the type of event photo gallery.', 'woocommerce'),
            'desc_tip' => true,
        ]);
    }

    public function epa_get_collection_name_and_total($collection_id){
        $user_id = get_current_user_id();
        global $wpdb; 
        $table_name = $wpdb->prefix . 'user_collections';
        $collection_data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE id = %d",
                $collection_id
            )
        );
        $all_collection_photos = get_user_meta( $user_id, '_epa_wishlist', true );
        foreach($all_collection_photos as $all_collection_photo){
            if($all_collection_photo->collection_id == $collection_id){
                $collection[] = $all_collection_photo->photo_id;
            }
        }
        $total_collection_image = count($collection);
        $collection_name = isset($collection_data[0]->name) ? $collection_data[0]->name : '';
        $result = ['collection_name' => $collection_name,'total_collection_image' => $total_collection_image];
        return $result;

    }
    public function epa_save_event_photo_gallery_meta($post_id) {
        $event_photo_gallery_enabled = isset($_POST['_event_photo_gallery']) ? 'yes' : 'no';
        update_post_meta($post_id, '_event_photo_gallery', $event_photo_gallery_enabled);

        if ($event_photo_gallery_enabled === 'yes' && isset($_POST['_event_photo_gallery_type'])) {
            update_post_meta($post_id, '_event_photo_gallery_type', sanitize_text_field($_POST['_event_photo_gallery_type']));
        } else {
            delete_post_meta($post_id, '_event_photo_gallery_type'); 
        }
    }

    public function epa_display_collection_data() {
        global $product;
        $product_id = $product->get_id();
        $photo_gallery_type = get_post_meta($product_id, '_event_photo_gallery_type', true);
        $collection_id = isset($_GET['collection_id']) ? intval($_GET['collection_id']) : 0;
        $photo_id = isset($_GET['photo_id']) ? intval($_GET['photo_id']) : 0;
        if($collection_id > 0 && $photo_gallery_type =='album'){
                
            $collection_data = $this->epa_get_collection_name_and_total($collection_id);
            $total_collection_image = $collection_data['total_collection_image'];
            $collection_name = $collection_data['collection_name'];

            echo '<div class="epa-collection"><p><span class="epa-title">PRINT COLLECTION: </span><span>'.esc_html($collection_name).'('.esc_html($total_collection_image).')</span><input type="hidden" name="collection_id" value='.$collection_id.'> </p></div>';
        }

        if($photo_id > 0 && $photo_gallery_type =='cup' || $photo_gallery_type =='t-shirt'){
            $watermark_img = get_post_meta($photo_id, 'watermark_img', true);
            echo '<div class="epa-collection">PRINT COLLECTION:<img src='.$watermark_img.' height="100" width="100"><input type="hidden" name="photo_id" value='.$photo_id.'></div>';
        }
    }

    public function add_custom_product_meta_to_cart($cart_item_data, $product_id) {
        if (isset($_POST['collection_id'])) {
            $cart_item_data['collection_id'] = sanitize_text_field($_POST['collection_id']);
        }
        if (isset($_POST['photo_id'])) {
            $cart_item_data['photo_id'] = sanitize_text_field($_POST['photo_id']);
        }


        $cart_item_data['unique_key'] = md5(microtime() . rand());

        return $cart_item_data;
    }

    public function display_custom_meta_in_cart($item_data, $cart_item) {
        if (isset($cart_item['collection_id'])) {

            $collection_data = $this->epa_get_collection_name_and_total($cart_item['collection_id']);
            $total_collection_image = $collection_data['total_collection_image'];
            $collection_name = $collection_data['collection_name'];
            $collection_data = '<p><span>'.esc_html($collection_name).' ('.esc_html($total_collection_image).')</span></p>';
            $item_data[] = array(
                'name' => 'Selected Collection',
                'value' => $collection_data,
            );
        }
        if (isset($cart_item['photo_id'])) {
            $watermark_img = get_post_meta($cart_item['photo_id'], 'watermark_img', true);
            $watermark_img = '<img src='.$watermark_img.' height="50" width="50">';
            $item_data[] = array(
                'name' => 'Selected Photo',
                'value' => $watermark_img,
            );
        }
        return $item_data;
    }

    public function display_custom_meta_on_order($item_id, $item, $order) {

        if (isset($item['collection_id'])) {

            $collection_data = $this->epa_get_collection_name_and_total($item['collection_id']);
            $total_collection_image = $collection_data['total_collection_image'];
            $collection_name = $collection_data['collection_name'];
            $collection_data = '<p><span>'.esc_html($collection_name).' ('.esc_html($total_collection_image).')</span></p>';

            wc_add_order_item_meta($item_id, 'Selected Collection', $collection_data);
        }

        if (isset($item['photo_id'])) {
            $thumbnail_url = wp_get_attachment_image_url($item['photo_id'], 'medium');

            $thumbnail_url = '<img src='.$thumbnail_url.' height="50" width="50">';
            wc_add_order_item_meta($item_id, 'Selected Photo', $thumbnail_url);
        }
    }

    public function epa_add_my_photos_tab($items) {
        $new_items = array();
        foreach ($items as $key => $item) {
            $new_items[$key] = $item; 
            if ($key == 'dashboard') {
                $new_items['my-photos'] = 'My Photos'; 
            }
            if ($key == 'dashboard') {
                $new_items['my-collections'] = 'My Collectionss'; 
            }
        }
        return $new_items;
    }

}
