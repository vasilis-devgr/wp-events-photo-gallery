<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Photo_Manager {

    public function __construct() {
        add_action( 'add_attachment', array( $this, 'watermark_on_image_upload' ) );
        add_action( 'delete_attachment', array( $this, 'delete_photo' ) );
        // Αρχικοποίηση αν χρειάζεται
    }


    public function add_watermark_to_uploaded_images($attachment_id) {
        $image_path = get_attached_file($attachment_id);
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['path'];  
        $new_watermark_image_name = 'watermark-'.basename( $image_path );
        $new_file_path = $upload_path . '/' . $new_watermark_image_name;
        $new_file_url = $upload_dir['url'] . '/' . $new_watermark_image_name;
        copy( $image_path, $new_file_path ); 
        $image_path = $new_file_path;
        update_post_meta($attachment_id,'watermark_img', $new_file_url);
        update_post_meta($attachment_id,'watermark_img_path', $new_file_path);

        if (!file_exists($image_path)) {
            return false;
        }
        $image_info = getimagesize($image_path);
        $mime_type = $image_info['mime'];

        switch ($mime_type) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($image_path);
                break;
            case 'image/png':
                $image = imagecreatefrompng($image_path);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($image_path);
                break;
            default:
                return false; // Unsupported image type
        }

        $watermark_text = get_option( 'epa_watermark_text', 'Sample Watermark' );

        $font_size = 50;
        $angle = 0;
        $font_path = EPA_PLUGIN_DIR . 'fonts/arial.ttf'; 

        if (!file_exists($font_path)) {
            error_log('Font file not found: ' . $font_path);
            return false;
        }


        $font_color = imagecolorallocatealpha($image, 255, 255, 255, 50);
        $image_width = imagesx($image);
        $image_height = imagesy($image);

        $text_box = imagettfbbox($font_size, $angle, $font_path, $watermark_text);
        $text_width = abs($text_box[2] - $text_box[0]);
        $text_height = abs($text_box[7] - $text_box[1]);

        $padding = 30;
        $x = $image_width - $text_width - $padding;
        $y = $image_height - $padding;

        imagettftext($image, $font_size, $angle, $x, $y, $font_color, $font_path, $watermark_text);

        switch ($mime_type) {
            case 'image/jpeg':
                imagejpeg($image, $image_path, 90); // Adjust quality if needed
                break;
            case 'image/png':
                imagepng($image, $image_path);
                break;
            case 'image/gif':
                imagegif($image, $image_path);
                break;
        }

        imagedestroy($image);

        return $new_image_name;
    }

    public function watermark_on_image_upload($attachment_id) {
        $attachment_type = get_post_mime_type($attachment_id);

        // Apply watermark only to image file types
        if (strpos($attachment_type, 'image/') !== false) {
            $new_image_name = $this->add_watermark_to_uploaded_images($attachment_id);

            if (!$result) {
                error_log("Failed to apply watermark to attachment ID: " . $attachment_id);
            }
        }
    }

    public function delete_photo( $photo_id ) {
        $watermark_img_path = get_post_meta($photo_id, 'watermark_img_path', true);
        if (!empty($watermark_img_path) && file_exists($watermark_img_path)) {
            unlink($watermark_img_path);
        }
    }

    public function get_album_photos( $album_id, $page, $posts_per_page ) {
        
        // Ελέγχουμε ότι το album ID είναι έγκυρο
        if ( empty( $album_id ) || ! is_numeric( $album_id ) ) {
            return array();
        }
        
        $args = array(
            'post_type'      => 'attachment',
            'posts_per_page' => $posts_per_page,
            'post_status'    => 'inherit',
            'post_parent'    => $album_id,
            'orderby'        => 'date',  // Προαιρετικό: ταξινόμηση με βάση την ημερομηνία
            'order'          => 'DESC',
            'paged'          => $page,
        );
        
        $photos = get_posts( $args );

        // Επιστρέφουμε κενό array αν δεν υπάρχουν φωτογραφίες
        if ( empty( $photos ) ) {
            return array();
        }

        return $photos;
    }
}
