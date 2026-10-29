<?php

if (! defined('ABSPATH')) {
    exit;
}

class FF_Addons_Elementor
{
    public function __construct()
    {
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        add_action('elementor/elements/categories_registered', [$this, 'register_category']);
    }

    public function register_category($elements_manager): void
    {
        $elements_manager->add_category('ff-addons', [
            'title' => __('FF Addons', 'ff-addons'),
            'icon' => 'fa fa-utensils',
        ]);
    }

    public function register_widgets($widgets_manager): void
    {
        require_once FF_ADDONS_DIR . 'elementor/widgets/class-ff-addons-widget-menu.php';
        require_once FF_ADDONS_DIR . 'elementor/widgets/class-ff-addons-widget-deals.php';
        require_once FF_ADDONS_DIR . 'elementor/widgets/class-ff-addons-widget-cta.php';

        $widgets_manager->register(new FF_Addons_Widget_Menu());
        $widgets_manager->register(new FF_Addons_Widget_Deals());
        $widgets_manager->register(new FF_Addons_Widget_Cta());
    }
}
