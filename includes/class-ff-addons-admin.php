<?php

if (! defined('ABSPATH')) {
    exit;
}

class FF_Addons_Admin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_menu(): void
    {
        add_options_page(
            __('FF Addons', 'ff-addons'),
            __('FF Addons', 'ff-addons'),
            'manage_options',
            'ff-addons',
            [$this, 'render_page']
        );
    }

    public function register_settings(): void
    {
        register_setting('ff_addons', 'ff_addons_api_base_url', [
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => 'https://app.facefood.cafe/api',
        ]);
        register_setting('ff_addons', 'ff_addons_cache_ttl', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 300,
        ]);
        register_setting('ff_addons', 'ff_addons_currency', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'Rs.',
        ]);
    }

    public function render_page(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $api = new FF_Addons_Api();
        $probe = $api->get_settings();
        $ok = ! is_wp_error($probe);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('FF Addons', 'ff-addons'); ?></h1>
            <p><?php echo esc_html__('Elementor + WPBakery blocks for Facefood. Uses the same public Laravel API as the mobile app.', 'ff-addons'); ?></p>

            <form method="post" action="options.php">
                <?php settings_fields('ff_addons'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="ff_addons_api_base_url"><?php echo esc_html__('API base URL', 'ff-addons'); ?></label></th>
                        <td>
                            <input type="url" class="regular-text" id="ff_addons_api_base_url" name="ff_addons_api_base_url"
                                   value="<?php echo esc_attr((string) get_option('ff_addons_api_base_url', 'https://app.facefood.cafe/api')); ?>">
                            <p class="description"><?php echo esc_html__('Default: https://app.facefood.cafe/api', 'ff-addons'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ff_addons_cache_ttl"><?php echo esc_html__('Cache TTL (seconds)', 'ff-addons'); ?></label></th>
                        <td>
                            <input type="number" min="60" class="small-text" id="ff_addons_cache_ttl" name="ff_addons_cache_ttl"
                                   value="<?php echo esc_attr((string) get_option('ff_addons_cache_ttl', 300)); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ff_addons_currency"><?php echo esc_html__('Currency label', 'ff-addons'); ?></label></th>
                        <td>
                            <input type="text" class="small-text" id="ff_addons_currency" name="ff_addons_currency"
                                   value="<?php echo esc_attr((string) get_option('ff_addons_currency', 'Rs.')); ?>">
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <h2><?php echo esc_html__('API status', 'ff-addons'); ?></h2>
            <?php if ($ok) : ?>
                <p style="color:#008a20;"><strong><?php echo esc_html__('Connected', 'ff-addons'); ?></strong></p>
            <?php else : ?>
                <p style="color:#b32d2e;"><strong><?php echo esc_html__('Could not reach API', 'ff-addons'); ?></strong>
                    — <?php echo esc_html($probe->get_error_message()); ?></p>
            <?php endif; ?>

            <h2><?php echo esc_html__('Elementor widgets', 'ff-addons'); ?></h2>
            <ul>
                <li><strong>FF Categories</strong> — <?php echo esc_html__('all menu categories (Shawarma, Burgers, …)', 'ff-addons'); ?></li>
                <li><strong>FF Menu</strong> — <?php echo esc_html__('products with category dropdown', 'ff-addons'); ?></li>
                <li><strong>FF Deals</strong> / <strong>FF CTA</strong></li>
            </ul>
            <p><?php echo esc_html__('In Elementor: search “FF” in the widget panel, or open category “FF Addons”. Style tab controls colors, typography, and spacing.', 'ff-addons'); ?></p>

            <p>
                <a href="<?php echo esc_url(FF_ADDONS_URL . 'docs/API.md'); ?>" target="_blank" rel="noopener noreferrer">
                    <?php echo esc_html__('Open API documentation', 'ff-addons'); ?>
                </a>
            </p>
        </div>
        <?php
    }
}
