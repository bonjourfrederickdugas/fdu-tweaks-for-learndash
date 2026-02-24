<?php
namespace FDU\Core;

use FDU\Core\Admin\Menu;
use FDU\Core\Admin\Settings;

final class Core {

    public const VERSION = '1.0.0';

    public static function init(): void {
        spl_autoload_register( function ( $class ) {
            if ( strpos( $class, __NAMESPACE__ ) !== 0 ) {
                return;
            }

            $path = __DIR__ . '/' . str_replace(
                [ __NAMESPACE__ . '\\', '\\' ],
                [ '', '/' ],
                $class
            ) . '.php';

            if ( file_exists( $path ) ) {
                require_once $path;
            }
        });

        Menu::register();
        Settings::register();
    }
}




