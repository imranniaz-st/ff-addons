<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Instagram API with Instagram Login (Business Login) OAuth helpers.
 *
 * Authorize → short-lived token → long-lived token → save for graph.instagram.com/me/media.
 */
class FF_Addons_Instagram_OAuth
{
    public const OPTION_APP_ID = 'ff_addons_instagram_app_id';
    public const OPTION_APP_SECRET = 'ff_addons_instagram_app_secret';
    public const OPTION_TOKEN = 'ff_addons_instagram_token';
    public const OPTION_USER_ID = 'ff_addons_instagram_user_id';
    public const OPTION_TOKEN_EXPIRES = 'ff_addons_instagram_token_expires';

    private const ACTION_CONNECT = 'ff_addons_ig_connect';
    private const ACTION_CALLBACK = 'ff_addons_ig_oauth';
    private const ACTION_DISCONNECT = 'ff_addons_ig_disconnect';
    private const STATE_TRANSIENT = 'ff_addons_ig_oauth_state';
    private const SCOPE = 'instagram_business_basic';
    private const GRAPH_VERSION = 'v21.0';

    public function __construct()
    {
        add_action('admin_post_' . self::ACTION_CONNECT, [$this, 'handle_connect']);
        add_action('admin_post_' . self::ACTION_CALLBACK, [$this, 'handle_callback']);
        // Meta may hit the callback without an authenticated WP cookie in rare cases;
        // still route through the same handler so capability checks run after login redirect.
        add_action('admin_post_nopriv_' . self::ACTION_CALLBACK, [$this, 'handle_callback']);
        add_action('admin_post_' . self::ACTION_DISCONNECT, [$this, 'handle_disconnect']);
        add_action('admin_notices', [$this, 'render_settings_notices']);

        add_action('ff_addons_instagram_refresh_token', [$this, 'cron_refresh_token']);
        if (! wp_next_scheduled('ff_addons_instagram_refresh_token')) {
            wp_schedule_event(time() + DAY_IN_SECONDS, 'weekly', 'ff_addons_instagram_refresh_token');
        }
    }

    public static function redirect_uri(): string
    {
        return admin_url('admin-post.php?action=' . self::ACTION_CALLBACK);
    }

    public static function connect_url(): string
    {
        return wp_nonce_url(
            admin_url('admin-post.php?action=' . self::ACTION_CONNECT),
            'ff_addons_ig_connect'
        );
    }

    public static function disconnect_url(): string
    {
        return wp_nonce_url(
            admin_url('admin-post.php?action=' . self::ACTION_DISCONNECT),
            'ff_addons_ig_disconnect'
        );
    }

    public static function is_connected(): bool
    {
        return trim((string) get_option(self::OPTION_TOKEN, '')) !== '';
    }

    public static function has_app_credentials(): bool
    {
        return trim((string) get_option(self::OPTION_APP_ID, '')) !== ''
            && trim((string) get_option(self::OPTION_APP_SECRET, '')) !== '';
    }

    public function handle_connect(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to connect Instagram.', 'ff-addons'), 403);
        }

        check_admin_referer('ff_addons_ig_connect');

        $appId = trim((string) get_option(self::OPTION_APP_ID, ''));
        $appSecret = trim((string) get_option(self::OPTION_APP_SECRET, ''));
        if ($appId === '' || $appSecret === '') {
            $this->redirect_settings('error', 'missing_app');
        }

        $state = wp_generate_password(32, false, false);
        set_transient(self::STATE_TRANSIENT . '_' . get_current_user_id(), $state, 15 * MINUTE_IN_SECONDS);

        $authUrl = add_query_arg(
            [
                'client_id' => $appId,
                'redirect_uri' => self::redirect_uri(),
                'scope' => self::SCOPE,
                'response_type' => 'code',
                'state' => $state,
            ],
            'https://www.instagram.com/oauth/authorize'
        );

        wp_redirect($authUrl);
        exit;
    }

    public function handle_callback(): void
    {
        if (! is_user_logged_in()) {
            auth_redirect();
            exit;
        }

        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to connect Instagram.', 'ff-addons'), 403);
        }

        // Meta appends #_ to the redirect; browsers strip the fragment before the request.
        $error = isset($_GET['error']) ? sanitize_text_field(wp_unslash((string) $_GET['error'])) : '';
        if ($error !== '') {
            $desc = isset($_GET['error_description'])
                ? sanitize_text_field(wp_unslash((string) $_GET['error_description']))
                : $error;
            $this->redirect_settings('error', 'oauth_denied', $desc);
        }

        $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash((string) $_GET['state'])) : '';
        $expected = (string) get_transient(self::STATE_TRANSIENT . '_' . get_current_user_id());
        delete_transient(self::STATE_TRANSIENT . '_' . get_current_user_id());
        if ($state === '' || $expected === '' || ! hash_equals($expected, $state)) {
            $this->redirect_settings('error', 'invalid_state');
        }

        $code = isset($_GET['code']) ? sanitize_text_field(wp_unslash((string) $_GET['code'])) : '';
        if ($code === '') {
            $this->redirect_settings('error', 'missing_code');
        }

        $appId = trim((string) get_option(self::OPTION_APP_ID, ''));
        $appSecret = trim((string) get_option(self::OPTION_APP_SECRET, ''));
        if ($appId === '' || $appSecret === '') {
            $this->redirect_settings('error', 'missing_app');
        }

        $short = $this->exchange_code_for_short_token($appId, $appSecret, $code);
        if (is_wp_error($short)) {
            $this->redirect_settings('error', 'token_exchange', $short->get_error_message());
        }

        $long = $this->exchange_for_long_lived_token($appSecret, (string) $short['access_token']);
        if (is_wp_error($long)) {
            $this->redirect_settings('error', 'long_lived', $long->get_error_message());
        }

        $accessToken = (string) ($long['access_token'] ?? '');
        if ($accessToken === '') {
            $this->redirect_settings('error', 'long_lived', __('Empty long-lived token response.', 'ff-addons'));
        }

        $userId = '';
        $me = $this->fetch_me($accessToken);
        if (! is_wp_error($me)) {
            // Instagram Login /me returns user_id (IG professional id) and/or id.
            $userId = (string) ($me['user_id'] ?? $me['id'] ?? '');
            if ($userId !== '' && ! preg_match('/^\d+$/', $userId)) {
                $userId = '';
            }
        }

        update_option(self::OPTION_TOKEN, $accessToken, false);
        update_option(self::OPTION_USER_ID, $userId, false);

        $expiresIn = isset($long['expires_in']) ? (int) $long['expires_in'] : (60 * DAY_IN_SECONDS);
        update_option(self::OPTION_TOKEN_EXPIRES, time() + max(DAY_IN_SECONDS, $expiresIn), false);

        FF_Addons_Plugin::instance()->clear_instagram_media_cache();

        $this->redirect_settings('success', 'connected');
    }

    public function handle_disconnect(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to disconnect Instagram.', 'ff-addons'), 403);
        }

        check_admin_referer('ff_addons_ig_disconnect');

        update_option(self::OPTION_TOKEN, '', false);
        update_option(self::OPTION_USER_ID, '', false);
        update_option(self::OPTION_TOKEN_EXPIRES, 0, false);
        FF_Addons_Plugin::instance()->clear_instagram_media_cache();

        $this->redirect_settings('success', 'disconnected');
    }

    /**
     * Weekly refresh of long-lived Instagram User tokens (valid ~60 days; refresh after 24h).
     */
    public function cron_refresh_token(): void
    {
        $token = trim((string) get_option(self::OPTION_TOKEN, ''));
        if ($token === '') {
            return;
        }

        $expires = (int) get_option(self::OPTION_TOKEN_EXPIRES, 0);
        // Only refresh when we know expiry and are within the last ~14 days, or expiry unknown.
        if ($expires > 0 && $expires > (time() + 14 * DAY_IN_SECONDS)) {
            return;
        }

        $url = add_query_arg(
            [
                'grant_type' => 'ig_refresh_token',
                'access_token' => $token,
            ],
            'https://graph.instagram.com/refresh_access_token'
        );

        $response = wp_remote_get($url, [
            'timeout' => 20,
            'headers' => ['Accept' => 'application/json'],
        ]);

        if (is_wp_error($response)) {
            return;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        if ($code < 200 || $code >= 300 || ! is_array($body) || empty($body['access_token'])) {
            return;
        }

        update_option(self::OPTION_TOKEN, (string) $body['access_token'], false);
        $expiresIn = isset($body['expires_in']) ? (int) $body['expires_in'] : (60 * DAY_IN_SECONDS);
        update_option(self::OPTION_TOKEN_EXPIRES, time() + max(DAY_IN_SECONDS, $expiresIn), false);
        FF_Addons_Plugin::instance()->clear_instagram_media_cache();
    }

    public function render_settings_notices(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash((string) $_GET['page'])) : '';
        if ($page !== 'ff-addons') {
            return;
        }

        $status = isset($_GET['ff_ig']) ? sanitize_text_field(wp_unslash((string) $_GET['ff_ig'])) : '';
        $code = isset($_GET['ff_ig_code']) ? sanitize_text_field(wp_unslash((string) $_GET['ff_ig_code'])) : '';
        if ($status === '' || $code === '') {
            return;
        }

        $detail = isset($_GET['ff_ig_msg']) ? sanitize_text_field(wp_unslash((string) $_GET['ff_ig_msg'])) : '';

        if ($status === 'success') {
            $message = $code === 'disconnected'
                ? __('Instagram disconnected. Access token and user ID were cleared.', 'ff-addons')
                : __('Instagram connected. Access token and user ID were saved; media cache cleared.', 'ff-addons');
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                esc_html($message)
            );

            return;
        }

        $map = [
            'missing_app' => __('Save your Instagram App ID and App Secret before connecting.', 'ff-addons'),
            'oauth_denied' => __('Instagram authorization was cancelled or denied.', 'ff-addons'),
            'invalid_state' => __('OAuth state mismatch. Try Connect Instagram again.', 'ff-addons'),
            'missing_code' => __('Instagram did not return an authorization code.', 'ff-addons'),
            'token_exchange' => __('Could not exchange the authorization code for an access token.', 'ff-addons'),
            'long_lived' => __('Could not exchange for a long-lived Instagram token.', 'ff-addons'),
        ];
        $message = $map[$code] ?? __('Instagram connection failed.', 'ff-addons');
        if ($detail !== '') {
            $message .= ' ' . $detail;
        }

        printf(
            '<div class="notice notice-error is-dismissible"><p>%s</p></div>',
            esc_html($message)
        );
    }

    /**
     * @return array{access_token: string, user_id?: string|int}|WP_Error
     */
    private function exchange_code_for_short_token(string $appId, string $appSecret, string $code)
    {
        $response = wp_remote_post('https://api.instagram.com/oauth/access_token', [
            'timeout' => 20,
            'body' => [
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => self::redirect_uri(),
                'code' => $code,
            ],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $http = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        if ($http < 200 || $http >= 300 || ! is_array($body) || empty($body['access_token'])) {
            $msg = is_array($body)
                ? (string) ($body['error_message'] ?? $body['error']['message'] ?? wp_json_encode($body))
                : __('Unexpected token response.', 'ff-addons');

            return new WP_Error('ff_addons_ig_short_token', $msg);
        }

        return $body;
    }

    /**
     * @return array{access_token: string, expires_in?: int}|WP_Error
     */
    private function exchange_for_long_lived_token(string $appSecret, string $shortToken)
    {
        $url = add_query_arg(
            [
                'grant_type' => 'ig_exchange_token',
                'client_secret' => $appSecret,
                'access_token' => $shortToken,
            ],
            'https://graph.instagram.com/access_token'
        );

        $response = wp_remote_get($url, [
            'timeout' => 20,
            'headers' => ['Accept' => 'application/json'],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $http = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        if ($http < 200 || $http >= 300 || ! is_array($body) || empty($body['access_token'])) {
            $msg = is_array($body)
                ? (string) ($body['error']['message'] ?? $body['error_message'] ?? wp_json_encode($body))
                : __('Unexpected long-lived token response.', 'ff-addons');

            return new WP_Error('ff_addons_ig_long_token', $msg);
        }

        return $body;
    }

    /**
     * @return array<string, mixed>|WP_Error
     */
    private function fetch_me(string $accessToken)
    {
        $url = add_query_arg(
            [
                'fields' => 'user_id,username,id',
                'access_token' => $accessToken,
            ],
            'https://graph.instagram.com/' . self::GRAPH_VERSION . '/me'
        );

        $response = wp_remote_get($url, [
            'timeout' => 15,
            'headers' => ['Accept' => 'application/json'],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $http = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        if ($http < 200 || $http >= 300 || ! is_array($body)) {
            return new WP_Error('ff_addons_ig_me', __('Could not fetch Instagram profile.', 'ff-addons'));
        }

        return $body;
    }

    private function redirect_settings(string $status, string $code, string $detail = ''): void
    {
        $args = [
            'page' => 'ff-addons',
            'ff_ig' => $status,
            'ff_ig_code' => $code,
        ];
        if ($detail !== '') {
            $args['ff_ig_msg'] = wp_strip_all_tags($detail);
        }

        wp_safe_redirect(add_query_arg($args, admin_url('options-general.php')));
        exit;
    }
}
