<?php

namespace PerformanceHygiene\BloatRemover\Admin;

use PerformanceHygiene\BloatRemover\Settings\Repository;

class SettingsPage
{
  public function register()
  {
    add_action('admin_menu', array($this, 'register_menu'));
    add_action('admin_post_phbr_save_settings', array($this, 'handle_save'));
    add_action('admin_post_phbr_reset_settings', array($this, 'handle_reset'));
    add_action('admin_post_phbr_export_settings', array($this, 'handle_export'));
    add_action('admin_post_phbr_import_settings', array($this, 'handle_import'));
  }

  public static function plugin_action_links($links)
  {
    $url = admin_url('options-general.php?page=phbr-settings');
    $links[] = '<a href="' . esc_url($url) . '">' . esc_html__('Settings', 'performance-hygiene') . '</a>';
    return $links;
  }

  public function register_menu()
  {
    add_options_page(
      esc_html__('Performance Hygiene', 'performance-hygiene'),
      esc_html__('Performance Hygiene', 'performance-hygiene'),
      'manage_options',
      'phbr-settings',
      array($this, 'render')
    );
  }

  public function handle_save()
  {
    if (!current_user_can('manage_options')) {
      wp_die(esc_html__('Insufficient permissions.', 'performance-hygiene'));
    }
    check_admin_referer('phbr_save_settings', 'phbr_nonce');
    $raw = isset($_POST['phbr']) ? (array) $_POST['phbr'] : array();
    Repository::save($raw);
    wp_safe_redirect(add_query_arg(array('page' => 'phbr-settings', 'phbr_status' => 'saved'), admin_url('options-general.php')));
    exit;
  }

  public function handle_reset()
  {
    if (!current_user_can('manage_options')) {
      wp_die(esc_html__('Insufficient permissions.', 'performance-hygiene'));
    }
    check_admin_referer('phbr_reset_settings', 'phbr_nonce');
    Repository::reset();
    wp_safe_redirect(add_query_arg(array('page' => 'phbr-settings', 'phbr_status' => 'reset'), admin_url('options-general.php')));
    exit;
  }

  public function handle_export()
  {
    if (!current_user_can('manage_options')) {
      wp_die(esc_html__('Insufficient permissions.', 'performance-hygiene'));
    }
    check_admin_referer('phbr_export_settings', 'phbr_nonce');
    $data = Repository::get();
    $json = wp_json_encode($data, JSON_PRETTY_PRINT);
    nocache_headers();
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename=phbr-settings.json');
    echo $json;
    exit;
  }

  public function handle_import()
  {
    if (!current_user_can('manage_options')) {
      wp_die(esc_html__('Insufficient permissions.', 'performance-hygiene'));
    }
    check_admin_referer('phbr_import_settings', 'phbr_nonce');
    if (!isset($_FILES['phbr_import']) || empty($_FILES['phbr_import']['tmp_name'])) {
      wp_safe_redirect(add_query_arg(array('page' => 'phbr-settings', 'phbr_status' => 'import_error'), admin_url('options-general.php')));
      exit;
    }
    $contents = file_get_contents($_FILES['phbr_import']['tmp_name']);
    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
      wp_safe_redirect(add_query_arg(array('page' => 'phbr-settings', 'phbr_status' => 'import_error'), admin_url('options-general.php')));
      exit;
    }
    Repository::save($decoded);
    wp_safe_redirect(add_query_arg(array('page' => 'phbr-settings', 'phbr_status' => 'imported'), admin_url('options-general.php')));
    exit;
  }

  public function render()
  {
    if (!current_user_can('manage_options')) {
      return;
    }
    $s = Repository::get();
    $woo_active = class_exists('\\WooCommerce');
    $status = isset($_GET['phbr_status']) ? sanitize_text_field(wp_unslash($_GET['phbr_status'])) : '';
    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Performance Hygiene', 'performance-hygiene') . '</h1>';
    if ($status === 'saved') {
      echo '<div class="notice notice-success"><p>' . esc_html__('Settings saved.', 'performance-hygiene') . '</p></div>';
    } elseif ($status === 'reset') {
      echo '<div class="notice notice-warning"><p>' . esc_html__('Settings reset to defaults.', 'performance-hygiene') . '</p></div>';
    } elseif ($status === 'imported') {
      echo '<div class="notice notice-success"><p>' . esc_html__('Settings imported.', 'performance-hygiene') . '</p></div>';
    } elseif ($status === 'import_error') {
      echo '<div class="notice notice-error"><p>' . esc_html__('Import failed. Invalid file.', 'performance-hygiene') . '</p></div>';
    }
    echo '<h2>' . esc_html__('General', 'performance-hygiene') . '</h2>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    echo '<input type="hidden" name="action" value="phbr_save_settings" />';
    wp_nonce_field('phbr_save_settings', 'phbr_nonce');
    echo '<table class="form-table" role="presentation"><tbody>';
    $fields = array(
      'head_cleanup' => __('Head cleanup', 'performance-hygiene'),
      'emojis' => __('Disable emojis', 'performance-hygiene'),
      'embeds' => __('Disable embeds', 'performance-hygiene'),
      'rest_head' => __('Remove REST head link', 'performance-hygiene'),
      'dashicons_front' => __('Disable dashicons for guests', 'performance-hygiene'),
      'duotone' => __('Disable duotone SVG filters', 'performance-hygiene'),
      'block_patterns' => __('Disable core block patterns', 'performance-hygiene'),
      'block_css' => __('Disable core block CSS on front-end (risky)', 'performance-hygiene'),
      'disable_comments' => __('Disable comments sitewide', 'performance-hygiene'),
      'feeds' => __('Disable default feeds', 'performance-hygiene'),
    );
    foreach ($fields as $k => $label) {
      echo '<tr><th scope="row">' . esc_html($label) . '</th><td>';
      echo '<label><input type="checkbox" name="phbr[' . esc_attr($k) . ']" value="1" ' . checked(!empty($s[$k]), true, false) . ' /> ' . esc_html__('Enable', 'performance-hygiene') . '</label>';
      echo '</td></tr>';
    }
    echo '</tbody></table>';

    echo '<h2>' . esc_html__('XML-RPC', 'performance-hygiene') . '</h2>';
    echo '<table class="form-table" role="presentation"><tbody>';
    echo '<tr><th scope="row">' . esc_html__('Disable XML-RPC pingbacks only', 'performance-hygiene') . '</th><td>';
    echo '<label><input type="checkbox" name="phbr[xmlrpc_pingback]" value="1" ' . checked(!empty($s['xmlrpc_pingback']), true, false) . ' /> ' . esc_html__('Enable', 'performance-hygiene') . '</label>';
    echo '</td></tr>';
    echo '<tr><th scope="row">' . esc_html__('Disable XML-RPC entirely (risky)', 'performance-hygiene') . '</th><td>';
    echo '<label><input type="checkbox" name="phbr[xmlrpc_full]" value="1" ' . checked(!empty($s['xmlrpc_full']), true, false) . ' /> ' . esc_html__('Enable', 'performance-hygiene') . '</label>';
    echo '</td></tr>';
    echo '</tbody></table>';

    echo '<h2>' . esc_html__('Heartbeat & Autosave', 'performance-hygiene') . '</h2>';
    echo '<table class="form-table" role="presentation"><tbody>';
    echo '<tr><th scope="row">' . esc_html__('Tame Heartbeat', 'performance-hygiene') . '</th><td>';
    echo '<label><input type="checkbox" name="phbr[heartbeat]" value="1" ' . checked(!empty($s['heartbeat']), true, false) . ' /> ' . esc_html__('Enable', 'performance-hygiene') . '</label>';
    echo '<p><label>' . esc_html__('Heartbeat interval (seconds)', 'performance-hygiene') . ' <input type="number" min="15" max="120" name="phbr[heartbeat_interval]" value="' . esc_attr((int) $s['heartbeat_interval']) . '" /></label></p>';
    echo '</td></tr>';
    echo '<tr><th scope="row">' . esc_html__('Raise autosave interval', 'performance-hygiene') . '</th><td>';
    echo '<label><input type="checkbox" name="phbr[autosave_interval]" value="1" ' . checked(!empty($s['autosave_interval']), true, false) . ' /> ' . esc_html__('Enable', 'performance-hygiene') . '</label>';
    echo '<p><label>' . esc_html__('Autosave interval (seconds)', 'performance-hygiene') . ' <input type="number" min="10" max="3600" name="phbr[autosave_interval_seconds]" value="' . esc_attr((int) $s['autosave_interval_seconds']) . '" /></label></p>';
    echo '</td></tr>';
    echo '<tr><th scope="row">' . esc_html__('Limit post revisions', 'performance-hygiene') . '</th><td>';
    echo '<label><input type="checkbox" name="phbr[limit_revisions]" value="1" ' . checked(!empty($s['limit_revisions']), true, false) . ' /> ' . esc_html__('Enable', 'performance-hygiene') . '</label>';
    echo '<p><label>' . esc_html__('Revisions to keep', 'performance-hygiene') . ' <input type="number" min="0" max="100" name="phbr[revisions_to_keep]" value="' . esc_attr((int) $s['revisions_to_keep']) . '" /></label></p>';
    echo '</td></tr>';
    echo '</tbody></table>';

    echo '<h2>' . esc_html__('WooCommerce', 'performance-hygiene') . '</h2>';
    if (!$woo_active) {
      echo '<p>' . esc_html__('WooCommerce is not active.', 'performance-hygiene') . '</p>';
    }
    echo '<table class="form-table" role="presentation"><tbody>';
    echo '<tr><th scope="row">' . esc_html__('WooCommerce optimizations', 'performance-hygiene') . '</th><td>';
    echo '<label><input type="checkbox" name="phbr[wc_optimizations]" value="1" ' . checked(!empty($s['wc_optimizations']), true, false) . ' /> ' . esc_html__('Enable', 'performance-hygiene') . '</label>';
    echo '</td></tr>';
    echo '<tr><th scope="row">' . esc_html__('Dequeue WooCommerce styles on non-Woo pages', 'performance-hygiene') . '</th><td>';
    echo '<label><input type="checkbox" name="phbr[wc_dequeue_styles]" value="1" ' . checked(!empty($s['wc_dequeue_styles']), true, false) . ' /> ' . esc_html__('Enable', 'performance-hygiene') . '</label>';
    echo '<p><label>' . esc_html__('Additional Woo style handles (comma-separated)', 'performance-hygiene') . ' <input type="text" name="phbr[wc_extra_style_handles]" value="' . esc_attr($s['wc_extra_style_handles']) . '" /></label></p>';
    echo '</td></tr>';
    echo '</tbody></table>';

    submit_button(esc_html__('Save Changes', 'performance-hygiene'));
    echo '</form>';

    echo '<hr />';
    echo '<h2>' . esc_html__('Tools', 'performance-hygiene') . '</h2>';
    echo '<div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline-block;margin-right:12px;">';
    echo '<input type="hidden" name="action" value="phbr_reset_settings" />';
    wp_nonce_field('phbr_reset_settings', 'phbr_nonce');
    submit_button(esc_html__('Reset to Defaults', 'performance-hygiene'), 'delete', 'submit', false);
    echo '</form>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline-block;margin-right:12px;">';
    echo '<input type="hidden" name="action" value="phbr_export_settings" />';
    wp_nonce_field('phbr_export_settings', 'phbr_nonce');
    submit_button(esc_html__('Export Settings', 'performance-hygiene'), 'secondary', 'submit', false);
    echo '</form>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" enctype="multipart/form-data" style="display:inline-block;">';
    echo '<input type="hidden" name="action" value="phbr_import_settings" />';
    wp_nonce_field('phbr_import_settings', 'phbr_nonce');
    echo '<input type="file" name="phbr_import" accept="application/json" /> ';
    submit_button(esc_html__('Import Settings', 'performance-hygiene'), 'secondary', 'submit', false);
    echo '</form>';
    echo '</div>';

    echo '</div>';
  }
}
