<?php
namespace FDU\TweaksForLearnDash\Tweaks;

defined( 'ABSPATH' ) || exit;

/**
 * Simplify LearnDash navigation labels.
 *
 * Replaces dynamic "Previous Lesson / Topic / Quiz" and
 * "Next Lesson / Topic / Quiz" labels with simple
 * "Previous" and "Next" while preserving URLs and logic.
 */
final class SimplifyLearnDashNavigation {

	/**
	 * Static entry point expected by the Tweaks Manager.
	 */
	public static function apply(): void {
		( new self() )->register();
	}

	/**
	 * Register LearnDash output filters.
	 */
	private function register(): void {

		// Filter LearnDash-rendered output only.
		add_filter( 'learndash_content', [ $this, 'simplify_navigation_labels' ], 20 );
		add_filter( 'learndash_the_content', [ $this, 'simplify_navigation_labels' ], 20 );
		add_filter( 'learndash_shortcode_output', [ $this, 'simplify_navigation_labels' ], 20 );

		/**
		 * Fallback for Focus Mode header navigation.
		 *
		 * The header is rendered outside LearnDash content filters,
		 * so we normalize labels client-side in that specific scope.
		 */
		add_action( 'wp_enqueue_scripts', function () {

			if ( ! is_singular() ) {
				return;
			}

			wp_add_inline_script(
				'learndash-front',
				'document.addEventListener("DOMContentLoaded", function () {
					var buttons = document.querySelectorAll(
						"#ld-focus-header .ld-content-actions .ld-text"
					);

					buttons.forEach(function(el) {
						if (/^\\s*Previous\\b/i.test(el.textContent)) {
							el.textContent = "Previous";
						}
						if (/^\\s*Next\\b/i.test(el.textContent)) {
							el.textContent = "Next";
						}
					});
				});'
			);

		}, 100 );
	}

	/**
	 * Simplify Previous / Next button labels inside ld-content-actions.
	 *
	 * @param string $content Rendered LearnDash HTML.
	 * @return string
	 */
	public function simplify_navigation_labels( $content ): string {

		if ( empty( $content ) || ! is_string( $content ) ) {
			return $content;
		}

		// Only operate on LearnDash navigation markup.
		if (
			strpos( $content, 'ld-content-actions' ) === false
			&& strpos( $content, 'ld-alert' ) === false
			&& strpos( $content, 'ld-focus-header' ) === false
		) {
			return $content;
		}

		/**
		 * Normalize Previous button labels.
		 */
		$content = preg_replace(
			'~(<span[^>]*class=\"[^\"]*ld-text[^\"]*\"[^>]*>)(\s*Previous\b[^<]*)(\s*</span>)~i',
			'$1Previous$3',
			$content
		);

		/**
		 * Normalize Next button labels.
		 */
		$content = preg_replace(
			'~(<span[^>]*class=\"[^\"]*ld-text[^\"]*\"[^>]*>)(\s*Next\b[^<]*)(\s*</span>)~i',
			'$1Next$3',
			$content
		);

		/**
		 * Fallback: Normalize Previous / Next labels when no ld-text span is present
		 * (e.g. "Next Quiz" buttons rendered directly inside <a>).
		 */
		$content = preg_replace(
			'~(<a[^>]*class=\"[^\"]*ld-button[^\"]*\"[^>]*>)([\s\S]*?)Previous\b[^<]*([\s\S]*?</a>)~i',
			'$1$2Previous$3',
			$content
		);

		$content = preg_replace(
			'~(<a[^>]*class=\"[^\"]*ld-button[^\"]*\"[^>]*>)([\s\S]*?)Next\b[^<]*([\s\S]*?</a>)~i',
			'$1$2Next$3',
			$content
		);

		/**
		 * Remove "Back to Course" / course step back links.
		 */
		$content = preg_replace(
			'~<a[^>]*class=\"[^\"]*ld-course-step-back[^\"]*\"[^>]*>.*?</a>~is',
			'',
			$content
		);

		return $content;
	}
}
