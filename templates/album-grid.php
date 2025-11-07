<div class="grid-container">
    <?php foreach ( $albums as $album ) : ?>
        <div class="grid-item">
            <a href="<?php echo esc_url( add_query_arg( 'album_id', $album->ID ) ); ?>">
                <img src="<?php echo EPA_PLUGIN_URL . 'assets/folder-icon.png'; ?>" alt="<?php echo esc_attr( $album->post_title ); ?>">
                <h3><?php echo esc_html( $album->post_title ); ?></h3>
            </a>
        </div>
    <?php endforeach; ?>
</div>
