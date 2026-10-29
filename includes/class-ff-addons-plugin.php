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
        if (get_option('ff_addons_instagram_token') === false) {
            add_option('ff_addons_instagram_token', '');
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
        add_shortcode('ff_addons_instagram', [$this, 'shortcode_instagram']);

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
        $css = FF_ADDONS_URL . 'assets/css/';
        $js = FF_ADDONS_URL . 'assets/js/';
        $ver = FF_ADDONS_VERSION;

        wp_register_style(
            'ff-addons-fonts-about',
            'https://fonts.googleapis.com/css2?family=Kanit:wght@600;800&family=Montserrat:wght@400;600;700;800&display=swap',
            [],
            null
        );
        wp_register_style('ff-addons-base', $css . 'base.css', [], $ver);
        wp_register_style('ff-addons-deals', $css . 'deals.css', ['ff-addons-base'], $ver);
        wp_register_style('ff-addons-categories', $css . 'categories.css', ['ff-addons-base'], $ver);
        wp_register_style('ff-addons-cta', $css . 'cta.css', ['ff-addons-base'], $ver);
        wp_register_style('ff-addons-about', $css . 'about.css', ['ff-addons-base', 'ff-addons-fonts-about'], $ver);
        wp_register_style('ff-addons-instagram', $css . 'instagram.css', ['ff-addons-base', 'ff-addons-fonts-about'], $ver);
        // Enqueued after component handles so breakpoint rules win.
        wp_register_style('ff-addons-responsive', $css . 'responsive.css', ['ff-addons-base'], $ver);
        wp_register_style('ff-addons-editor', $css . 'editor.css', [], $ver);

        wp_register_script('ff-addons-instagram', $js . 'instagram.js', [], $ver, true);
        wp_register_script(
            'instagram-embed',
            'https://www.instagram.com/embed.js',
            [],
            null,
            true
        );
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
        wp_enqueue_style('ff-addons-instagram');
        wp_enqueue_style('ff-addons-responsive');
        wp_enqueue_style('ff-addons-editor');
        wp_enqueue_script('ff-addons-instagram');
        wp_enqueue_script('instagram-embed');
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
     * @param  array<string, string>|string  $atts
     */
    public function shortcode_instagram($atts = []): string
    {
        $atts = shortcode_atts([
            'subtitle' => __('FOLLOW US', 'ff-addons'),
            'title' => __('Catch The Flavor On Instagram', 'ff-addons'),
            'handle' => '@facefood_pwd_',
            'profile_url' => 'https://www.instagram.com/facefood_pwd_/',
            'columns' => '4',
            'button_text' => __('Follow on Instagram', 'ff-addons'),
            'source' => 'posts',
            'post_urls' => '',
            'limit' => '8',
        ], $atts, 'ff_addons_instagram');

        $postUrls = array_values(array_filter(array_map('trim', preg_split('/[\s,]+/', (string) $atts['post_urls']) ?: [])));

        return $this->render_instagram([
            'subtitle' => (string) $atts['subtitle'],
            'title' => (string) $atts['title'],
            'handle' => (string) $atts['handle'],
            'profile_url' => (string) $atts['profile_url'],
            'profile_external' => true,
            'profile_nofollow' => true,
            'source' => (string) $atts['source'],
            'post_urls' => $postUrls,
            'items' => [],
            'columns' => (int) $atts['columns'],
            'limit' => (int) $atts['limit'],
            'button_text' => (string) $atts['button_text'],
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
        <div class="ff-addons-about about-section-wrapper">
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
     * Instagram gallery: curated Media Library, official post embeds, or Graph API media.
     *
     * @param  array<string, mixed>  $args
     */
    public function render_instagram(array $args = []): string
    {
        $this->enqueue_styles(['ff-addons-instagram']);
        wp_enqueue_script('ff-addons-instagram');

        $subtitle = (string) ($args['subtitle'] ?? '');
        $title = (string) ($args['title'] ?? '');
        $handle = (string) ($args['handle'] ?? '');
        $profileUrl = (string) ($args['profile_url'] ?? 'https://www.instagram.com/facefood_pwd_/');
        $profileExternal = (bool) ($args['profile_external'] ?? true);
        $profileNofollow = (bool) ($args['profile_nofollow'] ?? true);
        $buttonText = (string) ($args['button_text'] ?? '');
        $columns = max(2, min(6, (int) ($args['columns'] ?? 4)));
        $limit = max(1, min(24, (int) ($args['limit'] ?? 8)));
        $source = (string) ($args['source'] ?? 'posts');
        if (! in_array($source, ['curated', 'posts', 'api'], true)) {
            $source = 'posts';
        }

        $items = $args['items'] ?? [];
        if (! is_array($items)) {
            $items = [];
        }
        $postUrls = $args['post_urls'] ?? [];
        if (! is_array($postUrls)) {
            $postUrls = [];
        }

        $mode = 'empty';
        $galleryItems = [];
        $embedHtmls = [];
        $emptyMessage = '';

        if ($source === 'api') {
            $galleryItems = $this->fetch_instagram_graph_media($limit);
            if ($galleryItems !== []) {
                $mode = 'images';
            } else {
                $token = (string) get_option('ff_addons_instagram_token', '');
                $emptyMessage = $token === ''
                    ? __('Add an Instagram Graph API token under Settings → FF Addons, or switch Source to Instagram post URLs.', 'ff-addons')
                    : __('Could not load Instagram media. Check the token, or use Instagram post URLs instead.', 'ff-addons');
            }
        } elseif ($source === 'posts') {
            foreach ($postUrls as $url) {
                if (! is_string($url)) {
                    continue;
                }
                $url = $this->normalize_instagram_post_url($url);
                if ($url === '') {
                    continue;
                }
                $html = $this->get_instagram_post_embed($url);
                if ($html !== '') {
                    $embedHtmls[] = ['url' => $url, 'html' => $html];
                }
            }
            if ($embedHtmls !== []) {
                $mode = 'embeds';
                wp_enqueue_script('instagram-embed');
            } else {
                $emptyMessage = __('Add Instagram post URLs in the widget settings (open a post → Copy link).', 'ff-addons');
            }
        } else {
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $image = (string) ($item['image'] ?? '');
                if ($image === '') {
                    continue;
                }
                $galleryItems[] = [
                    'image' => $image,
                    'caption' => (string) ($item['caption'] ?? ''),
                    'link' => (string) ($item['link'] ?? ''),
                ];
            }
            if ($galleryItems !== []) {
                $mode = 'images';
            } else {
                $emptyMessage = __('Add gallery images from the Media Library, or switch Source to Instagram post URLs.', 'ff-addons');
            }
        }

        $igIcon = '<svg class="instagram-btn__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0 3.691a6.146 6.146 0 1 0 0 12.292 6.146 6.146 0 0 0 0-12.292zm0 10.146a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>';
        $igIconSmall = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0 3.691a6.146 6.146 0 1 0 0 12.292 6.146 6.146 0 0 0 0-12.292zm0 10.146a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>';

        $profileAttrs = '';
        if ($profileExternal) {
            $profileAttrs .= ' target="_blank" rel="noopener';
            if ($profileNofollow) {
                $profileAttrs .= ' nofollow';
            }
            $profileAttrs .= '"';
        } elseif ($profileNofollow) {
            $profileAttrs .= ' rel="nofollow"';
        }

        ob_start();
        ?>
        <div class="ff-addons-instagram instagram-section-wrapper" data-ig-source="<?php echo esc_attr($source); ?>">
            <div class="instagram-container">
                <div class="instagram-header">
                    <?php if ($subtitle !== '') : ?>
                        <span class="instagram-subtitle"><?php echo esc_html($subtitle); ?></span>
                    <?php endif; ?>
                    <?php if ($title !== '') : ?>
                        <h2 class="instagram-title"><?php echo esc_html($title); ?></h2>
                    <?php endif; ?>
                    <?php if ($handle !== '' && $profileUrl !== '') : ?>
                        <a class="instagram-handle" href="<?php echo esc_url($profileUrl); ?>"<?php echo $profileAttrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                            <span class="instagram-handle__icon"><?php echo $igIconSmall; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                            <?php echo esc_html($handle); ?>
                        </a>
                    <?php elseif ($handle !== '') : ?>
                        <span class="instagram-handle">
                            <span class="instagram-handle__icon"><?php echo $igIconSmall; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                            <?php echo esc_html($handle); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($mode === 'embeds') : ?>
                    <div class="instagram-grid instagram-grid--embeds" style="--ff-cols:<?php echo esc_attr((string) $columns); ?>">
                        <?php foreach ($embedHtmls as $embed) : ?>
                            <div class="instagram-item instagram-item--embed">
                                <?php echo $embed['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — oEmbed / official IG embed markup ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($mode === 'images') : ?>
                    <div class="instagram-grid" style="--ff-cols:<?php echo esc_attr((string) $columns); ?>">
                        <?php foreach ($galleryItems as $item) :
                            $image = (string) ($item['image'] ?? '');
                            if ($image === '') {
                                continue;
                            }
                            $caption = (string) ($item['caption'] ?? '');
                            $link = (string) ($item['link'] ?? '');
                            $alt = $caption !== '' ? $caption : $title;
                            $label = $caption !== ''
                                ? sprintf(
                                    /* translators: %s: image caption */
                                    __('Enlarge image: %s', 'ff-addons'),
                                    $caption
                                )
                                : __('Enlarge image', 'ff-addons');
                            ?>
                            <button
                                type="button"
                                class="instagram-item"
                                data-instagram-src="<?php echo esc_url($image); ?>"
                                data-instagram-caption="<?php echo esc_attr($caption); ?>"
                                <?php if ($link !== '') : ?>
                                    data-instagram-link="<?php echo esc_url($link); ?>"
                                <?php endif; ?>
                                aria-label="<?php echo esc_attr($label); ?>"
                            >
                                <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy">
                                <span class="instagram-item__badge" aria-hidden="true"><?php echo $igIconSmall; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                                <?php if ($caption !== '') : ?>
                                    <span class="instagram-item__overlay">
                                        <span class="instagram-item__caption"><?php echo esc_html($caption); ?></span>
                                    </span>
                                <?php else : ?>
                                    <span class="instagram-item__overlay" aria-hidden="true"></span>
                                <?php endif; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="instagram-empty">
                        <?php echo esc_html($emptyMessage !== '' ? $emptyMessage : __('No Instagram media to show yet.', 'ff-addons')); ?>
                        <?php if ($profileUrl !== '' && ! FF_Addons_Helpers::is_elementor_edit()) : ?>
                            <br>
                            <a class="instagram-empty__link" href="<?php echo esc_url($profileUrl); ?>"<?php echo $profileAttrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                                <?php echo esc_html__('Visit our Instagram', 'ff-addons'); ?>
                            </a>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>

                <?php if ($buttonText !== '' && $profileUrl !== '') : ?>
                    <div class="instagram-footer">
                        <a class="instagram-btn" href="<?php echo esc_url($profileUrl); ?>"<?php echo $profileAttrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                            <?php echo $igIcon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            <?php echo esc_html($buttonText); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    /**
     * Normalize a public Instagram post / reel URL.
     */
    private function normalize_instagram_post_url(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        $url = esc_url_raw($url);
        if ($url === '' || ! preg_match('#^https?://(www\.)?instagram\.com/(p|reel|tv)/[^/\s?#]+#i', $url)) {
            return '';
        }

        return untrailingslashit($url) . '/';
    }

    /**
     * Official Instagram embed (wp_oembed_get when available, else embed.js blockquote).
     */
    private function get_instagram_post_embed(string $url): string
    {
        $oembed = wp_oembed_get($url, ['width' => 540]);
        if (is_string($oembed) && $oembed !== '') {
            return $oembed;
        }

        $permalink = esc_url($url);

        return '<blockquote class="instagram-media" data-instgrm-permalink="' . $permalink . '" data-instgrm-version="14" style="background:#FFF;border:0;border-radius:12px;margin:0;max-width:100%;min-width:0;padding:0;width:100%;">'
            . '<a href="' . $permalink . '" target="_blank" rel="noopener noreferrer">' . esc_html($url) . '</a>'
            . '</blockquote>';
    }

    /**
     * Fetch recent media via Instagram Graph / Basic Display API (long-lived token).
     *
     * @return list<array{image: string, caption: string, link: string}>
     */
    private function fetch_instagram_graph_media(int $limit = 8): array
    {
        $token = trim((string) get_option('ff_addons_instagram_token', ''));
        if ($token === '') {
            return [];
        }

        $cacheKey = 'ff_addons_ig_media_' . md5($token . '|' . $limit);
        if (! FF_Addons_Helpers::is_elementor_edit()) {
            $cached = get_transient($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $endpoint = add_query_arg(
            [
                'fields' => 'id,caption,media_type,media_url,permalink,thumbnail_url',
                'limit' => $limit,
                'access_token' => $token,
            ],
            'https://graph.instagram.com/me/media'
        );

        $response = wp_remote_get($endpoint, [
            'timeout' => 15,
            'headers' => ['Accept' => 'application/json'],
        ]);

        if (is_wp_error($response)) {
            return [];
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        if ($code < 200 || $code >= 300 || ! is_array($body) || ! isset($body['data']) || ! is_array($body['data'])) {
            return [];
        }

        $items = [];
        foreach ($body['data'] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $type = strtoupper((string) ($row['media_type'] ?? ''));
            $image = '';
            if ($type === 'VIDEO' || $type === 'REELS') {
                $image = (string) ($row['thumbnail_url'] ?? $row['media_url'] ?? '');
            } else {
                $image = (string) ($row['media_url'] ?? $row['thumbnail_url'] ?? '');
            }
            if ($image === '') {
                continue;
            }
            $items[] = [
                'image' => $image,
                'caption' => (string) ($row['caption'] ?? ''),
                'link' => (string) ($row['permalink'] ?? ''),
            ];
            if (count($items) >= $limit) {
                break;
            }
        }

        $ttl = (int) get_option('ff_addons_cache_ttl', 300);
        set_transient($cacheKey, $items, max(60, $ttl));

        return $items;
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
