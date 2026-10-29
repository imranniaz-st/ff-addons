<?php

if (! defined('ABSPATH')) {
    exit;
}

class FF_Addons_Widget_Deals extends FF_Addons_Widget_Base
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

    public function get_keywords(): array
    {
        return ['facefood', 'deals', 'offers', 'ff addons'];
    }

    protected function ff_addon_categories(): array
    {
        return ['ff-addons-offers', 'ff-addons-general'];
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

        $this->add_columns_control('3');

        $this->end_controls_section();

        $this->add_card_style_section();
    }

    protected function render(): void
    {
        $s = $this->get_settings_for_display();
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo FF_Addons_Plugin::instance()->render_deals([
            'limit' => (int) ($s['limit'] ?? 6),
            'columns' => (int) ($s['columns'] ?? 3),
        ]);
    }
}
