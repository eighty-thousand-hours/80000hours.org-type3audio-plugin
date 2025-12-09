/**
 * TYPE III AUDIO Player Enhancements
 * Custom analytics tracking, scroll behavior, and heading filters for 80,000 Hours
 */

(function() {
    'use strict';

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

    // Custom analytics handler for TYPE III AUDIO player
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
        };
    }

    // Add scroll listener to hide player at bottom of page
    // (prevents player from showing below footer on browsers with rubber band effect)
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

    // Prevent heading play buttons on certain headings
    document.addEventListener("DOMContentLoaded", function() {
        const noPlayButtonTitles = ["Read more", "Read next", "Learn more"];
        document.querySelectorAll("h2, h3").forEach(function(element) {
            if (noPlayButtonTitles.includes(element.textContent.trim())) {
                element.classList.add("no-heading-play-button");
            }
        });
    });
})();
