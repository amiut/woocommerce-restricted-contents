<?php
/**
 * Plugin Name: Restricted contents for Woocommerce
 * Description: Create restricted posts or partials contents in woocommerce
 * Plugin URI:  https://wwww.dornaweb.com
 * Version:     1.0
 * Author:      Dornaweb
 * Author URI:  https://wwww.dornaweb.com
 * License:     GPL
 * Text Domain: dwrestricted
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

if (! defined('DW_RESTRICTED_CONTENTS_FILE')) {
	define('DW_RESTRICTED_CONTENTS_FILE', __FILE__);
}

/**
 * Load core packages and the autoloader.
 * The SPL Autoloader needs PHP 5.6.0+ and this plugin won't work on older versions
 */
if (version_compare(PHP_VERSION, '5.6.0', '>=')) {
	require __DIR__ . '/includes/class-autoloader.php';
}

/**
 * Returns the main instance of PDF Gen.
 *
 * @since  1.0
 * @return DW_RESTRICTED_CONTENTS\App
 */
function dw_restricted_contents() {
	return DW_RESTRICTED_CONTENTS\App::instance();
}

// Global for backwards compatibility.
$GLOBALS['dw_restricted_contents'] = dw_restricted_contents();
