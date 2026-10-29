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
        register_setting('ff_addons', 'ff_addons_instagram_app_id', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);
        register_setting('ff_addons', 'ff_addons_instagram_app_secret', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_instagram_app_secret'],
            'default' => '',
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

    public function sanitize_instagram_app_secret($value): string
    {
        $value = is_string($value) ? trim($value) : '';
        // Password field: empty / mask means keep the stored secret.
        if ($value === '' || $value === '********') {
            return (string) get_option('ff_addons_instagram_app_secret', '');
        }

        return sanitize_text_field($value);
    }

    public function sanitize_instagram_token($value): string
    {
        $value = is_string($value) ? trim($value) : '';
        if ($value === '' || $value === '********') {
            // Allow clearing via Disconnect; keep existing when the masked password is re-submitted.
            if ($value === '********') {
                return (string) get_option('ff_addons_instagram_token', '');
            }

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

        $igAppId = (string) get_option('ff_addons_instagram_app_id', '');
        $igAppSecret = (string) get_option('ff_addons_instagram_app_secret', '');
        $igToken = (string) get_option('ff_addons_instagram_token', '');
        $igUserId = (string) get_option('ff_addons_instagram_user_id', '');
        $igConnected = FF_Addons_Instagram_OAuth::is_connected();
        $igRedirectUri = FF_Addons_Instagram_OAuth::redirect_uri();
        $igTokenExpires = (int) get_option('ff_addons_instagram_token_expires', 0);
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
                </table>

                <h2><?php echo esc_html__('Instagram Connect', 'ff-addons'); ?></h2>
                <p class="description">
                    <?php echo esc_html__('Use Instagram API with Instagram Login (Business Login). Create a Meta app, add the Instagram product, then paste the Instagram App ID and App Secret below (App Dashboard → Instagram → API setup with Instagram business login — not the Meta app ID/secret under Settings → Basic).', 'ff-addons'); ?>
                </p>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="ff_addons_instagram_app_id"><?php echo esc_html__('Instagram App ID', 'ff-addons'); ?></label></th>
                        <td>
                            <input type="text" class="regular-text" id="ff_addons_instagram_app_id" name="ff_addons_instagram_app_id"
                                   value="<?php echo esc_attr($igAppId); ?>"
                                   autocomplete="off"
                                   placeholder="<?php echo esc_attr__('Instagram App ID from Meta', 'ff-addons'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ff_addons_instagram_app_secret"><?php echo esc_html__('Instagram App Secret', 'ff-addons'); ?></label></th>
                        <td>
                            <input type="password" class="regular-text" id="ff_addons_instagram_app_secret" name="ff_addons_instagram_app_secret"
                                   value="<?php echo $igAppSecret !== '' ? '********' : ''; ?>"
                                   autocomplete="new-password"
                                   placeholder="<?php echo esc_attr__('Instagram App Secret', 'ff-addons'); ?>">
                            <p class="description"><?php echo esc_html__('Stored securely. Leave as masked (or blank) to keep the current secret when saving.', 'ff-addons'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('OAuth redirect URI', 'ff-addons'); ?></th>
                        <td>
                            <code id="ff_addons_ig_redirect_uri" style="display:inline-block;word-break:break-all;"><?php echo esc_html($igRedirectUri); ?></code>
                            <p class="description">
                                <?php echo esc_html__('Copy this exact URL into Meta → Instagram → API setup with Instagram business login → Business login settings → Valid OAuth Redirect URIs. Trailing slash and query string must match exactly.', 'ff-addons'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Connection', 'ff-addons'); ?></th>
                        <td>
                            <?php if ($igConnected) : ?>
                                <p style="color:#008a20;margin-top:0;">
                                    <strong><?php echo esc_html__('Connected', 'ff-addons'); ?></strong>
                                    <?php if ($igUserId !== '') : ?>
                                        — <?php echo esc_html(sprintf(
                                            /* translators: %s: Instagram user id */
                                            __('User ID %s', 'ff-addons'),
                                            $igUserId
                                        )); ?>
                                    <?php endif; ?>
                                    <?php if ($igTokenExpires > 0) : ?>
                                        <br>
                                        <span class="description">
                                            <?php echo esc_html(sprintf(
                                                /* translators: %s: localized date/time */
                                                __('Long-lived token expires around %s (plugin refreshes weekly while active).', 'ff-addons'),
                                                wp_date(get_option('date_format') . ' ' . get_option('time_format'), $igTokenExpires)
                                            )); ?>
                                        </span>
                                    <?php endif; ?>
                                </p>
                                <p>
                                    <a class="button button-secondary" href="<?php echo esc_url(FF_Addons_Instagram_OAuth::disconnect_url()); ?>">
                                        <?php echo esc_html__('Disconnect Instagram', 'ff-addons'); ?>
                                    </a>
                                    <?php if (FF_Addons_Instagram_OAuth::has_app_credentials()) : ?>
                                        <a class="button button-primary" href="<?php echo esc_url(FF_Addons_Instagram_OAuth::connect_url()); ?>">
                                            <?php echo esc_html__('Reconnect Instagram', 'ff-addons'); ?>
                                        </a>
                                    <?php endif; ?>
                                </p>
                            <?php else : ?>
                                <p style="color:#646970;margin-top:0;">
                                    <strong><?php echo esc_html__('Not connected', 'ff-addons'); ?></strong>
                                    — <?php echo esc_html__('Save App ID and App Secret, then click Connect Instagram.', 'ff-addons'); ?>
                                </p>
                                <p>
                                    <?php if (FF_Addons_Instagram_OAuth::has_app_credentials()) : ?>
                                        <a class="button button-primary" href="<?php echo esc_url(FF_Addons_Instagram_OAuth::connect_url()); ?>">
                                            <?php echo esc_html__('Connect Instagram', 'ff-addons'); ?>
                                        </a>
                                    <?php else : ?>
                                        <button type="button" class="button button-primary" disabled>
                                            <?php echo esc_html__('Connect Instagram', 'ff-addons'); ?>
                                        </button>
                                        <span class="description"><?php echo esc_html__('Save App ID and App Secret first.', 'ff-addons'); ?></span>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                            <p class="description">
                                <?php echo esc_html__('Connect requests the instagram_business_basic scope (read own profile/media). In Development mode only app roles/testers can authorize; Live + App Review is required for other accounts.', 'ff-addons'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ff_addons_instagram_token"><?php echo esc_html__('Instagram access token', 'ff-addons'); ?></label></th>
                        <td>
                            <input type="password" class="regular-text" id="ff_addons_instagram_token" name="ff_addons_instagram_token"
                                   value="<?php echo $igToken !== '' ? '********' : ''; ?>"
                                   autocomplete="off"
                                   placeholder="<?php echo esc_attr__('Filled by Connect, or paste a long-lived token', 'ff-addons'); ?>">
                            <p class="description">
                                <?php echo esc_html__('Usually filled by Connect Instagram. You can still paste a long-lived token manually. Required for FF Instagram “Auto from Instagram” (custom thumbnail grid — not embeds).', 'ff-addons'); ?>
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
                                <?php echo esc_html__('Auto-filled by Connect when available. Only needed manually if graph.instagram.com/me/media fails and you use a Facebook Graph / Page token.', 'ff-addons'); ?>
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
            <?php if (! $igConnected) : ?>
                <p style="color:#646970;"><strong><?php echo esc_html__('Not connected', 'ff-addons'); ?></strong>
                    — <?php echo esc_html__('Use Connect Instagram above (or paste a token), then set FF Instagram Source to “Auto from Instagram”.', 'ff-addons'); ?></p>
            <?php else : ?>
                <p style="color:#008a20;"><strong><?php echo esc_html__('Connected', 'ff-addons'); ?></strong>
                    — <?php echo esc_html__('Feed refreshes automatically using Cache TTL. Connecting or saving a new token clears the media cache.', 'ff-addons'); ?></p>
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
