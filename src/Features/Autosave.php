<?php

namespace PerformanceHygiene\BloatRemover\Features;

class Autosave implements FeatureInterface
{
    public function register(): void
    {
        add_action('admin_init', array($this, 'raise_interval'));
    }

    public function raise_interval(): void
    {
        add_filter('autosave_interval', function ($seconds) {
            $seconds = (int) $seconds;
            $target = (int) apply_filters('phbr/autosave_interval', 120);
            return max($target, 1);
        });
    }
}
