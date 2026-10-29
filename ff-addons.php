<?php
/**
 * Plugin Name: FF Addons
 * Plugin URI: https://facefood.cafe
 * Description: Facefood add-ons for Elementor and WPBakery — menu, deals, and CTA blocks powered by the Facefood Laravel API.
 * Version: 1.1.0
 * Author: Facefood
 * Author URI: https://facefood.cafe
 * Text Domain: ff-addons
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Elementor tested up to: 3.25
 */

if (! defined('ABSPATH')) {
    exit;
}

define('FF_ADDONS_VERSION', '1.1.0');
define('FF_ADDONS_FILE', __FILE__);
define('FF_ADDONS_DIR', plugin_dir_path(__FILE__));
define('FF_ADDONS_URL', plugin_dir_url(__FILE__));
define('FF_ADDONS_BASENAME', plugin_basename(__FILE__));

require_once FF_ADDONS_DIR . 'includes/class-ff-addons-api.php';
require_once FF_ADDONS_DIR . 'includes/class-ff-addons-helpers.php';
require_once FF_ADDONS_DIR . 'includes/class-ff-addons-admin.php';
require_once FF_ADDONS_DIR . 'includes/class-ff-addons-plugin.php';

register_activation_hook(__FILE__, ['FF_Addons_Plugin', 'activate']);

add_action('plugins_loaded', static function (): void {
    FF_Addons_Plugin::instance()->init();
});
