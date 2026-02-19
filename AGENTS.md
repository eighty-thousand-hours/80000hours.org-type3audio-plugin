# Repository Guidelines

**New to this repository?** Start by reading [README.md](./README.md) for setup instructions, including the required Git hook installation.

## Project Structure & Module Organization
- `type-3-audio.php` is the WordPress plugin bootstrap; it wires core hooks and pulls in the modules under `includes/`.
- `includes/` groups feature-specific files: `admin-settings.php` renders the settings page, `regeneration.php` handles audio regeneration requests, and `shortcode-player.php` exposes the front-end player.
- `copy-to-wp.sh` deploys the plugin to the local WordPress installation for testing.

## Build, Test, and Development Commands
- `bash copy-to-wp.sh` — deploys the plugin to local WordPress for testing.
- `php -l type-3-audio.php includes/*.php` — quick syntax lint before committing.

## Coding Style & Naming Conventions
- Follow 4-space indentation and PSR-12-aligned brace placement already used in `includes/*.php`.
- Functions and hooks use snake_case (`type_iii_audio_*`), matching the plugin’s namespace; keep new identifiers consistent.
- Escape and sanitize data via WordPress helpers (`esc_html`, `sanitize_text_field`) when touching templates or option values.

## Testing Guidelines
- There is no automated test suite yet; run manual checks on a WordPress 6.x site with the plugin activated.
- After changes, regenerate audio via the admin tools and verify playback for posts embedding the shortcode and block variants.
- Capture console output and PHP error logs when exercising regeneration flows; attach findings to the PR.

## Commit & Pull Request Guidelines
- Use conventional commit prefixes observed in history (`feat:`, `fix:`, `refactor:`, `tweak:`) followed by a short imperative summary.
- Keep commits scoped to a single concern (e.g., “fix: guard regeneration when player disabled”), and reference issue IDs if applicable.
- PRs should include: overview of the change, manual test steps/results, screenshots or screen recordings for UI updates, and notes on deployment impact.

## Release & Deployment
- Deploy to local WordPress using `bash copy-to-wp.sh` for testing.
- After testing locally, commit changes and deploy the WordPress repo to staging/production.
- The plugin is maintained in this fork repo and deployed via the WordPress site repo.

### Asset Management

All CSS and JavaScript assets are **injected inline** when needed:
- `assets/css/player.css` - Injected when the `[type3_audio_player]` shortcode is used
- `assets/js/player-enhancements.js` - Injected with the player (analytics, scroll behavior, heading filters)
- `assets/js/manage-narration.js` - Injected in admin post editor for eligible post types

**No cache busting needed:** Inline assets are always fresh, eliminating stale cache issues.