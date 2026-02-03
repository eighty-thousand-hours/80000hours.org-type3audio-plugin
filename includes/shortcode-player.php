<?php
/**
 * Shortcode player functionality for TYPE III AUDIO
 */

if (!defined('ABSPATH')) {
    exit;
}

function t3a_enqueue_scripts() {
    wp_register_script('type-3-player', 'https://embed.type3.audio/player.js', array(), '1.0.0', true);
    wp_register_style('type-3-player-styles', T3A_PLUGIN_URL . '/assets/css/player.css', array(), T3A_VERSION);
}

add_action('wp_enqueue_scripts', 't3a_enqueue_scripts');

/**
 * Get the podcast subscribe URLs for the 80,000 Hours narrations feed.
 *
 * @return array<string,string> Array of subscribe URLs keyed by platform.
 */
function t3a_get_podcast_subscribe_urls() {
    return array(
        'apple' => 'https://podcasts.apple.com/us/podcast/80-000-hours-narrations/id1860831515',
        'spotify' => 'https://open.spotify.com/show/49no4dH0N52R2PCFaw7L8Z',
        'podcast-addict' => 'https://podcastaddict.com/podcast/80000-hours-narrations/4649902',
        'rss' => 'https://feeds.type3.audio/80000hours.rss',
    );
}

/**
 * Check if a post is published to the podcast feed via Type3.audio API (with caching).
 *
 * @param string $source_url The URL of the post to check.
 * @return bool True if published to podcast, false otherwise.
 */
function t3a_is_published_to_podcast($source_url) {
    // Create a cache key from the URL path
    $key_prefix = 'pubbed_to_narrations_podcast_';
    $key_suffix = wp_parse_url($source_url, PHP_URL_PATH) ?: '';
    $key_suffix = str_replace('/', '_', $key_suffix);
    $key_suffix = trim($key_suffix, '_');

    // WordPress transient keys max out at 172 chars; fall back to MD5 hash if too long
    if (strlen($key_prefix . $key_suffix) > 172) {
        $key_suffix = md5($source_url);
    }
    $cache_key = $key_prefix . $key_suffix;

    // Try to get cached data (we store 'yes'/'no' strings since get_transient returns false on miss)
    $cached_data = get_transient($cache_key);
    if ($cached_data !== false) {
        return $cached_data === 'yes';
    }

    // Determine the API base URL (same logic as in manage-narration-metabox.php)
    $current_host = (string) wp_parse_url(home_url(), PHP_URL_HOST);
    $status_base_url = ($current_host === 'wordpress.local')
        ? 'http://localhost:3003'
        : 'https://api.type3.audio';

    $status_url = $status_base_url . '/narration/status?source_url=' . rawurlencode($source_url);

    // Fetch the narration status from the API
    $response = wp_remote_get($status_url, array(
        'timeout' => 5,
        'headers' => array(
            'Accept' => 'application/json',
        ),
    ));

    // Handle errors - cache failures for 5 minutes to avoid hammering the API
    if (is_wp_error($response)) {
        set_transient($cache_key, 'no', 5 * MINUTE_IN_SECONDS);
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code !== 200) {
        set_transient($cache_key, 'no', 5 * MINUTE_IN_SECONDS);
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!is_array($data)) {
        set_transient($cache_key, 'no', 5 * MINUTE_IN_SECONDS);
        return false;
    }

    // Extract just the boolean we need
    $is_published = !empty($data['published_to_podcast']);

    // Cache as 'yes'/'no' string for 12 hours (can't use boolean since get_transient returns false on miss)
    set_transient($cache_key, $is_published ? 'yes' : 'no', 12 * HOUR_IN_SECONDS);

    return $is_published;
}

/**
 * Check if the current post should show podcast subscribe buttons.
 *
 * @param int|null $post_id The post ID to check. If null, uses the current post.
 * @return bool True if subscribe buttons should be shown, false otherwise.
 */
function t3a_should_show_podcast_subscribe($post_id = null) {
    if ($post_id) {
        $the_post = get_post($post_id);
    } else {
        global $post;
        $the_post = $post;
    }

    if (!$the_post) {
        return false;
    }

    // Check if the post type is eligible for narrations
    $eligible_post_types = array('skill_set', 'ai_career_guide_page', 'career_profile', 'problem_profile', 'article');
    if (!in_array($the_post->post_type, $eligible_post_types, true)) {
        return false;
    }

    // Get the permalink
    $permalink = get_permalink($the_post);
    if (!$permalink) {
        return false;
    }

    // Normalize 80000hours.org subdomains to production domain
    $host = wp_parse_url($permalink, PHP_URL_HOST);
    if ($host && strpos($host, '80000hours.org') !== false && $host !== '80000hours.org') {
        $permalink = str_replace($host, '80000hours.org', $permalink);
    }

    // Check if the narration is published to the podcast
    return t3a_is_published_to_podcast($permalink);
}

function type_3_player($atts) {
    // 80,000 Hours brand-specific player styling
    $t3a_primary_color = '#333';
    $t3a_secondary_color = '#aaa';
    $t3a_accent_color = '#2ebdd1';
    $t3a_primary_font = "'museo-sans','Helvetica Neue',Helvetica,Arial,sans-serif";
    $t3a_secondary_font = "'proxima-nova',Arial,sans-serif";

    // Note: All player CSS is now in assets/css/player.css
    // (Previously was in theme LESS file, but moved to plugin for easier fork maintenance)

    // Define default attributes
    $default_atts = array(
        'url' => '',
        'class' => 'margin-top-smaller margin-bottom-smaller',
        'background_color' => 'gray',
        'post_id' => '',
        'link_timestamps' => 'true',
        'header_play_buttons' => 'true',
        'sticky' => 'true',
        'compact' => false,
        'compact_text' => null,
        'title' => '',
        'cover_image_url' => '',
        'custom_css' => '',
    );

    // Extract shortcode attributes with defaults
    extract(shortcode_atts($default_atts, $atts));

    wp_enqueue_script('type-3-player');
    wp_script_add_data('type-3-player', array('type', 'crossorigin'), array('module', ''));

    // Enqueue player styles
    wp_enqueue_style('type-3-player-styles');

    // Add async attribute to <script> tag so that it's not blocking loading our
    // deferred scripts.
    // https://make.wordpress.org/core/2023/07/14/registering-scripts-with-async-and-defer-attributes-in-wordpress-6-3/
    wp_script_add_data(
        'type-3-player',
        'strategy',
        'async'
    );

    // Enqueue custom player enhancements (analytics, scroll behavior, heading filters)
    wp_enqueue_script(
        'type-3-player-enhancements',
        T3A_PLUGIN_URL . '/assets/js/player-enhancements.js',
        array(), // No dependencies
        T3A_VERSION,
        true // Load in footer
    );

    // If a post ID was passed, get post info from WordPress.
    if(!empty($post_id)):

        // Use post info unless specified in shortcode attributes.
        if(!$title): $title = get_the_title($post_id); endif;

        $thumb_id = get_post_thumbnail_id($post_id);
        $thumb_url_array = wp_get_attachment_image_src($thumb_id, 'medium', true);
        if(!$cover_image_url && is_array($thumb_url_array)): $cover_image_url = $thumb_url_array[0]; endif;

    endif;

    switch($background_color) {
        case 'white':
            $hex_background_color = '#ffffff';
            break;
        case 'gray':
        default:
            $hex_background_color = '#f1f1f1';
    }

    if ($compact && !$compact_text) {
        $compact_text = 'Listen to this article'; // Default text when none provided
    } elseif ($compact_text) {
        $compact = 'true'; // If compact text is given, assume they want the compact version
    }

    if ($compact === 'true'):
        $min_height = '60px';
    else:
        $min_height = '75px';
    endif;

    $html = '<div style="width: 100%; min-height: ' . esc_attr($min_height) . '; clear: both;" class="' . esc_attr($class) . '">';

    // Check if we should show podcast subscribe buttons. The t3a_should_show_podcast_subscribe() function
    // will use the global post if $post_id is not provided, so we can call it directly.
    $show_subscribe = false;
    $subscribe_urls = array();

    if (t3a_should_show_podcast_subscribe($post_id)) {
        $show_subscribe = true;
        $subscribe_urls = t3a_get_podcast_subscribe_urls();
    }

    // Properties for the <type-3-player> element are documented here:
    // https://docs.type3.audio/#attribute-reference
    $html .= '
        <type-3-player '
            . ($url ? ('mp3-url="' . esc_attr($url) . '" ') : '')
            . ($title ? ('title="' . esc_attr($title) . '" ') : '') . '
            cover-image-url="' . esc_attr($cover_image_url) . '"
            background-color="' . esc_attr($hex_background_color) . '"
            listen-to-this-page="' . esc_attr($compact) . '"
            listen-to-this-page-text="' . esc_attr($compact_text) . '"
            link-to-timestamp="' . esc_attr($link_timestamps) .'"
            header-play-buttons="' . esc_attr($header_play_buttons) . '"
            sticky="' . esc_attr($sticky) .'"
            primary-color="' . esc_attr($t3a_primary_color) . '"
            secondary-color="' . esc_attr($t3a_secondary_color) . '"
            accent-color="' . esc_attr($t3a_accent_color) . '"
            primary-font-family="' . esc_attr($t3a_primary_font) . '"
            secondary-font-family="' . esc_attr($t3a_secondary_font) . '"
            analytics="custom"
            t3a-logo="false"
            link-to-timestamp-selector=".type-3-player__replace-timestamps-with-links"
            feedback-button="false"
            custom-css="' . esc_attr($custom_css) . '"';

    // Add podcast subscribe URLs if applicable
    if ($show_subscribe && !empty($subscribe_urls)) {
        foreach ($subscribe_urls as $platform => $subscribe_url) {
            $html .= '
            subscribe-url--' . esc_attr($platform) . '="' . esc_attr($subscribe_url) . '"';
        }
    }

    $html .= '
        ></type-3-player>';

    $html .= '</div>';

    // If we're not serving a hardcoded MP3 URL, then we should only show
    // the player if the post is published.
    //
    // (Narrations cannot be created before the post is published, since the
    // TYPE III AUDIO crawler won't be able to access the post URL.)

    if (!t3a_is_hardcoded_mp3_url($atts)) {
        if (!t3a_is_post_published()) {
            $html = do_shortcode("[well margin='!tw--my-2']The audio player will display here when this post is published on the live site.[/well]");
            return $html;
        }
    }

    return $html;
}

function t3a_is_hardcoded_mp3_url($atts) {
    return isset($atts['url']) && $atts['url'] !== '';
}

function t3a_is_post_published() {
    if (defined('WP_ENV') && WP_ENV !== 'production') {
        return false;
    }
    global $post;
    return isset($post) && is_object($post) && $post->post_status === 'publish';
}

add_shortcode('type3_audio_player', 'type_3_player'); 