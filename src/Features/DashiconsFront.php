<?php

namespace PerformanceHygiene\BloatRemover\Features;

class DashiconsFront implements FeatureInterface
{
    public function register(): void
    {
        add_action('wp_enqueue_scripts', array($this, 'strip'), 100);
    }

    public function strip(): void
    {
        if (!is_user_logged_in()) {
            wp_deregister_style('dashicons');
        }
    }
}
