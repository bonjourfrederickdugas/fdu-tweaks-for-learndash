<?php
namespace FDU\TweaksForLearnDash\Rest;

class Routes {

    public static function register(): void {
        //add_action( 'rest_api_init', [ __CLASS__, 'routes' ] );
    }

    public static function routes(): void {
        register_rest_route(
            'fdu-plugin/v1',
            '/items',
            [
                'methods'             => 'GET',
                'callback'            => [ Controller::class, 'get_items' ],
                'permission_callback' => [ Permissions::class, 'can_read' ],
            ]
        );
    }
}