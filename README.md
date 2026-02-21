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

### 2. Copy to WordPress

Use the included script to copy the plugin to the WordPress repo:

```bash
sh copy-to-wp.sh
```

Then deploy that change to the WordPress site.

## Versioning

- `T3A_VERSION`: Tracks the upstream Type 3 Audio plugin version - **only bump when syncing from upstream**
- All CSS and JavaScript assets are injected inline (no cache busting needed)

## Upstream Repository

Original plugin: [TYPE III AUDIO on WordPress.org](https://wordpress.org/plugins/type-3-audio/)