<?php
namespace FDU\TweaksForLearnDash\Tweaks;

defined( 'ABSPATH' ) || exit;

/**
 * Disable LearnDash Challenge Exams.
 *
 * Hard-disables the ld-exam post type (unregisters it) and removes related
 * admin UI / block editor surfaces.
 */
final class DisableLearnDashChallengeExams {

	/**
	 * Static entry point expected by the Tweaks Manager.
	 */
	public static function apply(): void {
		( new self() )->register();
	}

	/**
	 * Register hooks.
	 */
	private function register(): void {

		/**
		 * Disable the ld-exam post type safely without unregistering it.
		 *
		 * This avoids REST/Gutenberg crashes caused by LearnDash expecting
		 * the post type to exist during course editing.
		 */
		add_filter( 'register_post_type_args', function ( $args, $post_type ) {

			if ( $post_type !== 'ld-exam' ) {
				return $args;
			}

			$args['public']              = false;
			$args['show_ui']             = false;
			$args['show_in_menu']        = false;
			$args['show_in_admin_bar']   = false;
			$args['show_in_rest']        = false;
			$args['exclude_from_search'] = true;

			// Lock down capabilities completely.
			$args['capabilities'] = [
				'edit_post'          => 'do_not_allow',
				'read_post'          => 'do_not_allow',
				'delete_post'        => 'do_not_allow',
				'edit_posts'         => 'do_not_allow',
				'edit_others_posts'  => 'do_not_allow',
				'delete_posts'       => 'do_not_allow',
				'publish_posts'      => 'do_not_allow',
				'read_private_posts' => 'do_not_allow',
				'create_posts'       => 'do_not_allow',
			];

			$args['map_meta_cap'] = true;

			return $args;

		}, 100, 2 );

		/**
		 * Remove Challenge Exam field from Course settings metabox.
		 */
		add_filter(
			'learndash_settings_fields',
			function ( $fields, $settings_section_key ) {

				if ( $settings_section_key === 'LearnDash_Settings_Metabox_Course_Display_Content' ) {
					if ( isset( $fields['exam_challenge'] ) ) {
						unset( $fields['exam_challenge'] );
					}
				}

				return $fields;
			},
			100,
			2
		);

		add_filter(
			'learndash_settings_section_fields',
			function ( $fields, $section_key ) {

				if ( $section_key === 'course_display_content' && isset( $fields['exam_challenge'] ) ) {
					unset( $fields['exam_challenge'] );
				}

				return $fields;
			},
			100,
			2
		);

		/**
		 * Remove Challenge Exam setting from Course Settings tab (alternate registry path).
		 */
		add_filter(
			'learndash_settings_metabox_fields',
			function ( $fields, $metabox_key ) {

				if ( $metabox_key === 'LearnDash_Settings_Metabox_Course_Display_Content' && isset( $fields['exam_challenge'] ) ) {
					unset( $fields['exam_challenge'] );
				}

				return $fields;
			},
			100,
			2
		);

		/**
		 * Absolute failsafe: remove Challenge Exam field after LearnDash fully renders the page.
		 * This runs in the admin footer to avoid timing issues with dynamic injections.
		 */
		add_action( 'admin_footer', function () {

			$screen = get_current_screen();
			if ( ! $screen || $screen->post_type !== 'sfwd-courses' ) {
				return;
			}
			?>
			<script>
			(function(){
				function removeChallengeExamField() {
					var el = document.getElementById('learndash-course-display-content-settings_exam_challenge_field');
					if (el) {
						el.remove();
					}
				}

				// Initial removal
				removeChallengeExamField();

				// Observe for any late re-injection
				var observer = new MutationObserver(function(){
					removeChallengeExamField();
				});

				observer.observe(document.body, {
					childList: true,
					subtree: true
				});
			})();
			</script>
			<?php
		}, 100 );

		/**
		 * Remove admin menu items (defensive).
		 */
		add_action( 'admin_menu', function () {

			// Remove as submenu under LearnDash, if registered there.
			remove_submenu_page(
				'learndash-lms',
				'edit.php?post_type=ld-exam'
			);

			// Fallback: remove top-level menu if registered differently.
			remove_menu_page( 'edit.php?post_type=ld-exam' );

		}, 100 );

		/**
		 * Block direct admin access to list/edit screens (failsafe).
		 */
		add_action( 'admin_init', function () {

			// Block list table access.
			if (
				is_admin()
				&& isset( $_GET['post_type'] )
				&& $_GET['post_type'] === 'ld-exam'
			) {
				wp_safe_redirect( admin_url() );
				exit;
			}

			// Block direct edit access.
			if (
				is_admin()
				&& isset( $_GET['post'], $_GET['action'] )
				&& $_GET['action'] === 'edit'
			) {
				$post_id = absint( $_GET['post'] );
				if ( $post_id && get_post_type( $post_id ) === 'ld-exam' ) {
					wp_safe_redirect( admin_url() );
					exit;
				}
			}

		}, 100 );

		/**
		 * Hard-remove LearnDash Exam blocks from the block inserter (even if registered late).
		 */
		add_filter(
			'allowed_block_types_all',
			function ( $allowed_block_types, $editor_context ) {

				if ( $allowed_block_types === true ) {
					$registry = \WP_Block_Type_Registry::get_instance();
					$allowed_block_types = array_keys( $registry->get_all_registered() );
				}

				if ( is_array( $allowed_block_types ) ) {
					$allowed_block_types = array_values(
						array_diff(
							$allowed_block_types,
							[
								'learndash/ld-exam',
								'learndash/ld-challenge-exam',
							]
						)
					);
				}

				return $allowed_block_types;
			},
			100,
			2
		);
	}
}
