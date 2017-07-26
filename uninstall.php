<?php

namespace ArcStone\AMO;

// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

include 'autoload.php';

Shortcodes::uninstall_hook();
Users::uninstall_hook();
