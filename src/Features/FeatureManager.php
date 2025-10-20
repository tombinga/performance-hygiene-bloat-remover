<?php

namespace PerformanceHygiene\BloatRemover\Features;

use PerformanceHygiene\BloatRemover\Settings\Repository;

class FeatureManager
{
    private array $settings;
    private array $flags;

    public function __construct(array $settings, array $flags)
    {
        $this->settings = $settings;
        $this->flags = $flags;
    }

    public function register(): void
    {
        $features = [];

        if (!empty($this->flags['head_cleanup'])) {
            $features[] = new HeadCleanup();
        }
        if (!empty($this->flags['emojis'])) {
            $features[] = new Emojis();
        }
        if (!empty($this->flags['embeds'])) {
            $features[] = new Embeds();
        }
        if (!empty($this->flags['rest_head'])) {
            $features[] = new RestHead();
        }
        if (!empty($this->flags['xmlrpc_pingback']) || !empty($this->flags['xmlrpc_full'])) {
            $features[] = new XmlRpc();
        }
        if (!empty($this->flags['heartbeat'])) {
            $features[] = new Heartbeat();
        }
        if (!empty($this->flags['autosave_interval'])) {
            $features[] = new Autosave();
        }
        if (!empty($this->flags['limit_revisions'])) {
            $features[] = new Revisions();
        }
        if (!empty($this->flags['dashicons_front'])) {
            $features[] = new DashiconsFront();
        }
        if (!empty($this->flags['duotone'])) {
            $features[] = new Duotone();
        }
        if (!empty($this->flags['block_patterns'])) {
            $features[] = new BlockPatterns();
        }
        if (!empty($this->flags['block_css'])) {
            $features[] = new BlockCss();
        }
        if (!empty($this->flags['disable_comments'])) {
            $features[] = new DisableComments();
        }
        if (!empty($this->flags['feeds'])) {
            $features[] = new Feeds();
        }
        if (!empty($this->flags['wc_optimizations'])) {
            $features[] = new WooCommerceOptimizations($this->settings);
        }

        foreach ($features as $feature) {
            if ($feature instanceof FeatureInterface) {
                $feature->register();
            }
        }

        add_filter('emoji_svg_url', '__return_false');
    }
}
