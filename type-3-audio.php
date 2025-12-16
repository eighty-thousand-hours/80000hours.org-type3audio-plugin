<?php
/**
 * Plugin Name: TYPE III AUDIO - Audio player & automatic narration (PATCHED)
 * Plugin URI: https://type3.audio
 * Description: PATCHED. UPDATE WITH CARE. Audio player for your MP3s. Narrations for your web pages.
 * Version: 1.7
 * Text Domain: type_3_player
 * Author: TYPE III AUDIO
 * Author URI: https://type3.audio
 */


/*
 * This plugin is HEAVILY PATCHED.
 *
 * This is a fork of the externally-maintained TYPE III AUDIO plugin with custom patches
 * for 80,000 Hours. The fork is maintained at:
 * https://github.com/eighty-thousand-hours/80000hours.org-type3audio-plugin
 *
 * When updating from upstream, carefully compare with the upstream version to preserve
 * all custom patches. Use a diffchecker to identify what needs to be re-applied.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('T3A_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('T3A_PLUGIN_URL', plugins_url('', __FILE__));
define('T3A_VERSION', '1.7');

// Include required files
require_once T3A_PLUGIN_PATH . 'includes/shortcode-player.php';
require_once T3A_PLUGIN_PATH . 'includes/admin-settings.php';
require_once T3A_PLUGIN_PATH . 'includes/regeneration.php';
require_once T3A_PLUGIN_PATH . 'includes/manage-narration-metabox.php';
