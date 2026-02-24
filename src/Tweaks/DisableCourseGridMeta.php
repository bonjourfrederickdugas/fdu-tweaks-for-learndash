<?php
namespace FDU\TweaksForLearnDash\Tweaks;

defined( 'ABSPATH' ) || exit;

final class DisableCourseGridMeta {

    public static function apply(): void {
        add_filter(
            'learndash_course_grid_excluded_post_types',
            [ __CLASS__, 'excluded_post_types' ],
            99
        );
    }

    public static function excluded_post_types(): array {
        return [
            'sfwd-courses',
            'sfwd-lessons',
            'sfwd-topic',
            'sfwd-quiz',
            'sfwd-question',
            'sfwd-transactions',
            'sfwd-essays',
            'sfwd-assignment',
            'sfwd-certificates',
            'attachment',
            'post',
            'page',
            'product',
        ];
    }
}