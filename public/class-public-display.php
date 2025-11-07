<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Public_Display {

    public function __construct() {
        add_action( 'init', array( $this, 'add_custom_endpoints' ) );
        add_action( 'after_setup_theme', array( $this, 'add_custom_endpoints' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) );
        add_action( 'woocommerce_account_menu_items', array( $this, 'add_endpoints_to_account_menu' ) );
        add_action( 'woocommerce_account_my-photos_endpoint', array( $this, 'my_photos_content' ) );
        add_action( 'woocommerce_account_my-collections_endpoint', array( $this, 'my_collections_content' ) ); // Νέο End Point
    }

    public function add_custom_endpoints() {
        add_rewrite_endpoint( 'my-photos', EP_ROOT | EP_PAGES );
        add_rewrite_endpoint( 'my-collections', EP_ROOT | EP_PAGES );
        flush_rewrite_rules(); // Temporary: Remove this after debugging!
    }


    public function enqueue_public_scripts() {
        wp_enqueue_style( 'epa-public-style', EPA_PLUGIN_URL . 'public/css/public-style.css', array(), time() );
        wp_enqueue_script( 'epa-public-script', EPA_PLUGIN_URL . 'public/js/public-ajax.js', array( 'jquery' ), time(), true );

        wp_localize_script( 'epa-public-script', 'epa_ajax_object', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'epa_nonce' ),
        ));
    }

    public function add_endpoints_to_account_menu( $items ) {
        $items['my-photos'] = 'My Photos'; // Διατηρείται
        $items['my-collections'] = 'My Collections'; // Νέο End Point
        return $items;
    }

    public function my_photos_content() {
        echo do_shortcode( '[user_albums]' );
    }

    public function my_collections_content() {
        echo do_shortcode( '[user_wishlist_photos]' ); // Νέο shortcode για Wishlist Photos
    }
}
