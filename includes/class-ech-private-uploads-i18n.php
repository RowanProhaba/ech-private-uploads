<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://127.0.0.1
 * @since      1.0.0
 *
 * @package    Ech_Private_Uploads
 * @subpackage Ech_Private_Uploads/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Ech_Private_Uploads
 * @subpackage Ech_Private_Uploads/includes
 * @author     Rowan Chang <rowanchang@prohaba.com>
 */
class Ech_Private_Uploads_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'ech-private-uploads',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
