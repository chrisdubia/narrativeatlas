<?php
namespace com\cminds\wp\common\upload_helper\v_1_0_0\model;

class Attachment extends PostType {
	
	const POST_TYPE = 'attachment';
	
	const META_WP_ATTACHED_FILE = '_wp_attached_file';
	
	const IMAGE_SIZE_THUMB = 'thumbnail';
	const IMAGE_SIZE_MEDIUM = 'medium';
	const IMAGE_SIZE_LARGE = 'large';
	const IMAGE_SIZE_FULL = 'full';
	
	/**
	 * Get instance
	 *
	 * @param WP_Post|int $post Post object or ID
	 * @return com\cminds\wp\common\upload_helper\v_1_0_0\model\Attachment
	 */
	static function getInstance($post) {
		return parent::getInstance($post);
	}
	
	static function registerPostType() {
		// do not register since attachment is already registered
	}
	
	static function create($filePath, $mimeType, $parentPostId = 0, $title = '', $description = '') {
		
		if (empty($mimeType)) {
			$type = wp_check_filetype( basename( $filePath ), null );
			$mimeType = $filetype['type'];
		}
		
		if (empty($title)) {
			$title = sanitize_title(basename($filePath));
		}
		
		$post = array(
			'post_title' => $title,
			'post_content' => $description,
			'post_status' => 'inherit',
			'post_mime_type' => $mimeType,
		);
		$attach_id = wp_insert_attachment($post, $filePath, $parentPostId);
		
		// Generate the metadata for the attachment, and update the database record.
		static::updateMetaData($attach_id, $filePath);
		//$attach_data = wp_generate_attachment_metadata( $attach_id, $filePath );
		//wp_update_attachment_metadata( $attach_id, $attach_data );
		
		return $attach_id;
		
	}
	
	static function getForPost($postId) {
		$posts = get_posts(array(
			'posts_per_page' => -1,
			'post_type' => Attachment::POST_TYPE,
			'post_status' => 'any',
			'post_parent' => $postId,
			'orderby' => 'menu_order',
			'order' => 'asc',
		));
		return array_filter(array_map(array(__CLASS__, 'getInstance'), $posts));
	}
	
	/**
	 * 
	 * @param string $url
	 * @return \com\cminds\wp\common\upload_helper\v_1_0_0\model\Attachment
	 */
	static function getByUrl($url) {
		global $wpdb;
		if ($path = parse_url($url, PHP_URL_PATH)) {
			$dir = wp_upload_dir();
			$path = substr($path, strlen(parse_url($dir['baseurl'], PHP_URL_PATH))+1, 9999);
			$sql = $wpdb->prepare("SELECT p.* FROM $wpdb->postmeta m
				JOIN $wpdb->posts p ON p.ID = m.post_id
				WHERE m.meta_key = %s
				AND m.meta_value LIKE %s
				AND p.post_type = %s",
				self::META_WP_ATTACHED_FILE,
				$path,
				static::POST_TYPE
			);
			$post = $wpdb->get_row($sql);
			if ($post) {
				return static::getInstance($post);
			}
		}
	}
	
	function getPostMetaKey($name) {
		return $name;
	}
	
	function getFilePath() {
		return get_attached_file($this->getId());
	}

	function getUrl() {
		return wp_get_attachment_url($this->getId());
	}
	
	function isImage() {
		return (strpos($this->post->post_mime_type, 'image') !== false);
	}
	
	function isVideo() {
		return (strpos($this->post->post_mime_type, 'video') !== false);
	}
	
	static function updateMetaData($postId, $filePath) {
		// you must first include the image.php file
		// for the function wp_generate_attachment_metadata() to work
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		$attach_data = wp_generate_attachment_metadata($postId, $filePath);
		return wp_update_attachment_metadata($postId, $attach_data);
	}
	
	static function sanitizeFileName($title, $unique = true) {
		return ($unique ? floor(microtime(true)*1000) . '-' : '') . sanitize_file_name($title);
	}
	
	static function getUploadDir($subfolder) {
		$uploadDir = wp_upload_dir();
		if ($uploadDir['error']) {
			throw new \Exception(__('Error while getting wp_upload_dir():' . $uploadDir['error']));
		} else {
			$dir = $uploadDir['basedir'] . '/' . $subfolder . '/';
			if(!is_dir($dir)) {
				if(!wp_mkdir_p($dir)) {
					throw new \Exception(__('Script couldn\'t create the upload folder:' . $dir));
				}
			}
			return $dir;
		}
	}
	
	static function upload($file, $uploadDir) {
		if (is_uploaded_file($file['tmp_name'])) {
			$destination = trailingslashit(static::getUploadDir($uploadDir)) . md5(microtime() . $file['name'] . $file['tmp_name']) .'_'. $file['name'];
			if (move_uploaded_file($file['tmp_name'], $destination)) {
				chmod($destination, 0666);
				return $destination;
			} else throw new \Exception('Failed to move uploaded file.');
		} else {
			throw new \Exception('This is not uploaded file.');
		}
	}
	
	/**
	 * Returns the icon for the attachment.
	 */
	function getIconUrl() {
		if ($this->isImage()) {
			return $this->getImageUrl(static::IMAGE_SIZE_THUMB);
		}
	}
	
	function getImageInfo($size = self::IMAGE_SIZE_FULL, $icon = false) {
		if ($this->isImage()) {
			return wp_get_attachment_image_src($this->getId(), $size, $icon);
		}
	}
	
	function getImageUrl($size = self::IMAGE_SIZE_FULL, $icon = false) {
		if ($this->isImage()) {
			$result = wp_get_attachment_image_src($this->getId(), $size, $icon);
			if (!empty($result[0])) {
				return $result[0];
			}
		}
		/*
		else if ($this->isVideo()) {
			if (static::isYouTubeUrl($this->getUrl())) {
				return $this->getYoutubeThumbUrl();
			} else {
				return App::url('asset/img/play-video.png');
			}
		}
		*/
	}
	
	static function upload_image_from_url($image_url) {
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		
		$image_url_arr = explode("?", $image_url);
		$image_url = $image_url_arr[0];
		
		// Download image to temp location
		$temp_file = download_url($image_url);
		
		if (is_wp_error($temp_file)) {
			return $temp_file;
		}
		
		// Array based on $_FILE as seen in PHP file uploads
		$file = array(
			'name'     => basename($image_url),
			'type'     => mime_content_type($temp_file),
			'tmp_name' => $temp_file,
			'error'    => 0,
			'size'     => filesize($temp_file),
		);
		
		$overrides = array(
			'test_form' => false,
			'test_type' => true,
		);
		
		// Move the temporary file into the uploads directory
		$results = wp_handle_sideload($file, $overrides);
		
		if (!empty($results['error'])) {
			return new WP_Error('upload_error', $results['error']);
		}
		
		// Get the file URL and path
		$filename = $results['file'];
		$local_url = $results['url'];
		
		// Create attachment
		$attachment = array(
			'post_mime_type' => $results['type'],
			'post_title'     => sanitize_file_name(basename($filename)),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);
		
		// Insert the attachment
		$attach_id = wp_insert_attachment($attachment, $filename);
		
		if (!is_wp_error($attach_id)) {
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			$attach_data = wp_generate_attachment_metadata($attach_id, $filename);
			wp_update_attachment_metadata($attach_id, $attach_data);
		}
		
		return $attach_id;
	}
	
}