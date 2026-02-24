<?php
declare(strict_types=1);

namespace FDU\Core\Filesystem;

/**
 * Centralized filesystem directories management for all FDU plugins.
 *
 * This class is responsible for ensuring shared directories exist
 * under wp-content/uploads/fdu/.
 *
 * It is safe to call these methods multiple times.
 */
final class Directories
{
    /**
     * Ensure the base FDU uploads directory exists.
     *
     * Creates: wp-content/uploads/fdu/
     *
     * @return string Absolute path to the base FDU directory.
     */
    public static function ensure_base(): string
    {
        $uploads = wp_upload_dir();

        $basedir = isset( $uploads['basedir'] ) && is_string( $uploads['basedir'] )
            ? $uploads['basedir']
            : '';

        $base_dir = trailingslashit( $basedir ) . 'fdu';

        if ( ! is_dir( $base_dir ) ) {
            wp_mkdir_p( $base_dir );
        }

        return $base_dir;
    }

    /**
     * Ensure a plugin-specific directory exists under uploads/fdu/.
     *
     * Example:
     *  ensure_plugin( 'uald' ) => wp-content/uploads/fdu/uald/
     *
     * @param string $slug Plugin slug or directory name.
     * @return string Absolute path to the plugin directory.
     */
    public static function ensure_plugin( string $slug ): string
    {
        $base_dir = self::ensure_base();

        $slug = sanitize_key( $slug );

        $plugin_dir = trailingslashit( $base_dir ) . $slug;

        if ( ! is_dir( $plugin_dir ) ) {
            wp_mkdir_p( $plugin_dir );
        }

        return $plugin_dir;
    }

    /**
     * Get the base FDU uploads directory path without creating it.
     *
     * @return string Absolute path.
     */
    public static function get_base(): string
    {
        $uploads = wp_upload_dir();

        $basedir = isset( $uploads['basedir'] ) && is_string( $uploads['basedir'] )
            ? $uploads['basedir']
            : '';

        return trailingslashit( $basedir ) . 'fdu';
    }

    /**
     * Get the base FDU uploads directory URL.
     *
     * @return string Base URL.
     */
    public static function get_base_url(): string
    {
        $uploads = wp_upload_dir();

        $baseurl = isset( $uploads['baseurl'] ) && is_string( $uploads['baseurl'] )
            ? $uploads['baseurl']
            : '';

        return trailingslashit( $baseurl ) . 'fdu';
    }
}
