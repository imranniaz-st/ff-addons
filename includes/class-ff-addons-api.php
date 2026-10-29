<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Thin client for the Facefood Laravel public API (app.facefood.cafe/api).
 */
class FF_Addons_Api
{
    private string $baseUrl;

    public function __construct(?string $baseUrl = null)
    {
        $configured = $baseUrl ?? (string) get_option('ff_addons_api_base_url', 'https://app.facefood.cafe/api');
        $this->baseUrl = untrailingslashit($configured);
    }

    public function get_products(array $query = []): array|WP_Error
    {
        return $this->get('/products', $query);
    }

    public function get_categories(array $query = []): array|WP_Error
    {
        return $this->get('/categories', $query);
    }

    public function get_deals(array $query = []): array|WP_Error
    {
        return $this->get('/deals', $query);
    }

    public function get_settings(): array|WP_Error
    {
        return $this->get('/settings');
    }

    /**
     * @param  array<string, scalar|null>  $query
     */
    private function get(string $path, array $query = []): array|WP_Error
    {
        $url = $this->baseUrl . '/' . ltrim($path, '/');
        if ($query !== []) {
            $url = add_query_arg(array_filter($query, static fn ($v) => $v !== null && $v !== ''), $url);
        }

        $cacheKey = 'ff_addons_' . md5($url);
        $cached = get_transient($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $response = wp_remote_get($url, [
            'timeout' => 15,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);

        if ($code < 200 || $code >= 300 || ! is_array($body)) {
            return new WP_Error(
                'ff_addons_api_error',
                sprintf(
                    /* translators: %d: HTTP status code */
                    __('Facefood API request failed (HTTP %d).', 'ff-addons'),
                    $code
                )
            );
        }

        $ttl = (int) get_option('ff_addons_cache_ttl', 300);
        set_transient($cacheKey, $body, max(60, $ttl));

        return $body;
    }
}
