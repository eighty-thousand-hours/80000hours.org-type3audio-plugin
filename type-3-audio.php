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

/*
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    ⚠️  VERSION BUMPING REQUIRED  ⚠️                        ║
 * ║                                                                           ║
 * ║  DUAL VERSION SYSTEM:                                                     ║
 * ║  - T3A_VERSION: Tracks upstream Type 3 Audio plugin version (rarely bump)║
 * ║  - T3A_80K_ASSET_REV: 80k-specific asset revision (bump for CSS/JS changes)
 * ║                                                                           ║
 * ║  When you modify assets/css/player.css or assets/js/*.js:                ║
 * ║                                                                           ║
 * ║  1. Bump T3A_80K_ASSET_REV below (increment: 1 → 2 → 3...)              ║
 * ║  2. DO NOT bump T3A_VERSION (only when syncing from upstream)            ║
 * ║                                                                           ║
 * ║  Cache busting uses: T3A_VERSION . '.' . T3A_80K_ASSET_REV              ║
 * ║  Example: 1.7.1 → 1.7.2 → 1.7.3 (80k changes) → 1.8.1 (upstream update) ║
 * ║                                                                           ║
 * ║  Assets that use this for cache busting:                                 ║
 * ║  - assets/css/player.css (via shortcode-player.php)                      ║
 * ║  - assets/js/player-enhancements.js (via shortcode-player.php)           ║
 * ║  - assets/js/manage-narration.js (via manage-narration-metabox.php)      ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 */
define('T3A_VERSION', '1.7');           // Upstream Type 3 Audio version
define('T3A_80K_ASSET_REV', '1');       // 80k-specific asset revision

// Include required files
require_once T3A_PLUGIN_PATH . 'includes/shortcode-player.php';
require_once T3A_PLUGIN_PATH . 'includes/admin-settings.php';
require_once T3A_PLUGIN_PATH . 'includes/regeneration.php';
require_once T3A_PLUGIN_PATH . 'includes/manage-narration-metabox.php';
