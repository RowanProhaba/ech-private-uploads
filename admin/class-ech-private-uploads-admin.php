<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://127.0.0.1
 * @since      1.0.0
 *
 * @package    Ech_Private_Uploads
 * @subpackage Ech_Private_Uploads/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ech_Private_Uploads
 * @subpackage Ech_Private_Uploads/admin
 * @author     Rowan Chang <rowanchang@prohaba.com>
 */
class Ech_Private_Uploads_Admin
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Ech_Private_Uploads_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Ech_Private_Uploads_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        if ((isset($_GET['page']) && $_GET['page'] == 'ech_private_uploads_library') || (isset($_GET['page']) && $_GET['page'] == 'ech_private_uploads_upload')) {
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ech-private-uploads-admin.css', [], $this->version, 'all');
        }

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Ech_Private_Uploads_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Ech_Private_Uploads_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        if ((isset($_GET['page']) && $_GET['page'] == 'ech_private_uploads_library') || (isset($_GET['page']) && $_GET['page'] == 'ech_private_uploads_upload')) {
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ech-private-uploads-admin.js', [ 'jquery' ], $this->version, false);
        }

    }

    /**
     * ^^^ Add ECH Private Uploads Admin menu
     *
     * @since    1.0.0
     */
    public function epu_admin_menu()
    {
        // Main Menu
        add_menu_page(
            'ECH Private Uploads',
            'ECH Private Uploads',
            'manage_options',
            'ech_private_uploads_upload',
            [ $this, 'epu_upload_page'],
            'dashicons-hidden',
            10,
        );
    }

    // return views
    public function epu_upload_page()
    {
        require_once('partials/ech-private-uploads-admin-display.php');
    }

    /**
     * ^^^ Register custom fields for plugin settings
     *
     * @since    1.0.0
     */

    public function reg_epu_upload_management()
    {

        register_setting(
            'ech_private_uploads_settings',
            'ech_private_uploads_folder_name',
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => 'ech-private-uploads',
            ],
        );
    }

    public function ech_private_uploads_file()
    {
        // 檢查 nonce
        check_ajax_referer('ech_private_upload_nonce', 'nonce');

        // 檢查是否有檔案上傳
        if (empty($_FILES['ech_file'])) {
            wp_send_json_error('No files uploaded');
        }

        $max_file_size = 10 * 1024 * 1024; // 10MB

        $allowed_types = [
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png'          => 'image/png',
            'gif'          => 'image/gif',
            'svg'          => 'image/svg',
            'webp'          => 'image/webp',
            'pdf'          => 'application/pdf',
        ];

        $files = $_FILES['ech_file'];
        if (!is_array($files['name'])) {
            $files = [
                'name'     => [$files['name']],
                'type'     => [$files['type']],
                'tmp_name' => [$files['tmp_name']],
                'error'    => [$files['error']],
                'size'     => [$files['size']],
            ];
        }

        $folder = get_option('ech_private_uploads_folder_name', 'ech-private-uploads');
        $target_dir = WP_CONTENT_DIR . '/' . $folder;

        // 確保資料夾存在
        if (!file_exists($target_dir)) {
            wp_mkdir_p($target_dir);
        }

        // 確保資料夾可寫
        if (!is_writable($target_dir)) {
            wp_send_json_error('Target directory is not writable: ' . $target_dir);
        }

        $results = [];
        $errors = [];

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $errors[] = 'Upload error for ' . $files['name'][$i];
                continue;
            }

            if ($files['size'][$i] > $max_file_size) {
                $errors[] = 'File too large: ' . $files['name'][$i] . ' (max 10MB)';
                continue;
            }

            // check file type
            $filetype = wp_check_filetype(basename($files['name'][$i]), $allowed_types);
            if (!$filetype['type']) {
                $errors[] = 'Invalid file type: ' . $files['name'][$i] . '. Allowed types: JPG, PNG, GIF, SVG, WEBP, PDF';
                continue;
            }

            // rename file
			$info = pathinfo($files['name'][$i]);
			$ext = isset($info['extension']) ? '.' . strtolower($info['extension']) : '';
			$newname = wp_generate_password(10, false);
			$file_name = $newname . $ext;

            // generate a unique filename
            $file_name = wp_unique_filename($target_dir, $file_name);
            $target_file = $target_dir . '/' . $file_name;
            $relative_path = $folder . '/' . $file_name;

            // move file to target directory
            if (!move_uploaded_file($files['tmp_name'][$i], $target_file)) {
                $errors[] = 'Move failed for ' . $files['name'][$i];
                continue;
            }

            $attachment = [
                'guid'           => WP_CONTENT_URL . '/' . $relative_path,
                'post_mime_type' => $filetype['type'],
                'post_title'     => sanitize_file_name($file_name),
                'post_content'   => '',
                'post_status'    => 'private',
                'post_parent'    => 0,
            ];

            // save to database
            $attachment_id = wp_insert_attachment($attachment, $relative_path);
            if (is_wp_error($attachment_id)) {
                $errors[] = 'Failed to create attachment for ' . $file_name . ': ' . $attachment_id->get_error_message();
                continue;
            }

            $attachment_data = [
                'file' => $relative_path,
                'width' => 0,
                'height' => 0,
                'sizes' => [],
            ];
            wp_update_attachment_metadata($attachment_id, $attachment_data);

            $results[] = [
                'file_name'      => $file_name,
                'url'           => WP_CONTENT_URL . '/' . $relative_path,
                'mime_type'     => $filetype['type'],
                'attachment_id' => $attachment_id,
            ];
        }

        if (empty($results) && !empty($errors)) {
            wp_send_json_error(implode(', ', $errors));
        } elseif (!empty($results)) {
            wp_send_json_success($results);
        } else {
            wp_send_json_error('No files processed');
        }
    }

    //delete file
    public function delete_private_file($post_id)
    {
        $folder = get_option('ech_private_uploads_folder_name', 'ech-private-uploads');
        $file   = get_post_meta($post_id, '_wp_attached_file', true);

        if ($file && strpos($file, $folder . '/') === 0) {
            $full_path = WP_CONTENT_DIR . '/' . $file;

            if (file_exists($full_path)) {
                @unlink($full_path); // 刪檔案
            }
        }
    }
    //修正Media Library page file URL
    public function fix_private_upload_url($url, $post_id)
    {
        $folder = get_option('ech_private_uploads_folder_name', 'ech-private-uploads');
        $file   = get_post_meta($post_id, '_wp_attached_file', true);

        if ($file && strpos($file, $folder . '/') === 0) {
            return content_url($file);
        }
        return $url;
    }

    // 不產生縮圖
    public function disable_thumbnails_for_private_uploads($sizes, $metadata)
    {
        $folder = get_option('ech_private_uploads_folder_name', 'ech-private-uploads');

        if (isset($metadata['file']) && strpos($metadata['file'], $folder . '/') === 0) {
            return [];
        }

        return $sizes;
    }

    public function remove_edit_links($actions, $post)
    {
        $folder = get_option('ech_private_uploads_folder_name', 'ech-private-uploads');
        $file   = get_post_meta($post->ID, '_wp_attached_file', true);

        if ($file && strpos($file, $folder . '/') === 0) {
            unset($actions['edit']);
        }

        return $actions;
    }


    public function disable_edit_fields($form_fields, $post)
    {
        $folder = get_option('ech_private_uploads_folder_name', 'ech-private-uploads');
        $file   = get_post_meta($post->ID, '_wp_attached_file', true);
				
        if ($file && strpos($file, $folder . '/') === 0) {
            return [];
        }

        return $form_fields;
    }

    public function block_edit_page()
    {
        if (isset($_GET['post'])) {
            $post_id = intval($_GET['post']);
            $file = get_post_meta($post_id, '_wp_attached_file', true);
            $folder = get_option('ech_private_uploads_folder_name', 'ech-private-uploads');

            if ($file && strpos($file, $folder . '/') === 0) {
                wp_die(__('Editing is disabled for private uploads.', 'ech-private-uploads'));
            }
        }
    }

	public function add_robots_txt_rules($output, $public)
    {
        $folder = get_option('ech_private_uploads_folder_name', 'ech-private-uploads');
        // 避免重複 User-agent: *
        if (strpos($output, 'User-agent: *') === false) {
            $output .= "User-agent: *\n";
        }
        // 加 Disallow 規則 避免重複
        if (strpos($output, "Disallow: /wp-content/{$folder}/") === false) {
            $output .= "Disallow: /wp-content/{$folder}/\n";
        }
        return $output;
    }

    public function add_noindex_headers()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $folder = get_option('ech_private_uploads_folder_name', 'ech-private-uploads');
            if (strpos($_SERVER['REQUEST_URI'], "/wp-content/{$folder}/") !== false) {
                header('X-Robots-Tag: noindex, nofollow');
            }
        }
    }


}
