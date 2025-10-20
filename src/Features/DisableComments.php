<?php

namespace PerformanceHygiene\BloatRemover\Features;

class DisableComments implements FeatureInterface
{
    public function register(): void
    {
        add_action('admin_init', array($this, 'remove_support'));
        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);
        add_filter('comments_array', function ($comments) { return array(); }, 20, 2);
        add_action('admin_menu', array($this, 'remove_menu'));
        add_action('admin_init', array($this, 'redirect_screens'));
        add_action('init', array($this, 'remove_admin_bar_menu'));
        add_filter('comment_cookie_lifetime', '__return_zero');
    }

    public function remove_support(): void
    {
        $post_types = get_post_types();
        foreach ($post_types as $type) {
            if (post_type_supports($type, 'comments')) {
                remove_post_type_support($type, 'comments');
                remove_post_type_support($type, 'trackbacks');
            }
        }
    }

    public function remove_menu(): void
    {
        remove_menu_page('edit-comments.php');
    }

    public function redirect_screens(): void
    {
        global $pagenow;
        if ('edit-comments.php' === $pagenow) {
            wp_safe_redirect(admin_url());
            exit;
        }
    }

    public function remove_admin_bar_menu(): void
    {
        if (is_admin_bar_showing()) {
            remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }
    }
}
