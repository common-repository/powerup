<?php

/**
 *
 * @package   PowerUp
 * @author    GS Plugins <hello@gsplugins.com>
 * @license   GPL-2.0+
 * @link      https://www.gsplugins.com
 * @copyright 2022 GS Plugins
 *
 * @wordpress-plugin
 * Plugin Name:		PowerUp
 * Plugin URI:		https://www.gsplugins.com/product/powerup
 * Description:     SuperCharge your WordPress site with the PowerUp plugin.
 * Version:         1.0.4
 * Author:       	GS Plugins
 * Author URI:      https://www.gsplugins.com
 * Text Domain:     powerup
 * Domain Path:     /languages
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 */


/**
 * Protect direct access
 */
if (!defined('ABSPATH')) die();

/**
 * Defining constants
 */
if (!defined('GSPU_VERSION')) {
    define('GSPU_VERSION', '1.0.4');
}
if (!defined('GSPU_PLUGIN_DIR')) {
    define('GSPU_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('GSPU_PLUGIN_URI')) {
    define('GSPU_PLUGIN_URI', plugins_url('', __FILE__));
}
if (!defined('GSPU_BASE_NAME')) {
    define('GSPU_BASE_NAME', plugin_basename(__FILE__));
}

require_once GSPU_PLUGIN_DIR . 'includes/plugin.php';
