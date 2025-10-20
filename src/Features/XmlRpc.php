<?php

namespace PerformanceHygiene\BloatRemover\Features;

use PerformanceHygiene\BloatRemover\Settings\Repository;

class XmlRpc implements FeatureInterface
{
    public function register(): void
    {
        add_filter('xmlrpc_enabled', array($this, 'enabled'));
        add_filter('xmlrpc_methods', array($this, 'filter_methods'));
    }

    public function enabled($enabled)
    {
        $s = Repository::get();
        if (!empty($s['xmlrpc_full'])) {
            return false;
        }
        return $enabled;
    }

    public function filter_methods($methods): array
    {
        $s = Repository::get();
        if (!empty($s['xmlrpc_pingback'])) {
            unset($methods['pingback.ping'], $methods['pingback.extensions.getPingbacks']);
        }
        return $methods;
    }
}
