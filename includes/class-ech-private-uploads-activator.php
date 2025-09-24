<?php

/**
 * Fired during plugin activation
 *
 * @link       https://127.0.0.1
 * @since      1.0.0
 *
 * @package    Ech_Private_Uploads
 * @subpackage Ech_Private_Uploads/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ech_Private_Uploads
 * @subpackage Ech_Private_Uploads/includes
 * @author     Rowan Chang <rowanchang@prohaba.com>
 */
class Ech_Private_Uploads_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$getFolderName = get_option( 'ech_private_uploads_folder_name' );
		if(empty($getFolderName) || !$getFolderName ) {
			add_option( 'ech_private_uploads_folder_name', 'ech-private-uploads' );
		}
    $folder_dir = WP_CONTENT_DIR . '/' . $getFolderName;
		error_log($folder_dir);
		if (!file_exists($folder_dir)) {
			wp_mkdir_p($folder_dir);
		}
	}

}
