<?php
/**
 * Plugin Name: FDU Tweaks for LearnDash
 * Description: Tweaks and enhancements for LearnDash by Boreal Shifts Consulting (FDU).
 * Version: 1.0.0
 * Author: Frederick Dugas Consultingc
 * Author URI: https://borealshifts.com
 * License: GPL-2.0+
 * Text Domain: fdu-plugin
 */

require_once __DIR__ . '/vendor/fdu-core/bootstrap.php';
require_once __DIR__ . '/src/Plugin.php';
require_once __DIR__ . '/src/Database/Installer.php';

use FDU\Core\Licensing\LicenseGate;
use FDU\TweaksForLearnDash\Plugin;

defined( 'ABSPATH' ) || exit;

define( 'FDU_TWEAKS_VERSION', '1.0.0' );
define( 'FDU_TWEAKS_DB_VERSION', '1.0.0' );
define( 'FDU_TWEAKS_FILE', __FILE__ );
define( 'FDU_TWEAKS_DIR', plugin_dir_path( __FILE__ ) );
define( 'FDU_TWEAKS_URL', plugin_dir_url( __FILE__ ) );

register_activation_hook(
    FDU_TWEAKS_FILE,
    [ 'FDU\\TweaksForLearnDash\\Database\\Installer', 'install' ]
);

if ( ! LicenseGate::allow( __FILE__ ) ) {
    return;
}

Plugin::init();