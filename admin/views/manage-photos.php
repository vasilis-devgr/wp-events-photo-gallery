<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Φέρνουμε τα albums για την εμφάνιση στο dropdown
$album_manager = new Album_Manager();
$albums = $album_manager->get_albums();

// Δημιουργία του nonce για προστασία των AJAX αιτημάτων
$epa_nonce = wp_create_nonce( 'epa_nonce' );
?>

<div class="wrap">
    <h1>Manage Photos</h1>

    <h2>Select Album to Manage Photos</h2>
    <form id="epa-select-album-form">
        <label for="epa-album-select">Choose Album:</label>
        <select id="epa-album-select" name="album_id" required>
            <option value="">Select Album</option>
            <?php foreach ( $albums as $album ) : ?>
                <option value="<?php echo esc_attr( $album->ID ); ?>"><?php echo esc_html( $album->post_title ); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="button" id="epa-open-media-library" class="button button-primary" disabled>Upload Photos to Album</button>
    </form>

    <div id="epa-photo-gallery"></div>
    <div class="epa-photo-gallery-load-more"></div>
</div>

