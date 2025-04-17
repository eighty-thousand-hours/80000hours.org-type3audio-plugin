<?php
/**
 * Regeneration functionality for TYPE III AUDIO
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sends a regeneration request to the TYPE III AUDIO API when a post is saved
 *
 * @param int $post_ID The post ID
 * @param WP_Post $post The post object
 * @param bool $update Whether this is an existing post being updated
 * @return void
 */
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

// Hook for post regeneration
add_action('save_post', 't3a_send_regenerate_request', 10, 3); 