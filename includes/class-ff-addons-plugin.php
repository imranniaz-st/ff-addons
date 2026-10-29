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
        if (get_option('ff_addons_api_base_url') === false) {
            add_option('ff_addons_api_base_url', 'https://app.facefood.cafe/api');
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

        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_shortcode('ff_addons_menu', [$this, 'shortcode_menu']);
        add_shortcode('ff_addons_deals', [$this, 'shortcode_deals']);
        add_shortcode('ff_addons_cta', [$this, 'shortcode_cta']);

        add_action('elementor/loaded', static function (): void {
            require_once FF_ADDONS_DIR . 'elementor/class-ff-addons-elementor.php';
            new FF_Addons_Elementor();
        });

        add_action('vc_before_init', static function (): void {
            require_once FF_ADDONS_DIR . 'wpbakery/class-ff-addons-wpbakery.php';
            FF_Addons_WPBakery::register();
        });
    }

    public function enqueue_assets(): void
    {
        wp_register_style(
            'ff-addons-frontend',
            FF_ADDONS_URL . 'assets/css/frontend.css',
            [],
            FF_ADDONS_VERSION
        );
        wp_enqueue_style('ff-addons-frontend');
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
        ], $atts, 'ff_addons_menu');

        return $this->render_menu((int) $atts['limit'], (string) $atts['category'], (int) $atts['columns']);
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

        return $this->render_deals((int) $atts['limit'], (int) $atts['columns']);
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

        return $this->render_cta(
            (string) $atts['title'],
            (string) $atts['text'],
            (string) $atts['button'],
            (string) $atts['url']
        );
    }

    public function render_menu(int $limit = 8, string $category = '', int $columns = 4): string
    {
        $api = new FF_Addons_Api();
        $query = ['per_page' => max(1, min(48, $limit))];
        if ($category !== '') {
            $query['category'] = $category;
        }

        $response = $api->get_products($query);
        if (is_wp_error($response)) {
            return '<p class="ff-addons-error">' . esc_html($response->get_error_message()) . '</p>';
        }

        $items = $response['data'] ?? $response;
        if (! is_array($items)) {
            $items = [];
        }

        $currency = (string) get_option('ff_addons_currency', 'Rs.');
        $columns = max(1, min(6, $columns));

        ob_start();
        echo '<div class="ff-addons-grid ff-addons-menu" style="--ff-cols:' . esc_attr((string) $columns) . '">';
        foreach ($items as $product) {
            if (! is_array($product)) {
                continue;
            }
            $name = (string) ($product['name'] ?? '');
            $price = $product['price'] ?? null;
            $image = (string) ($product['image_url'] ?? '');
            $desc = (string) ($product['description'] ?? '');
            echo '<article class="ff-addons-card">';
            if ($image !== '') {
                echo '<div class="ff-addons-card__media"><img src="' . esc_url($image) . '" alt="' . esc_attr($name) . '" loading="lazy"></div>';
            }
            echo '<div class="ff-addons-card__body">';
            echo '<h3 class="ff-addons-card__title">' . esc_html($name) . '</h3>';
            if ($desc !== '') {
                echo '<p class="ff-addons-card__text">' . esc_html(wp_trim_words($desc, 18)) . '</p>';
            }
            if ($price !== null && $price !== '') {
                echo '<p class="ff-addons-card__price">' . esc_html($currency . ' ' . number_format((float) $price, 0)) . '</p>';
            }
            echo '</div></article>';
        }
        echo '</div>';

        return (string) ob_get_clean();
    }

    public function render_deals(int $limit = 6, int $columns = 3): string
    {
        $api = new FF_Addons_Api();
        $response = $api->get_deals(['per_page' => max(1, min(24, $limit))]);
        if (is_wp_error($response)) {
            return '<p class="ff-addons-error">' . esc_html($response->get_error_message()) . '</p>';
        }

        $items = $response['data'] ?? $response;
        if (! is_array($items)) {
            $items = [];
        }

        $currency = (string) get_option('ff_addons_currency', 'Rs.');
        $columns = max(1, min(6, $columns));

        ob_start();
        echo '<div class="ff-addons-grid ff-addons-deals" style="--ff-cols:' . esc_attr((string) $columns) . '">';
        foreach ($items as $deal) {
            if (! is_array($deal)) {
                continue;
            }
            $title = (string) ($deal['title'] ?? $deal['name'] ?? '');
            $price = $deal['deal_price'] ?? $deal['price'] ?? null;
            $image = (string) ($deal['image_url'] ?? '');
            echo '<article class="ff-addons-card ff-addons-card--deal">';
            if ($image !== '') {
                echo '<div class="ff-addons-card__media"><img src="' . esc_url($image) . '" alt="' . esc_attr($title) . '" loading="lazy"></div>';
            }
            echo '<div class="ff-addons-card__body">';
            echo '<h3 class="ff-addons-card__title">' . esc_html($title) . '</h3>';
            if ($price !== null && $price !== '') {
                echo '<p class="ff-addons-card__price">' . esc_html($currency . ' ' . number_format((float) $price, 0)) . '</p>';
            }
            echo '</div></article>';
        }
        echo '</div>';

        return (string) ob_get_clean();
    }

    public function render_cta(string $title, string $text, string $button, string $url): string
    {
        ob_start();
        ?>
        <section class="ff-addons-cta">
            <div class="ff-addons-cta__inner">
                <h2 class="ff-addons-cta__title"><?php echo esc_html($title); ?></h2>
                <p class="ff-addons-cta__text"><?php echo esc_html($text); ?></p>
                <a class="ff-addons-cta__button" href="<?php echo esc_url($url); ?>"><?php echo esc_html($button); ?></a>
            </div>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
