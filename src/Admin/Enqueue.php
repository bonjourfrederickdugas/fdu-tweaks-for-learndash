<?php
namespace FDU\TweaksForLearnDash\Admin;

class Enqueue {

    public static function init(): void {
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin' ] );
    }

    public static function admin(): void {
        wp_enqueue_style(
            'fdu-plugin-admin',
            FDU_TWEAKS_URL . 'assets/css/admin.css',
            [],
            FDU_TWEAKS_VERSION
        );
    }
}
