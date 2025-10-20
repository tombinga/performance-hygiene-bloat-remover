<?php

namespace PerformanceHygiene\BloatRemover\Features;

class Revisions implements FeatureInterface
{
    public function register(): void
    {
        add_filter('wp_revisions_to_keep', array($this, 'limit'), 10, 2);
    }

    public function limit($num, $post): int
    {
        unset($post);
        $target = (int) apply_filters('phbr/revisions_to_keep', 5);
        return max($target, 0);
    }
}
