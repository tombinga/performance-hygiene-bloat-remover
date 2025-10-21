<?php
/**
 * Plugin Name: Performance Hygiene – Bloat Remover
 * Description: A comprehensive, modular functionality plugin to safely remove common WordPress bloat and improve performance. All features are filterable.
 * Version: 1.0.0
 * Author: Tom Binga
 * License: GPL-2.0-or-later
 * Text Domain: performance-hygiene
 *
 * @package PerformanceHygiene\BloatRemover
 */

namespace PerformanceHygiene\BloatRemover;

defined('ABSPATH') || exit;

// Lightweight autoloader for plugin classes under src/
spl_autoload_register(function ($class) {
  $prefix = __NAMESPACE__ . '\\';
  if (strpos($class, $prefix) !== 0) {
    return;
  }
  $relative = substr($class, strlen($prefix));
  $path = plugin_dir_path(__FILE__) . 'src/' . str_replace('\\', '/', $relative) . '.php';
  if (file_exists($path)) {
    require_once $path;
  }
});

if (!class_exists(__NAMESPACE__ . '\\Plugin')):

  final class Plugin
  {

    /**
     * Singleton.
     * @var Plugin
     */
    private static $instance;
    /**
     * Default feature toggles (safe defaults).
     *
     * @return array
     */
    public static function default_features()
    {
      return \PerformanceHygiene\BloatRemover\Settings\Repository::default_features();
    }

    /**
     * Get instance.
     *
     * @return Plugin
     */
    public static function instance()
    {
      if (!isset(self::$instance)) {
        self::$instance = new self();
      }
      return self::$instance;
    }

    /**
     * Boot plugin.
     */
    private function __construct()
    {
      $settings = \PerformanceHygiene\BloatRemover\Settings\Repository::get();
      $features = \PerformanceHygiene\BloatRemover\Settings\Repository::feature_flags($settings);
      $features = apply_filters('phbr/features', $features, $settings);

      $manager = new \PerformanceHygiene\BloatRemover\Features\FeatureManager($settings, $features);
      $manager->register();

      if (is_admin()) {
        $admin = new \PerformanceHygiene\BloatRemover\Admin\SettingsPage();
        $admin->register();
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array('PerformanceHygiene\\BloatRemover\\Admin\\SettingsPage', 'plugin_action_links'));
        \PerformanceHygiene\BloatRemover\Updates::init();
      }

      add_filter('phbr/heartbeat_interval', array('PerformanceHygiene\\BloatRemover\\Settings\\Repository', 'heartbeat_interval'));
      add_filter('phbr/autosave_interval', array('PerformanceHygiene\\BloatRemover\\Settings\\Repository', 'autosave_interval'));
      add_filter('phbr/revisions_to_keep', array('PerformanceHygiene\\BloatRemover\\Settings\\Repository', 'revisions_to_keep'));
      add_filter('phbr/wc_extra_style_handles', array('PerformanceHygiene\\BloatRemover\\Settings\\Repository', 'wc_extra_style_handles'));
    }

    /* ---------------------------
     * Feature implementations moved to src/Features/*
     * --------------------------*/
  }

  // Bootstrap.
  Plugin::instance();

endif; // class_exists
