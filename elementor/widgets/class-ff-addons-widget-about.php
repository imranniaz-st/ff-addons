<?php

if (! defined('ABSPATH')) {
    exit;
}

class FF_Addons_Widget_About extends FF_Addons_Widget_Base
{
    public function get_name(): string
    {
        return 'ff_addons_about';
    }

    public function get_title(): string
    {
        return __('FF About Us', 'ff-addons');
    }

    public function get_icon(): string
    {
        return 'eicon-person';
    }

    public function get_keywords(): array
    {
        return ['about', 'about us', 'aboutus', 'team', 'story', 'ff', 'ff about', 'ff addons', 'general'];
    }

    protected function ff_addon_categories(): array
    {
        return ['ff-addons-general'];
    }

    public function get_style_depends(): array
    {
        return ['ff-addons-fonts-about', 'ff-addons-base', 'ff-addons-about', 'ff-addons-responsive'];
    }

    protected function register_controls(): void
    {
        $this->start_controls_section('content', [
            'label' => __('Content', 'ff-addons'),
        ]);

        $this->add_control('subtitle', [
            'label' => __('Subtitle', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('OUR STORY & PASSION', 'ff-addons'),
            'label_block' => true,
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('title', [
            'label' => __('Title', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => __('Crafting Flavor-Packed Moments In Every Single Bite.', 'ff-addons'),
            'label_block' => true,
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('text', [
            'label' => __('Text', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => __("We don't just cook food; we craft crave-worthy culinary experiences. From sizzle-hot burgers loaded with premium melted cheese to hand-crafted shawarmas burst with authentic spices, every dish is prepared fresh using 100% natural, top-tier ingredients.", 'ff-addons'),
            'rows' => 5,
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('highlight_text', [
            'label' => __('Highlight text', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => __('Fast service, hygienic preparation, and unmatched taste — served hot straight to your table or delivered right to your doorstep.', 'ff-addons'),
            'rows' => 3,
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('features', [
            'label' => __('Features', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => [
                [
                    'name' => 'icon',
                    'label' => __('Icon', 'ff-addons'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => '✓',
                ],
                [
                    'name' => 'text',
                    'label' => __('Text', 'ff-addons'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => '',
                    'label_block' => true,
                ],
            ],
            'default' => [
                ['icon' => '✓', 'text' => __('100% Fresh & Organic', 'ff-addons')],
                ['icon' => '✓', 'text' => __('Master Chefs', 'ff-addons')],
                ['icon' => '✓', 'text' => __('Fast Doorstep Delivery', 'ff-addons')],
                ['icon' => '✓', 'text' => __('Hygienic Kitchen', 'ff-addons')],
            ],
            'title_field' => '{{{ text }}}',
        ]);

        $this->add_control('button_text', [
            'label' => __('Button text', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Explore Our Story', 'ff-addons'),
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('button_url', [
            'label' => __('Button URL', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::URL,
            'default' => [
                'url' => '#menu',
            ],
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('badge_number', [
            'label' => __('Badge number', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('10+', 'ff-addons'),
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('badge_text', [
            'label' => __('Badge text', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Years Of Excellence', 'ff-addons'),
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
            'default' => __('Our Restaurant Kitchen', 'ff-addons'),
            'dynamic' => ['active' => true],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('style_about', [
            'label' => __('About', 'ff-addons'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('accent_color', [
            'label' => __('Accent color', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-about .about-subtitle' => 'color: {{VALUE}};',
                '{{WRAPPER}} .ff-addons-about .feature-icon' => 'color: {{VALUE}}; border-color: {{VALUE}}; background-color: color-mix(in srgb, {{VALUE}} 15%, transparent);',
                '{{WRAPPER}} .ff-addons-about .about-btn' => 'background-color: {{VALUE}}; box-shadow: 0 4px 15px color-mix(in srgb, {{VALUE}} 30%, transparent);',
                '{{WRAPPER}} .ff-addons-about .image-frame::before' => 'border-color: {{VALUE}};',
                '{{WRAPPER}} .ff-addons-about .exp-badge' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('title_color', [
            'label' => __('Title color', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-about .about-title' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('text_color', [
            'label' => __('Body text color', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-about .about-text:not(.highlight-text)' => 'color: {{VALUE}};',
            ],
        ]);

        $this->end_controls_section();
    }

    protected function render(): void
    {
        $s = $this->get_settings_for_display();
        $image = is_array($s['image'] ?? null) ? (string) ($s['image']['url'] ?? '') : '';
        $buttonUrl = is_array($s['button_url'] ?? null) ? (string) ($s['button_url']['url'] ?? '') : '';
        $features = is_array($s['features'] ?? null) ? $s['features'] : [];

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo FF_Addons_Plugin::instance()->render_about([
            'subtitle' => (string) ($s['subtitle'] ?? ''),
            'title' => (string) ($s['title'] ?? ''),
            'text' => (string) ($s['text'] ?? ''),
            'highlight_text' => (string) ($s['highlight_text'] ?? ''),
            'features' => $features,
            'button_text' => (string) ($s['button_text'] ?? ''),
            'button_url' => $buttonUrl,
            'badge_number' => (string) ($s['badge_number'] ?? ''),
            'badge_text' => (string) ($s['badge_text'] ?? ''),
            'image' => $image,
            'image_alt' => (string) ($s['image_alt'] ?? ''),
        ]);
    }
}
