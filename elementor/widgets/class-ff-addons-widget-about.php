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
        return ['ff-addons-about', 'ff-addons-responsive'];
    }

    protected function register_controls(): void
    {
        $this->start_controls_section('content', [
            'label' => __('Content', 'ff-addons'),
        ]);

        $this->add_control('title', [
            'label' => __('Title', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('About Us', 'ff-addons'),
            'label_block' => true,
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('description', [
            'label' => __('Description', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::WYSIWYG,
            'default' => '<p>' . esc_html__('Add a short introduction about your business, team, or story.', 'ff-addons') . '</p>',
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
            'default' => __('About Us', 'ff-addons'),
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('primary_button', [
            'label' => __('Primary button label', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Learn more', 'ff-addons'),
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('primary_url', [
            'label' => __('Primary button URL', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::URL,
            'default' => [
                'url' => home_url('/'),
            ],
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('secondary_button', [
            'label' => __('Secondary button label', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Contact us', 'ff-addons'),
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('secondary_url', [
            'label' => __('Secondary button URL', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::URL,
            'default' => [
                'url' => home_url('/'),
            ],
            'dynamic' => ['active' => true],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('style_about', [
            'label' => __('About', 'ff-addons'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('title_color', [
            'label' => __('Title color', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-about__title' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('text_color', [
            'label' => __('Text color', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-about' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('primary_btn_bg', [
            'label' => __('Primary button background', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-about__btn--primary' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('primary_btn_color', [
            'label' => __('Primary button text', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-about__btn--primary' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('secondary_btn_color', [
            'label' => __('Secondary button text', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-about__btn--secondary' => 'color: {{VALUE}}; border-color: {{VALUE}};',
            ],
        ]);

        $this->end_controls_section();
    }

    protected function render(): void
    {
        $s = $this->get_settings_for_display();
        $image = is_array($s['image'] ?? null) ? (string) ($s['image']['url'] ?? '') : '';
        $primaryUrl = is_array($s['primary_url'] ?? null) ? (string) ($s['primary_url']['url'] ?? '') : '';
        $secondaryUrl = is_array($s['secondary_url'] ?? null) ? (string) ($s['secondary_url']['url'] ?? '') : '';

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo FF_Addons_Plugin::instance()->render_about([
            'title' => (string) ($s['title'] ?? ''),
            'description' => (string) ($s['description'] ?? ''),
            'image' => $image,
            'image_alt' => (string) ($s['image_alt'] ?? ''),
            'primary_button' => (string) ($s['primary_button'] ?? ''),
            'primary_url' => $primaryUrl,
            'secondary_button' => (string) ($s['secondary_button'] ?? ''),
            'secondary_url' => $secondaryUrl,
        ]);
    }
}
