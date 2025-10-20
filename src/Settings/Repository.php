<?php

namespace PerformanceHygiene\BloatRemover\Settings;

class Repository
{
  const OPTION = 'phbr_settings';

  public static function default_features()
  {
    return array(
      'head_cleanup' => true,
      'emojis' => true,
      'embeds' => true,
      'rest_head' => true,
      'xmlrpc_pingback' => true,
      'xmlrpc_full' => false,
      'heartbeat' => true,
      'autosave_interval' => true,
      'limit_revisions' => true,
      'dashicons_front' => true,
      'duotone' => true,
      'block_patterns' => true,
      'block_css' => false,
      'disable_comments' => false,
      'feeds' => false,
      'wc_optimizations' => false,
    );
  }

  public static function default_parameters()
  {
    return array(
      'heartbeat_interval' => 60,
      'autosave_interval_seconds' => 120,
      'revisions_to_keep' => 5,
      'wc_dequeue_styles' => false,
      'wc_extra_style_handles' => '',
    );
  }

  public static function defaults()
  {
    return array_merge(self::default_features(), self::default_parameters());
  }

  public static function get()
  {
    $raw = get_option(self::OPTION, array());
    if (!is_array($raw)) {
      $raw = array();
    }
    $merged = array_merge(self::defaults(), $raw);
    return self::sanitize($merged);
  }

  public static function save(array $settings)
  {
    $clean = self::sanitize($settings);
    update_option(self::OPTION, $clean, true);
    return $clean;
  }

  public static function reset()
  {
    $clean = self::sanitize(self::defaults());
    update_option(self::OPTION, $clean, true);
    return $clean;
  }

  public static function sanitize(array $in)
  {
    $out = array();
    $bools = array('head_cleanup', 'emojis', 'embeds', 'rest_head', 'xmlrpc_pingback', 'xmlrpc_full', 'heartbeat', 'autosave_interval', 'limit_revisions', 'dashicons_front', 'duotone', 'block_patterns', 'block_css', 'disable_comments', 'feeds', 'wc_optimizations', 'wc_dequeue_styles');
    foreach ($bools as $k) {
      $out[$k] = !empty($in[$k]) ? true : false;
    }
    if (!empty($out['xmlrpc_full'])) {
      $out['xmlrpc_pingback'] = false;
    }
    $hi = isset($in['heartbeat_interval']) ? (int) $in['heartbeat_interval'] : 60;
    $out['heartbeat_interval'] = max(15, min(120, $hi));
    $ai = isset($in['autosave_interval_seconds']) ? (int) $in['autosave_interval_seconds'] : 120;
    $out['autosave_interval_seconds'] = max(10, min(3600, $ai));
    $rv = isset($in['revisions_to_keep']) ? (int) $in['revisions_to_keep'] : 5;
    $out['revisions_to_keep'] = max(0, min(100, $rv));
    $extra = isset($in['wc_extra_style_handles']) ? (string) $in['wc_extra_style_handles'] : '';
    $tokens = preg_split('/[,\s]+/', $extra);
    $clean = array();
    if (is_array($tokens)) {
      foreach ($tokens as $t) {
        $t = strtolower(trim($t));
        if ($t !== '' && preg_match('/^[a-z0-9._-]+$/', $t)) {
          $clean[$t] = true;
        }
      }
    }
    $out['wc_extra_style_handles'] = implode(',', array_keys($clean));
    return array_merge(self::default_parameters(), self::default_features(), $out);
  }

  public static function feature_flags(?array $settings = null)
  {
    $settings = is_array($settings) ? $settings : self::get();
    $keys = array('head_cleanup', 'emojis', 'embeds', 'rest_head', 'xmlrpc_pingback', 'xmlrpc_full', 'heartbeat', 'autosave_interval', 'limit_revisions', 'dashicons_front', 'duotone', 'block_patterns', 'block_css', 'disable_comments', 'feeds', 'wc_optimizations');
    $out = array();
    foreach ($keys as $k) {
      $out[$k] = !empty($settings[$k]);
    }
    return $out;
  }

  // Filter-friendly getters
  public static function heartbeat_interval($current)
  {
    $s = self::get();
    if (!empty($s['heartbeat'])) {
      return (int) $s['heartbeat_interval'];
    }
    return $current;
  }

  public static function autosave_interval($current)
  {
    $s = self::get();
    if (!empty($s['autosave_interval'])) {
      return (int) $s['autosave_interval_seconds'];
    }
    return $current;
  }

  public static function revisions_to_keep($current)
  {
    $s = self::get();
    if (!empty($s['limit_revisions'])) {
      return (int) $s['revisions_to_keep'];
    }
    return $current;
  }

  public static function wc_extra_style_handles($current)
  {
    $s = self::get();
    if (empty($s['wc_optimizations']) || empty($s['wc_dequeue_styles'])) {
      return $current;
    }
    $val = isset($s['wc_extra_style_handles']) ? (string) $s['wc_extra_style_handles'] : '';
    $tokens = preg_split('/[,\s]+/', $val);
    $out = array();
    if (is_array($tokens)) {
      foreach ($tokens as $t) {
        $t = strtolower(trim($t));
        if ($t !== '' && preg_match('/^[a-z0-9._-]+$/', $t)) {
          $out[$t] = true;
        }
      }
    }
    return array_keys($out);
  }
}
