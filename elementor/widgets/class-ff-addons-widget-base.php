<?php

if (! defined('ABSPATH')) {
    exit;
}

abstract class FF_Addons_Widget_Base extends \Elementor\Widget_Base
{
    /**
     * Always under FF Addons + Elementor General; subclasses add Menu / Offers / General.
     *
     * @return list<string>
     */
    public function get_categories(): array
    {
        return array_values(array_unique(array_merge(
            ['ff-addons', 'general'],
            $this->ff_addon_categories()
        )));
    }

    /**
     * Extra FF Addons panel groups for this widget.
     *
     * @return list<string>
     */
    protected function ff_addon_categories(): array
    {
        return ['ff-addons-general'];
    }

    public function get_style_depends(): array
    {
        return ['ff-addons-frontend'];
    }

    protected function add_columns_control(string $default = '4'): void
    {
        $this->add_control('columns', [
            'label' => __('Columns', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => $default,
            'options' => [
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
                '6' => '6',
                '7' => '7',
                '8' => '8',
            ],
        ]);
    }

    protected function add_card_style_section(): void
    {
        $this->start_controls_section('style_card', [
            'label' => __('Cards', 'ff-addons'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('card_bg', [
            'label' => __('Background', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-card' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('title_color', [
            'label' => __('Title color', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .ff-addons-card__title' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('price_color', [
            'label' => __('Price color', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#00AEEF',
            'selectors' => [
                '{{WRAPPER}} .ff-addons-card__price' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typo',
                'selector' => '{{WRAPPER}} .ff-addons-card__title',
            ]
        );

        $this->add_responsive_control('card_radius', [
            'label' => __('Border radius', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range' => ['px' => ['min' => 0, 'max' => 40]],
            'selectors' => [
                '{{WRAPPER}} .ff-addons-card' => 'border-radius: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('gap', [
            'label' => __('Gap', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range' => ['px' => ['min' => 0, 'max' => 48]],
            'selectors' => [
                '{{WRAPPER}} .ff-addons-grid' => 'gap: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();
    }
}
