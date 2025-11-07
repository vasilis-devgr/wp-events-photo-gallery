jQuery(document).ready(function($) {

    // Load More Photos
    let page = 1;
    $(document).on('click', '.epa-load-more', function(e) {
        e.preventDefault();
        page++;

        var button = $(this),
        albumId = button.data('album-id');
        button.prop('disabled', true).text('Loading...');
        let type = $('.epa-list-view-button').text();

        $.ajax({
            url: epa_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'load_more_photos_frontend',
                album_id: albumId,
                type: type,
                page: page,
                security: epa_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.epa-photo-container').append(response.data.content);
                    button.prop('disabled', false).text('Load More');
                }
                if (response.data.no_more_images == 0) {
                    button.hide();
                }
            }
        });
    });

    // Αφαίρεση φωτογραφίας από Wishlist
    $(document).on('click', '.epa-remove-wishlist-icon', function (e) {
        e.preventDefault();

        var button = $(this);
        var photoId = button.data('photo-id');
        var collectionId = button.data('collection-id');

        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you really want to remove this image from wishlist?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true // Swaps the positions of confirm and cancel
        }).then((result) => {
            if (result.isConfirmed) {
                    $.ajax({
                    url: epa_ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'remove_from_wishlist',
                        photo_id: photoId,
                        collection_id: collectionId,
                        security: epa_ajax_object.nonce
                    },
                    success: function (response) {
                        if (response.success) {
                            button.closest('.wishlist-photo-item').remove();
                            Swal.fire({
                                title: 'Wishlist',
                                text: 'Photo removed from wishlist.',
                                icon: 'success',
                                showCloseButton: true,
                                showConfirmButton: true,
                                timerProgressBar: true,
                                timer: 5000
                            }).then((result) => {
                                if (result.isConfirmed || result.isDismissed) {
                                    location.reload(); 
                                }
                            });

                        } else {
                            Swal.fire({
                                title: 'Wishlist',
                                text: 'Failed to remove photo from wishlist.',
                                icon: 'error',
                                showCloseButton: true,
                                showConfirmButton: true,
                                timerProgressBar: true,
                                timer: 5000
                            });
                        }
                    },
                    error: function () {
                        alert('An error occurred while removing the photo.');
                    }
                });
            }
        });
    });

    // Lightbox για τις φωτογραφίες
    $(document).on('click', '.epa-photo-item img', function() {
        var imageUrl = $(this).attr('data-full-url');
        $('body').append('<div class="epa-lightbox"><img src="' + imageUrl + '"></div>');
    });

    $(document).on('click', '.epa-lightbox', function() {
        $(this).remove();
    });

    // Load More My Photos
    let my_page = 1;
    $(document).on('click', '.epa-wishlist-load-more', function(e) {
        e.preventDefault();
        my_page++;

        var button = $(this);
        button.prop('disabled', true).text('Loading...');
        var collectionId = button.data('collection-id');

        $.ajax({
            url: epa_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'load_more_from_wishlist',
                page: my_page,
                collection_id: collectionId,
                security: epa_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.epa-wishlist-photo-container').append(response.data.content);
                    button.prop('disabled', false).text('Load More');
                }
                if (response.data.no_more_images == 0) {
                    button.hide();
                }
            }
        });
    });

    function epaPerformSearch(){
        let albumId = $('.epa-search-button').data('album-id');
        let search_query = $('.epa-search-input').val();
        let type = $('.epa-list-view-button').text();
        $.ajax({
            url: epa_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'epa_search_album_images',
                album_id: albumId,
                type: type,
                search: search_query,
                security: epa_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.epa-photo-container').html(response.data.content);
                }else{
                    $('.epa-photo-container').html(response.data.content);
                }

                if (response.data.no_more_images == 0) {
                    $('.epa-load-more').hide();
                }
            }
        });

    }

    // Trigger search on button click
    $(document).on('click', '.epa-search-button', function(e){
       e.preventDefault();
       epaPerformSearch();
    });

    // Trigger search on Enter key press
    $(document).on('keypress', '.epa-search-input', function (e) {
        if (e.which === 13) { 
            e.preventDefault();
            epaPerformSearch();
        }
    });

    // List and grid view

    $(document).on('click','.epa-list-view-button', function(e){

        let $button = $(this);
        let $span = $button.find('.epa-list-view-text');
        let $icon = $button.find('i');
        if ($span.text().trim() === 'list view') {
            $span.text('grid view'); 
            $button.data('view', 'list'); 
            $icon.removeClass('fa-list').addClass('fa-th-large');
            $('.epa-photo-container').removeClass('epa-photo-grid-container').addClass('epa-photo-list-container');
            $('.epa-photo-item').removeClass('epa-photo-grid').addClass('epa-photo-list');
        } else {
            $span.text('list view'); 
            $button.data('view', 'grid');
            $icon.removeClass('fa-th-large').addClass('fa-list');
            $('.epa-photo-container').removeClass('epa-photo-list-container').addClass('epa-photo-grid-container');
            $('.epa-photo-item').removeClass('epa-photo-list').addClass('epa-photo-grid');
        }

    });

    // Save collection popup
    $(document).on('click','.epa-wishlist-icon', function(e){
        // Open Popup
        $('#epapopupContainer').css({
            'visibility': 'visible',
            'opacity': '1'
        });
        getAllCollections();
        var parentDiv = $(this).closest('.epa-photo-item');
        var imgElement = parentDiv.find('img');
        var titleElement = parentDiv.find('p.epa-photo-filename').text();
        var imageUrl = imgElement.attr('data-full-url') || imgElement.attr('src');
        $('.epa-popup-image').attr('src',imageUrl);
        $('.epa-image-name').text(titleElement);
        let photoId = $(this).data('photo-id');
        $('.epa-save-btn').attr('data-id',photoId);
        $('.epa-add-collection-btn').attr('data-thumbnail-id',photoId);

    });

    $(document).on('click','#epaClosePopupBtn', function(e){
        $('#epapopupContainer').css({
            'visibility': 'hidden',
            'opacity': '0'
        });
    });

    $(document).on('click','#epapopupContainer', function(e){
        if ($(e.target).is('#epapopupContainer')) {
            $('#epapopupContainer').css({
                'visibility': 'hidden',
                'opacity': '0'
            });
        }
    });
    $(document).on('click','.epa-collection-item', function(e){
        $('.epa-collection-item').removeClass('selected');
        $(this).addClass('selected');
        $('.epa-save-btn').prop("disabled", false);
    });

    function epaInsertCollection(){
        var collectionName = $('.epa-collection-name').val();
        var userId = $('.epa-add-collection-btn').data('user-id');
        var thumbnailId = $('.epa-add-collection-btn').data('thumbnail-id');
        $.ajax({
            url: epa_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'epa_save_user_collections',
                collection_name: collectionName,
                user_id: userId,
                thumbnail_id: thumbnailId,
                security: epa_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data);
                    getAllCollections();
                }else{
                    alert(response.data);
                }
            }
        });

    }

    $(document).on('click','.epa-add-collection-btn', function(e){
        e.preventDefault();
        epaInsertCollection();
    });

    $(document).on('keypress', '.epa-collection-name', function (e) {
        if (e.which === 13) { 
            e.preventDefault();
            epaInsertCollection();
        }
    });

    $(document).on('click','.epa-save-btn', function(e){
        e.preventDefault();
        let collectionArray = [];
        let photoId = $(this).data('id');
        var collectionId = $('.epa-collection-item.selected span').data('id');
        let multiplePhotoId = $("#multipleimages").val();
        let photoIds = multiplePhotoId.split(',');

        if(multiplePhotoId.length > 0){
            photoIds.forEach(photoId => {
                collectionArray.push({
                    'collection_id': collectionId,
                    'photo_id': photoId
                });
            });
        }else{
            collectionArray.push({
                'collection_id': collectionId,
                'photo_id': photoId
            });
        }

        $.ajax({
            url: epa_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'add_to_wishlist',
                collection_data: JSON.stringify(collectionArray),
                security: epa_ajax_object.nonce
            },
            success: function(response) {
                $('#epapopupContainer').css({
                    'visibility': 'hidden',
                    'opacity': '0'
                });

                if (response.success) {
                    Swal.fire({
                        title: 'Wishlist',
                        text: response.data,
                        icon: 'success',
                        showCloseButton: true,
                        showConfirmButton: true,
                        timerProgressBar: true,
                        timer: 5000
                    }).then((result) => {
                        location.reload();
                    });
                }else{
                    Swal.fire({
                        title: 'Wishlist',
                        text: response.data,
                        icon: 'error',
                        showCloseButton: true,
                        showConfirmButton: true,
                        timerProgressBar: true,
                        timer: 5000
                    }).then((result) => {
                        location.reload();
                    });
                }
            }
        });
    });


    function searchCollectionName(search){
        $.ajax({
            url: epa_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'epa_search_existing_collection',
                search: search,
                security: epa_ajax_object.nonce
            },
            success: function(response) {
                if(response.success){
                    $('.epa-collection-list').html(response.data.content);
                }else{
                    $('.epa-collection-list').html(response.data);
                }
            }
        });
    }

    $(document).on('click','.epa-search-collection-button', function(e){
        let search = $('.epa-search-collection-input').val();
        searchCollectionName(search);
    });


    $(document).on('keyup', '.epa-search-collection-input', function (e) {
        let search = $(this).val();
        searchCollectionName(search);
    });


    // All Collection 

    function getAllCollections(){
        $.ajax({
            url: epa_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'epa_get_all_collection_lists',
                security: epa_ajax_object.nonce
            },
            success: function(response) {
                if(response.success){
                    $('.epa-collection-list').html(response.data.content);
                }else{
                    $('.epa-collection-list').html(response.data);
                }
            }
        });
    }

    // Product select 
    let infoShown = false;
    $(document).on('change','#epa_product_select', function(e){
        let selectedOption = $(this).find(':selected').attr('data-event-type');
        if(selectedOption === 'cup' || selectedOption === 't-shirt'){
            $(document).on('click','.epa-wishlist-item', function(e){
                $('.epa-wishlist-item').removeClass('selected');
                $(this).addClass('selected');
                $('.epa-wishlist-photo-msg').html('');
            });
            $('.epa-wishlist-photo-msg').html('<div class="woocommerce-info">Please choose an image?</div>');
        }else{
            $('.epa-wishlist-photo-msg').html('');
            $(document).on('click','.epa-wishlist-item', function(e){
                $('.epa-wishlist-item').removeClass('selected');
            });
        }
        $('.epa-wishlist-item').removeClass('selected');
    });

    $(document).on('click','.epa-add-to-cart', function(e){
        let selectedOption = $('#epa_product_select option:selected');
        let productId = selectedOption.val();
        let collectionId = selectedOption.data('collection-id');
        let eventType = selectedOption.data('event-type');
        let photoId = '';
        if (!productId) {
            Swal.fire({
                icon: 'error',
                title: 'No Option Selected',
                text: 'Please select a print option before proceeding.',
            });
            return;
        }

        if (eventType === 'album') {

            $.ajax({
                url: epa_ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'epa_add_to_cart_single_product_page',
                    product_id: productId,
                    event_type: eventType,
                    collection_id: collectionId,
                    photo_id: photoId,
                    security: epa_ajax_object.nonce
                },
                success: function(response) {
                    if(response.success){
                        window.location.href = response.data.url;
                    }
                }
            });

        } else if (eventType === 'cup' || eventType === 't-shirt') {
            
            if (!infoShown) {
                Swal.fire({
                    icon: 'info',
                    title: 'Image Selected',
                    text: 'You can now proceed with this selection.',
                });
                infoShown = true;
            }

            photoId = $('.epa-wishlist-item.selected span').data('photo-id');
            if (!photoId) {
                Swal.fire({
                    icon: 'error',
                    title: 'No Image Selected',
                    text: 'Please click on an image to select it before proceeding.',
                });
                return;
            }

             $.ajax({
                url: epa_ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'epa_add_to_cart_single_product_page',
                    product_id: productId,
                    event_type: eventType,
                    collection_id: collectionId,
                    photo_id: photoId,
                    security: epa_ajax_object.nonce
                },
                success: function(response) {
                    if(response.success){
                        window.location.href = response.data.url;
                    }
                }
            });
        }
    });

    // List grid view on wishlist

    $(document).on('click','.epa-wishlist-list-view-button', function(e){

        let $button = $(this);
        let $span = $button.find('.epa-wishlist-list-view-text');
        let $icon = $button.find('i');
        if ($span.text().trim() === 'list view') {
            $span.text('grid view'); 
            $button.data('view', 'list'); 
            $icon.removeClass('fa-list').addClass('fa-th-large');

            $('.epa-wishlist-photo-container').removeClass('epa-wishlist-photo-grid-container').addClass('epa-wishlist-photo-list-container');
            $('.epa-photo-item').removeClass('epa-photo-grid').addClass('epa-photo-list');

            $('.epa-wishlist-item').removeClass('epa-wishlist-grid').addClass('epa-wishlist-list');
        } else {
            $span.text('list view'); 
            $button.data('view', 'grid');
            $icon.removeClass('fa-th-large').addClass('fa-list');
            $('.epa-wishlist-photo-container').removeClass('epa-wishlist-photo-list-container').addClass('epa-wishlist-photo-grid-container');
            $('.epa-wishlist-item').removeClass('epa-wishlist-list').addClass('epa-wishlist-grid');
        }

    });

    // Edit Collection
    $(document).on('click','.epa-edit-collection', function(e){
        let collectionId = $(this).data('collection-id');
        let collectionName = $(this).data('collection-name');
        Swal.fire({
            title: 'Edit Collection Name',
            input: 'text',
            inputValue: collectionName, // Optional: set the initial value of the input
            showCancelButton: true,
            confirmButtonText: 'Submit',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) {
                    return 'Please enter a collection name!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let newName = result.value;
                $.ajax({
                    url: epa_ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'epa_edit_user_collections',
                        collection_id: collectionId,
                        collection_name: newName,
                        security: epa_ajax_object.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Collection',
                                text: response.data,
                                icon: 'success',
                                showCloseButton: true,
                                showConfirmButton: true,
                                timerProgressBar: true,
                                timer: 5000
                            }).then((result) => {
                                if (result.isConfirmed || result.isDismissed) {
                                    location.reload(); 
                                }
                            });
                        }else{
                            Swal.fire({
                                title: 'Collection',
                                text: response.data,
                                icon: 'error',
                                showCloseButton: true,
                                showConfirmButton: true,
                                timerProgressBar: true,
                                timer: 5000
                            });
                        }
                    },error: function() {
                        Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
                    }
                });
            }
        });

    });

    // Remove collection
    $(document).on('click','.epa-remove-collection', function(e){
        e.preventDefault();
        let collectionId = $(this).data('collection-id');
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you really want to remove this collection?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true // Swaps the positions of confirm and cancel
        }).then((result) => {
            if (result.isConfirmed) {
                    $.ajax({
                    url: epa_ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'epa_remove_user_collections',
                        collection_id: collectionId,
                        security: epa_ajax_object.nonce
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Collection',
                                text: 'Collection removed sucessfully.',
                                icon: 'success',
                                showCloseButton: true,
                                showConfirmButton: true,
                                timerProgressBar: true,
                                timer: 5000
                            }).then((result) => {
                                if (result.isConfirmed || result.isDismissed) {
                                    location.reload(); 
                                }
                            });

                        } else {
                            Swal.fire({
                                title: 'Collection',
                                text: 'Failed to remove photo from collection.',
                                icon: 'error',
                                showCloseButton: true,
                                showConfirmButton: true,
                                timerProgressBar: true,
                                timer: 5000
                            });
                        }
                    },
                    error: function () {
                        alert('An error occurred while removing the photo.');
                    }
                });
            }
        });
    });
	$('[data-fancybox="gallery"]').fancybox({
	  buttons: [
		"slideShow",
		"thumbs",
		"zoom",
		"fullScreen",
		"share",
		"close"
	  ],
	  loop: false,
	  protect: true
	});

    // My Photos Album Search

    function searchMyPhotosAlbum(search){
        $.ajax({
            url: epa_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'search_user_album_photos',
                search: search,
                security: epa_ajax_object.nonce
            },
            success: function(response) {
                if(response.success){
                    $('.epa-album-grid').html(response.data.content);
                }else{
                    $('.epa-album-grid').html(response.data);
                }
            }
        });
    }

    $(document).on('keypress', '.epa-search-my-photos', function (e) {
        let searchMyPhoto = $(this).val();
        if (e.which === 13) { 
            e.preventDefault();
            searchMyPhotosAlbum(searchMyPhoto);
        }
    });

    $(document).on('click', '.epa-search-button-my-photos', function (e) {
        let searchMyPhoto = $('.epa-search-my-photos').val();
        e.preventDefault();
        searchMyPhotosAlbum(searchMyPhoto);
    });

    $(document).on('change','.epa-myphotos-select', function(e){
        e.preventDefault();
        let order = $(this).val();
        let albumId = $('.epa-search-button').data('album-id');
        let search_query = $('.epa-search-input').val().trim();
        let type = $('.epa-list-view-button').text();
        $.ajax({
            url: epa_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'epa_search_album_images',
                album_id: albumId,
                type: type,
                order: order,
                search: search_query,
                security: epa_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.epa-photo-container').html(response.data.content);
                }else{
                    $('.epa-photo-container').html(response.data.content);
                }

                if (response.data.no_more_images == 0) {
                    $('.epa-load-more').hide();
                }
            }
        });

    });

    $(document).on('click', '#epa-myphotos-save', function(e){
        e.preventDefault();
        let checkedValues = [];
        $('.epa-popup-thumbnails').empty();
        $('input[name="my-photo-checkbox"]:checked').each(function (index) {
            var parentDiv = $(this).closest('.epa-photo-item');
            var imgElement = parentDiv.find('img');
            var titleElement = parentDiv.find('p.epa-photo-filename').text();
            var imageUrl = imgElement.attr('data-full-url') || imgElement.attr('src');
            let photoId = $(this).val();

            if (index === 0) {
               $('.epa-popup-image').attr('src', imageUrl);
                $('.epa-image-name').text(titleElement);
                $('.epa-save-btn').attr('data-id', photoId);
                $('.epa-add-collection-btn').attr('data-thumbnail-id', photoId);
            }else{
                var thumbnailHtml = `
                    <div class="thumbnail">
                        <img src="${imageUrl}" alt="${titleElement}" data-id="${photoId}" class="thumbnail-image">
                    </div>`;
                $('.epa-popup-thumbnails').append(thumbnailHtml);
            }
            checkedValues.push(photoId);
        });

        if(checkedValues.length > 0){
            $("#multipleimages").val(checkedValues);
            $('#epapopupContainer').css({
                'visibility': 'visible',
                'opacity': '1'
            });
            getAllCollections();
        }else{
            alert('Please select atleast one image');
            return false;
        }

    });

    // Search Collection start 

    function searchUserCollections(search){
        $.ajax({
            url: epa_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'search_user_collections',
                search: search,
                security: epa_ajax_object.nonce
            },
            success: function(response) {
                if(response.success){
                    $('.epa-collection-album-grid').html(response.data.content);
                }else{
                    $('.epa-collection-album-grid').html(response.data);
                }
            }
        });
    }

    $(document).on('keypress', '.epa-search-collection', function (e) {
        let searchMyPhoto = $(this).val();
        if (e.which === 13) { 
            e.preventDefault();
            searchUserCollections(searchMyPhoto);
        }
    });

    $(document).on('click', '.epa-search-button-collection', function (e) {
        let searchMyPhoto = $('.epa-search-collection').val();
        e.preventDefault();
        searchUserCollections(searchMyPhoto);
    });
    // Search Collection end 

    // Search wishlist start 

    function searchUserWishlist(search){
        var searchValue = search.toLowerCase();
        $('.epa-wishlist-item').each(function() {
            var title = $(this).find('.epa-photo-filename').text().toLowerCase();
            if (title.includes(searchValue)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    $(document).on('keypress', '.epa-wishlist-input', function (e) {
        let searchMyPhoto = $(this).val();
        if (e.which === 13) { 
            e.preventDefault();
            searchUserWishlist(searchMyPhoto);
        }
    });

    $(document).on('click', '.epa-wishlist-button', function (e) {
        let searchMyPhoto = $('.epa-search-collection').val();
        e.preventDefault();
        searchUserWishlist(searchMyPhoto);
    });
    // Search Collection end 


});