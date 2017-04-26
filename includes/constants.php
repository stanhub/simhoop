<?php

	// ******************
	// site constants
	// doesn't need connection to DB
	// ******************

	// let's show errors!
	if ( isset( $_GET[ "show_errors" ] ) ) {
		ini_set( "display_errors", 1 );
		ini_set( "display_startup_errors", 1 );
		error_reporting( E_ALL );
	}

?>