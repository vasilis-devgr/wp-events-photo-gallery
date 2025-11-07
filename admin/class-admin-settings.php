<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Admin_Settings {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
    }

    public function add_admin_menu() {
        add_menu_page(
            'Events Photo Album',
            'Events Photo Album',
            'manage_options',
            'events-photo-album',
            array( $this, 'render_manage_photos_page' ),
            'dashicons-images-alt2',
            6
        );

        add_submenu_page(
            'events-photo-album',
            'Manage Photos',
            'Manage Photos',
            'manage_options',
            'events-photo-album',
            array( $this, 'render_manage_photos_page' )
        );

        add_submenu_page(
            'events-photo-album',
            'Manage Users',
            'Manage Users',
            'manage_options',
            'epa-manage-users',
            array( $this, 'render_manage_users_page' )
        );

        add_submenu_page(
            'events-photo-album',
            'Settings',
            'Settings',
            'manage_options',
            'epa-settings',
            array( $this, 'render_settings_page' )
        );
    }

public function enqueue_admin_scripts( $hook ) {
    // Έλεγχος αν βρισκόμαστε στη σελίδα του plugin
    if ( strpos( $hook, 'events-photo-album' ) !== false ) {
        wp_enqueue_style('epa-admin-style',EPA_PLUGIN_URL . 'admin/css/admin-style.css',array(), time());

        // Φόρτωση του admin script και του media library script
       
        wp_enqueue_script( 'epa-admin-script', EPA_PLUGIN_URL . 'admin/js/admin-script.js', array( 'jquery' ), time(), true );
        wp_enqueue_media();

        // Προσθήκη του αντικειμένου `epa_ajax_object` με το URL του AJAX και το nonce
        wp_localize_script( 'epa-admin-script', 'epa_ajax_object', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'epa_nonce' ),
        ));
    }
}



    public function render_manage_photos_page() {
        include EPA_PLUGIN_DIR . 'admin/views/manage-photos.php';
    }

    public function render_manage_users_page() {
        include EPA_PLUGIN_DIR . 'admin/views/manage-users.php';
    }

    public function render_settings_page() {
        include EPA_PLUGIN_DIR . 'admin/views/settings.php';
    }
}
