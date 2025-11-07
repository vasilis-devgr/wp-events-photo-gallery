<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( isset( $_POST['epa_settings_submitted'] ) && check_admin_referer( 'epa_settings_form', 'epa_settings_nonce' ) ) {
    update_option( 'epa_watermark_text', sanitize_text_field( $_POST['epa_watermark_text'] ) );
    update_option( 'epa_number_of_images', sanitize_text_field( $_POST['epa_number_of_images'] ) );
    echo '<div class="updated"><p>Settings saved.</p></div>';
}

$watermark_text = get_option( 'epa_watermark_text', 'Sample Watermark' );
$epa_number_of_images = get_option( 'epa_number_of_images', 10 );
?>

<div class="wrap">
    <h1>Events Photo Album Settings</h1>
    <form method="post">
        <?php wp_nonce_field( 'epa_settings_form', 'epa_settings_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="epa_watermark_text">Watermark Text</label></th>
                <td><input name="epa_watermark_text" type="text" id="epa_watermark_text" value="<?php echo esc_attr( $watermark_text ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="epa_number_of_images">Number of images</label></th>
                <td><input name="epa_number_of_images" type="text" id="epa_number_of_images" value="<?php echo esc_attr( $epa_number_of_images ); ?>" class="regular-text"></td>
            </tr>
        </table>
        <input type="hidden" name="epa_settings_submitted" value="1">
        <?php submit_button(); ?>
    </form>
</div>
