# Performance Hygiene – Bloat Remover

## Purpose
Define an Admin Settings page that allows site owners to enable/disable and configure the plugin’s performance features without code. The page exposes safe defaults, warns about risky changes, and persists settings in a single option.

## Location & Access
- **Menu**: Settings → Performance Hygiene
- **Capability**: `manage_options`
- **Visibility**: Always visible when the plugin is active

## Storage & Data Model
- **Option name**: `phbr_settings`
- **Structure**: Single associative array with keys for feature toggles and parameters
- **Autoload**: Yes (array is small)
- **Defaults**: Mirror `Plugin::default_features()` for toggles and sensible defaults for parameters
- **Merging**: On runtime, settings should be merged over defaults so new features pick up defaults automatically

## Sections & Fields

### 1) General
- **Head Cleanup** (`head_cleanup`): Toggle
  - Removes various `wp_head` cruft (RSD, WLW, generator, shortlink, REST links). Safe.
- **Disable Emojis** (`emojis`): Toggle
  - Removes emoji scripts/styles from front/admin/emails/feeds. Safe.
- **Disable Embeds** (`embeds`): Toggle
  - Disables oEmbed discovery and deregisters `wp-embed` on front. Safe.
- **Remove REST Head Link** (`rest_head`): Toggle
  - Removes REST API link tags from head/headers; API still functional. Safe.
- **Disable Dashicons for Guests** (`dashicons_front`): Toggle
  - Deregisters `dashicons` on front-end if user not logged in. Safe.
- **Disable Duotone SVG Filters** (`duotone`): Toggle
  - Prevents duotone/global styles SVG filters output. Safe.
- **Disable Core Block Patterns** (`block_patterns`): Toggle
  - Removes core block patterns and the editor block directory assets. Safe.
- **Disable Core Block CSS on Front-End** (`block_css`): Toggle
  - Dequeues core block styles on front. Risky; may break theme styling.
  - UI must show a prominent warning before enabling.
- **Disable Comments Sitewide** (`disable_comments`): Toggle
  - Disables comments and related admin UI globally. Potentially disruptive.
  - UI must show a warning and consequences.
- **Disable Default Feeds** (`feeds`): Toggle
  - Disables site feeds and removes head feed links; returns HTTP 410 for feed endpoints.
  - UI must show a warning (may affect SEO/workflows).

### 2) XML-RPC
- **Disable XML-RPC Pingbacks Only** (`xmlrpc_pingback`): Toggle
  - Removes pingback methods while keeping XML-RPC available. Safe default.
- **Disable XML-RPC Entirely** (`xmlrpc_full`): Toggle
  - Disables XML-RPC completely. Risky; breaks Jetpack/mobile apps/remote publishing.
  - When enabled, it supersedes `xmlrpc_pingback`.
  - UI must show a warning before enabling.
- **Mutual Behavior**: If `xmlrpc_full` is enabled, `xmlrpc_pingback` becomes irrelevant. The UI should either disable the pingback toggle or show it as overridden.

### 3) Heartbeat & Autosave
- **Tame Heartbeat** (`heartbeat`): Toggle
  - Front-end heartbeat disabled; admin heartbeat slowed but preserved on post screens.
- **Heartbeat Interval (seconds)** (`heartbeat_interval`): Integer input, only active when heartbeat toggle is on
  - Range: 15–120 seconds
  - Default: 60
  - Applied in admin via Heartbeat settings filter
- **Raise Autosave Interval** (`autosave_interval`): Toggle
- **Autosave Interval (seconds)** (`autosave_interval_seconds`): Integer input
  - Range: 10–3600
  - Default: 120
- **Limit Post Revisions** (`limit_revisions`): Toggle
- **Revisions To Keep** (`revisions_to_keep`): Integer input
  - Range: 0–100 (0 means keep none; use cautiously)
  - Default: 5

### 4) WooCommerce (conditional)
- **WooCommerce Optimizations** (`wc_optimizations`): Toggle; only relevant if WooCommerce is active
  - Disables `wc-cart-fragments` on non-cart/checkout/account pages
- **Dequeue WooCommerce Styles on Non-Woo Pages** (`wc_dequeue_styles`): Toggle, shown only if `wc_optimizations` is enabled
  - Dequeues common Woo styles when not on Woo pages
- **Additional Woo Style Handles** (`wc_extra_style_handles`): Text input (comma-separated list)
  - Allows specifying extra style handles to dequeue (advanced)

## Validation & Sanitization
- **Toggles**: Cast to boolean
- **Integers**: Cast and clamp to allowed ranges
  - `heartbeat_interval`: 15–120
  - `autosave_interval_seconds`: 10–3600
  - `revisions_to_keep`: 0–100
- **Lists**: `wc_extra_style_handles` parsed as comma-separated slugs; trim, lowercase, allow `[a-z0-9-_.]` only; drop invalid tokens
- **Dependencies**:
  - If `xmlrpc_full` is true, treat `xmlrpc_pingback` as false or visually overridden
  - `wc_dequeue_styles` and `wc_extra_style_handles` only effective if `wc_optimizations` is true and WooCommerce is active
- **Security**: Nonce, capability checks on save; reject if invalid

## Defaults (mirroring current behavior)
- `head_cleanup`: true
- `emojis`: true
- `embeds`: true
- `rest_head`: true
- `xmlrpc_pingback`: true
- `xmlrpc_full`: false
- `heartbeat`: true
- `heartbeat_interval`: 60
- `autosave_interval`: true
- `autosave_interval_seconds`: 120
- `limit_revisions`: true
- `revisions_to_keep`: 5
- `dashicons_front`: true
- `duotone`: true
- `block_patterns`: true
- `block_css`: false
- `disable_comments`: false
- `feeds`: false
- `wc_optimizations`: false
- `wc_dequeue_styles`: false
- `wc_extra_style_handles`: "" (empty)

## Runtime Application
- On plugin boot, the saved `phbr_settings` are merged over defaults to create the active features array.
- Feature toggles map to existing hooks in `performance-hygiene-bloat-remover.php`.
- Parameterized features feed the existing filter-driven internals:
  - Heartbeat: use setting to determine interval
  - Autosave: use setting for interval
  - Revisions: use setting for count
  - Woo extra handles: use setting to add to dequeue list
- Filters remain available to developers; explicit settings should take precedence unless a filter explicitly overrides.

## User Experience
- **Layout**: Tabbed or sectioned form with clear labels and short descriptions under each field
- **Warnings**: Prominent inline warnings for risky options (`block_css`, `xmlrpc_full`, `disable_comments`, `feeds`)
- **Contextual Visibility**: Hide Woo-specific options unless WooCommerce is active
- **Actions**:
  - Save Changes (primary)
  - Reset to Defaults (secondary, with confirmation)
  - Export Settings (JSON download)
  - Import Settings (JSON upload, with confirmation)
- **Notices**: Success/error admin notices after actions
- **Help**: Help tab with overview and links to documentation; short explanations inline under fields

## Multisite Behavior
- Initial scope: per-site settings page under each site’s admin
- Future/optional: Network Admin page to define network defaults and optionally lock certain settings; per-site settings merge over network defaults unless locked

## Internationalization
- All user-facing strings translatable under `performance-hygiene` text domain

## Accessibility
- Keyboard navigable
- Proper field associations (labels, descriptions)
- Sufficient contrast and clear focus states

## Non-Functional Requirements
- No noticeable performance impact on admin page render
- Avoid enqueueing unnecessary assets on non-settings pages
- Defensive coding: do not error if third-party plugins/themes alter hooks

## Out of Scope (for initial implementation)
- Per-post-type granularity for comments disable
- Per-role targeting for dashicons or heartbeat
- Fine-grained control over REST API beyond head link removal

## Testing Criteria
- Verify toggles correctly enable/disable existing behaviors
- Validate numeric bounds and sanitization
- Confirm risky options show warnings
- Confirm Woo options appear only when WooCommerce is active
- Confirm import/export and reset behaviors
- Smoke test on multisite (site-level)
