<?php
namespace FDU\TweaksForLearnDash\Tweaks;

defined( 'ABSPATH' ) || exit;

/**
 * Disable LearnDash-generated H2 / H3 headings in blocks and shortcodes output.
 *
 * This tweak strips structural headings injected by LearnDash while leaving
 * user-authored content, Gutenberg blocks, and page builder output untouched.
 */
final class DisableLearnDashBlocksHeadings {

	/**
	 * Static entry point expected by the Tweaks Manager.
	 */
	public static function apply(): void {
		( new self() )->register();
	}

	/**
	 * Register all filters.
	 */
	private function register(): void {

		/**
		 * Filter LearnDash-rendered content only.
		 *
		 * This runs after LearnDash builds its HTML but before it is printed.
		 */
		add_filter( 'learndash_content', [ $this, 'strip_headings' ], 20 );
		add_filter( 'learndash_the_content', [ $this, 'strip_headings' ], 20 );
		add_filter( 'learndash_shortcode_output', [ $this, 'strip_headings' ], 20 );
	}

	/**
	 * Remove H2 and H3 tags injected by LearnDash while preserving inner text.
	 *
	 * @param string $content Rendered HTML output from LearnDash.
	 * @return string
	 */
	public function strip_headings( $content ): string {

		if ( empty( $content ) || ! is_string( $content ) ) {
			return $content;
		}

		// Only operate on LearnDash-wrapped markup to avoid false positives.
		if ( strpos( $content, 'learndash' ) === false && strpos( $content, 'ld-' ) === false ) {
			return $content;
		}

		/**
		 * Remove H2/H3 tags but keep their inner content.
		 * This allows designers to re-style or re-wrap text as needed.
		 */
		$content = preg_replace(
			'~<\s*(h2|h3)(?:\s[^>]*)?>\s*(.*?)\s*</\s*\1\s*>~is',
			'',
			$content
		);

		return $content;
	}
}