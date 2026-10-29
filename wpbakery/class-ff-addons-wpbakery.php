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

        $categoryOptions = FF_Addons_Helpers::category_options(true);
        $categoryDropdown = [];
        foreach ($categoryOptions as $slug => $label) {
            $categoryDropdown[$label] = $slug;
        }

        $allCategoryOptions = FF_Addons_Helpers::category_options(false);
        $allCategoryDropdown = [];
        foreach ($allCategoryOptions as $slug => $label) {
            $allCategoryDropdown[$label] = $slug;
        }

        vc_map([
            'name' => __('FF Menu', 'ff-addons'),
            'base' => 'ff_addons_menu',
            'category' => [
                __('FF Addons', 'ff-addons'),
                __('FF Addons · Menu', 'ff-addons'),
            ],
            'icon' => 'icon-wpb-woocommerce',
            'description' => __('Facefood product grid — pick any menu category.', 'ff-addons'),
            'params' => [
                [
                    'type' => 'dropdown',
                    'heading' => __('Category', 'ff-addons'),
                    'param_name' => 'category',
                    'value' => $categoryDropdown,
                    'std' => '',
                ],
                [
                    'type' => 'textfield',
                    'heading' => __('Limit', 'ff-addons'),
                    'param_name' => 'limit',
                    'value' => '8',
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
                [
                    'type' => 'dropdown',
                    'heading' => __('Show description', 'ff-addons'),
                    'param_name' => 'show_description',
                    'value' => [
                        __('Yes', 'ff-addons') => 'yes',
                        __('No', 'ff-addons') => 'no',
                    ],
                    'std' => 'yes',
                ],
            ],
        ]);

        vc_map([
            'name' => __('FF Categories', 'ff-addons'),
            'base' => 'ff_addons_categories',
            'category' => [
                __('FF Addons', 'ff-addons'),
                __('FF Addons · Menu', 'ff-addons'),
            ],
            'icon' => 'icon-wpb-application-icon-large',
            'description' => __('All Facefood categories (Shawarma, Burgers, …).', 'ff-addons'),
            'params' => [
                [
                    'type' => 'dropdown',
                    'heading' => __('Layout', 'ff-addons'),
                    'param_name' => 'layout',
                    'value' => [
                        __('Grid', 'ff-addons') => 'grid',
                        __('Horizontal bar', 'ff-addons') => 'bar',
                    ],
                    'std' => 'grid',
                ],
                [
                    'type' => 'checkbox',
                    'heading' => __('Categories to show', 'ff-addons'),
                    'param_name' => 'categories',
                    'value' => $allCategoryDropdown,
                    'description' => __('Leave empty to show all.', 'ff-addons'),
                ],
                [
                    'type' => 'dropdown',
                    'heading' => __('Columns', 'ff-addons'),
                    'param_name' => 'columns',
                    'value' => [
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                        '6' => '6',
                        '8' => '8',
                    ],
                    'std' => '6',
                ],
                [
                    'type' => 'textfield',
                    'heading' => __('Link base URL', 'ff-addons'),
                    'param_name' => 'link_base',
                    'value' => '',
                ],
                [
                    'type' => 'dropdown',
                    'heading' => __('Show product count', 'ff-addons'),
                    'param_name' => 'show_count',
                    'value' => [
                        __('Yes', 'ff-addons') => 'yes',
                        __('No', 'ff-addons') => 'no',
                    ],
                    'std' => 'yes',
                ],
            ],
        ]);

        vc_map([
            'name' => __('FF Deals', 'ff-addons'),
            'base' => 'ff_addons_deals',
            'category' => [
                __('FF Addons', 'ff-addons'),
                __('FF Addons · Offers', 'ff-addons'),
            ],
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
            'category' => [
                __('FF Addons', 'ff-addons'),
                __('FF Addons · General', 'ff-addons'),
            ],
            'icon' => 'icon-wpb-ui-button',
            'description' => __('Facefood call-to-action banner.', 'ff-addons'),
            'params' => [
                [
                    'type' => 'textfield',
                    'heading' => __('Title', 'ff-addons'),
                    'param_name' => 'title',
                    'value' => __('Get Started', 'ff-addons'),
                ],
                [
                    'type' => 'textarea',
                    'heading' => __('Text', 'ff-addons'),
                    'param_name' => 'text',
                    'value' => __('Add a short call-to-action message here.', 'ff-addons'),
                ],
                [
                    'type' => 'textfield',
                    'heading' => __('Button label', 'ff-addons'),
                    'param_name' => 'button',
                    'value' => __('Learn More', 'ff-addons'),
                ],
                [
                    'type' => 'textfield',
                    'heading' => __('Button URL', 'ff-addons'),
                    'param_name' => 'url',
                    'value' => '#',
                ],
            ],
        ]);

        vc_map([
            'name' => __('FF About Us', 'ff-addons'),
            'base' => 'ff_addons_about',
            'category' => [
                __('FF Addons', 'ff-addons'),
                __('FF Addons · General', 'ff-addons'),
            ],
            'icon' => 'icon-wpb-ui-custom_heading',
            'description' => __('About Us section with image and dual CTAs.', 'ff-addons'),
            'params' => [
                [
                    'type' => 'textfield',
                    'heading' => __('Title', 'ff-addons'),
                    'param_name' => 'title',
                    'value' => __('About Us', 'ff-addons'),
                ],
                [
                    'type' => 'textarea_html',
                    'heading' => __('Description', 'ff-addons'),
                    'param_name' => 'content',
                ],
                [
                    'type' => 'textfield',
                    'heading' => __('Image URL', 'ff-addons'),
                    'param_name' => 'image',
                    'value' => '',
                ],
                [
                    'type' => 'textfield',
                    'heading' => __('Image alt text', 'ff-addons'),
                    'param_name' => 'image_alt',
                    'value' => __('About Us', 'ff-addons'),
                ],
                [
                    'type' => 'textfield',
                    'heading' => __('Primary button label', 'ff-addons'),
                    'param_name' => 'primary_button',
                    'value' => __('Learn more', 'ff-addons'),
                ],
                [
                    'type' => 'textfield',
                    'heading' => __('Primary button URL', 'ff-addons'),
                    'param_name' => 'primary_url',
                    'value' => '/',
                ],
                [
                    'type' => 'textfield',
                    'heading' => __('Secondary button label', 'ff-addons'),
                    'param_name' => 'secondary_button',
                    'value' => __('Contact us', 'ff-addons'),
                ],
                [
                    'type' => 'textfield',
                    'heading' => __('Secondary button URL', 'ff-addons'),
                    'param_name' => 'secondary_url',
                    'value' => '/',
                ],
            ],
        ]);
    }
}
