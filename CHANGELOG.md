# Changelog

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](https://semver.org/) and follows a simplified form of the [Keep a Changelog](https://keepachangelog.com/) format.

## [1.0.0] - 2025-10-21

### Added
- Initial release of Performance Hygiene – Bloat Remover.
- Modular feature architecture under `src/Features/` with `FeatureManager`.
- Settings repository under `src/Settings/Repository.php` with safe defaults.
- Admin settings page (`src/Admin/SettingsPage.php`) with Save, Reset, Import, Export.
- Filters for developers: `phbr/features`, `phbr/heartbeat_interval`, `phbr/autosave_interval`, `phbr/revisions_to_keep`, `phbr/wc_extra_style_handles`.
- WooCommerce-specific optimizations (conditional when WooCommerce is active).
- Plugin Update Checker integration (`src/Updates.php`) with baked-in public GitHub repo.
- Documentation: `README.md` and `docs/architecture.md`.

[1.0.0]: https://github.com/tombinga/performance-hygiene-bloat-remover/releases/tag/v1.0.0
