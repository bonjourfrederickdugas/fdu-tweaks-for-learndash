<?php
namespace FDU\TweaksForLearnDash\Tweaks;

defined( 'ABSPATH' ) || exit;

final class DisableAccountEmails {

    public static function apply(): void {
        // Disable admin notification on new user registration.
        add_filter( 'wp_new_user_notification_email_admin', '__return_false' );

        // Disable user password reset emails.
        add_filter( 'send_password_change_email', '__return_false' );
        add_filter( 'send_email_change_email', '__return_false' );
        add_filter( 'retrieve_password_notification_email', '__return_false' );

        // Ensure only user-facing notifications are sent when explicitly requested.
        add_filter(
            'wp_new_user_notification_email',
            [ __CLASS__, 'filter_new_user_notification' ],
            10,
            3
        );
    }

    /**
     * Filters new user notification emails.
     *
     * Prevents admin notifications and allows user notifications only.
     */
    public static function filter_new_user_notification(
        $email,
        $user,
        $blogname
    ) {
        // If WordPress tries to send an admin email, block it.
        if ( empty( $email ) || empty( $user ) ) {
            return false;
        }

        return $email;
    }
}
