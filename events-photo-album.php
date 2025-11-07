<?php
/*
Plugin Name: Events Photo Album
Description: Plugin για τη διαχείριση και προβολή φωτογραφιών events σε albums.
Version: 1.2
Author: Vasilis Papageorgiou
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Αποτροπή άμεσης πρόσβασης
}

// Ορισμός σταθερών
define( 'EPA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EPA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Φόρτωση αρχείων κλάσεων
require_once EPA_PLUGIN_DIR . 'includes/class-album-manager.php';
require_once EPA_PLUGIN_DIR . 'includes/class-user-manager.php';
require_once EPA_PLUGIN_DIR . 'includes/class-photo-manager.php';
require_once EPA_PLUGIN_DIR . 'includes/class-shortcodes.php';
require_once EPA_PLUGIN_DIR . 'includes/class-ajax-handler.php';
require_once EPA_PLUGIN_DIR . 'includes/class-woocommerce-manager.php';

// Φόρτωση Admin κλάσεων
if ( is_admin() ) {
    require_once EPA_PLUGIN_DIR . 'admin/class-admin-settings.php';
}

// Φόρτωση Public κλάσεων
require_once EPA_PLUGIN_DIR . 'public/class-public-display.php';

// Αρχικοποίηση κλάσεων
function epa_init_plugin() {
    new Album_Manager();
    new User_Manager();
    new Photo_Manager();
    new Shortcodes();
    new Ajax_Handler();
    new Woocommerce_Manager();

    if ( is_admin() ) {
        new Admin_Settings();
    } else {
        new Public_Display();
    }
}
add_action( 'plugins_loaded', 'epa_init_plugin' );

// Φόρτωση των CSS και JS αρχείων για admin
function epa_enqueue_admin_scripts() {
    wp_enqueue_style('epa-admin-style',EPA_PLUGIN_URL . 'admin/css/admin-style.css',array(), time());

    wp_enqueue_script( 'epa-admin-script', EPA_PLUGIN_URL . 'admin/js/admin-script.js', array( 'jquery' ), time(), true );
     wp_enqueue_script('sweetalert', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js', array(), null, true);
    wp_enqueue_style('sweetalert-css', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css', array(), null);
    // AJAX variables for admin
    wp_localize_script( 'epa-admin-script', 'epa_ajax_object', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'epa_nonce' ),
    ));
}
add_action( 'admin_enqueue_scripts', 'epa_enqueue_admin_scripts' );

// Φόρτωση των CSS και JS αρχείων για public
function epa_enqueue_public_scripts() {
    wp_enqueue_style( 'epa-public-style', EPA_PLUGIN_URL . 'public/css/public-style.css', array(), time() );
    wp_enqueue_style('epa-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), null);
    wp_enqueue_script('sweetalert', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js', array(), null, true);
    wp_enqueue_style('sweetalert-css', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css', array(), null);
	 wp_enqueue_style('fancybox-css', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css', array(), null);
    wp_enqueue_script('fancybox', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js', array(), null, true);
	wp_enqueue_script( 'epa-public-script', EPA_PLUGIN_URL . 'public/js/public-ajax.js', array( 'jquery' ), time(), true );

    // AJAX variables for public
    wp_localize_script( 'epa-public-script', 'epa_ajax_object', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'epa_nonce' ),
    ));
}
add_action( 'wp_enqueue_scripts', 'epa_enqueue_public_scripts' );


function epa_create_user_collections_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'user_collections';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        name VARCHAR(255) NOT NULL,
		thumbnail_id INT(11) UNSIGNED NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $wpdb->query($sql); // Directly run the query
}

register_activation_hook(__FILE__, 'epa_create_user_collections_table');