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
        update_option("type_iii_audio_preview_mode", isset($_POST["preview_mode"]) ? "1" : "0");
        update_option("type_iii_audio_header_play_buttons", isset($_POST["header_play_buttons"]) ? "1" : "0");
        update_option("type_iii_audio_header_play_buttons_css", $_POST["header_play_buttons_css"]);
        ?>
        <div class="updated"><p><strong><?php _e("Settings saved."); ?></strong></p></div>
        <?php
    }
    $auth_key = get_option("type_iii_audio_auth_key");
    $preview_mode = get_option("type_iii_audio_preview_mode", "0");
    $header_play_buttons = get_option("type_iii_audio_header_play_buttons", "0");
    $header_play_buttons_css = get_option("type_iii_audio_header_play_buttons_css", "
/* Heading play button should not be shown on small screens */
.t3a-heading-play-button {
  display: none;
}

/* Set minimum width at which heading play button should be shown */
@media screen and (min-width: 850px) {
  .t3a-heading-play-button {
    display: block;
    position: absolute;
    top: 0;
    left: 0;
    margin-left: -34px;
    margin-right: 10px;
    border-radius: 9999px;
    border: none;
    width: 1.5rem;
    height: 1.5rem;
    outline: none;
    cursor: pointer;
    transform: translate(0, 0);
    z-index: 10;

    /* Colour of the play button */
    background-color: #ddd;
    /* Colour of the play button icon. */
    color: #fff;
  }

  .t3a-heading-play-button:hover {
    /* Colour of the play on hover */
    background-color: #333;
  }

  .t3a-heading-play-button:focus {
    outline: none;
  }

  .t3a-heading-play-icon {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-left: 2px;  
  }

  /* Refine this to match the dimensions of your heading typeface */
  h1 .t3a-heading-play-button { margin-top: 9px } 
  h2 .t3a-heading-play-button { margin-top: 9px } 
  h3 .t3a-heading-play-button { margin-top: 3px }
}

/* Show the heading play button only after the user starts playback */
.t3a-heading-play-button {
  display: none;
}

.t3a-playback-started .t3a-heading-play-button {
  display: block;
}
");
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
                    <tr>
                        <th scope="row">
                            <label for="preview_mode">Preview Mode:</label>
                        </th>
                        <td>
                            <input type="checkbox" id="preview_mode" name="preview_mode" value="1" <?php checked($preview_mode, "1"); ?>>
                            <p class="description">
                                When enabled, the audio player will only be visible to logged-in WordPress users.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="header_play_buttons">Header Play Buttons:</label>
                        </th>
                        <td>
                            <input type="checkbox" id="header_play_buttons" name="header_play_buttons" value="1" <?php checked($header_play_buttons, "1"); ?>>
                            <p class="description">
                                When enabled, adds <a href="https://docs.type3.audio/#header-play-buttons">header play buttons</a> to the TYPE III AUDIO player.
                            </p>
                        </td>
                    </tr>
                    <tr class="header-play-buttons-css-row" style="display: <?php echo $header_play_buttons === "1" ? "table-row" : "none"; ?>">
                        <th scope="row">
                            <label for="header_play_buttons_css">Header Play Buttons CSS:</label>
                        </th>
                        <td>
                            <textarea id="header_play_buttons_css" name="header_play_buttons_css" rows="50" class="large-text code" ><?php echo esc_textarea($header_play_buttons_css); ?></textarea>
                            <p class="description">
                                CSS styles for the header play buttons. Default values are <a href="https://docs.type3.audio/#header-play-buttons">shown here</a>.
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <hr />
            <?php submit_button(); ?>
        </form>
    </div>

    <script>
        // Initialize CodeMirror
        function initCodeMirror() {
            var editor = wp.codeEditor.initialize('header_play_buttons_css', {
                mode: 'css',
                lineNumbers: true,
                indentUnit: 4,
                tabSize: 4,
                lineWrapping: true,
                autoCloseBrackets: true,
                matchBrackets: true,
                autoCloseTags: true,
                matchTags: true,
                indentWithTabs: false,
                theme: 'default',
                height: '80em'
            }); 

            // Force the height after initialization
            jQuery('.CodeMirror').css('height', '80em');
        }

        // Add CSS to ensure height persists
        var style = document.createElement('style');
        style.textContent = '.CodeMirror { height: 80em !important; }';
        document.head.appendChild(style);

        document.getElementById('header_play_buttons').addEventListener('change', function() {
            const cssRow = document.querySelector('.header-play-buttons-css-row');
            cssRow.style.display = this.checked ? 'table-row' : 'none';
            
            // Reinitialize CodeMirror after a short delay to ensure the textarea is visible
            if (this.checked) {
                setTimeout(initCodeMirror, 100);
            }
        });

        // Initial initialization if the textarea is visible
        jQuery(document).ready(function($) {
            if (document.getElementById('header_play_buttons').checked) {
                initCodeMirror();
            }
        });
    </script>

<?php
}

// Enqueue CodeMirror
function t3a_enqueue_code_editor() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'settings_page_type_iii_audio') {
        wp_enqueue_code_editor(array(
            'type' => 'text/css',
            'codemirror' => array(
                'mode' => 'css',
                'lineNumbers' => true,
                'indentUnit' => 4,
                'tabSize' => 4,
                'lineWrapping' => true,
                'autoCloseBrackets' => true,
                'matchBrackets' => true,
                'autoCloseTags' => true,
                'matchTags' => true,
                'indentWithTabs' => false,
                'theme' => 'default',
                'height' => '50em'
            )
        ));
    }
}
add_action('admin_enqueue_scripts', 't3a_enqueue_code_editor');

add_action('save_post', 't3a_send_regenerate_request', 10, 3);

function t3a_send_regenerate_request($post_ID, $post, $update) {
    if (wp_is_post_revision($post_ID) || !$update) {
        return;
    }
    
    if (get_post_status($post_ID) !== 'publish') {
        return;
    }

    $post_url = get_permalink($post_ID);
    $post_type = get_post_type($post_ID);
    $auth_key = get_option("type_iii_audio_auth_key");

    if($post_type === "podcast") {
        return;
    }

    $api_url = 'https://api.type3.audio/narration/regenerate';
    $payload = json_encode(array(
        'url' => $post_url,
        'post_type' => $post_type
    ));

    $args = array(
        'body' => $payload,
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => $auth_key
        ),
    );
    wp_remote_post($api_url, $args);
} 