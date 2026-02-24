<?php
namespace FDU\TweaksForLearnDash;

require_once __DIR__ . '/Admin/Enqueue.php';
require_once __DIR__ . '/Admin/Menu.php';
require_once __DIR__ . '/Blocks/Registry.php';
require_once __DIR__ . '/Rest/Routes.php';
require_once __DIR__ . '/Tweaks/Manager.php';
require_once __DIR__ . '/Admin/Settings.php';

use FDU\TweaksForLearnDash\Admin\Enqueue;
use FDU\TweaksForLearnDash\Admin\Menu;
use FDU\TweaksForLearnDash\Blocks\Registry;
use FDU\TweaksForLearnDash\Rest\Routes;
use FDU\TweaksForLearnDash\Tweaks\Manager;
use FDU\TweaksForLearnDash\Admin\Settings;

final class Plugin {

    public static function init(): void {
    Routes::register();

    Menu::register();
    Enqueue::init();
    Registry::init();

    Manager::init();
    Settings::init();
    }
}