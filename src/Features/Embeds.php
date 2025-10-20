<?php

namespace PerformanceHygiene\BloatRemover\Features;

class Embeds implements FeatureInterface
{
    public function register(): void
    {
        add_action('init', array($this, 'disable'), 999);
    }

    public function disable(): void
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
}
