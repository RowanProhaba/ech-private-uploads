(function ($) {
	'use strict';
	$(function () {

		const dropzone = $("#drag-drop-area");
		const fileInput = $("#ech-file-input");
		const list = $("#ech-upload-list");
		const nonce = $("#epu_upload_form input[name='nonce']").val();
		const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB in bytes
		$("#ech-select-btn").on("click", function (e) {
			e.preventDefault();
			fileInput.click();
		});

		dropzone.on('dragover', function (e) {
			e.preventDefault();
			e.stopPropagation();
			dropzone.css("background", "#e1e1e1");
		});

		dropzone.on('dragleave', function(e) {
			e.preventDefault();
			e.stopPropagation();
			dropzone.css("background", "#fafafa");
		});

		dropzone.on('drop', function (e) {
			e.preventDefault();
			dropzone.css("background", "#fafafa");
			const files = e.originalEvent.dataTransfer.files;
			uploadFiles(files);
		});

		fileInput.on("change", function (e) {
			uploadFiles(e.target.files);
		});
		function uploadFiles(files) {
			list.empty();
			if (files.length === 0) {
				list.append($("<li>").text("請選擇檔案"));
				return;
			}

			if (files.length > 5) {
					list.append($("<li>").text("最多上傳 5 個檔案"));
					return;
			}
			const fileProgress = {};
			for (let i = 0; i < files.length; i++) {
				const file = files[i];

				if (file.size > MAX_FILE_SIZE) {
					list.append($("<li>").text("檔案過大: " + file.name + " (最大 10MB)"));
          continue;
				}
				const li = $("<li>").text("正在上傳 " + file.name + "...");
        const progress = $("<div>").addClass("progress-bar").css({ width: "0%", height: "20px", backgroundColor: "#0073aa", color: "white", textAlign: "center" });
        li.append(progress);
        list.append(li);

				const formData = new FormData();
        formData.append("action", "ech_private_uploads_file");
        formData.append("ech_file", file);
        formData.append("nonce", nonce);

				fileProgress[file.name] = { li: li, progress: progress };

				$.ajax({
					url: ajaxurl,
					type: "POST",
					data: formData,
					processData: false,
					contentType: false,
					xhr: function() {
						const xhr = new window.XMLHttpRequest();
						xhr.upload.addEventListener("progress", function(evt) {
								if (evt.lengthComputable) {
										var percent = Math.round((evt.loaded / evt.total) * 100);
										fileProgress[file.name].progress.css("width", percent + "%").text(percent + "%");
								}
						}, false);
						return xhr;
					},
					success: function (res) {
						const li = fileProgress[file.name].li;
            if (res.success) {
                const result = res.data.find(item => item.filename === file.name) || res.data[0];
                li.html("✔ 已上傳: <a href='" + result.url + "' target='_blank'>" + result.file_name + "</a> (ID: " + result.attachment_id + ")");
            } else {
                li.text("✗ 上傳失敗: " + (res.data || "未知錯誤"));
            }
					},
					error: function(jqXHR, textStatus, errorThrown) {
							fileProgress[file.name].li.text("✗ 上傳 " + file.name + " 錯誤: " + errorThrown);
					}
				});
			}
		}

	});
})(jQuery);
