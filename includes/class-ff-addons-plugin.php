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

        add_action('elementor/loaded', static function (): void {
            require_once FF_ADDONS_DIR . 'elementor/class-ff-addons-elementor.php';
            new FF_Addons_Elementor();
        });

        add_action('vc_before_init', static function (): void {
            require_once FF_ADDONS_DIR . 'wpbakery/class-ff-addons-wpbakery.php';
            FF_Addons_WPBakery::register();
        });
    }

    public function register_assets(): void
    {
        $base = FF_ADDONS_URL . 'assets/css/';
        $ver = FF_ADDONS_VERSION;

        wp_register_style('ff-addons-base', $base . 'base.css', [], $ver);
        wp_register_style('ff-addons-deals', $base . 'deals.css', ['ff-addons-base'], $ver);
        wp_register_style('ff-addons-categories', $base . 'categories.css', ['ff-addons-base'], $ver);
        wp_register_style('ff-addons-cta', $base . 'cta.css', ['ff-addons-base'], $ver);
        wp_register_style('ff-addons-about', $base . 'about.css', ['ff-addons-base'], $ver);
        // After components so breakpoint rules win over base layouts.
        wp_register_style('ff-addons-responsive', $base . 'responsive.css', ['ff-addons-base'], $ver);
        wp_register_style('ff-addons-editor', $base . 'editor.css', [], $ver);
    }

    /** Elementor editor / preview: load all component styles. */
    public function enqueue_editor_assets(): void
    {
        $this->register_assets();
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
            'title' => __('Order Facefood', 'ff-addons'),
            'text' => __('Fresh meals delivered fast. Open the app or shop online.', 'ff-addons'),
            'button' => __('Order now', 'ff-addons'),
            'url' => home_url('/'),
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
            'title' => __('About Us', 'ff-addons'),
            'description' => '',
            'image' => 'https://driphydration.com/wp-content/uploads/2025/05/Our-Nurses.png',
            'image_alt' => __('About Us', 'ff-addons'),
            'primary_button' => __('BOOK IV THERAPY', 'ff-addons'),
            'primary_url' => 'https://driphydration.com/iv-therapy/',
            'secondary_button' => __('VIEW PLANS', 'ff-addons'),
            'secondary_url' => 'https://driphydration.com/vitamin-drip-membership/',
        ], $atts, 'ff_addons_about');

        $description = (string) $atts['description'];
        if (is_string($content) && trim($content) !== '') {
            $description = $content;
        }

        return $this->render_about([
            'title' => (string) $atts['title'],
            'description' => $description,
            'image' => (string) $atts['image'],
            'image_alt' => (string) $atts['image_alt'],
            'primary_button' => (string) $atts['primary_button'],
            'primary_url' => (string) $atts['primary_url'],
            'secondary_button' => (string) $atts['secondary_button'],
            'secondary_url' => (string) $atts['secondary_url'],
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

        $title = (string) ($args['title'] ?? __('Order Facefood', 'ff-addons'));
        $text = (string) ($args['text'] ?? '');
        $button = (string) ($args['button'] ?? __('Order now', 'ff-addons'));
        $url = (string) ($args['url'] ?? home_url('/'));

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

        $title = (string) ($args['title'] ?? __('About Us', 'ff-addons'));
        $description = (string) ($args['description'] ?? '');
        if ($description === '') {
            $description = '<p>' . esc_html__('Drip Hydration brings IV therapy and medical care directly to you, in the comfort of your home. With providers in 100+ cities worldwide, our goal is simple: make getting quality care convenient, comfortable, and personal.', 'ff-addons') . '</p><p>' . esc_html__('Our nurses are a big part of what makes the experience different. Many come from emergency rooms, ICUs, labor and delivery, and other fast-paced clinical settings where starting IVs and caring for patients is part of their everyday work.', 'ff-addons') . '</p>';
        }
        $image = (string) ($args['image'] ?? 'https://driphydration.com/wp-content/uploads/2025/05/Our-Nurses.png');
        $imageAlt = (string) ($args['image_alt'] ?? $title);
        $primaryButton = (string) ($args['primary_button'] ?? __('BOOK IV THERAPY', 'ff-addons'));
        $primaryUrl = (string) ($args['primary_url'] ?? 'https://driphydration.com/iv-therapy/');
        $secondaryButton = (string) ($args['secondary_button'] ?? __('VIEW PLANS', 'ff-addons'));
        $secondaryUrl = (string) ($args['secondary_url'] ?? 'https://driphydration.com/vitamin-drip-membership/');

        ob_start();
        ?>
        <section class="ff-addons-about">
            <?php if ($image !== '') : ?>
                <picture class="ff-addons-about__picture">
                    <img
                        class="ff-addons-about__image"
                        src="<?php echo esc_url($image); ?>"
                        alt="<?php echo esc_attr($imageAlt !== '' ? $imageAlt : $title); ?>"
                        loading="lazy"
                    >
                </picture>
            <?php endif; ?>
            <div class="ff-addons-about__container">
                <div class="ff-addons-about__content">
                    <?php if ($title !== '') : ?>
                        <div class="ff-addons-about__heading">
                            <h1 class="ff-addons-about__title"><?php echo esc_html($title); ?></h1>
                        </div>
                    <?php endif; ?>
                    <?php if ($description !== '') : ?>
                        <div class="ff-addons-about__body">
                            <div class="ff-addons-about__text"><?php echo wp_kses_post($description); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (($primaryButton !== '' && $primaryUrl !== '') || ($secondaryButton !== '' && $secondaryUrl !== '')) : ?>
                        <div class="ff-addons-about__actions">
                            <?php if ($primaryButton !== '' && $primaryUrl !== '') : ?>
                                <a class="ff-addons-about__btn ff-addons-about__btn--primary" href="<?php echo esc_url($primaryUrl); ?>"><?php echo esc_html($primaryButton); ?></a>
                            <?php endif; ?>
                            <?php if ($secondaryButton !== '' && $secondaryUrl !== '') : ?>
                                <a class="ff-addons-about__btn ff-addons-about__btn--secondary" href="<?php echo esc_url($secondaryUrl); ?>"><?php echo esc_html($secondaryButton); ?></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
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
