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
      return array(
        // Head cleanup (safe).
        'head_cleanup' => true,
        // Disable emojis (front + admin + emails + feeds).
        'emojis' => true,
        // Disable embeds/oEmbed discovery + wp-embed on front.
        'embeds' => true,
        // Remove REST API link tag from <head> (keeps API functional).
        'rest_head' => true,
        // Disable XML-RPC OR just pingbacks (safer default: only pingbacks).
        'xmlrpc_pingback' => true,
        'xmlrpc_full' => false, // Set true to disable XML-RPC entirely.
        // Tame Heartbeat (reduce frequency, disable on front-end).
        'heartbeat' => true,
        // Raise autosave interval (default 120s).
        'autosave_interval' => true,
        // Limit post revisions (default 5).
        'limit_revisions' => true,
        // Remove dashicons on front for non-logged-in users.
        'dashicons_front' => true,
        // Remove duotone SVG filters output.
        'duotone' => true,
        // Disable block patterns (core).
        'block_patterns' => true,
        // Disable core block CSS on front-end (OFF by default for safety).
        'block_css' => false,
        // Disable comments sitewide (OFF by default; enables robust blockers).
        'disable_comments' => false,
        // Disable default feeds (OFF by default; some sites need feeds).
        'feeds' => false,
        // WooCommerce optimizations (auto-detect; OFF by default).
        'wc_optimizations' => false,
      );
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
      $features = apply_filters('phbr/features', self::default_features());

      if (!empty($features['head_cleanup'])) {
        add_action('init', array($this, 'head_cleanup'));
      }
      if (!empty($features['emojis'])) {
        add_action('init', array($this, 'disable_emojis'));
      }
      if (!empty($features['embeds'])) {
        add_action('init', array($this, 'disable_embeds'), 999);
      }
      if (!empty($features['rest_head'])) {
        add_action('init', array($this, 'remove_rest_head_link'));
      }
      if (!empty($features['xmlrpc_pingback']) || !empty($features['xmlrpc_full'])) {
        add_filter('xmlrpc_enabled', array($this, 'xmlrpc_enabled'));
        add_filter('xmlrpc_methods', array($this, 'filter_xmlrpc_methods'));
      }
      if (!empty($features['heartbeat'])) {
        add_action('init', array($this, 'tune_heartbeat'));
      }
      if (!empty($features['autosave_interval'])) {
        add_action('admin_init', array($this, 'raise_autosave_interval'));
      }
      if (!empty($features['limit_revisions'])) {
        add_filter('wp_revisions_to_keep', array($this, 'limit_revisions'), 10, 2);
      }
      if (!empty($features['dashicons_front'])) {
        add_action('wp_enqueue_scripts', array($this, 'strip_dashicons_for_guests'), 100);
      }
      if (!empty($features['duotone'])) {
        add_action('after_setup_theme', array($this, 'disable_duotone'));
      }
      if (!empty($features['block_patterns'])) {
        add_action('init', array($this, 'disable_block_patterns'));
      }
      if (!empty($features['block_css'])) {
        add_action('wp_enqueue_scripts', array($this, 'dequeue_block_css'), 100);
        add_filter('should_load_separate_core_block_assets', '__return_false', 10);
      }
      if (!empty($features['disable_comments'])) {
        $this->disable_comments();
      }
      if (!empty($features['feeds'])) {
        add_action('do_feed', array($this, 'kill_feed'), 1);
        add_action('do_feed_rdf', array($this, 'kill_feed'), 1);
        add_action('do_feed_rss', array($this, 'kill_feed'), 1);
        add_action('do_feed_rss2', array($this, 'kill_feed'), 1);
        add_action('do_feed_atom', array($this, 'kill_feed'), 1);
        add_action('template_redirect', array($this, 'remove_feed_links'));
      }
      if (!empty($features['wc_optimizations'])) {
        add_action('init', array($this, 'woocommerce_optimizations'));
      }

      // Always: minor niceties safe everywhere.
      add_filter('emoji_svg_url', '__return_false');
    }

    /* ---------------------------
     * Feature implementations
     * --------------------------*/

    /**
     * Clean <head> of common cruft.
     */
    public function head_cleanup()
    {
      remove_action('wp_head', 'rsd_link');
      remove_action('wp_head', 'wlwmanifest_link');
      remove_action('wp_head', 'wp_generator');
      remove_action('wp_head', 'wp_shortlink_wp_head');
      remove_action('wp_head', 'rest_output_link_wp_head', 10);
      remove_action('template_redirect', 'rest_output_link_header', 11);
      remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
      // Leave feed links in place by default; toggled via 'feeds' feature.
    }

    /**
     * Disable emojis everywhere.
     */
    public function disable_emojis()
    {
      remove_action('admin_print_styles', 'print_emoji_styles');
      remove_action('wp_head', 'print_emoji_detection_script', 7);
      remove_action('admin_print_scripts', 'print_emoji_detection_script');
      remove_action('wp_print_styles', 'print_emoji_styles');
      remove_filter('the_content_feed', 'wp_staticize_emoji');
      remove_filter('comment_text_rss', 'wp_staticize_emoji');
      remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
      add_filter('tiny_mce_plugins', function ($plugins) {
        if (is_array($plugins)) {
          return array_diff($plugins, array('wpemoji'));
        }
        return array();
      });
    }

    /**
     * Disable embeds and oEmbed discovery, and drop front-end wp-embed.
     */
    public function disable_embeds()
    {
      remove_action('rest_api_init', 'wp_oembed_register_route');
      add_filter('embed_oembed_discover', '__return_false', 10);
      remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
      remove_action('wp_head', 'wp_oembed_add_discovery_links');
      remove_action('wp_head', 'wp_oembed_add_host_js');
      add_action('wp_enqueue_scripts', function () {
        wp_deregister_script('wp-embed');
      }, 100);
    }

    /**
     * Remove REST API link tag from head; keep API working.
     */
    public function remove_rest_head_link()
    {
      remove_action('wp_head', 'rest_output_link_wp_head', 10);
      remove_action('template_redirect', 'rest_output_link_header', 11);
    }

    /**
     * XML-RPC hardening/disable.
     *
     * @param bool $enabled Core's XML-RPC enabled flag.
     * @return bool
     */
    public function xmlrpc_enabled($enabled)
    {
      $features = apply_filters('phbr/features', self::default_features());
      if (!empty($features['xmlrpc_full'])) {
        return false; // Disable XML-RPC entirely.
      }
      return $enabled;
    }

    /**
     * Filter XML-RPC methods (remove pingbacks at minimum).
     *
     * @param array $methods Methods array.
     * @return array
     */
    public function filter_xmlrpc_methods($methods)
    {
      $features = apply_filters('phbr/features', self::default_features());
      if (!empty($features['xmlrpc_pingback'])) {
        unset($methods['pingback.ping'], $methods['pingback.extensions.getPingbacks']);
      }
      return $methods;
    }

    /**
     * Heartbeat tuning: disable on front, slow in admin (60s), keep on post screens.
     */
    public function tune_heartbeat()
    {
      add_action('init', function () {
        if (!is_admin()) {
          wp_deregister_script('heartbeat');
        }
      }, 1);

      add_filter('heartbeat_settings', function ($settings) {
        // Default 60s; override via 'phbr/heartbeat_interval'.
        $settings['interval'] = (int) apply_filters('phbr/heartbeat_interval', 60);
        return $settings;
      });
    }

    /**
     * Increase autosave interval (default 120 seconds).
     */
    public function raise_autosave_interval()
    {
      add_filter('autosave_interval', function ($seconds) {
        $seconds = (int) $seconds;
        $target = (int) apply_filters('phbr/autosave_interval', 120);
        return max($target, 1);
      });
    }

    /**
     * Limit revisions (default 5).
     *
     * @param int     $num      Number to keep.
     * @param \WP_Post $post    Post object.
     * @return int
     */
    public function limit_revisions($num, $post)
    {
      unset($post); // Unused by default; kept for dev overrides.
      $target = (int) apply_filters('phbr/revisions_to_keep', 5);
      return max($target, 0);
    }

    /**
     * Remove dashicons for non-logged-in on front-end.
     */
    public function strip_dashicons_for_guests()
    {
      if (!is_user_logged_in()) {
        wp_deregister_style('dashicons');
      }
    }

    /**
     * Disable duotone SVG filters output.
     */
    public function disable_duotone()
    {
      remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
      remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
      remove_action('in_admin_header', 'wp_global_styles_render_svg_filters');
    }

    /**
     * Disable block patterns (core).
     */
    public function disable_block_patterns()
    {
      remove_theme_support('core-block-patterns');
      add_action('admin_init', function () {
        remove_action('enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets');
      });
    }

    /**
     * Optionally dequeue core block CSS on front-end.
     * Use with caution; can break styling if your theme depends on it.
     */
    public function dequeue_block_css()
    {
      if (is_admin()) {
        return;
      }
      wp_dequeue_style('wp-block-library');
      wp_dequeue_style('wp-block-library-theme');
      wp_dequeue_style('global-styles');
      wp_dequeue_style('classic-theme-styles');
    }

    /**
     * Robust sitewide comment disable (front + admin).
     */
    private function disable_comments()
    {
      // Disable support for comments and trackbacks in post types.
      add_action('admin_init', function () {
        $post_types = get_post_types();
        foreach ($post_types as $type) {
          if (post_type_supports($type, 'comments')) {
            remove_post_type_support($type, 'comments');
            remove_post_type_support($type, 'trackbacks');
          }
        }
      });

      // Close comments on front-end.
      add_filter('comments_open', '__return_false', 20, 2);
      add_filter('pings_open', '__return_false', 20, 2);
      add_filter('comments_array', function ($comments) {
        return array();
      }, 20, 2);

      // Remove menu page and redirect comments screens.
      add_action('admin_menu', function () {
        remove_menu_page('edit-comments.php');
      });
      add_action('admin_init', function () {
        global $pagenow;
        if ('edit-comments.php' === $pagenow) {
          wp_safe_redirect(admin_url()); // phpcs:ignore WordPress.Security.SafeRedirect.wp_safe_redirect
          exit;
        }
      });

      // Remove comments from admin bar.
      add_action('init', function () {
        if (is_admin_bar_showing()) {
          remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }
      });

      // Remove comment author cookie.
      add_filter('comment_cookie_lifetime', '__return_zero');
    }

    /**
     * Kill feeds and remove feed links.
     */
    public function kill_feed()
    {
      wp_die(
        esc_html__('Feeds are disabled on this site.', 'performance-hygiene'),
        esc_html__('Feeds disabled', 'performance-hygiene'),
        array('response' => 410)
      );
    }

    /**
     * Remove default feed link tags if feeds are disabled.
     */
    public function remove_feed_links()
    {
      remove_action('wp_head', 'feed_links', 2);
      remove_action('wp_head', 'feed_links_extra', 3);
    }

    /**
     * WooCommerce optimizations (conditional).
     * - Disable cart fragments on non-cart/checkout pages.
     * - Optionally dequeue Woo styles/scripts on non-WC pages.
     */
    public function woocommerce_optimizations()
    {
      if (!class_exists('\WooCommerce')) {
        return;
      }

      // Disable cart fragments except on cart/checkout/my-account.
      add_action('wp_enqueue_scripts', function () {
        if (function_exists('is_cart') && (is_cart() || is_checkout() || is_account_page())) {
          return;
        }
        wp_dequeue_script('wc-cart-fragments');
        wp_deregister_script('wc-cart-fragments');
      }, 100);

      // Optional: dequeue Woo styles on non-Woo pages.
      add_action('wp_enqueue_scripts', function () {
        if (function_exists('is_woocommerce') && (is_woocommerce() || is_cart() || is_checkout() || is_account_page())) {
          return;
        }
        wp_dequeue_style('woocommerce-general');
        wp_dequeue_style('woocommerce-layout');
        wp_dequeue_style('woocommerce-smallscreen');
        // Some themes register consolidated handles; allow devs to add more via filter below.
        $more = (array) apply_filters('phbr/wc_extra_style_handles', array());
        foreach ($more as $handle) {
          wp_dequeue_style($handle);
        }
      }, 100);
    }
  }

  // Bootstrap.
  Plugin::instance();

endif; // class_exists
