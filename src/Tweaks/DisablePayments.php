<?php
namespace FDU\TweaksForLearnDash\Tweaks;

defined( 'ABSPATH' ) || exit;

final class DisablePayments {

    public static function apply(): void {

        add_filter(
            'learndash_settings_fields',
            [ __CLASS__, 'filter_price_types' ],
            10,
            2
        );

        add_filter(
            'learndash_admin_tab_sets',
            [ __CLASS__, 'remove_payments_tab' ],
            10,
            3
        );

        add_filter(
            'learndash_payment_gateways',
            [ __CLASS__, 'disable_gateways' ],
            99
        );

        /**
         * Completely disable LearnDash Orders / Transactions (sfwd-transactions).
         */
        add_action( 'admin_menu', function () {

            // Remove Orders/Transactions submenu under LearnDash
            remove_submenu_page(
                'learndash-lms',
                'edit.php?post_type=sfwd-transactions'
            );

            // Fallback: remove as top-level menu if registered differently
            remove_menu_page( 'edit.php?post_type=sfwd-transactions' );

        }, 100 );

        add_action( 'admin_init', function () {

            // Block list table access
            if (
                is_admin()
                && isset( $_GET['post_type'] )
                && $_GET['post_type'] === 'sfwd-transactions'
            ) {
                wp_safe_redirect( admin_url() );
                exit;
            }

            // Block direct edit access
            if (
                is_admin()
                && isset( $_GET['post'], $_GET['action'] )
                && $_GET['action'] === 'edit'
            ) {
                $post_id = absint( $_GET['post'] );
                if ( $post_id && get_post_type( $post_id ) === 'sfwd-transactions' ) {
                    wp_safe_redirect( admin_url() );
                    exit;
                }
            }

        }, 100 );
    }

    public static function filter_price_types( $fields, $metabox_key ) {
        foreach ( [ 'course_price_type', 'group_price_type' ] as $key ) {
            if ( isset( $fields[ $key ]['options'] ) ) {
                unset(
                    $fields[ $key ]['options']['paynow'],
                    $fields[ $key ]['options']['sufduribe'],
                    $fields[ $key ]['options']['free'],
                    $fields[ $key ]['options']['open']
                );
            }
        }

        return $fields;
    }

    public static function remove_payments_tab( $tabsets ) {
        foreach ( $tabsets as $key => $tab ) {
            if ( isset( $tab['id'] ) && $tab['id'] === 'admin_page_learndash_lms_payments' ) {
                unset( $tabsets[ $key ] );
            }
        }
        return $tabsets;
    }

    public static function disable_gateways( $gateways ) {
        if ( is_array( $gateways ) && class_exists( 'Learndash_Unknown_Gateway' ) ) {
            return [ new \Learndash_Unknown_Gateway() ];
        }
        return [];
    }
}