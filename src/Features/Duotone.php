<?php

namespace PerformanceHygiene\BloatRemover\Features;

class Duotone implements FeatureInterface
{
    public function register(): void
    {
        add_action('after_setup_theme', array($this, 'disable'));
    }

    public function disable(): void
    {
        remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
        remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
        remove_action('in_admin_header', 'wp_global_styles_render_svg_filters');
    }
}
