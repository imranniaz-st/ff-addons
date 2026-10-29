<?php

if (! defined('ABSPATH')) {
    exit;
}

class FF_Addons_Widget_Menu extends \Elementor\Widget_Base
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

    public function get_categories(): array
    {
        return ['ff-addons'];
    }

    public function get_keywords(): array
    {
        return ['facefood', 'menu', 'products', 'food'];
    }

    protected function register_controls(): void
    {
        $this->start_controls_section('content', [
            'label' => __('Content', 'ff-addons'),
        ]);

        $this->add_control('limit', [
            'label' => __('Limit', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::NUMBER,
            'default' => 8,
            'min' => 1,
            'max' => 48,
        ]);

        $this->add_control('category', [
            'label' => __('Category slug (optional)', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => '',
        ]);

        $this->add_control('columns', [
            'label' => __('Columns', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => '4',
            'options' => [
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
                '6' => '6',
            ],
        ]);

        $this->end_controls_section();
    }

    protected function render(): void
    {
        $s = $this->get_settings_for_display();
        echo FF_Addons_Plugin::instance()->render_menu(
            (int) ($s['limit'] ?? 8),
            (string) ($s['category'] ?? ''),
            (int) ($s['columns'] ?? 4)
        );
    }
}
