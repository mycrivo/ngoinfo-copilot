<?php
/**
 * Autoloader for NGOInfo Copilot plugin classes
 *
 * @package NGOInfo\Copilot
 */

namespace NGOInfo\Copilot;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PSR-4 Autoloader for plugin classes
 */
class Autoloader {

	/**
	 * Register autoloader
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Autoload classes
	 *
	 * @param string $class_name Class name to load.
	 */
	public static function autoload( $class_name ) {
		// Check if this is our namespace
		if ( 0 !== strpos( $class_name, 'NGOInfo\\Copilot\\' ) ) {
			return;
		}

		// Remove namespace prefix
		$class_name = str_replace( 'NGOInfo\\Copilot\\', '', $class_name );

		// Convert class name to file name
		$class_name = str_replace( '_', '-', strtolower( $class_name ) );
		$file_name  = 'class-' . $class_name . '.php';

		// Build file path
		$file_path = NGOINFO_COPILOT_PLUGIN_DIR . 'includes/' . $file_name;

		// Load file if it exists
		if ( file_exists( $file_path ) ) {
			require_once $file_path;
		}
	}
}




