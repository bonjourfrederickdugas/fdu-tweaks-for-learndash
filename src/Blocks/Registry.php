<?php
namespace FDU\TweaksForLearnDash\Blocks;

class Registry {

    public static function init(): void {
        add_action( 'init', [ __CLASS__, 'register' ] );
    }

    public static function register(): void {
        register_block_type(
            'fdu/tweaksforlearndash-block',
            [
                'editor_script' => 'fdu-tweaksforlearndash-blocks',
                'editor_style'  => 'fdu-tweaksforlearndash-blocks',
                'style'         => 'fdu-tweaksforlearndash-blocks',
                'render_callback' => [ __CLASS__, 'render' ],
            ]
        );
    }

    public static function render(): string {
        return '<div class="fdu-tweaksforlearndash-block">FDU TweaksForLearnDash Block Output</div>';
    }
}
