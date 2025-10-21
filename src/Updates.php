<?php
namespace PerformanceHygiene\BloatRemover;

if (!defined('ABSPATH')) {
    exit;
}

class Updates
{
    public static function init(): void
    {
        if (!\is_admin()) {
            return;
        }

        // Default public repo (can be overridden via constant or filter below)
        $repo = defined('PHBR_UPDATE_REPO') ? PHBR_UPDATE_REPO : 'https://github.com/tombinga/performance-hygiene-bloat-remover/';
        if (function_exists('apply_filters')) {
            $repo = (string) apply_filters('phbr/update_repo', $repo);
        }
        if (empty($repo)) {
            return;
        }

        $base = rtrim(dirname(__DIR__), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $lib = $base . 'vendor/plugin-update-checker/plugin-update-checker.php';
        if (file_exists($lib)) {
            require_once $lib;
        } else {
            return;
        }

        $pluginFile = $base . 'performance-hygiene-bloat-remover.php';
        $slug = 'performance-hygiene-bloat-remover';

        $factoryClass = '\\YahnisElsts\\PluginUpdateChecker\\v5p6\\PucFactory';
        try {
            $updateChecker = is_callable([$factoryClass, 'buildUpdateChecker'])
                ? call_user_func([$factoryClass, 'buildUpdateChecker'], $repo, $pluginFile, $slug)
                : null;
        } catch (\Throwable $e) {
            $updateChecker = null;
        }
        if (!$updateChecker) {
            return;
        }

        $branch = defined('PHBR_UPDATE_BRANCH') ? PHBR_UPDATE_BRANCH : 'main';
        if (method_exists($updateChecker, 'setBranch')) {
            try {
                $updateChecker->setBranch($branch);
            } catch (\Throwable $e) {
            }
        }

        $token = null;
        if (defined('PHBR_UPDATE_TOKEN') && PHBR_UPDATE_TOKEN) {
            $token = PHBR_UPDATE_TOKEN;
        } elseif (!empty(getenv('PHBR_UPDATE_TOKEN'))) {
            $token = getenv('PHBR_UPDATE_TOKEN');
        }
        if (!empty($token) && method_exists($updateChecker, 'setAuthentication')) {
            try {
                $updateChecker->setAuthentication($token);
            } catch (\Throwable $e) {
            }
        }

        if (method_exists($updateChecker, 'getVcsApi')) {
            $api = $updateChecker->getVcsApi();
            if ($api && method_exists($api, 'enableReleaseAssets')) {
                try {
                    $api->enableReleaseAssets();
                } catch (\Throwable $e) {
                }
            }
        }
    }
}
