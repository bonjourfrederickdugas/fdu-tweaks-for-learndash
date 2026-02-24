<?php
namespace FDU\TweaksForLearnDash\Rest;

class Permissions {

    public static function can_read(): bool {
        return current_user_can( 'read' );
    }

    public static function can_manage(): bool {
        return current_user_can( 'manage_options' );
    }
}