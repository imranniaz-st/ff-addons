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
        add_action('update_option_ff_addons_instagram_token', [$this, 'on_instagram_credentials_changed'], 10, 0);
        add_action('update_option_ff_addons_instagram_user_id', [$this, 'on_instagram_credentials_changed'], 10, 0);
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
        register_setting('ff_addons', 'ff_addons_instagram_token', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_instagram_token'],
            'default' => '',
        ]);
        register_setting('ff_addons', 'ff_addons_instagram_user_id', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_instagram_user_id'],
            'default' => '',
        ]);
    }

    public function sanitize_instagram_token($value): string
    {
        $value = is_string($value) ? trim($value) : '';
        if ($value === '') {
            return '';
        }

        return sanitize_text_field($value);
    }

    public function sanitize_instagram_user_id($value): string
    {
        $value = is_string($value) ? trim($value) : '';
        if ($value === '' || ! preg_match('/^\d+$/', $value)) {
            return '';
        }

        return $value;
    }

    public function sanitize_api_base_url($value): string
    {
        $value = is_string($value) ? trim($value) : '';
        if ($value === '') {
            return '';
        }

        return esc_url_raw($value);
    }

    public function on_instagram_credentials_changed(): void
    {
        FF_Addons_Plugin::instance()->clear_instagram_media_cache();
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

        $igToken = (string) get_option('ff_addons_instagram_token', '');
        $igUserId = (string) get_option('ff_addons_instagram_user_id', '');
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
                            <p class="description"><?php echo esc_html__('Also controls how often the Instagram auto feed refreshes from Instagram.', 'ff-addons'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ff_addons_currency"><?php echo esc_html__('Currency label', 'ff-addons'); ?></label></th>
                        <td>
                            <input type="text" class="small-text" id="ff_addons_currency" name="ff_addons_currency"
                                   value="<?php echo esc_attr((string) get_option('ff_addons_currency', 'Rs.')); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ff_addons_instagram_token"><?php echo esc_html__('Instagram access token', 'ff-addons'); ?></label></th>
                        <td>
                            <input type="password" class="regular-text" id="ff_addons_instagram_token" name="ff_addons_instagram_token"
                                   value="<?php echo esc_attr($igToken); ?>"
                                   autocomplete="off"
                                   placeholder="<?php echo esc_attr__('Long-lived Instagram Graph API token', 'ff-addons'); ?>">
                            <p class="description">
                                <?php echo esc_html__('Required for FF Instagram “Auto from Instagram”. Creates a custom thumbnail grid (media_url / caption / permalink) — not Instagram embeds. Use a long-lived token from a Meta app connected to your Instagram professional account.', 'ff-addons'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ff_addons_instagram_user_id"><?php echo esc_html__('Instagram user ID (optional)', 'ff-addons'); ?></label></th>
                        <td>
                            <input type="text" class="regular-text" id="ff_addons_instagram_user_id" name="ff_addons_instagram_user_id"
                                   value="<?php echo esc_attr($igUserId); ?>"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   placeholder="<?php echo esc_attr__('Numeric Instagram professional account ID', 'ff-addons'); ?>">
                            <p class="description">
                                <?php echo esc_html__('Only needed if graph.instagram.com/me/media fails and you use a Facebook Graph / Page token. Leave empty for Instagram Login tokens.', 'ff-addons'); ?>
                            </p>
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

            <h2><?php echo esc_html__('Instagram feed', 'ff-addons'); ?></h2>
            <?php if ($igToken === '') : ?>
                <p style="color:#646970;"><strong><?php echo esc_html__('Token not set', 'ff-addons'); ?></strong>
                    — <?php echo esc_html__('Paste a long-lived token above, then set FF Instagram Source to “Auto from Instagram”.', 'ff-addons'); ?></p>
            <?php else : ?>
                <p style="color:#008a20;"><strong><?php echo esc_html__('Token saved', 'ff-addons'); ?></strong>
                    — <?php echo esc_html__('Feed refreshes automatically using Cache TTL. Saving a new token clears the media cache.', 'ff-addons'); ?></p>
            <?php endif; ?>

            <h2><?php echo esc_html__('Elementor widgets', 'ff-addons'); ?></h2>
            <ul>
                <li><strong><?php echo esc_html__('FF Addons · General', 'ff-addons'); ?></strong> — FF About Us, FF Instagram, FF Right For You, FF CTA</li>
                <li><strong><?php echo esc_html__('FF Addons · Menu', 'ff-addons'); ?></strong> — FF Categories, FF Menu</li>
                <li><strong><?php echo esc_html__('FF Addons · Offers', 'ff-addons'); ?></strong> — FF Deals</li>
            </ul>
            <p><?php echo esc_html__('In Elementor’s widgets panel: search “About Us” or “FF”, or open FF Addons / FF Addons · General. Widgets also appear under Elementor General. Drag onto the canvas — no shortcode needed. Style tab controls colors, typography, and spacing.', 'ff-addons'); ?></p>

            <p>
                <a href="<?php echo esc_url(FF_ADDONS_URL . 'docs/API.md'); ?>" target="_blank" rel="noopener noreferrer">
                    <?php echo esc_html__('Open API documentation', 'ff-addons'); ?>
                </a>
            </p>
        </div>
        <?php
    }
}
