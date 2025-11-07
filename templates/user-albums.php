<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Shortcode function to display user albums in the front-end
defined('ABSPATH') || exit;

function epa_user_albums_shortcode() {
    ob_start();
    
    // Check if user is logged in
    $user_id = get_current_user_id();
    if ($user_id) {
        // Get albums for the current user
        $album_manager = new Album_Manager();
        $albums = $album_manager->get_user_albums($user_id);
        
        // Include the user-albums template
        include plugin_dir_path(__FILE__) . '../templates/user-albums.php';
    } else {
        // Display message for non-logged-in users
        echo '<p>' . __('Please log in to see your albums.', 'events-photo-album') . '</p>';
    }
    
    return ob_get_clean();
}
add_shortcode('user_albums', 'epa_user_albums_shortcode');

// AJAX function to get album photos
function epa_get_album_photos() {
    // Verify nonce for security
    check_ajax_referer('epa_nonce', 'security');
    
    // Get album ID from AJAX request
    $album_id = intval($_POST['album_id']);
    
    $photo_manager = new Photo_Manager();
    $photos = $photo_manager->get_photos_by_album($album_id);
    
    if (!empty($photos)) {
        ob_start();
        foreach ($photos as $photo) {
            ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card shadow-sm">
                    <img src="<?php echo esc_url($photo['url']); ?>" alt="<?php echo esc_attr($photo['title']); ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                </div>
            </div>
            <?php
        }
        $html = ob_get_clean();
        wp_send_json_success(['html' => $html]);
    } else {
        wp_send_json_error();
    }
}
add_action('wp_ajax_epa_get_album_photos', 'epa_get_album_photos');
add_action('wp_ajax_nopriv_epa_get_album_photos', 'epa_get_album_photos');

?>
