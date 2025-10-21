# Performance Hygiene – Bloat Remover

A comprehensive, modular functionality plugin to safely remove common WordPress bloat and improve performance. All features are toggleable in the admin and filterable for developers.

## Highlights
- **Modular features**: enable only what you need
- **Safe defaults**: sensible setup out of the box
- **Admin UI**: Settings → Performance Hygiene
- **Import/Export/Reset**: manage settings easily
- **Developer-friendly**: stable filters and clean architecture

## Features
- **Head cleanup**: remove RSD, WLW, generator, shortlink, REST head links, adjacent posts links
- **Disable emojis**: remove emoji scripts/styles (front, admin, emails, feeds)
- **Disable embeds**: disable oEmbed discovery and deregister `wp-embed` on front
- **REST head link**: remove `<link rel="https://api.w.org/" ...>` without disabling the REST API
- **XML-RPC**: remove pingbacks only (safe) or disable XML-RPC entirely (risky)
- **Heartbeat tuning**: disable front-end heartbeat; slow admin heartbeat; configurable interval
- **Autosave interval**: raise autosave interval; configurable seconds
- **Limit revisions**: reduce post revisions to a configurable count
- **Dashicons for guests**: remove dashicons on front-end for non-logged-in users
- **Duotone filters**: remove Global Styles duotone SVG filters output
- **Block patterns**: disable core block patterns and editor block directory assets
- **Core block CSS**: optionally dequeue core block CSS on front-end (risky; may affect styling)
- **Disable comments**: robust, sitewide disabling (front + admin)
- **Disable feeds**: return 410 for feeds and remove head feed links
- **WooCommerce optimizations** (conditional):
  - Disable cart fragments on non-cart/checkout/account pages
  - Optionally dequeue Woo styles on non-Woo pages
  - Add extra style handles to dequeue

## Requirements
- **WordPress**: 6.x or later (recommended)
- **PHP**: 7.4+ (typed properties used)
- **WooCommerce**: optional; Woo features are only active if WooCommerce is active

## Installation
1. Copy the plugin folder `performance-hygiene-bloat-remover/` into `wp-content/plugins/`.
2. Activate “Performance Hygiene – Bloat Remover” in Plugins.
3. Go to **Settings → Performance Hygiene** to configure.

## Updates (Plugin Update Checker)
- Place the library at `wp-content/plugins/performance-hygiene-bloat-remover/vendor/plugin-update-checker/`.
  - You can copy it from `wp-content/plugins/singleplatform-menu/vendor/plugin-update-checker/`.
- Enable updates by providing a repo URL via constant or filter:

```php
// In wp-config.php (or a must-use plugin):
define('PHBR_UPDATE_REPO', 'https://github.com/your-org/performance-hygiene-bloat-remover/');
// Optional:
define('PHBR_UPDATE_BRANCH', 'main'); // default 'main'
define('PHBR_UPDATE_TOKEN', 'ghp_xxx'); // for private repos
```

Or via filter:

```php
add_filter('phbr/update_repo', function ($repo) {
    return 'https://github.com/your-org/performance-hygiene-bloat-remover/';
});
```

Notes:
- Update checks only run in admin.
- Release assets are preferred when available.
- If no repo is configured or the library is missing, the updater is skipped silently.

## Settings
- Stored as a single option: `phbr_settings` (autoloaded)
- UI sections:
  - General feature toggles (head cleanup, emojis, embeds, REST head link, dashicons, duotone, block patterns, core block CSS, comments, feeds)
  - XML-RPC (pingbacks only, or full disable)
  - Heartbeat & Autosave (with numeric inputs)
  - Revisions (numeric input)
  - WooCommerce (conditional; optional style dequeue and extra handles)
- Tools:
  - Save changes
  - Reset to defaults
  - Export settings (JSON)
  - Import settings (JSON)

## Defaults
Defaults mirror safe behavior. Key defaults:
- `head_cleanup`, `emojis`, `embeds`, `rest_head`, `heartbeat`, `autosave_interval`, `limit_revisions`, `dashicons_front`, `duotone`, `block_patterns` are enabled
- `block_css`, `disable_comments`, `feeds`, `wc_optimizations` are disabled
- `heartbeat_interval = 60`, `autosave_interval_seconds = 120`, `revisions_to_keep = 5`

## Developer Hooks
- **`phbr/features`**: filter final feature flags before registration
- **`phbr/heartbeat_interval`**: override heartbeat interval (seconds)
- **`phbr/autosave_interval`**: override autosave interval (seconds)
- **`phbr/revisions_to_keep`**: override number of revisions to keep
- **`phbr/wc_extra_style_handles`**: add more Woo style handles to dequeue

These filters are applied even with the admin UI present, allowing code-based overrides where needed.

## Architecture
- **Bootstrap**: `performance-hygiene-bloat-remover.php`
  - Lightweight autoloader for `PerformanceHygiene\\BloatRemover\\...` under `src/`
  - Loads settings and registers features via `FeatureManager`
  - Registers admin settings page
- **Settings**: `src/Settings/Repository.php`
  - Defaults, sanitize, get/save/reset; exposes filter-facing getters
- **Admin**: `src/Admin/SettingsPage.php`
  - Renders settings UI; handles save/reset/import/export
- **Features**: `src/Features/`
  - `FeatureInterface`, `FeatureManager`
  - One class per feature, each attaches its own hooks in `register()`

## Multisite
- Per-site settings page in each site’s admin. (Network-level defaults/locks can be added later.)

## Safety & Notes
- Options are sanitized and clamped to safe ranges
- Risky toggles are clearly labeled in the UI (e.g., core block CSS, disabling XML-RPC fully, disabling comments/feeds)
- WooCommerce options are only effective if WooCommerce is active

## License
GPL-2.0-or-later

## Support
File issues or requests in your project environment. For custom needs, extend via the provided filters and feature architecture.
