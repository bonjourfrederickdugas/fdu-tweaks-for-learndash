<?php
namespace FDU\TweaksForLearnDash\Tweaks;

defined( 'ABSPATH' ) || exit;

final class DisableDashboard {

    public static function apply(): void {
        add_filter( 'learndash_dashboard_is_enabled', '__return_false' );
        add_filter( 'learndash_dashboard_tab_is_default', '__return_false' );

        add_filter(
            'learndash_header_tab_menu',
            [ __CLASS__, 'remove_tab' ],
            10,
            3
        );
    }

    public static function remove_tab( $tabs, $menu_tab_key, $post_type ) {
        foreach ( $tabs as $key => $tab ) {
            if (
                isset( $tab['id'] ) &&
                $tab['id'] === 'learndash_' . $post_type . '_dashboard'
            ) {
                unset( $tabs[ $key ] );
                break;
            }
        }

        return array_values( $tabs );
    }
}