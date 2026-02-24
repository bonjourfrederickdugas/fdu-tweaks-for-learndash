<?php
namespace FDU\TweaksForLearnDash\Tweaks;

defined( 'ABSPATH' ) || exit;

/**
 * Disable LearnDash Groups (safe soft-disable).
 *
 * LearnDash Groups are referenced internally for access and reporting. Unregistering the CPT
 * can cause REST/Gutenberg or course editor errors. This tweak therefore disables Groups
 * by hiding UI, locking capabilities, disabling REST exposure, removing blocks, removing menus,
 * and blocking direct admin access.
 */
final class DisableLearnDashGroups {

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
		 * 1) Disable the Groups post type safely (do not unregister).
		 */
		add_filter( 'register_post_type_args', function ( $args, $post_type ) {

			if ( $post_type !== 'groups' ) {
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
		 * 2) Remove admin menu items (defensive).
		 */
		add_action( 'admin_menu', function () {

			// Remove as submenu under LearnDash, if registered there.
			remove_submenu_page(
				'learndash-lms',
				'edit.php?post_type=groups'
			);

			// Fallback: remove top-level menu if registered differently.
			remove_menu_page( 'edit.php?post_type=groups' );

		}, 100 );

		/**
		 * 3) Block direct admin access to list/edit screens (failsafe).
		 */
		add_action( 'admin_init', function () {

			// Block list table access.
			if (
				is_admin()
				&& isset( $_GET['post_type'] )
				&& $_GET['post_type'] === 'groups'
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
				if ( $post_id && get_post_type( $post_id ) === 'groups' ) {
					wp_safe_redirect( admin_url() );
					exit;
				}
			}

		}, 100 );

		/**
		 * 4) Remove known LearnDash Group blocks (guarded).
		 */
		add_action( 'init', function () {

			if ( ! function_exists( 'unregister_block_type' ) || ! class_exists( '\WP_Block_Type_Registry' ) ) {
				return;
			}

			$registry = \WP_Block_Type_Registry::get_instance();
			$blocks   = [
				'learndash/ld-group',
				'learndash/ld-group-list',
				'learndash/ld-group-enrollment',
			];

			foreach ( $blocks as $block_name ) {
				$is_registered = false;

				if ( is_object( $registry ) ) {
					if ( method_exists( $registry, 'is_registered' ) ) {
						$is_registered = $registry->is_registered( $block_name );
					} elseif ( method_exists( $registry, 'get_registered' ) ) {
						$is_registered = (bool) $registry->get_registered( $block_name );
					}
				}

				if ( $is_registered ) {
					unregister_block_type( $block_name );
				}
			}

		}, 100 );

		/**
		 * 5) Hard-remove Group blocks from the inserter (even if registered late).
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
								'learndash/ld-group',
								'learndash/ld-group-list',
								'learndash/ld-group-enrollment',
							]
						)
					);
				}

				return $allowed_block_types;
			},
			100,
			2
		);

		/**
		 * 6) Remove any course/user selectors that query the Groups post type.
		 *
		 * LearnDash injects many settings fields dynamically using Select2. The most reliable
		 * way to remove only Group-related fields without impacting other settings is to remove
		 * any field wrapper that contains a select2 query targeting post_type=groups.
		 */
		add_action( 'admin_footer', function () {

			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

			$is_course_edit   = ( $screen && $screen->post_type === 'sfwd-courses' );
			$is_user_edit     = ( $screen && in_array( $screen->base, [ 'user-edit', 'profile' ], true ) );
			$is_ld_settings   = ( $screen && $screen->id === 'learndash-lms_page_learndash_lms_settings' );

			if ( ! $is_course_edit && ! $is_user_edit && ! $is_ld_settings ) {
				return;
			}
			?>
			<script>
			(function(){
				function removeGroupSelectors() {

					// Select2 query payloads are embedded in data-select2-query-data.
					var selects = document.querySelectorAll(
						'select[data-select2-query-data*="\\\"post_type\\\":\\\"groups\\\""],' +
						'select[data-select2-query-data*="post_type\\\":\\\"groups\\\""],' +
						'select[data-select2-query-data*="\\\"post_type\\\":\\\"groups\\\""]'
					);

					selects.forEach(function(sel){
						// Prefer removing the LearnDash settings field wrapper.
						var wrapper = sel.closest(".sfwd_input");
						if (wrapper) {
							wrapper.remove();
							return;
						}

						// Fallback: remove nearest container if structure differs.
						var parent = sel.parentElement;
						if (parent) {
							parent.remove();
						}
					});
				}

				function removeGroupAppearanceSetting() {

					// Remove the "Group Page" row from LearnDash → Settings → Appearance
					var checkbox = document.getElementById('learndash_settings_appearance_group_enabled');
					if (!checkbox) {
						return;
					}

					var row = checkbox.closest('tr');
					if (row) {
						row.remove();
					}
				}

				// Initial pass
				removeGroupSelectors();
				removeGroupAppearanceSetting();

				// Observe for late LearnDash injections (Select2 / settings refresh)
				var observer = new MutationObserver(function(){
					removeGroupSelectors();
					removeGroupAppearanceSetting();
				});

				observer.observe(document.body, { childList: true, subtree: true });
			})();
			</script>
			<?php
		}, 100 );
	}
}