<?php

if (! defined('ABSPATH')) {
    exit;
}

class FF_Addons_Plugin
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public static function activate(): void
    {
        // API base URL is optional — configure in Settings → FF Addons when needed.
        if (get_option('ff_addons_api_base_url') === false) {
            add_option('ff_addons_api_base_url', '');
        }
        if (get_option('ff_addons_cache_ttl') === false) {
            add_option('ff_addons_cache_ttl', 300);
        }
        if (get_option('ff_addons_currency') === false) {
            add_option('ff_addons_currency', 'Rs.');
        }
    }

    public function init(): void
    {
        load_plugin_textdomain('ff-addons', false, dirname(FF_ADDONS_BASENAME) . '/languages');

        if (is_admin()) {
            new FF_Addons_Admin();
        }

        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
        add_action('elementor/preview/enqueue_styles', [$this, 'enqueue_editor_assets']);
        add_action('elementor/editor/after_enqueue_styles', [$this, 'enqueue_editor_assets']);

        add_shortcode('ff_addons_menu', [$this, 'shortcode_menu']);
        add_shortcode('ff_addons_deals', [$this, 'shortcode_deals']);
        add_shortcode('ff_addons_cta', [$this, 'shortcode_cta']);
        add_shortcode('ff_addons_categories', [$this, 'shortcode_categories']);
        add_shortcode('ff_addons_about', [$this, 'shortcode_about']);

        // Elementor may fire elementor/loaded before this plugin hooks it
        // (plugin load order). Boot immediately if already loaded.
        $boot_elementor = static function (): void {
            if (! class_exists('\\Elementor\\Plugin')) {
                return;
            }
            require_once FF_ADDONS_DIR . 'elementor/class-ff-addons-elementor.php';
            new FF_Addons_Elementor();
        };
        if (did_action('elementor/loaded')) {
            $boot_elementor();
        } else {
            add_action('elementor/loaded', $boot_elementor);
        }

        add_action('vc_before_init', static function (): void {
            require_once FF_ADDONS_DIR . 'wpbakery/class-ff-addons-wpbakery.php';
            FF_Addons_WPBakery::register();
        });
    }

    public function register_assets(): void
    {
        $base = FF_ADDONS_URL . 'assets/css/';
        $ver = FF_ADDONS_VERSION;

        wp_register_style(
            'ff-addons-fonts-about',
            'https://fonts.googleapis.com/css2?family=Kanit:wght@600;800&family=Montserrat:wght@400;600;700;800&display=swap',
            [],
            null
        );
        wp_register_style('ff-addons-base', $base . 'base.css', [], $ver);
        wp_register_style('ff-addons-deals', $base . 'deals.css', ['ff-addons-base'], $ver);
        wp_register_style('ff-addons-categories', $base . 'categories.css', ['ff-addons-base'], $ver);
        wp_register_style('ff-addons-cta', $base . 'cta.css', ['ff-addons-base'], $ver);
        wp_register_style('ff-addons-about', $base . 'about.css', ['ff-addons-base', 'ff-addons-fonts-about'], $ver);
        // Enqueued after component handles so breakpoint rules win.
        wp_register_style('ff-addons-responsive', $base . 'responsive.css', ['ff-addons-base'], $ver);
        wp_register_style('ff-addons-editor', $base . 'editor.css', [], $ver);
    }

    /** Elementor editor / preview: load all component styles. */
    public function enqueue_editor_assets(): void
    {
        $this->register_assets();
        wp_enqueue_style('ff-addons-fonts-about');
        wp_enqueue_style('ff-addons-base');
        wp_enqueue_style('ff-addons-deals');
        wp_enqueue_style('ff-addons-categories');
        wp_enqueue_style('ff-addons-cta');
        wp_enqueue_style('ff-addons-about');
        wp_enqueue_style('ff-addons-responsive');
        wp_enqueue_style('ff-addons-editor');
    }

    /** @param list<string> $handles */
    private function enqueue_styles(array $handles): void
    {
        $this->register_assets();
        foreach ($handles as $handle) {
            wp_enqueue_style($handle);
        }
        wp_enqueue_style('ff-addons-responsive');
    }

    /**
     * @param  array<string, string>|string  $atts
     */
    public function shortcode_menu($atts = []): string
    {
        $atts = shortcode_atts([
            'limit' => '8',
            'category' => '',
            'columns' => '4',
            'show_description' => 'yes',
        ], $atts, 'ff_addons_menu');

        return $this->render_menu([
            'limit' => (int) $atts['limit'],
            'category' => (string) $atts['category'],
            'columns' => (int) $atts['columns'],
            'show_description' => $atts['show_description'] !== 'no',
        ]);
    }

    /**
     * @param  array<string, string>|string  $atts
     */
    public function shortcode_deals($atts = []): string
    {
        $atts = shortcode_atts([
            'limit' => '6',
            'columns' => '3',
        ], $atts, 'ff_addons_deals');

        return $this->render_deals([
            'limit' => (int) $atts['limit'],
            'columns' => (int) $atts['columns'],
        ]);
    }

    /**
     * @param  array<string, string>|string  $atts
     */
    public function shortcode_cta($atts = []): string
    {
        $atts = shortcode_atts([
            'title' => __('Get Started', 'ff-addons'),
            'text' => __('Add a short call-to-action message here.', 'ff-addons'),
            'button' => __('Learn More', 'ff-addons'),
            'url' => '#',
        ], $atts, 'ff_addons_cta');

        return $this->render_cta([
            'title' => (string) $atts['title'],
            'text' => (string) $atts['text'],
            'button' => (string) $atts['button'],
            'url' => (string) $atts['url'],
        ]);
    }

    /**
     * @param  array<string, string>|string  $atts
     */
    public function shortcode_categories($atts = []): string
    {
        $atts = shortcode_atts([
            'layout' => 'grid',
            'columns' => '6',
            'link_base' => '',
            'show_count' => 'yes',
            'categories' => '',
        ], $atts, 'ff_addons_categories');

        return $this->render_categories([
            'layout' => (string) $atts['layout'],
            'columns' => (int) $atts['columns'],
            'link_base' => (string) $atts['link_base'],
            'show_count' => $atts['show_count'] !== 'no',
            'categories' => (string) $atts['categories'],
        ]);
    }

    /**
     * @param  array<string, string>|string  $atts
     */
    public function shortcode_about($atts = [], ?string $content = null): string
    {
        $atts = shortcode_atts([
            'subtitle' => __('OUR STORY & PASSION', 'ff-addons'),
            'title' => __('Crafting Flavor-Packed Moments In Every Single Bite.', 'ff-addons'),
            'text' => __("We don't just cook food; we craft crave-worthy culinary experiences. From sizzle-hot burgers loaded with premium melted cheese to hand-crafted shawarmas burst with authentic spices, every dish is prepared fresh using 100% natural, top-tier ingredients.", 'ff-addons'),
            'highlight_text' => __('Fast service, hygienic preparation, and unmatched taste — served hot straight to your table or delivered right to your doorstep.', 'ff-addons'),
            'features' => '100% Fresh & Organic|Master Chefs|Fast Doorstep Delivery|Hygienic Kitchen',
            'button_text' => __('Explore Our Story', 'ff-addons'),
            'button_url' => '#menu',
            'badge_number' => __('10+', 'ff-addons'),
            'badge_text' => __('Years Of Excellence', 'ff-addons'),
            'image' => '',
            'image_alt' => __('Our Restaurant Kitchen', 'ff-addons'),
        ], $atts, 'ff_addons_about');

        $text = (string) $atts['text'];
        if (is_string($content) && trim($content) !== '') {
            $text = $content;
        }

        $features = [];
        foreach (array_filter(array_map('trim', explode('|', (string) $atts['features']))) as $label) {
            $features[] = ['icon' => '✓', 'text' => $label];
        }

        return $this->render_about([
            'subtitle' => (string) $atts['subtitle'],
            'title' => (string) $atts['title'],
            'text' => $text,
            'highlight_text' => (string) $atts['highlight_text'],
            'features' => $features,
            'button_text' => (string) $atts['button_text'],
            'button_url' => (string) $atts['button_url'],
            'badge_number' => (string) $atts['badge_number'],
            'badge_text' => (string) $atts['badge_text'],
            'image' => (string) $atts['image'],
            'image_alt' => (string) $atts['image_alt'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $args
     */
    public function render_menu(array $args = []): string
    {
        $this->enqueue_styles(['ff-addons-base']);

        $limit = max(1, min(48, (int) ($args['limit'] ?? 8)));
        $category = (string) ($args['category'] ?? '');
        $columns = max(1, min(6, (int) ($args['columns'] ?? 4)));
        $showDescription = (bool) ($args['show_description'] ?? true);

        $api = new FF_Addons_Api();
        if (FF_Addons_Helpers::is_elementor_edit()) {
            $api->bypass_cache(true);
        }

        $query = ['per_page' => $limit];
        if ($category !== '') {
            $query['category'] = $category;
        }

        $response = $api->get_products($query);
        if (is_wp_error($response)) {
            return $this->render_error($response->get_error_message());
        }

        $items = FF_Addons_Helpers::extract_list($response);
        if ($items === []) {
            return '<p class="ff-addons-empty">' . esc_html__('No products found.', 'ff-addons') . '</p>';
        }

        ob_start();
        echo '<div class="ff-addons-grid ff-addons-menu" style="--ff-cols:' . esc_attr((string) $columns) . '">';
        foreach ($items as $product) {
            $name = (string) ($product['name'] ?? '');
            $price = $product['price'] ?? null;
            $image = (string) ($product['image_url'] ?? '');
            $desc = (string) ($product['description'] ?? '');
            $catName = (string) (($product['category']['name'] ?? '') ?: '');
            echo '<article class="ff-addons-card">';
            if ($image !== '') {
                echo '<div class="ff-addons-card__media"><img src="' . esc_url($image) . '" alt="' . esc_attr($name) . '" loading="lazy"></div>';
            }
            echo '<div class="ff-addons-card__body">';
            if ($catName !== '') {
                echo '<p class="ff-addons-card__meta">' . esc_html($catName) . '</p>';
            }
            echo '<h3 class="ff-addons-card__title">' . esc_html($name) . '</h3>';
            if ($showDescription && $desc !== '') {
                echo '<p class="ff-addons-card__text">' . esc_html(wp_trim_words($desc, 18)) . '</p>';
            }
            $formatted = FF_Addons_Helpers::format_price($price);
            if ($formatted !== '') {
                echo '<p class="ff-addons-card__price">' . esc_html($formatted) . '</p>';
            }
            echo '</div></article>';
        }
        echo '</div>';

        return (string) ob_get_clean();
    }

    /**
     * @param  array<string, mixed>  $args
     */
    public function render_deals(array $args = []): string
    {
        $this->enqueue_styles(['ff-addons-deals']);

        $limit = max(1, min(24, (int) ($args['limit'] ?? 6)));
        $columns = max(1, min(6, (int) ($args['columns'] ?? 3)));

        $api = new FF_Addons_Api();
        if (FF_Addons_Helpers::is_elementor_edit()) {
            $api->bypass_cache(true);
        }

        $response = $api->get_deals(['per_page' => $limit]);
        if (is_wp_error($response)) {
            return $this->render_error($response->get_error_message());
        }

        $items = FF_Addons_Helpers::extract_list($response);
        if ($items === []) {
            return '<p class="ff-addons-empty">' . esc_html__('No deals found.', 'ff-addons') . '</p>';
        }

        ob_start();
        echo '<div class="ff-addons-grid ff-addons-deals" style="--ff-cols:' . esc_attr((string) $columns) . '">';
        foreach ($items as $deal) {
            $title = (string) ($deal['title'] ?? $deal['name'] ?? '');
            $price = $deal['deal_price'] ?? $deal['price'] ?? null;
            $image = (string) ($deal['image_url'] ?? '');
            echo '<article class="ff-addons-card ff-addons-card--deal">';
            if ($image !== '') {
                echo '<div class="ff-addons-card__media"><img src="' . esc_url($image) . '" alt="' . esc_attr($title) . '" loading="lazy"></div>';
            }
            echo '<div class="ff-addons-card__body">';
            echo '<h3 class="ff-addons-card__title">' . esc_html($title) . '</h3>';
            $formatted = FF_Addons_Helpers::format_price($price);
            if ($formatted !== '') {
                echo '<p class="ff-addons-card__price">' . esc_html($formatted) . '</p>';
            }
            echo '</div></article>';
        }
        echo '</div>';

        return (string) ob_get_clean();
    }

    /**
     * @param  array<string, mixed>  $args
     */
    public function render_cta(array $args = []): string
    {
        $this->enqueue_styles(['ff-addons-cta']);

        $title = (string) ($args['title'] ?? __('Get Started', 'ff-addons'));
        $text = (string) ($args['text'] ?? '');
        $button = (string) ($args['button'] ?? __('Learn More', 'ff-addons'));
        $url = (string) ($args['url'] ?? '#');

        ob_start();
        ?>
        <section class="ff-addons-cta">
            <div class="ff-addons-cta__inner">
                <?php if ($title !== '') : ?>
                    <h2 class="ff-addons-cta__title"><?php echo esc_html($title); ?></h2>
                <?php endif; ?>
                <?php if ($text !== '') : ?>
                    <p class="ff-addons-cta__text"><?php echo esc_html($text); ?></p>
                <?php endif; ?>
                <?php if ($button !== '' && $url !== '') : ?>
                    <a class="ff-addons-cta__button" href="<?php echo esc_url($url); ?>"><?php echo esc_html($button); ?></a>
                <?php endif; ?>
            </div>
        </section>
        <?php

        return (string) ob_get_clean();
    }

    /**
     * @param  array<string, mixed>  $args
     */
    public function render_about(array $args = []): string
    {
        $this->enqueue_styles(['ff-addons-about']);

        $subtitle = (string) ($args['subtitle'] ?? '');
        $title = (string) ($args['title'] ?? '');
        $text = (string) ($args['text'] ?? '');
        $highlightText = (string) ($args['highlight_text'] ?? '');
        $features = $args['features'] ?? [];
        if (! is_array($features)) {
            $features = [];
        }
        $buttonText = (string) ($args['button_text'] ?? '');
        $buttonUrl = (string) ($args['button_url'] ?? '');
        $badgeNumber = (string) ($args['badge_number'] ?? '');
        $badgeText = (string) ($args['badge_text'] ?? '');
        $image = (string) ($args['image'] ?? '');
        $imageAlt = (string) ($args['image_alt'] ?? $title);

        ob_start();
        ?>
        <div class="about-section-wrapper">
            <div class="about-container">
                <div class="about-content">
                    <?php if ($subtitle !== '') : ?>
                        <span class="about-subtitle"><?php echo esc_html($subtitle); ?></span>
                    <?php endif; ?>
                    <?php if ($title !== '') : ?>
                        <h2 class="about-title"><?php echo esc_html($title); ?></h2>
                    <?php endif; ?>
                    <?php if ($text !== '') : ?>
                        <p class="about-text"><?php echo esc_html($text); ?></p>
                    <?php endif; ?>
                    <?php if ($highlightText !== '') : ?>
                        <p class="about-text highlight-text"><?php echo esc_html($highlightText); ?></p>
                    <?php endif; ?>

                    <?php if ($features !== []) : ?>
                        <div class="about-features">
                            <?php foreach ($features as $feature) :
                                if (! is_array($feature)) {
                                    continue;
                                }
                                $icon = (string) ($feature['icon'] ?? '✓');
                                $featureText = (string) ($feature['text'] ?? '');
                                if ($featureText === '') {
                                    continue;
                                }
                                ?>
                                <div class="feature-item">
                                    <div class="feature-icon"><?php echo esc_html($icon !== '' ? $icon : '✓'); ?></div>
                                    <span><?php echo esc_html($featureText); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($buttonText !== '' && $buttonUrl !== '') : ?>
                        <a href="<?php echo esc_url($buttonUrl); ?>" class="about-btn"><?php echo esc_html($buttonText); ?></a>
                    <?php endif; ?>
                </div>

                <div class="about-image-wrapper">
                    <div class="image-frame">
                        <?php if ($image !== '') : ?>
                            <img
                                src="<?php echo esc_url($image); ?>"
                                alt="<?php echo esc_attr($imageAlt !== '' ? $imageAlt : $title); ?>"
                                loading="lazy"
                            >
                        <?php else : ?>
                            <span class="about-image-placeholder" aria-hidden="true"></span>
                        <?php endif; ?>

                        <?php if ($badgeNumber !== '' || $badgeText !== '') : ?>
                            <div class="exp-badge">
                                <?php if ($badgeNumber !== '') : ?>
                                    <span class="exp-number"><?php echo esc_html($badgeNumber); ?></span>
                                <?php endif; ?>
                                <?php if ($badgeText !== '') : ?>
                                    <span class="exp-text"><?php echo esc_html($badgeText); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    /**
     * @param  array<string, mixed>  $args
     */
    public function render_categories(array $args = []): string
    {
        $this->enqueue_styles(['ff-addons-categories']);

        $layout = (string) ($args['layout'] ?? 'grid');
        $columns = max(2, min(8, (int) ($args['columns'] ?? 6)));
        $linkBase = (string) ($args['link_base'] ?? '');
        $showCount = (bool) ($args['show_count'] ?? true);
        $selected = $args['categories'] ?? [];
        if (is_string($selected) && $selected !== '') {
            $selected = array_filter(array_map('trim', explode(',', $selected)));
        }
        if (! is_array($selected)) {
            $selected = [];
        }
        $selected = array_values(array_filter(array_map('strval', $selected)));

        $api = new FF_Addons_Api();
        if (FF_Addons_Helpers::is_elementor_edit()) {
            $api->bypass_cache(true);
        }

        $response = $api->get_categories();
        if (is_wp_error($response)) {
            return $this->render_error($response->get_error_message());
        }

        $items = FF_Addons_Helpers::extract_list($response);
        if ($items === []) {
            // Still show known Facefood categories so Elementor is editable offline.
            foreach (FF_Addons_Helpers::fallback_categories() as $slug => $name) {
                $items[] = [
                    'slug' => $slug,
                    'name' => $name,
                    'image_url' => '',
                    'products_count' => null,
                ];
            }
        }

        usort($items, static function (array $a, array $b): int {
            return ((int) ($a['sort_order'] ?? 0)) <=> ((int) ($b['sort_order'] ?? 0));
        });

        if ($selected !== []) {
            $items = array_values(array_filter(
                $items,
                static fn (array $cat): bool => in_array((string) ($cat['slug'] ?? ''), $selected, true)
            ));
        }

        $mod = $layout === 'bar' ? 'ff-addons-categories--bar' : 'ff-addons-categories--grid';

        ob_start();
        echo '<div class="ff-addons-categories ' . esc_attr($mod) . '" style="--ff-cols:' . esc_attr((string) $columns) . '">';
        foreach ($items as $cat) {
            $slug = (string) ($cat['slug'] ?? '');
            $name = (string) ($cat['name'] ?? $slug);
            $image = (string) ($cat['image_url'] ?? '');
            $count = $cat['products_count'] ?? null;
            $href = $linkBase !== ''
                ? trailingslashit($linkBase) . rawurlencode($slug)
                : '#ff-cat-' . rawurlencode($slug);

            echo '<a class="ff-addons-cat" href="' . esc_url($href) . '" data-category="' . esc_attr($slug) . '">';
            if ($image !== '') {
                echo '<span class="ff-addons-cat__media"><img src="' . esc_url($image) . '" alt="' . esc_attr($name) . '" loading="lazy"></span>';
            } else {
                echo '<span class="ff-addons-cat__media ff-addons-cat__media--placeholder" aria-hidden="true"></span>';
            }
            echo '<span class="ff-addons-cat__body">';
            echo '<span class="ff-addons-cat__title">' . esc_html($name) . '</span>';
            if ($showCount && $count !== null && $count !== '') {
                echo '<span class="ff-addons-cat__count">' . esc_html(sprintf(
                    /* translators: %d: product count */
                    _n('%d item', '%d items', (int) $count, 'ff-addons'),
                    (int) $count
                )) . '</span>';
            }
            echo '</span></a>';
        }
        echo '</div>';

        return (string) ob_get_clean();
    }

    private function render_error(string $message): string
    {
        $hint = FF_Addons_Helpers::is_elementor_edit()
            ? ' ' . __('Check Settings → FF Addons API URL.', 'ff-addons')
            : '';

        return '<p class="ff-addons-error">' . esc_html($message . $hint) . '</p>';
    }
}
