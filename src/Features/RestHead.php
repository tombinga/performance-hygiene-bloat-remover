<?php

namespace PerformanceHygiene\BloatRemover\Features;

class RestHead implements FeatureInterface
{
    public function register(): void
    {
        add_action('init', array($this, 'remove')); 
    }

    public function remove(): void
    {
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('template_redirect', 'rest_output_link_header', 11);
    }
}
