<?php
namespace FDU\Core\Admin;

final class Menu {

    public static function register(): void {
        add_action( 'admin_menu', [ __CLASS__, 'add_menu' ] );
    }

    public static function add_menu(): void {
        add_menu_page(
            'FDU Plugins',
            'FDU Plugins',
            'manage_options',
            'fdu-plugins',
            [ __CLASS__, 'render' ],
            'dashicons-admin-plugins',
            100
        );
    }

    public static function render(): void {
        echo '<div class="wrap"><h1>FDU Plugins</h1></div>';
    }
}