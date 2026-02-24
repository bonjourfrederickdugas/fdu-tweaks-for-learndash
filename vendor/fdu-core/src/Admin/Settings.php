<?php
namespace FDU\Core\Admin;

final class Settings {

    public static function register(): void {
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
    }

    public static function register_settings(): void {
        register_setting(
            'fdu_global_settings_group', // settings_fields()
            'fdu_global_settings',       // option name
            [
                'type'              => 'array',
                'sanitize_callback' => [ __CLASS__, 'sanitize' ],
                'default'           => [],
            ]
        );
    }

    /**
     * Sanitize the full global settings array.
     */
    public static function sanitize( $input ): array {
        if ( ! is_array( $input ) ) {
            $input = [];
        }

        $sanitized = [];

        foreach ( $input as $plugin_file => $plugin_settings ) {
            if ( ! is_array( $plugin_settings ) ) {
                continue;
            }

            $result = apply_filters(
                'fdu_sanitize_plugin_settings',
                [],
                $plugin_file,
                $plugin_settings
            );

            $sanitized[ $plugin_file ] = is_array( $result ) ? $result : [];
        }

        return $sanitized;
    }
}