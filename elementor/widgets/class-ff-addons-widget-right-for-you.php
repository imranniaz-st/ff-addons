<?php

if (! defined('ABSPATH')) {
    exit;
}

class FF_Addons_Widget_Right_For_You extends FF_Addons_Widget_Base
{
    public function get_name(): string
    {
        return 'ff_addons_right_for_you';
    }

    public function get_title(): string
    {
        return __('FF Right For You', 'ff-addons');
    }

    public function get_icon(): string
    {
        return 'eicon-image-box';
    }

    public function get_keywords(): array
    {
        return [
            'right for you',
            'rightforyou',
            'article',
            'events',
            'image text',
            'cta',
            'ff',
            'ff right for you',
            'ff addons',
            'general',
        ];
    }

    protected function ff_addon_categories(): array
    {
        return ['ff-addons-general'];
    }

    public function get_style_depends(): array
    {
        return ['ff-addons-fonts-right-for-you', 'ff-addons-base', 'ff-addons-right-for-you', 'ff-addons-responsive'];
    }

    protected function register_controls(): void
    {
        $this->start_controls_section('content', [
            'label' => __('Content', 'ff-addons'),
        ]);

        $this->add_control('title', [
            'label' => __('Title', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('events', 'ff-addons'),
            'label_block' => true,
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('text', [
            'label' => __('Text', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => __('From poolside parties to exclusive retreats, bring premium wellness directly to your event. Whether it’s IV therapy, vitamin boosts, or beauty treatments, our experienced team ensures every guest feels their best – without compromising on safety or medical integrity. All of our Registered Nurses are licensed, highly trained, and bring critical care expertise from ER and ICU backgrounds. That means you get festival-level fun with hospital-level safety.', 'ff-addons'),
            'rows' => 6,
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('text_secondary', [
            'label' => __('Secondary text', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => __('Let us help elevate your event with wellness services your guests will remember, and thank you for.', 'ff-addons'),
            'rows' => 3,
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('button_text', [
            'label' => __('Button text', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('BOOK MY EVENT', 'ff-addons'),
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('button_url', [
            'label' => __('Button URL', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::URL,
            'default' => [
                'url' => '#',
            ],
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('image', [
            'label' => __('Image', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::MEDIA,
            'default' => [
                'url' => '',
            ],
        ]);

        $this->add_control('image_alt', [
            'label' => __('Image alt text', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('events', 'ff-addons'),
            'dynamic' => ['active' => true],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('style_right_for_you', [
            'label' => __('Right For You', 'ff-addons'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('title_color', [
            'label' => __('Title color', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-right-for-you__title' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('text_color', [
            'label' => __('Body text color', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-right-for-you__text' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('button_color', [
            'label' => __('Button text / border', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-right-for-you__button' => 'color: {{VALUE}}; border-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('button_hover_color', [
            'label' => __('Button hover color', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-right-for-you__button:hover' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_responsive_control('section_padding', [
            'label' => __('Padding', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em', '%'],
            'selectors' => [
                '{{WRAPPER}} .ff-addons-right-for-you' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();
    }

    protected function render(): void
    {
        $s = $this->get_settings_for_display();
        $image = is_array($s['image'] ?? null) ? (string) ($s['image']['url'] ?? '') : '';
        $buttonUrl = is_array($s['button_url'] ?? null) ? $s['button_url'] : [];
        if (! is_array($buttonUrl)) {
            $buttonUrl = [];
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo FF_Addons_Plugin::instance()->render_right_for_you([
            'title' => (string) ($s['title'] ?? ''),
            'text' => (string) ($s['text'] ?? ''),
            'text_secondary' => (string) ($s['text_secondary'] ?? ''),
            'button_text' => (string) ($s['button_text'] ?? ''),
            'button_url' => (string) ($buttonUrl['url'] ?? ''),
            'button_external' => ! empty($buttonUrl['is_external']),
            'button_nofollow' => ! empty($buttonUrl['nofollow']),
            'image' => $image,
            'image_alt' => (string) ($s['image_alt'] ?? ''),
        ]);
    }
}
