<?php
/**
 * Plugin Name: FDU Tweaks for LearnDash
 * Description: Tweaks and enhancements for LearnDash by Frederick Dugas Consulting.
 * Version: 1.0.0
 * Author: Frederick Dugas Consulting
 * Author URI: https://frederickdugas.com
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

if ( ! class_exists( 'LearnDash_LMS' ) && ! defined( 'LEARNDASH_VERSION' ) ) {
    return;
}

Plugin::init();