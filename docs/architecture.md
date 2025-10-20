# Architecture Overview

This document describes the modular structure of the Performance Hygiene – Bloat Remover plugin.

## Goals
- Separation of concerns (settings storage, admin UI, features).
- Maintain backward compatibility with existing filters.
- Make features easy to extend or disable.

## Directory Structure
- `performance-hygiene-bloat-remover.php` – Bootstrap (autoloader, Plugin singleton, feature wiring)
- `src/Settings/Repository.php` – Option storage, defaults, sanitization, typed getters, filter-facing helpers
- `src/Admin/SettingsPage.php` – Admin screen rendering and form handlers (save/reset/import/export)
- `src/Features/` – Feature classes (planned):
  - `FeatureInterface.php` – Contract (register hooks via `register()`)
  - `FeatureManager.php` – Instantiates and registers active features
  - Individual features (one class per feature)

## Autoloading
- Lightweight PSR-4 style autoloader loads classes in the `PerformanceHygiene\BloatRemover\` namespace from `src/`.

## Settings Model
- Option name: `phbr_settings`
- Repository merges saved settings over defaults, sanitizes, and exposes:
  - Feature flags map (booleans)
  - Parameter getters (heartbeat interval, autosave interval, revisions, Woo handles)
- Repository bridges to public filters:
  - `phbr/heartbeat_interval`
  - `phbr/autosave_interval`
  - `phbr/revisions_to_keep`
  - `phbr/wc_extra_style_handles`

## Admin UI
- Menu: Settings → Performance Hygiene
- Capability: `manage_options`
- Actions:
  - Save changes
  - Reset to defaults
  - Export (JSON)
  - Import (JSON)

## Feature Wiring (current state)
- The main `Plugin` class still contains feature methods (e.g., `head_cleanup()`, `disable_emojis()`).
- `Repository` provides the active feature flags and parameters used during boot.
- Planned: Extract each feature into its own class under `src/Features/`, registered by `FeatureManager`.

## Backward Compatibility
- Existing filters remain intact:
  - `phbr/features` for last-mile feature map adjustment
  - Parameter filters above for intervals and Woo handles
- Feature behavior unchanged; only code organization improves.

## Migration Plan
- Phase 1 (Done): Autoloader, Repository, SettingsPage, main plugin refactor
- Phase 2 (Next): Create `FeatureInterface`, `FeatureManager`, and extract features into individual classes
- Phase 3: Wire feature classes in `Plugin` and remove corresponding methods
- Phase 4: Regression test and finalize

## Notes
- WooCommerce-specific logic uses guards for page checks and respects settings for dequeue toggles and extra handles.
- Multisite remains per-site scope for now.
