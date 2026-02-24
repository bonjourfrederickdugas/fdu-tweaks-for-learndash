<?php
namespace FDU\TweaksForLearnDash\Tweaks;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/DisableDashboard.php';
require_once __DIR__ . '/DisablePayments.php';
require_once __DIR__ . '/DisableCourseGridMeta.php';
require_once __DIR__ . '/DisableCertificates.php';
require_once __DIR__ . '/DisableLearnDashBlocksHeadings.php';
require_once __DIR__ . '/SimplifyLearnDashNavigation.php';
require_once __DIR__ . '/DisableLearnDashChallengeExams.php';
require_once __DIR__ . '/DisableLearnDashGroups.php';
require_once __DIR__ . '/DisableAccountEmails.php';
require_once __DIR__ . '/DisableSearch.php';

final class Manager {

    /**
     * Register runtime hooks.
     */
    public static function init(): void {
        add_action( 'init', [ __CLASS__, 'maybe_apply' ] );
    }

    /**
     * Apply enabled tweaks.
     */
    public static function maybe_apply(): void {
        $settings = static::get_settings();

        $map = [
            'disable_dashboard'        => DisableDashboard::class,
            'disable_payments'         => DisablePayments::class,
            'disable_course_grid_meta' => DisableCourseGridMeta::class,
            'disable_certificates'      => DisableCertificates::class,
            'disable_learndash_blocks_headings' => DisableLearnDashBlocksHeadings::class,
            'simplify_learndash_navigation' => SimplifyLearnDashNavigation::class,
            'disable_learndash_challenge_exams' => DisableLearnDashChallengeExams::class,
            'disable_learndash_groups' => DisableLearnDashGroups::class,
            'disable_wp_account_emails' => DisableAccountEmails::class,
            'disable_wp_search' => DisableSearch::class,
        ];

        foreach ( $map as $key => $class ) {
            if ( ! empty( $settings[ $key ] ) ) {
                $class::apply();
            }
        }
    }

    /**
     * Get plugin settings from global store.
     */
    protected static function get_settings(): array {
        $all         = get_option( 'fdu_global_settings', [] );
        $plugin_file = plugin_basename( FDU_TWEAKS_FILE );

        return isset( $all[ $plugin_file ] ) && is_array( $all[ $plugin_file ] )
            ? $all[ $plugin_file ]
            : [];
    }
}