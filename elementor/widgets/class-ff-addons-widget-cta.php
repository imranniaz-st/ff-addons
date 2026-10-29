<?php

if (! defined('ABSPATH')) {
    exit;
}

class FF_Addons_Widget_Cta extends FF_Addons_Widget_Base
{
    public function get_name(): string
    {
        return 'ff_addons_cta';
    }

    public function get_title(): string
    {
        return __('FF CTA', 'ff-addons');
    }

    public function get_icon(): string
    {
        return 'eicon-call-to-action';
    }

    public function get_keywords(): array
    {
        return ['facefood', 'cta', 'order'];
    }

    protected function register_controls(): void
    {
        $this->start_controls_section('content', [
            'label' => __('Content', 'ff-addons'),
        ]);

        $this->add_control('title', [
            'label' => __('Title', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Order Facefood', 'ff-addons'),
            'label_block' => true,
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('text', [
            'label' => __('Text', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => __('Fresh meals delivered fast. Open the app or shop online.', 'ff-addons'),
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('button', [
            'label' => __('Button label', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Order now', 'ff-addons'),
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('url', [
            'label' => __('Button URL', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::URL,
            'default' => [
                'url' => home_url('/'),
            ],
            'dynamic' => ['active' => true],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('style_cta', [
            'label' => __('CTA', 'ff-addons'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'cta_bg',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .ff-addons-cta',
            ]
        );

        $this->add_control('cta_title_color', [
            'label' => __('Title color', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-cta__title' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('cta_text_color', [
            'label' => __('Text color', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-cta__text' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('cta_btn_bg', [
            'label' => __('Button background', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-cta__button' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('cta_btn_color', [
            'label' => __('Button text', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-cta__button' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_responsive_control('cta_padding', [
            'label' => __('Padding', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em', '%'],
            'selectors' => [
                '{{WRAPPER}} .ff-addons-cta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();
    }

    protected function render(): void
    {
        $s = $this->get_settings_for_display();
        $url = is_array($s['url'] ?? null) ? (string) ($s['url']['url'] ?? home_url('/')) : home_url('/');
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo FF_Addons_Plugin::instance()->render_cta([
            'title' => (string) ($s['title'] ?? ''),
            'text' => (string) ($s['text'] ?? ''),
            'button' => (string) ($s['button'] ?? ''),
            'url' => $url,
        ]);
    }
}
