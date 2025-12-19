# Repository Guidelines

**New to this repository?** Start by reading [README.md](./README.md) for setup instructions, including the required Git hook installation.

## Project Structure & Module Organization
- `type-3-audio.php` is the WordPress plugin bootstrap; it wires core hooks and pulls in the modules under `includes/`.
- `includes/` groups feature-specific files: `admin-settings.php` renders the settings page, `block-editor.php` integrates the block UI, `regeneration.php` handles audio regeneration requests, and `shortcode-player.php` exposes the front-end player.
- `build` is a Bash helper that zips the plugin excluding `.git`; it writes `type-3-audio.zip`, which should be treated as a build artifact and regenerated rather than edited.
- `deploy` runs the build script and pushes the current `main` branch; use it from a clean tree to publish.

## Build, Test, and Development Commands
- `bash build` — packages the plugin into `type-3-audio.zip` for release or manual installation.
- `sh deploy` — rebuilds the archive and pushes `main`; confirm tests and version bumps first.
- `php -l type-3-audio.php includes/*.php` — quick syntax lint before committing.
- `wp plugin deactivate type-3-audio && wp plugin activate type-3-audio` — reloads the plugin on a local wp-env/Local install after code changes.
- `bash hooks/install.sh` — installs Git hooks (run once after cloning the repo).

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
- Before running `sh deploy`, bump the plugin header version in `type-3-audio.php` and any readme changelog entries.
- Validate the generated `type-3-audio.zip` by installing it on a staging WordPress site; smoke test settings, regeneration, and playback before marking the release complete.

### ⚠️ CRITICAL: Version Bumping for Cache Busting

**ALWAYS bump the asset revision when modifying these files:**
- `assets/css/player.css`
- `assets/js/player-enhancements.js`
- `assets/js/manage-narration.js`

**Why:** These assets are enqueued with `T3A_VERSION . '.' . T3A_80K_ASSET_REV` as the cache-busting query parameter. Without a version bump, browsers will serve stale cached files even after deployment.

**Dual Version System:**
- `T3A_VERSION` (e.g., `1.7`) - Tracks the upstream Type 3 Audio plugin version. **Only bump when syncing from upstream.**
- `T3A_80K_ASSET_REV` (e.g., `1`, `2`, `3`...) - 80k-specific asset revision. **Bump this for all CSS/JS changes.**

**How to bump the asset revision:**
1. Open `type-3-audio.php`
2. Update the `T3A_80K_ASSET_REV` constant definition → increment the number (`1` → `2` → `3`...)
3. **Do NOT** bump `T3A_VERSION` or the plugin header version unless syncing from upstream

### Git Hook Protection

A **pre-commit hook** is included to automatically enforce version bumping:

- **Installation:** Run `bash hooks/install.sh` once after cloning the repo
- **What it does:** Blocks commits that modify ANY `.css` or `.js` files in `assets/` without bumping `T3A_80K_ASSET_REV`
- **Bypass:** Use `git commit --no-verify` if you need to skip the check (not recommended)
- **Maintenance:** The hook is stored in `hooks/pre-commit` (version controlled) and copied to `.git/hooks/` during installation