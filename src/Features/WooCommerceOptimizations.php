<?php

namespace PerformanceHygiene\BloatRemover\Features;

use PerformanceHygiene\BloatRemover\Settings\Repository;

class WooCommerceOptimizations implements FeatureInterface
{
    private array $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function register(): void
    {
        if (!class_exists('\\WooCommerce')) {
            return;
        }
        add_action('wp_enqueue_scripts', array($this, 'maybe_disable_cart_fragments'), 100);
        add_action('wp_enqueue_scripts', array($this, 'maybe_dequeue_styles'), 100);
    }

    public function maybe_disable_cart_fragments(): void
    {
        $on_wc_page =
            (function_exists('is_cart') && is_cart()) ||
            (function_exists('is_checkout') && is_checkout()) ||
            (function_exists('is_account_page') && is_account_page());
        if ($on_wc_page) {
            return;
        }
        wp_dequeue_script('wc-cart-fragments');
        wp_deregister_script('wc-cart-fragments');
    }

    public function maybe_dequeue_styles(): void
    {
        $on_wc =
            (function_exists('is_woocommerce') && is_woocommerce()) ||
            (function_exists('is_cart') && is_cart()) ||
            (function_exists('is_checkout') && is_checkout()) ||
            (function_exists('is_account_page') && is_account_page());
        if ($on_wc) {
            return;
        }
        if (empty($this->settings['wc_optimizations']) || empty($this->settings['wc_dequeue_styles'])) {
            return;
        }
        wp_dequeue_style('woocommerce-general');
        wp_dequeue_style('woocommerce-layout');
        wp_dequeue_style('woocommerce-smallscreen');
        $more = (array) apply_filters('phbr/wc_extra_style_handles', array());
        foreach ($more as $handle) {
            wp_dequeue_style($handle);
        }
    }
}
