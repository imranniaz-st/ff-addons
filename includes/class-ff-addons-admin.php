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
            'sanitize_callback' => [$this, 'sanitize_api_base_url'],
            'default' => '',
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

    public function sanitize_api_base_url($value): string
    {
        $value = is_string($value) ? trim($value) : '';
        if ($value === '') {
            return '';
        }

        return esc_url_raw($value);
    }

    public function render_page(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $apiUrl = (string) get_option('ff_addons_api_base_url', '');
        $api = new FF_Addons_Api();
        $probe = null;
        $ok = false;
        if ($api->has_base_url()) {
            $probe = $api->get_settings();
            $ok = ! is_wp_error($probe);
        }
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
                                   value="<?php echo esc_attr($apiUrl); ?>"
                                   placeholder="https://example.com/api">
                            <p class="description"><?php echo esc_html__('Optional. Leave empty to disable API-backed widgets until configured.', 'ff-addons'); ?></p>
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
            <?php if ($apiUrl === '') : ?>
                <p style="color:#646970;"><strong><?php echo esc_html__('Not configured', 'ff-addons'); ?></strong>
                    — <?php echo esc_html__('Enter an API base URL above to enable catalog requests.', 'ff-addons'); ?></p>
            <?php elseif ($ok) : ?>
                <p style="color:#008a20;"><strong><?php echo esc_html__('Connected', 'ff-addons'); ?></strong></p>
            <?php else : ?>
                <p style="color:#b32d2e;"><strong><?php echo esc_html__('Could not reach API', 'ff-addons'); ?></strong>
                    — <?php echo esc_html(is_wp_error($probe) ? $probe->get_error_message() : ''); ?></p>
            <?php endif; ?>

            <h2><?php echo esc_html__('Elementor widgets', 'ff-addons'); ?></h2>
            <ul>
                <li><strong><?php echo esc_html__('FF Addons · General', 'ff-addons'); ?></strong> — FF CTA</li>
                <li><strong><?php echo esc_html__('FF Addons · Menu', 'ff-addons'); ?></strong> — FF Categories, FF Menu</li>
                <li><strong><?php echo esc_html__('FF Addons · Offers', 'ff-addons'); ?></strong> — FF Deals</li>
            </ul>
            <p><?php echo esc_html__('In Elementor: search “FF”, or open FF Addons / General / Menu / Offers. Widgets also appear under Elementor General. Style tab controls colors, typography, and spacing.', 'ff-addons'); ?></p>

            <p>
                <a href="<?php echo esc_url(FF_ADDONS_URL . 'docs/API.md'); ?>" target="_blank" rel="noopener noreferrer">
                    <?php echo esc_html__('Open API documentation', 'ff-addons'); ?>
                </a>
            </p>
        </div>
        <?php
    }
}
