<?php

if (! defined('ABSPATH')) {
    exit;
}

class FF_Addons_Widget_Menu extends FF_Addons_Widget_Base
{
    public function get_name(): string
    {
        return 'ff_addons_menu';
    }

    public function get_title(): string
    {
        return __('FF Menu', 'ff-addons');
    }

    public function get_icon(): string
    {
        return 'eicon-products';
    }

    public function get_keywords(): array
    {
        return ['facefood', 'menu', 'products', 'food', 'category', 'ff addons'];
    }

    protected function ff_addon_categories(): array
    {
        return ['ff-addons-menu', 'ff-addons-general'];
    }

    protected function register_controls(): void
    {
        $this->start_controls_section('content', [
            'label' => __('Content', 'ff-addons'),
        ]);

        $this->add_control('category', [
            'label' => __('Category', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::SELECT2,
            'options' => FF_Addons_Helpers::category_options(true),
            'default' => '',
            'label_block' => true,
            'description' => __('All Facefood menu categories from the API.', 'ff-addons'),
        ]);

        $this->add_control('limit', [
            'label' => __('Limit', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::NUMBER,
            'default' => 8,
            'min' => 1,
            'max' => 48,
        ]);

        $this->add_columns_control('4');

        $this->add_control('show_description', [
            'label' => __('Show description', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => __('Yes', 'ff-addons'),
            'label_off' => __('No', 'ff-addons'),
            'return_value' => 'yes',
            'default' => 'yes',
        ]);

        $this->end_controls_section();

        $this->add_card_style_section();
    }

    protected function render(): void
    {
        $s = $this->get_settings_for_display();
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside renderer
        echo FF_Addons_Plugin::instance()->render_menu([
            'limit' => (int) ($s['limit'] ?? 8),
            'category' => (string) ($s['category'] ?? ''),
            'columns' => (int) ($s['columns'] ?? 4),
            'show_description' => ($s['show_description'] ?? 'yes') === 'yes',
        ]);
    }
}
