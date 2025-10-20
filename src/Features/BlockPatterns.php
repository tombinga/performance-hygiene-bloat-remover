<?php

namespace PerformanceHygiene\BloatRemover\Features;

class BlockPatterns implements FeatureInterface
{
    public function register(): void
    {
        add_action('init', array($this, 'disable'));
    }

    public function disable(): void
    {
        remove_theme_support('core-block-patterns');
        add_action('admin_init', function () {
            remove_action('enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets');
        });
    }
}
