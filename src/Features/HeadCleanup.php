<?php

namespace PerformanceHygiene\BloatRemover\Features;

class HeadCleanup implements FeatureInterface
{
    public function register(): void
    {
        add_action('init', array($this, 'run'));
    }

    public function run(): void
    {
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('template_redirect', 'rest_output_link_header', 11);
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
    }
}
