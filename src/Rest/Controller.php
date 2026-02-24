<?php
namespace FDU\TweaksForLearnDash\Rest;

use WP_REST_Request;
use WP_REST_Response;

class Controller {

    public static function get_items( WP_REST_Request $request ): WP_REST_Response {
        // Example payload
        $data = [
            'status' => 'ok',
            'time'   => current_time( 'mysql' ),
        ];

        return new WP_REST_Response( $data, 200 );
    }
}