<?php

namespace PerformanceHygiene\BloatRemover\Features;

class BlockCss implements FeatureInterface
{
    public function register(): void
    {
        add_action('wp_enqueue_scripts', array($this, 'dequeue'), 100);
        add_filter('should_load_separate_core_block_assets', '__return_false', 10);
    }

    public function dequeue(): void
    {
        if (is_admin()) {
            return;
        }
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('global-styles');
        wp_dequeue_style('classic-theme-styles');
    }
}
