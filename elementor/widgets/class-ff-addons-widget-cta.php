<?php

if (! defined('ABSPATH')) {
    exit;
}

class FF_Addons_Widget_Cta extends \Elementor\Widget_Base
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

    public function get_categories(): array
    {
        return ['ff-addons'];
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
        ]);

        $this->add_control('text', [
            'label' => __('Text', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => __('Fresh meals delivered fast. Open the app or shop online.', 'ff-addons'),
        ]);

        $this->add_control('button', [
            'label' => __('Button label', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Order now', 'ff-addons'),
        ]);

        $this->add_control('url', [
            'label' => __('Button URL', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::URL,
            'default' => [
                'url' => home_url('/'),
            ],
        ]);

        $this->end_controls_section();
    }

    protected function render(): void
    {
        $s = $this->get_settings_for_display();
        $url = is_array($s['url'] ?? null) ? (string) ($s['url']['url'] ?? home_url('/')) : home_url('/');
        echo FF_Addons_Plugin::instance()->render_cta(
            (string) ($s['title'] ?? ''),
            (string) ($s['text'] ?? ''),
            (string) ($s['button'] ?? ''),
            $url
        );
    }
}
