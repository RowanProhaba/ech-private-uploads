<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://127.0.0.1
 * @since      1.0.0
 *
 * @package    Ech_Private_Uploads
 * @subpackage Ech_Private_Uploads/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<h1>ECH Private Upload File</h1>
<div class="wrap">

  <form method="post" id="epu_upload_form">
    <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('ech_private_upload_nonce')); ?>" />
    <div id="plupload-upload-ui" class="hide-if-no-js drag-drop">
      <div id="drag-drop-area" style="position: relative;">
        <div class="drag-drop-inside">
          <p class="drag-drop-info">Drop files to upload</p>
          <p>or</p>
          <p class="drag-drop-buttons">
            <input type="file" id="ech-file-input" multiple style="display:none;">
            <input id="ech-select-btn" type="button" value="Select Files" class="button" style="position: relative; z-index: 1;">
          </p>
        </div>
      </div>
      <div id="media-items" class="hide-if-no-js">
        <div id="upload-progress" style="display: none;">
            <p>正在上傳...</p>
            <div class="progress-bar"></div>
        </div>
        <ul id="ech-upload-list"></ul>
    </div>
  </form>
  <div class="statusMsg"></div>

</div>