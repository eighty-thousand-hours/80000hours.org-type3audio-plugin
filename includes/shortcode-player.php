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

    // Always register and enqueue custom CSS
    t3a_register_custom_css();

    wp_enqueue_script('type-3-player');
    wp_script_add_data('type-3-player', array('type', 'crossorigin'), array('module', ''));

    // Add async attribute to <script> tag so that it's not blocking loading our
    // deferred scripts.
    // https://make.wordpress.org/core/2023/07/14/registering-scripts-with-async-and-defer-attributes-in-wordpress-6-3/
    wp_script_add_data(
        'type-3-player',
        'strategy',
        'async'
    );

    // If a post ID was passed, get post info from WordPress.
    if(!empty($post_id)):

        // Use post info unless specified in shortcode attributes.
        if(!$title): $title = get_the_title($post_id); endif;

        $thumb_id = get_post_thumbnail_id($post_id);
        $thumb_url_array = wp_get_attachment_image_src($thumb_id, 'medium', true);
        if(!$cover_image_url): $cover_image_url = $thumb_url_array[0]; endif;

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

    $html = '<div style="width: 100%; min-height: ' . $min_height . '; clear: both;" class="' . $class . '">';

    // Properties for the <type-3-player> element are documented here:
    // https://docs.type3.audio/#attribute-reference
    $html .= '
        <type-3-player '
            . ($url ? ('mp3-url="' . $url . '"') : '')
            . ($title ? ('title="' . $title . '"') : '') . '
            cover-image-url="' . $cover_image_url . '"
            background-color="' . $hex_background_color . '"
            listen-to-this-page="' . $compact . '"
            listen-to-this-page-text="' . $compact_text . '"
            link-to-timestamp="' . $link_timestamps .'"
            header-play-buttons="' . $header_play_buttons . '"
            sticky="' . $sticky .'"
            primary-color="#333"
            secondary-color="#aaa"
            accent-color="#2ebdd1"
            primary-font-family="\'museo-sans\',\'Helvetica Neue\',Helvetica,Arial,sans-serif"
            secondary-font-family="\'proxima-nova\',Arial,sans-serif"
            analytics="custom"
            t3a-logo="false"
            link-to-timestamp-selector=".type-3-player__replace-timestamps-with-links"
            feedback-button="false"
            custom-css="' . $custom_css . '"
        ></type-3-player>';

    // Add custom analytics handler.
    // Also add an event listener which ensures that, even on browsers that
    // support the rubber band effect, the player isn't visible below the footer.
    $html .= '<script type="text/javascript" data-cfasync="false">
        // Track cumulative listening time so we can fire a custom event at 6 minutes
        // NOTE: This tracker is shared globally across ALL audio players on the page.
        // If multiple players are present, listening to any of them adds to the total.
        // This measures total audio engagement per page, not per-player engagement.
        if (!window.t3aListeningTimeTracker) {
            window.t3aListeningTimeTracker = {
                totalSecondsListened: 0,
                hasFiredSixMinuteEvent: false
            };
        }
        if (!window.t3aAnalytics) {
            window.t3aAnalytics = function(eventType, event) {
            analytics.track(eventType, event);
            gtag("event", eventType, event);
            if (typeof plausible === "function") {
                plausible(eventType, {props: event});
            }
            if (eventType === "continued-listening") {
                window.t3aListeningTimeTracker.totalSecondsListened += 30;
                // Fire a one-time event when user has listened for 6 minutes (360 seconds)
                if (!window.t3aListeningTimeTracker.hasFiredSixMinuteEvent &&
                    window.t3aListeningTimeTracker.totalSecondsListened >= 360) {
                    // Set flag first to prevent any race conditions
                    window.t3aListeningTimeTracker.hasFiredSixMinuteEvent = true;
                    var sixMinuteEvent = {
                        ...event,
                        action: "Listened for 6 minutes",
                        totalSecondsListened: window.t3aListeningTimeTracker.totalSecondsListened
                    };
                    // Send to all tracking services
                    analytics.track("Listened for 6 minutes", sixMinuteEvent);
                    gtag("event", "Listened for 6 minutes", sixMinuteEvent);
                    if (typeof plausible === "function") {
                        plausible("Listened for 6 minutes", {props: sixMinuteEvent});
                    }
                    // Trigger custom event for key page engagement tracking
                    if (typeof window.eightyKAudioListened6Min === "function") {
                        window.eightyKAudioListened6Min();
                    }
                }
            }
            }
        }
        if (!window.t3aScrollListenerAdded) {
            window.addEventListener("scroll", function() {
                const players = document.querySelectorAll("type-3-player");
                const tocButton = document.querySelector(".sidebar-toc__open-button-wrap");
                const scrollTop = window.scrollY;
                const viewportHeight = window.innerHeight;
                const totalHeight = document.documentElement.scrollHeight;
                if (scrollTop + viewportHeight >= totalHeight) {
                    players.forEach(player => player.style.display = "none");
                    if (tocButton) {
                        tocButton.style.display = "none";
                    }
                } else {
                    players.forEach(player => player.style.display = "");
                    if (tocButton) {
                        tocButton.style.display = "";
                    }
                }
            });
            window.t3aScrollListenerAdded = true;
        }
    </script>';

    $html .= '<script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            const noPlayButtonTitles = ["Read more", "Read next", "Learn more"];
            document.querySelectorAll("h2, h3").forEach(function(element) {
                if (noPlayButtonTitles.includes(element.textContent.trim())) {
                    element.classList.add("no-heading-play-button");
                }
            });
        });
    </script>';

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
    return isset($atts['mp3-url']) && $atts['mp3-url'] !== '';
}

function t3a_is_post_published() {
    if (defined('WP_ENV') && WP_ENV !== 'production') {
        return false;
    }
    global $post;
    return isset($post) && is_object($post) && $post->post_status === 'publish';
}

add_shortcode('type_3_player', 'type_3_player'); 