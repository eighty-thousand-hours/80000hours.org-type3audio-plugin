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
        update_option("type_iii_audio_floating_player", isset($_POST["floating_player"]) ? "1" : "0");
        update_option("type_iii_audio_custom_css", $_POST["custom_css"]);
        ?>
        <div class="updated"><p><strong><?php _e("Settings saved."); ?></strong></p></div>
        <?php
    }
    $auth_key = get_option("type_iii_audio_auth_key");
    $preview_mode = get_option("type_iii_audio_preview_mode", "0");
    $header_play_buttons = get_option("type_iii_audio_header_play_buttons", "0");
    $floating_player = get_option("type_iii_audio_floating_player", "0");
    $custom_css = get_option("type_iii_audio_custom_css", "
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
                    <tr>
                        <th scope="row">
                            <label for="floating_player">Floating Player:</label>
                        </th>
                        <td>
                            <input type="checkbox" id="floating_player" name="floating_player" value="1" <?php checked($floating_player, "1"); ?>>
                            <p class="description">
                                When enabled, makes the audio player float/stick to the bottom of the screen while scrolling.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="custom_css">Custom CSS:</label>
                        </th>
                        <td>
                            <textarea id="custom_css" name="custom_css" rows="50" class="large-text code" ><?php echo esc_textarea($custom_css); ?></textarea>
                            <p class="description">
                                Custom CSS styles for the TYPE III AUDIO player. This CSS will be injected whenever the player shortcode is used on a page.
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
            var editor = wp.codeEditor.initialize('custom_css', {
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

        // Initial initialization
        jQuery(document).ready(function($) {
            initCodeMirror();
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