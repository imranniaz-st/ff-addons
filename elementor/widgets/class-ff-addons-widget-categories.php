<?php

if (! defined('ABSPATH')) {
    exit;
}

class FF_Addons_Widget_Categories extends FF_Addons_Widget_Base
{
    public function get_name(): string
    {
        return 'ff_addons_categories';
    }

    public function get_title(): string
    {
        return __('FF Categories', 'ff-addons');
    }

    public function get_icon(): string
    {
        return 'eicon-gallery-grid';
    }

    public function get_keywords(): array
    {
        return ['facefood', 'category', 'categories', 'menu', 'shawarma', 'burgers'];
    }

    protected function register_controls(): void
    {
        $catOptions = FF_Addons_Helpers::category_options(false);

        $this->start_controls_section('content', [
            'label' => __('Categories', 'ff-addons'),
        ]);

        $this->add_control('layout', [
            'label' => __('Layout', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'grid',
            'options' => [
                'grid' => __('Grid', 'ff-addons'),
                'bar' => __('Horizontal bar', 'ff-addons'),
            ],
        ]);

        $this->add_control('categories', [
            'label' => __('Show categories', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::SELECT2,
            'options' => $catOptions,
            'multiple' => true,
            'label_block' => true,
            'default' => array_keys($catOptions),
            'description' => __('Leave all selected to show every Facefood category. Deselect to hide some.', 'ff-addons'),
        ]);

        $this->add_control('columns', [
            'label' => __('Columns (grid)', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => '6',
            'options' => [
                '3' => '3',
                '4' => '4',
                '5' => '5',
                '6' => '6',
                '7' => '7',
                '8' => '8',
            ],
            'condition' => [
                'layout' => 'grid',
            ],
        ]);

        $this->add_control('show_count', [
            'label' => __('Show product count', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes',
            'default' => 'yes',
        ]);

        $this->add_control('link_base', [
            'label' => __('Link base URL (optional)', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::URL,
            'placeholder' => 'https://facefood.cafe/product-category/',
            'description' => __('Category slug is appended. Leave empty for in-page anchors (#ff-cat-slug).', 'ff-addons'),
            'show_external' => false,
        ]);

        $this->end_controls_section();

        $this->start_controls_section('style_cats', [
            'label' => __('Category tiles', 'ff-addons'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('cat_bg', [
            'label' => __('Background', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-cat' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('cat_title_color', [
            'label' => __('Title color', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-cat__title' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'cat_title_typo',
                'selector' => '{{WRAPPER}} .ff-addons-cat__title',
            ]
        );

        $this->add_responsive_control('cat_radius', [
            'label' => __('Border radius', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range' => ['px' => ['min' => 0, 'max' => 40]],
            'selectors' => [
                '{{WRAPPER}} .ff-addons-cat' => 'border-radius: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('cat_gap', [
            'label' => __('Gap', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range' => ['px' => ['min' => 0, 'max' => 48]],
            'selectors' => [
                '{{WRAPPER}} .ff-addons-categories' => 'gap: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();
    }

    protected function render(): void
    {
        $s = $this->get_settings_for_display();
        $link = '';
        if (is_array($s['link_base'] ?? null) && ! empty($s['link_base']['url'])) {
            $link = (string) $s['link_base']['url'];
        }

        $selected = $s['categories'] ?? [];
        if (! is_array($selected)) {
            $selected = [];
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo FF_Addons_Plugin::instance()->render_categories([
            'layout' => (string) ($s['layout'] ?? 'grid'),
            'columns' => (int) ($s['columns'] ?? 6),
            'link_base' => $link,
            'show_count' => ($s['show_count'] ?? 'yes') === 'yes',
            'categories' => $selected,
        ]);
    }
}
