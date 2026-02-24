<?php
namespace FDU\Core;

if ( defined( 'FDU_CORE_LOADED' ) ) {
    return;
}

define( 'FDU_CORE_LOADED', true );

require_once __DIR__ . '/src/Core.php';

Core::init();
