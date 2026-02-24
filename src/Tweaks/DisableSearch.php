<?php
namespace FDU\TweaksForLearnDash\Tweaks;

defined( 'ABSPATH' ) || exit;

use WP_Block_Type_Registry;

final class DisableSearch {

    public static function apply(): void {
        // Disable the classic search widget.
        add_action( 'widgets_init', [ __CLASS__, 'disable_search_widget' ], 1 );

        // Disable frontend search queries.
        if ( ! is_admin() ) {
            add_action( 'parse_query', [ __CLASS__, 'block_search_query' ], 5 );
        }

        // Remove search form output.
        add_filter( 'get_search_form', '__return_empty_string', 999 );

        // Remove search from admin bar.
        add_action( 'admin_bar_menu', [ __CLASS__, 'remove_admin_bar_search' ], 99999 );

        // Disable Yoast SEO search schema if present.
        add_filter( 'disable_wpseo_json_ld_search', '__return_true' );

        // Disable core search block.
        add_action( 'init', [ __CLASS__, 'disable_core_search_block' ], 11 );
    }

    public static function disable_search_widget(): void {
        unregister_widget( 'WP_Widget_Search' );
    }

    public static function disable_core_search_block(): void {
        if ( function_exists( 'unregister_block_type' ) ) {
            $block = 'core/search';

            if ( WP_Block_Type_Registry::get_instance()->is_registered( $block ) ) {
                unregister_block_type( $block );
            }
        }
    }

    public static function block_search_query( $query ): void {
        if ( $query->is_search() && $query->is_main_query() ) {
            unset( $_GET['s'], $_POST['s'], $_REQUEST['s'] );
            unset( $query->query['s'] );

            $query->set( 's', '' );
            $query->is_search = false;
            $query->set_404();

            status_header( 404 );
            nocache_headers();
        }
    }

    public static function remove_admin_bar_search( $wp_admin_bar ): void {
        $wp_admin_bar->remove_menu( 'search' );
    }
}