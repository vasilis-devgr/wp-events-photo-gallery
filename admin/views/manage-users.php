<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_manager = new User_Manager();
$users = $user_manager->get_users();
$album_manager = new Album_Manager();
$albums = $album_manager->get_albums();

add_action( 'admin_notices', function() {
    if ( $message = get_transient( 'epa_admin_notice' ) ) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
        delete_transient( 'epa_admin_notice' );
    }
});


?>



<div class="wrap">
    <h1>Manage Users</h1>

    <table class="widefat">
        <thead>
            <tr>
                <th>User</th>
                <th>Assigned Albums</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $users as $user ) : ?>
                <tr>
                    <td><?php echo esc_html( $user->display_name ); ?> (<?php echo esc_html( $user->user_email ); ?>)</td>
                    <td>
                        <?php
                        // Debugging για το user_meta
                        error_log( 'User meta for _assigned_albums for user ID ' . $user->ID . ': ' . print_r( get_user_meta( $user->ID, '_assigned_albums', true ), true ) );

                        // Ανάκτηση των ανατεθειμένων albums
                        $assigned_albums = $user_manager->get_user_assigned_albums( $user->ID );
                        if (empty($assigned_albums)) {
                            echo 'No assigned albums.';
                        } else {
                            foreach ($assigned_albums as $album) {
                                echo esc_html($album->post_title) . ',';
                            }
                        }
                        
                        ?>
                    </td>
                    <td>
                        <a href="#" class="epa-assign-albums" data-user-id="<?php echo esc_attr( $user->ID ); ?>">Assign Albums</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Modal για αντιστοίχιση albums -->
    <div id="epa-assign-albums-modal" style="display:none;">
    <h2>Assign Albums to User: <span id="epa-modal-user-name"></span></h2>
    <form method="post" class="epa-assign-albums-form">
        <input type="hidden" name="user_id" id="epa-modal-user-id">
        <!-- Τα checkboxes για albums -->
        <div id="album-checkboxes">
            <!-- Γεννιούνται μέσω JavaScript -->
        </div>
        <button type="submit" class="button button-primary" id="epa-assign-albums-form">Assign Albums</button>
    </form>
</div>



</div>
