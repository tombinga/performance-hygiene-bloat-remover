<?php

namespace PerformanceHygiene\BloatRemover\Features;

class Heartbeat implements FeatureInterface
{
    public function register(): void
    {
        add_action('init', array($this, 'tune'));
    }

    public function tune(): void
    {
        add_action('init', function () {
            if (!is_admin()) {
                wp_deregister_script('heartbeat');
            }
        }, 1);

        add_filter('heartbeat_settings', function ($settings) {
            $settings['interval'] = (int) apply_filters('phbr/heartbeat_interval', 60);
            return $settings;
        });
    }
}
