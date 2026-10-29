<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * WPBakery Page Builder (js_composer) elements for FF Addons.
 */
class FF_Addons_WPBakery
{
    public static function register(): void
    {
        if (! function_exists('vc_map')) {
            return;
        }

        vc_map([
            'name' => __('FF Menu', 'ff-addons'),
            'base' => 'ff_addons_menu',
            'category' => __('FF Addons', 'ff-addons'),
            'icon' => 'icon-wpb-woocommerce',
            'description' => __('Facefood product grid from the Laravel API.', 'ff-addons'),
            'params' => [
                [
                    'type' => 'textfield',
                    'heading' => __('Limit', 'ff-addons'),
                    'param_name' => 'limit',
                    'value' => '8',
                ],
                [
                    'type' => 'textfield',
                    'heading' => __('Category slug', 'ff-addons'),
                    'param_name' => 'category',
                    'value' => '',
                ],
                [
                    'type' => 'dropdown',
                    'heading' => __('Columns', 'ff-addons'),
                    'param_name' => 'columns',
                    'value' => [
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                        '6' => '6',
                    ],
                    'std' => '4',
                ],
            ],
        ]);

        vc_map([
            'name' => __('FF Deals', 'ff-addons'),
            'base' => 'ff_addons_deals',
            'category' => __('FF Addons', 'ff-addons'),
            'icon' => 'icon-wpb-woocommerce',
            'description' => __('Facefood deals grid from the Laravel API.', 'ff-addons'),
            'params' => [
                [
                    'type' => 'textfield',
                    'heading' => __('Limit', 'ff-addons'),
                    'param_name' => 'limit',
                    'value' => '6',
                ],
                [
                    'type' => 'dropdown',
                    'heading' => __('Columns', 'ff-addons'),
                    'param_name' => 'columns',
                    'value' => [
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                    ],
                    'std' => '3',
                ],
            ],
        ]);

        vc_map([
            'name' => __('FF CTA', 'ff-addons'),
            'base' => 'ff_addons_cta',
            'category' => __('FF Addons', 'ff-addons'),
            'icon' => 'icon-wpb-ui-button',
            'description' => __('Facefood call-to-action banner.', 'ff-addons'),
            'params' => [
                [
                    'type' => 'textfield',
                    'heading' => __('Title', 'ff-addons'),
                    'param_name' => 'title',
                    'value' => __('Order Facefood', 'ff-addons'),
                ],
                [
                    'type' => 'textarea',
                    'heading' => __('Text', 'ff-addons'),
                    'param_name' => 'text',
                    'value' => __('Fresh meals delivered fast. Open the app or shop online.', 'ff-addons'),
                ],
                [
                    'type' => 'textfield',
                    'heading' => __('Button label', 'ff-addons'),
                    'param_name' => 'button',
                    'value' => __('Order now', 'ff-addons'),
                ],
                [
                    'type' => 'textfield',
                    'heading' => __('Button URL', 'ff-addons'),
                    'param_name' => 'url',
                    'value' => '/',
                ],
            ],
        ]);
    }
}
