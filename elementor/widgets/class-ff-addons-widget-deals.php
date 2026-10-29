<?php

if (! defined('ABSPATH')) {
    exit;
}

class FF_Addons_Widget_Deals extends \Elementor\Widget_Base
{
    public function get_name(): string
    {
        return 'ff_addons_deals';
    }

    public function get_title(): string
    {
        return __('FF Deals', 'ff-addons');
    }

    public function get_icon(): string
    {
        return 'eicon-price-table';
    }

    public function get_categories(): array
    {
        return ['ff-addons'];
    }

    public function get_keywords(): array
    {
        return ['facefood', 'deals', 'offers'];
    }

    protected function register_controls(): void
    {
        $this->start_controls_section('content', [
            'label' => __('Content', 'ff-addons'),
        ]);

        $this->add_control('limit', [
            'label' => __('Limit', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::NUMBER,
            'default' => 6,
            'min' => 1,
            'max' => 24,
        ]);

        $this->add_control('columns', [
            'label' => __('Columns', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => '3',
            'options' => [
                '2' => '2',
                '3' => '3',
                '4' => '4',
            ],
        ]);

        $this->end_controls_section();
    }

    protected function render(): void
    {
        $s = $this->get_settings_for_display();
        echo FF_Addons_Plugin::instance()->render_deals(
            (int) ($s['limit'] ?? 6),
            (int) ($s['columns'] ?? 3)
        );
    }
}
