<?php

namespace PerformanceHygiene\BloatRemover\Features;

class Feeds implements FeatureInterface
{
    public function register(): void
    {
        add_action('do_feed', array($this, 'kill'), 1);
        add_action('do_feed_rdf', array($this, 'kill'), 1);
        add_action('do_feed_rss', array($this, 'kill'), 1);
        add_action('do_feed_rss2', array($this, 'kill'), 1);
        add_action('do_feed_atom', array($this, 'kill'), 1);
        add_action('template_redirect', array($this, 'remove_links'));
    }

    public function kill(): void
    {
        wp_die(
            esc_html__('Feeds are disabled on this site.', 'performance-hygiene'),
            esc_html__('Feeds disabled', 'performance-hygiene'),
            array('response' => 410)
        );
    }

    public function remove_links(): void
    {
        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'feed_links_extra', 3);
    }
}
