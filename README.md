# TYPE III AUDIO Plugin (80,000 Hours Fork)

This is a **heavily patched fork** of the [TYPE III AUDIO WordPress plugin](https://type3.audio) with custom modifications for 80,000 Hours.

## ⚠️ Important: This Plugin is Patched

This fork includes significant custom patches. When updating from upstream, carefully review changes to preserve all customizations. See `AGENTS.md` for detailed development guidelines.

## Getting Started

### 1. Clone the Repository

```bash
git clone <repository-url>
cd 80000hours.org-type3audio-plugin
```

### 2. Install Git Hooks

Install the pre-commit hook that enforces version bumping for cache busting:

```bash
bash hooks/install.sh
```

This hook ensures you never forget to bump the plugin version when modifying CSS or JavaScript files. Without version bumps, browsers will serve stale cached assets to users.

### 3. Copy to WordPress

Use the included script to copy the plugin to the WordPress repo:

```bash
sh copy-to-wp.sh
```

Then deploy that change to the WordPress site.

## Versioning

- **Asset files** (`assets/css/*.css`, `assets/js/*.js`) require bumping `T3A_80K_ASSET_REV` in `type-3-audio.php` after any modifications
- **Dual version system:**
  - `T3A_VERSION`: Upstream plugin version - **only bump when syncing from upstream**
  - `T3A_80K_ASSET_REV`: 80k asset revision - **bump this for all CSS/JS changes** (1 → 2 → 3...)
- **Why:** Cache busting uses `T3A_VERSION . '.' . T3A_80K_ASSET_REV` (e.g., `1.7.1`)

## Upstream Repository

Original plugin: [TYPE III AUDIO on WordPress.org](https://wordpress.org/plugins/type-3-audio/)