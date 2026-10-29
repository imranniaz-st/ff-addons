<?php

if (! defined('ABSPATH')) {
    exit;
}

class FF_Addons_Helpers
{
    /**
     * Normalize Laravel resource / list payloads to a plain list of arrays.
     *
     * @return list<array<string, mixed>>
     */
    public static function extract_list(array|WP_Error $response): array
    {
        if (is_wp_error($response)) {
            return [];
        }

        $items = $response['data'] ?? $response;
        if (! is_array($items)) {
            return [];
        }

        $out = [];
        foreach ($items as $item) {
            if (is_array($item)) {
                $out[] = $item;
            }
        }

        return $out;
    }

    /**
     * Category slug => label for Elementor / WPBakery selects.
     *
     * @return array<string, string>
     */
    public static function category_options(bool $include_all = true): array
    {
        $options = [];
        if ($include_all) {
            $options[''] = __('All categories', 'ff-addons');
        }

        $api = new FF_Addons_Api();
        // Fresh list while editing in Elementor.
        if (self::is_elementor_edit()) {
            $api->bypass_cache(true);
        }

        $list = self::extract_list($api->get_categories());
        usort($list, static function (array $a, array $b): int {
            return ((int) ($a['sort_order'] ?? 0)) <=> ((int) ($b['sort_order'] ?? 0));
        });

        foreach ($list as $cat) {
            $slug = (string) ($cat['slug'] ?? '');
            $name = (string) ($cat['name'] ?? $slug);
            if ($slug === '') {
                continue;
            }
            $count = isset($cat['products_count']) ? (int) $cat['products_count'] : null;
            $label = $count !== null ? sprintf('%s (%d)', $name, $count) : $name;
            $options[$slug] = $label;
        }

        // Fallback if API is unreachable — still useful in the editor.
        if (count($options) <= ($include_all ? 1 : 0)) {
            foreach (self::fallback_categories() as $slug => $name) {
                $options[$slug] = $name;
            }
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public static function fallback_categories(): array
    {
        return [
            'shawarma' => 'Shawarma',
            'afghani' => 'Afghani',
            'burgers' => 'Burgers',
            'drinks' => 'Drinks',
            'roll-paratha' => 'Roll Paratha',
            'platters' => 'Platters',
            'broast' => 'Broast',
            'wraps' => 'Wraps',
            'fries' => 'Fries',
            'crispy' => 'Crispy',
            'kids-meal' => 'Kids Meal',
            'extras' => 'Extras',
        ];
    }

    public static function currency(): string
    {
        return (string) get_option('ff_addons_currency', 'Rs.');
    }

    public static function format_price(float|int|string|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '';
        }

        return self::currency() . ' ' . number_format((float) $amount, 0);
    }

    public static function is_elementor_edit(): bool
    {
        if (! class_exists('\Elementor\Plugin')) {
            return false;
        }

        $plugin = \Elementor\Plugin::$instance;
        if (! empty($plugin->editor) && method_exists($plugin->editor, 'is_edit_mode') && $plugin->editor->is_edit_mode()) {
            return true;
        }
        if (! empty($plugin->preview) && method_exists($plugin->preview, 'is_preview_mode') && $plugin->preview->is_preview_mode()) {
            return true;
        }

        return false;
    }
}
