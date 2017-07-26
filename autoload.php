<?php

namespace ArcStone\AMO;

defined( 'ABSPATH' ) or die();

spl_autoload_register( function ( $name ) {

	if ( strpos( strtolower($name), 'arcstone' ) !== 0 ) {
		return;
	}

	$ds = DIRECTORY_SEPARATOR;
    $file = str_replace( '\\', $ds, $name );
    $filepath = __DIR__ . $ds . 'classes' . $ds .$file.'.php';

    if ( is_file( $filepath ) ) {
		require_once $filepath;
    }
});
