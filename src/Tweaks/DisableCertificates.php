<?php
namespace FDU\TweaksForLearnDash\Tweaks;

defined( 'ABSPATH' ) || exit;

class DisableCertificates {

	public static function apply() {
		( new self() )->register();
	}

	public function register() {

		/**
		 * 1. Hide the certificate post type from admin UI without unregistering it.
		 */
		add_action( 'admin_menu', function () {
			// Remove Certificates from the LearnDash menu (submenu)
			remove_submenu_page(
				'learndash-lms',
				'edit.php?post_type=sfwd-certificates'
			);

			// Fallback: remove as top-level menu if registered that way
			remove_menu_page( 'edit.php?post_type=sfwd-certificates' );
		}, 100 );

		/**
		 * Block direct access to the Certificates admin list screen and direct edit access.
		 */
		add_action( 'admin_init', function () {

			// Block list table access
			if (
				is_admin()
				&& isset( $_GET['post_type'] )
				&& $_GET['post_type'] === 'sfwd-certificates'
			) {
				wp_safe_redirect( admin_url() );
				exit;
			}

			// Block direct edit access to certificate posts
			if (
				is_admin()
				&& isset( $_GET['post'], $_GET['action'] )
				&& $_GET['action'] === 'edit'
			) {
				$post_id = absint( $_GET['post'] );
				if ( $post_id && get_post_type( $post_id ) === 'sfwd-certificates' ) {
					wp_safe_redirect( admin_url() );
					exit;
				}
			}
		}, 100 );

		add_filter( 'learndash_post_type_args_sfwd-certificates', function ( $args ) {
			$args['show_ui'] = false;
			$args['show_in_menu'] = false;
			$args['show_in_admin_bar'] = false;
			return $args;
		}, 100 );

		add_filter( 'register_post_type_args', function ( $args, $post_type ) {
			if ( $post_type === 'sfwd-certificates' ) {
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
			}
			return $args;
		}, 100, 2 );

		add_filter( 'wp_insert_post_data', function ( $data, $postarr ) {
			if ( isset( $postarr['post_type'] ) && $postarr['post_type'] === 'sfwd-certificates' ) {
				return null;
			}
			return $data;
		}, 10, 2 );

		/**
		 * 2. Remove known LearnDash certificate shortcodes.
		 */
		add_action( 'init', function () {
			remove_shortcode( 'ld_certificate' );
			remove_shortcode( 'learndash_certificate' );
		}, 20 );

		/**
		 * 3. Prevent certificate output/rendering.
		 */
		add_filter( 'learndash_certificate_content', '__return_empty_string', 99 );

		/**
		 * 4. Block certificate printing/downloading.
		 */
		add_filter( 'learndash_certificate_allow_printing', '__return_false', 99 );

		/**
		 * 5. Remove certificate selectors from course completion settings UI.
		 */
		add_filter(
			'learndash_settings_fields',
			function ( $fields, $settings_section_key ) {
				if ( $settings_section_key === 'LearnDash_Settings_Metabox_Course_Completion_Awards' ) {
					if ( isset( $fields['certificate'] ) ) {
						unset( $fields['certificate'] );
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
				if ( $section_key === 'course_completion_awards' && isset( $fields['certificate'] ) ) {
					unset( $fields['certificate'] );
				}
				return $fields;
			},
			100,
			2
		);

		add_action( 'admin_enqueue_scripts', function () {
			$screen = get_current_screen();
			if ( ! $screen || $screen->post_type !== 'sfwd-courses' ) {
				return;
			}
			wp_add_inline_script(
				'learndash-admin',
				'jQuery(function($){
					$("#learndash-course-completion-awards_certificate_field").remove();
				});'
			);
		}, 100 );

		/**
		 * 6. Remove the entire Completion Awards metabox from course edit screens.
		 */
		add_action( 'add_meta_boxes', function () {
			remove_meta_box(
				'learndash-course-completion-awards',
				'sfwd-courses',
				'normal'
			);
		}, 100 );

		/**
		 * 7. Strip certificate shortcodes from frontend content if present.
		 */
		add_filter( 'the_content', function ( $content ) {
			if ( has_shortcode( $content, 'ld_certificate' ) || has_shortcode( $content, 'learndash_certificate' ) ) {
				$content = preg_replace( '/\[(ld_certificate|learndash_certificate)[^\]]*\]/i', '', $content );
			}
			return $content;
		}, 5 );

		/**
		 * 8. Remove certificate shortcode output injected by LearnDash course templates.
		 */
		add_action( 'wp_enqueue_scripts', function () {
			if ( ! is_singular( 'sfwd-courses' ) ) {
				return;
			}

			wp_add_inline_script(
				'learndash-front',
				'document.addEventListener("DOMContentLoaded", function () {
					var nodes = document.querySelectorAll(".learndash-wrapper");
					nodes.forEach(function(wrapper){
						if (wrapper.innerHTML.indexOf("[ld_certificate") !== -1) {
							wrapper.innerHTML = wrapper.innerHTML.replace(/\\[ld_certificate[^\\]]*\\]/gi, "");
						}
					});
				});'
			);
		}, 100 );

		/**
		 * 9. Remove certificate links from User Profile course progress details.
		 */
		add_action( 'admin_enqueue_scripts', function () {
			$screen = get_current_screen();
			if ( ! $screen || $screen->base !== 'user-edit' ) {
				return;
			}

			wp_add_inline_style(
				'wp-admin',
				'.learndash-profile-course-certificate-link { display: none !important; }'
			);

			wp_add_inline_script(
				'wp-admin',
				'document.addEventListener("DOMContentLoaded", function () {
					document.querySelectorAll(".learndash-profile-course-certificate-link").forEach(function(el){
						el.remove();
					});
				});'
			);
		}, 100 );

		/**
		 * 10. Hard-disable certificate generation at the data level.
		 */
		add_filter( 'learndash_course_certificate_link', '__return_false', 100, 3 );
		add_filter( 'learndash_user_course_certificate_link', '__return_false', 100, 3 );
		add_filter( 'learndash_certificate_url', '__return_false', 100, 3 );
		add_filter( 'learndash_get_course_certificate_id', '__return_false', 100, 2 );

		add_filter( 'learndash_course_settings', function ( $settings, $course_id ) {
			if ( isset( $settings['certificate'] ) ) {
				$settings['certificate'] = 0;
			}
			return $settings;
		}, 100, 2 );

		/**
		 * 11. Remove Certificates stat from LearnDash Profile shortcode/block.
		 */
		add_filter(
			'learndash_profile_stats',
			function ( $stats, $user_id ) {
				if ( empty( $stats ) || ! is_array( $stats ) ) {
					return $stats;
				}

				$filtered = [];

				foreach ( $stats as $stat ) {
					$stat_class = is_array( $stat ) && isset( $stat['class'] ) ? (string) $stat['class'] : '';
					$stat_title = is_array( $stat ) && isset( $stat['title'] ) ? (string) $stat['title'] : '';

					if ( $stat_class === 'ld-profile-stat-certificates' || strcasecmp( $stat_title, 'Certificates' ) === 0 ) {
						continue;
					}

					$filtered[] = $stat;
				}

				return $filtered;
			},
			100,
			2
		);

		/**
		 * 12. Deregister the LearnDash Certificate block.
		 */
		add_action( 'init', function () {
			if ( function_exists( 'unregister_block_type' ) ) {
				unregister_block_type( 'learndash/ld-certificate' );
			}
		}, 100 );

		/**
		 * 13. Remove LearnDash Certificate block from the block inserter.
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
							[ 'learndash/ld-certificate' ]
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