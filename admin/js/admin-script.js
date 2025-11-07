jQuery(document).ready(function($) {
    if (typeof epa_ajax_object === 'undefined') {
        console.error('epa_ajax_object is not defined');
        return;
    }

    const checkbox = $('#_event_photo_gallery');
    const dropdown = $('#_event_photo_gallery_type').closest('p.form-field');
    function toggleDropdown() {
        if (checkbox.is(':checked')) {
            dropdown.show();
        } else {
            dropdown.hide();
        }
    }
    toggleDropdown();
    checkbox.on('change', toggleDropdown);

    let selectedAlbumId;

// Υποβολή της φόρμας μέσω AJAX για αποθήκευση αντιστοιχιών
$(document).on('click','#epa-assign-albums-form', function(e){
    e.preventDefault();
    const userId = $('#epa-modal-user-id').val();
    const selectedAlbums = [];
    $('input[name="album_ids[]"]:checked').each(function () {
        selectedAlbums.push($(this).val());
    });
     $.ajax({
        url: epa_ajax_object.ajax_url,
        type: 'POST',
        data: {
            action: 'assign_albums_to_user',
            user_id: userId,
            album_ids: selectedAlbums,
            security: epa_ajax_object.nonce
        },
        success: function (response) {
            if (response.success) {
                const adminNotice = `
                    <div id="message" class="updated notice is-dismissible">
                        <p>Albums successfully assigned to user.</p>
                    </div>`;
                $('.wrap').prepend(adminNotice);
                setTimeout(() => {
                    location.reload();
                }, 2000);
            }else{
                const adminNotice = `
                    <div id="message" class="error notice is-dismissible">
                        <p>Failed to assign albums. Please try again.</p>
                    </div>`;
                $('.wrap').prepend(adminNotice);
            }
        },
        error: function (xhr, status, error) {
            const adminNotice = `
                <div id="message" class="error notice is-dismissible">
                    <p>An error occurred. Please try again.</p>
                </div>`;
            $('.wrap').prepend(adminNotice);
        }
    });

});

function refreshAllAlbumsAndUsers(userId) {
    $.ajax({
        url: epa_ajax_object.ajax_url,
        type: 'POST',
        data: {
            action: 'refresh_all_assignments',
            user_id: userId,
            security: epa_ajax_object.nonce,
        },
        success: function (response) {
            if (response.success) {
                // Ενημέρωση UI
                $('#assigned-users-list').html(response.data.users_html);
                $('#assigned-albums-list').html(response.data.albums_html);
            } else {
                console.error('Failed to refresh assignments:', response.data);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', status, error);
        },
    });
}



// Συνάρτηση για ανανέωση της λίστας των Assigned Albums
function refreshAssignedAlbums(userId) {
    $.ajax({
        url: epa_ajax_object.ajax_url,
        type: 'POST',
        data: {
            action: 'get_assigned_albums',
            user_id: userId,
            security: epa_ajax_object.nonce
        },
        success: function(response) {
            if (response.success) {
                $(`#user-assigned-albums-${userId}`).html(response.data);
            } else {
                console.error('Failed to refresh assigned albums:', response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
        }
    });
}


    // Άνοιγμα του modal και φόρτωση albums
    $('.epa-assign-albums').on('click', function (e) {
        e.preventDefault();

        const userId = $(this).data('user-id');
        const userName = $(this).closest('tr').find('td:first').text();

        $('#epa-modal-user-id').val(userId);
        $('#epa-modal-user-name').text(userName);

        // Φόρτωση albums μέσω AJAX
        $.ajax({
            url: epa_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'get_assigned_albums',
                user_id: userId,
                security: epa_ajax_object.nonce,
            },
            success: function (response) {
                if (response.success) {
                    $('#album-checkboxes').html(response.data);
                    $('#epa-assign-albums-modal').show();
                } else {
                    console.error('Failed to load albums:', response.data);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
            },
        });
    });


    // Κλείσιμο του modal όταν ο χρήστης κάνει κλικ εκτός του modal ή στο κουμπί "Close"
    $(document).mouseup(function(e) {
        const modal = $('#epa-assign-albums-modal');
        if (!modal.is(e.target) && modal.has(e.target).length === 0) {
            modal.hide();
        }
    });

    // Υποβολή της φόρμας
    $('#epa-assign-albums-form').on('submit', function (e) {
        e.preventDefault();

        const userId = $('#epa-modal-user-id').val();
        const selectedAlbums = $('input[name="album_ids[]"]:checked')
            .map(function () {
                return $(this).val();
            })
            .get();

        $.ajax({
            url: epa_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'assign_albums_to_user',
                user_id: userId,
                album_ids: selectedAlbums,
                security: epa_ajax_object.nonce,
            },
            success: function (response) {
                if (response.success) {
                    location.reload(); // Ανανεώνει τη σελίδα για να εμφανιστεί το admin notice
                } else {
                    console.error('Failed to assign albums:', response.data);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
            },
        });
    });



    // Enable the button only when an album is selected
    $('#epa-album-select').on('change', function() {
        selectedAlbumId = $(this).val();
        $('#epa-open-media-library').prop('disabled', !selectedAlbumId);
    });

    let frame;
    // Open WordPress Media Library
    $('#epa-open-media-library').on('click', function(e) {
        e.preventDefault();

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: 'Select Photos for Album',
            button: { text: 'Add to Album' },
            multiple: true
        });

        frame.on('select', function() {
            let images = frame.state().get('selection').map(function(attachment) {
                attachment = attachment.toJSON();
                return attachment.id;
            });

            // Κλείσιμο του Media Library αμέσως μετά την επιλογή
            frame.close();

            // AJAX αίτημα για προσθήκη των επιλεγμένων εικόνων στο album
            $.ajax({
                url: epa_ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'epa_add_photos_to_album',
                    album_id: selectedAlbumId,
                    image_ids: images,
                    security: epa_ajax_object.nonce
                },
                success: function(response) {
                    if (response.success) {
                            Swal.fire({
                                title: 'Album',
                                text: 'Photos added successfully to album.',
                                icon: 'success',
                                showCloseButton: true,
                                showConfirmButton: true,
                                timerProgressBar: true,
                                timer: 5000
                            }).then((result) => {
                                if (result.isConfirmed || result.isDismissed) {
                                    loadAlbumPhotos(selectedAlbumId);
                                }
                            });

                        } else {
                            Swal.fire({
                                title: 'Album',
                                text: 'Failed to add photos unknown error',
                                icon: 'error',
                                showCloseButton: true,
                                showConfirmButton: true,
                                timerProgressBar: true,
                                timer: 5000
                            });
                        }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            });
        });

        frame.open();
    });

    let page = 1;
    // Load photos for the selected album
    function loadAlbumPhotos(albumId) {
        page = 1;
        $.ajax({
            url: epa_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'load_more_photos',
                album_id: albumId,
                page: page,
                security: epa_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#epa-photo-gallery').html(response.data.content);
                    if(response.data.no_more_images == 1){
                        $(".epa-photo-gallery-load-more").html(`<button class="button button-primary" id="load-more-photos" data-album-id='${albumId}'>Load More</button>`);
                    }

                    // Προσθήκη event για διαγραφή φωτογραφίας
                    $(document).on("click",".epa-delete-photo", function(e){
                        const photoId = $(this).data('photo-id');
                        deletePhoto(photoId);
                    });
                } else {
                    $('#epa-photo-gallery').html('<p>No photos found for this album.</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                $('#epa-photo-gallery').html('<p>An error occurred while loading photos.</p>');
            }
        });
    }

    // AJAX request για διαγραφή φωτογραφίας
    function deletePhoto(photoId) {

        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you really want to delete this image?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true // Swaps the positions of confirm and cancel
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: epa_ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'epa_delete_photo',
                        photo_id: photoId,
                        security: epa_ajax_object.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Album',
                                text: 'Photo removed from album.',
                                icon: 'success',
                                showCloseButton: true,
                                showConfirmButton: true,
                                timerProgressBar: true,
                                timer: 5000
                            }).then((result) => {
                                if (result.isConfirmed || result.isDismissed) {
                                    loadAlbumPhotos(selectedAlbumId);
                                }
                            });

                        } else {
                            Swal.fire({
                                title: 'Album',
                                text: 'Failed to delete photo unknown error',
                                icon: 'error',
                                showCloseButton: true,
                                showConfirmButton: true,
                                timerProgressBar: true,
                                timer: 5000
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                    }
                });
            }
        });
    }

    // Load photos whenever a new album is selected
    $('#epa-album-select').on('change', function() {
        const albumId = $(this).val();
        if (albumId) {
            loadAlbumPhotos(albumId);
        } else {
            $('#epa-photo-gallery').empty();
        }
    });
    $(document).on("click","#load-more-photos", function(e){
        page++;
        const albumId = $(this).data('album-id');
        const $button = $(this);
        $button.prop('disabled', true).text('Loading...');
        $.ajax({
            url: epa_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'load_more_photos', 
                album_id: albumId,
                page: page,
                security: epa_ajax_object.nonce 
            },
            success: function(response) {
                if (response.success) {
                    console.log(response.data);
                    $('#epa-photo-gallery').append(response.data.content);
                    $button.prop('disabled', false).text('Load More');

                    if(response.data.no_more_images == 0){
                        $button.hide();
                    }
                }else {
                    $button.prop('disabled', false).text('Load More');
                    $button.hide();
                }
            }
        });
    })


});
