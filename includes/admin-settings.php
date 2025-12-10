<?php
/**
 * Admin settings functionality for TYPE III AUDIO
 */

if (!defined('ABSPATH')) {
    exit;
}

// Hook for adding admin menus
add_action('admin_menu', 'type_iii_audio_menu');

// Action function for the above hook
function type_iii_audio_menu() {
    add_options_page(
        'TYPE III AUDIO',           // Page title
        'TYPE III AUDIO',           // Menu title
        'manage_options',           // Capability
        'type_iii_audio',           // Menu slug
        'type_iii_audio_options'    // Function that handles the options page
    );
}

// Function to display the options page
function type_iii_audio_options() {
    if (!current_user_can('manage_options'))  {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    if (@$_POST["update_settings"] == "Y") {
        update_option("type_iii_audio_auth_key", $_POST["auth_key"]);
        ?>
        <div class="updated"><p><strong><?php _e("Settings saved."); ?></strong></p></div>
        <?php
    }
    $auth_key = get_option("type_iii_audio_auth_key");
    ?>

    <div class="wrap">
        <h2>TYPE III AUDIO</h2>
        <form method="post" action="">
            <input type="hidden" name="update_settings" value="Y" />
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="auth_key">Authorization Key:</label>
                        </th>
                        <td>
                            <input type="text" id="auth_key" name="auth_key" value="<?php echo $auth_key; ?>" class="regular-text">
                            <p class="description">
                                This key is used to authenticate requests to create and regenerate narrations.
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <hr />
            <?php submit_button(); ?>
        </form>
    </div>

<?php
}

 