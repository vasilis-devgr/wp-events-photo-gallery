<?php
$back_url = site_url().'/my-account/my-collections';
?>
<div class="epa-photo-back">
    <a href="<?php echo $back_url; ?>" class="back-button">
        <i class="fas fa-chevron-left"></i> <span>back</span>
    </a>
</div>
<div class="epa-wishlist-topbar">
    <div class="epa-wishlist-title">
        <h1> <?php echo esc_html($collection_name); ?> <?php echo '(' . esc_html($total_collection_image) . ')'; ?> </h1>
    </div>
</div>

<div class="epa-wishlist-content">
    <div class="epa-wishlist-container">
        <!-- Search Box -->
        <div class="epa-wishlist-box">
            <input type="text" class="epa-wishlist-input" placeholder="Search by file name">
            <button class="epa-wishlist-button" data-album-id="441">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <!-- List View Toggle -->
       <!--  <div class="epa-wishlist-view-toggle">
            <button class="epa-wishlist-list-view-button" data-view="list">
                <i class="fas fa-list"></i>
                <span class="epa-wishlist-list-view-text">list view</span>
            </button>
        </div> -->
    </div>

    <div class="epa-wishlist-container">

        <div class="epa-wishlist-products">
            <!-- Dropdown for Print Options -->
            <?php 

            $args = array(
                'post_type' => 'product', 
                'posts_per_page' => -1, 
                'meta_key' => '_event_photo_gallery', 
                'meta_value' => 'yes', 
                'meta_compare' => 'EXISTS' 
            );
            $product_query = new WP_Query($args);
            if ($product_query->have_posts()) {
                echo '<select name="epa_product_select" id="epa_product_select">';
                echo '<option value="">' . __('Print options', 'woocommerce') . '</option>';
                while ($product_query->have_posts()) {
                    $product_query->the_post(); 
                    $product_id = get_the_ID();
                    $product_name = get_the_title();
                    $photo_gallery_type = get_post_meta($product_id, '_event_photo_gallery_type', true);
                    echo '<option value='.esc_attr($product_id).' data-event-type=' . esc_attr($photo_gallery_type). ' data-collection-id='.esc_attr($collection_id).' data-photo-id>'  . esc_html($product_name) . '</option>';
                }
                echo '</select>';
            }
            wp_reset_postdata();
            ?>
        </div>

        <div class="epa-wishlist-add-to-cart">
            <button class="epa-add-to-cart">BUY</button>
        </div>
    </div>
</div>
<div class="epa-wishlist-photo-msg"></div>
<div class="epa-wishlist-photo-container epa-wishlist-photo-grid-container">
    <!-- Dynamic Image Grid -->
    <?php foreach ($photos as $photo) : ?>
        <div class="epa-wishlist-item epa-wishlist-grid">
            <?php
            $thumbnail_url = wp_get_attachment_image_url($photo->ID, 'medium');
            $file_name = basename(get_attached_file($photo->ID));
            $watermark_img = get_post_meta($photo->ID, 'watermark_img', true);
            if (empty($watermark_img)) {
                $watermark_img = $thumbnail_url;
            }
            ?>
            <a href="<?php echo esc_url($watermark_img); ?>" data-fancybox="gallery" data-caption="<?php echo esc_html( pathinfo( $file_name, PATHINFO_FILENAME ) ); ?>">
            <img src="<?php echo esc_url($watermark_img); ?>" data-full-url="<?php echo esc_url($watermark_img); ?>" alt="Wishlist Image">
            </a>
            <p class="epa-photo-filename"><?php echo esc_html( pathinfo( $file_name, PATHINFO_FILENAME ) ); ?></p>
			<div class="bottom-actions">
				<span class="epa-remove-wishlist-icon" data-photo-id="<?php echo esc_attr($photo->ID); ?>" data-collection-id="<?php echo esc_attr($collection_id); ?>">
                <i class="fas fa-times"></i> Remove
            </span>
			</div>
            
        </div>
    <?php endforeach; ?>
</div>

<!-- Load More Button -->
<?php if ($no_more_images == 1) { ?>
    <div class="epa-photo-load-more">
        <button class="epa-wishlist-load-more" data-collection-id="<?php echo esc_attr($collection_id); ?>">Load More</button>
    </div>
<?php } ?>