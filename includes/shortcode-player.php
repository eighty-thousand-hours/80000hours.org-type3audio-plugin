<?php
/**
 * Shortcode player functionality for TYPE III AUDIO
 */

if (!defined('ABSPATH')) {
    exit;
}

function t3a_enqueue_scripts() {
    wp_register_script('type-3-player', 'https://embed.type3.audio/player.js', array(), '1.0.0', true);
}

add_action('wp_enqueue_scripts', 't3a_enqueue_scripts');

function t3a_register_custom_css() {
    $custom_css = get_option("type_iii_audio_custom_css", "");
    
    if (!empty($custom_css)) {
        wp_register_style(
            'type-3-custom-css',
            false // No external file
        );
        wp_enqueue_style('type-3-custom-css');
        wp_add_inline_style('type-3-custom-css', $custom_css);
    }
}

function type_3_player($atts) {
    $attributes = '';

    // Check if preview mode is enabled and user is not logged in
    $preview_mode = get_option("type_iii_audio_preview_mode", "0");
    if ($preview_mode === "1" && !is_user_logged_in()) {
        return ''; // Return empty string for logged-out users when preview mode is enabled
    }

    // Check if header play buttons are enabled
    $header_play_buttons = get_option("type_iii_audio_header_play_buttons", "0");
    if ($header_play_buttons === "1") {
        $attributes .= 'header-play-buttons="true" ';
    }

    // Check if floating player is enabled
    $floating_player = get_option("type_iii_audio_floating_player", "0");
    if ($floating_player === "1") {
        $attributes .= 'sticky="true" ';
    }

    // Always register and enqueue custom CSS
    t3a_register_custom_css();

    $atts = shortcode_atts($default_atts, $atts);

    foreach($atts as $key => $value) {
        $attributes .= $key .'="' . $value . '" ';
    }

    wp_enqueue_script('type-3-player');
    wp_script_add_data('type-3-player', array('type', 'crossorigin'), array('module', ''));

    $html = '
        <type-3-player
        ' . $attributes . '
        >
        </type-3-player>
    ';

    // If we're not serving a hardcoded MP3 URL, then we should only show 
    // the player if the post is published.
    //
    // (Narrations cannot be created before the post is published, since the
    // TYPE III AUDIO crawler won't be able to access the post URL.)
    
    if (!t3a_is_hardcoded_mp3_url($atts)) {
        if (!t3a_is_post_published()) {
            $html = "<p style='padding: 10px; border: 1px dashed #ccc; border-radius: 4px; text-align: center;'>The TYPE III AUDIO player will display here when this post is published.</p>";
            return $html;
        }
    }

    return $html;
}

function t3a_is_hardcoded_mp3_url($atts) {
    return isset($atts['mp3-url']) && $atts['mp3-url'] !== '';
}

function t3a_is_post_published() {
    global $post;
    return isset($post) && is_object($post) && $post->post_status === 'publish';
}

add_shortcode('type_3_player', 'type_3_player'); 