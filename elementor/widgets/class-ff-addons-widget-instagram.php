<?php

if (! defined('ABSPATH')) {
    exit;
}

class FF_Addons_Widget_Instagram extends FF_Addons_Widget_Base
{
    public function get_name(): string
    {
        return 'ff_addons_instagram';
    }

    public function get_title(): string
    {
        return __('FF Instagram', 'ff-addons');
    }

    public function get_icon(): string
    {
        return 'eicon-instagram-gallery';
    }

    public function get_keywords(): array
    {
        return ['instagram', 'ig', 'gallery', 'social', 'feed', 'ff', 'ff instagram', 'ff addons', 'general'];
    }

    protected function ff_addon_categories(): array
    {
        return ['ff-addons-general'];
    }

    public function get_style_depends(): array
    {
        return ['ff-addons-fonts-about', 'ff-addons-base', 'ff-addons-instagram', 'ff-addons-responsive'];
    }

    public function get_script_depends(): array
    {
        return ['ff-addons-instagram'];
    }

    protected function register_controls(): void
    {
        $this->start_controls_section('content', [
            'label' => __('Content', 'ff-addons'),
        ]);

        $this->add_control('subtitle', [
            'label' => __('Subtitle', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('FOLLOW US', 'ff-addons'),
            'label_block' => true,
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('title', [
            'label' => __('Title', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Catch The Flavor On Instagram', 'ff-addons'),
            'label_block' => true,
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('handle', [
            'label' => __('Handle', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => '@facefood_pwd_',
            'label_block' => true,
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('profile_url', [
            'label' => __('Profile URL', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::URL,
            'default' => [
                'url' => 'https://www.instagram.com/facefood_pwd_/',
                'is_external' => true,
                'nofollow' => true,
            ],
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('source', [
            'label' => __('Source', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'api',
            'options' => [
                'api' => __('Auto from Instagram (token)', 'ff-addons'),
                'curated' => __('Curated Media Library', 'ff-addons'),
            ],
            'description' => __('Auto mode fetches recent posts via the Instagram Graph API and renders them as custom gallery cards (no Instagram embeds). Updates refresh after Cache TTL.', 'ff-addons'),
        ]);

        $this->add_control('api_limit', [
            'label' => __('Media limit', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::NUMBER,
            'default' => 8,
            'min' => 1,
            'max' => 24,
            'condition' => ['source' => 'api'],
        ]);

        $this->add_control('api_hint', [
            'type' => \Elementor\Controls_Manager::RAW_HTML,
            'condition' => ['source' => 'api'],
            'raw' => '<p style="margin:0;">' . esc_html__('Paste a long-lived Instagram access token under Settings → FF Addons. Thumbnails, captions, and permalinks come from the API and display in this plugin’s own grid.', 'ff-addons') . '</p>',
        ]);

        $this->add_control('items', [
            'label' => __('Gallery items', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::REPEATER,
            'condition' => ['source' => 'curated'],
            'fields' => [
                [
                    'name' => 'image',
                    'label' => __('Image', 'ff-addons'),
                    'type' => \Elementor\Controls_Manager::MEDIA,
                    'default' => ['url' => ''],
                ],
                [
                    'name' => 'caption',
                    'label' => __('Caption', 'ff-addons'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => '',
                    'label_block' => true,
                ],
                [
                    'name' => 'link',
                    'label' => __('Link', 'ff-addons'),
                    'type' => \Elementor\Controls_Manager::URL,
                    'default' => ['url' => ''],
                    'placeholder' => __('Instagram post URL (optional)', 'ff-addons'),
                ],
            ],
            'default' => [],
            'title_field' => '{{{ caption || "Image" }}}',
        ]);

        $this->add_columns_control('4');

        $this->add_control('button_text', [
            'label' => __('Button text', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Follow on Instagram', 'ff-addons'),
            'dynamic' => ['active' => true],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('style_instagram', [
            'label' => __('Instagram', 'ff-addons'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('accent_color', [
            'label' => __('Accent color', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .instagram-subtitle' => 'color: {{VALUE}};',
                '{{WRAPPER}} .instagram-handle' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('title_color', [
            'label' => __('Title color', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .instagram-title' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_responsive_control('gap', [
            'label' => __('Grid gap', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range' => ['px' => ['min' => 0, 'max' => 48]],
            'selectors' => [
                '{{WRAPPER}} .instagram-grid' => 'gap: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('item_radius', [
            'label' => __('Item radius', 'ff-addons'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range' => ['px' => ['min' => 0, 'max' => 40]],
            'selectors' => [
                '{{WRAPPER}} .instagram-item' => 'border-radius: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();
    }

    protected function render(): void
    {
        $s = $this->get_settings_for_display();
        $profile = is_array($s['profile_url'] ?? null) ? $s['profile_url'] : [];
        $profileUrl = (string) ($profile['url'] ?? '');
        $profileExternal = ! empty($profile['is_external']);
        $profileNofollow = ! empty($profile['nofollow']);

        $items = [];
        $rawItems = is_array($s['items'] ?? null) ? $s['items'] : [];
        foreach ($rawItems as $row) {
            if (! is_array($row)) {
                continue;
            }
            $image = is_array($row['image'] ?? null) ? (string) ($row['image']['url'] ?? '') : '';
            if ($image === '') {
                continue;
            }
            $link = is_array($row['link'] ?? null) ? $row['link'] : [];
            $items[] = [
                'image' => $image,
                'caption' => (string) ($row['caption'] ?? ''),
                'link' => (string) ($link['url'] ?? ''),
                'is_external' => ! empty($link['is_external']),
                'nofollow' => ! empty($link['nofollow']),
            ];
        }

        $source = (string) ($s['source'] ?? '');
        // Legacy "posts" (embeds) maps to auto API feed; older curated-only widgets keep curated when items exist.
        if ($source === 'posts') {
            $source = 'api';
        }
        if (! in_array($source, ['curated', 'api'], true)) {
            $token = trim((string) get_option('ff_addons_instagram_token', ''));
            if ($items !== []) {
                $source = 'curated';
            } elseif ($token !== '') {
                $source = 'api';
            } else {
                $source = 'api';
            }
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo FF_Addons_Plugin::instance()->render_instagram([
            'subtitle' => (string) ($s['subtitle'] ?? ''),
            'title' => (string) ($s['title'] ?? ''),
            'handle' => (string) ($s['handle'] ?? ''),
            'profile_url' => $profileUrl,
            'profile_external' => $profileExternal,
            'profile_nofollow' => $profileNofollow,
            'source' => $source,
            'items' => $items,
            'columns' => (int) ($s['columns'] ?? 4),
            'limit' => (int) ($s['api_limit'] ?? 8),
            'button_text' => (string) ($s['button_text'] ?? ''),
        ]);
    }
}
