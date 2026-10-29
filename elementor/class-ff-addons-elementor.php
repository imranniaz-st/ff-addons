<?php

if (! defined('ABSPATH')) {
    exit;
}

class FF_Addons_Elementor
{
    private static bool $registered = false;

    public function __construct()
    {
        add_action('elementor/elements/categories_registered', [$this, 'register_category'], 5);
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        // Older Elementor hooks (still used on some Hostinger installs).
        add_action('elementor/widgets/widgets_registered', [$this, 'register_widgets']);
    }

    public function register_category($elements_manager): void
    {
        // Panel groups so widgets are easy to find while editing.
        $categories = [
            'ff-addons' => [
                'title' => __('FF Addons', 'ff-addons'),
                'icon' => 'eicon-apps',
            ],
            'ff-addons-general' => [
                'title' => __('FF Addons · General', 'ff-addons'),
                'icon' => 'eicon-tools',
            ],
            'ff-addons-menu' => [
                'title' => __('FF Addons · Menu', 'ff-addons'),
                'icon' => 'eicon-food-menu',
            ],
            'ff-addons-offers' => [
                'title' => __('FF Addons · Offers', 'ff-addons'),
                'icon' => 'eicon-price-table',
            ],
        ];

        foreach ($categories as $slug => $config) {
            $elements_manager->add_category($slug, $config);
        }
    }

    public function register_widgets($widgets_manager): void
    {
        if (self::$registered) {
            return;
        }
        self::$registered = true;

        require_once FF_ADDONS_DIR . 'elementor/widgets/class-ff-addons-widget-base.php';
        require_once FF_ADDONS_DIR . 'elementor/widgets/class-ff-addons-widget-menu.php';
        require_once FF_ADDONS_DIR . 'elementor/widgets/class-ff-addons-widget-deals.php';
        require_once FF_ADDONS_DIR . 'elementor/widgets/class-ff-addons-widget-cta.php';
        require_once FF_ADDONS_DIR . 'elementor/widgets/class-ff-addons-widget-categories.php';

        $widgets = [
            new FF_Addons_Widget_Menu(),
            new FF_Addons_Widget_Deals(),
            new FF_Addons_Widget_Cta(),
            new FF_Addons_Widget_Categories(),
        ];

        foreach ($widgets as $widget) {
            if (method_exists($widgets_manager, 'register')) {
                $widgets_manager->register($widget);
            } elseif (method_exists($widgets_manager, 'register_widget_type')) {
                $widgets_manager->register_widget_type($widget);
            }
        }
    }
}
