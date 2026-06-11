<?php
namespace com\cminds\wp\common\upload_helper\v_1_0_0\model;

abstract class PostType {
	
	const POST_TYPE = '';
	
	const TYPE_MODEL = 'model';
	const TYPE_POST = 'post';
	
	static protected $postTypeOptions = array();
	static protected $instances;
	
	protected $post;
	
	static function registerPostType() {
		static::$postTypeOptions['labels'] = static::getPostTypeLabels();
		register_post_type(static::POST_TYPE, static::$postTypeOptions);
	}
	
	static function clearInstances() {
		static::$instances = array();
	}
	
	/**
	 * Get instance
	 * 
	 * @param WP_Post|int $post Post object or ID
	 * @return com\cminds\mapsroutesmanager\model\PostType
	 */
	static function getInstance($post) {
		if (is_scalar($post)) {
			if (!empty(static::$instances[$post])) return static::$instances[$post];
			else if (is_numeric($post)) $post = get_post($post);
			else $post = get_post(array('post_name' => $post, 'post_status' => 'any'));
		}
		if (!empty($post) AND is_object($post) AND $post->post_type == static::POST_TYPE) {
			if (empty(static::$instances[$post->ID]) OR !(static::$instances[$post->ID] instanceof static)) {
				static::$instances[$post->ID] = new static($post);
			}
			return static::$instances[$post->ID];
		}
	}
	
	static function getAll($query = array(), $returnType = self::TYPE_MODEL) {
		$query['post_type'] = static::POST_TYPE;
		$posts = get_posts($query);
		switch ($returnType) {
			case static::TYPE_MODEL:
				return array_filter(array_map(function($post) {
					return static::getInstance($post);
				}, $posts));
					break;
			case static::TYPE_POST:
			default:
				return $posts;
		}
	}
	
	static protected function getPostTypeLabels() {
		return array();
	}
	
	function __construct($post = null) {
		if (empty($post)) $post = new \stdClass();
		if (is_array($post)) $post = (object)$post;
		$this->post = $post;
	}
	
	function getId() {
		if (isset($this->post->ID)) {
			return $this->post->ID;
		}
	}
	
	function getPostMeta($name, $single = true) {
		return get_post_meta($this->getId(), $this->getPostMetaKey($name), $single);
	}
	
	function setPostMeta($name, $value) {
		update_post_meta($this->getId(), $this->getPostMetaKey($name), $value);
		return $this;
	}
	
	function getPostMetaKey($name) {
		return $name;
	}

	function getTitle() {
		if (isset($this->post->post_title)) {
			return $this->post->post_title;
		}
	}
	
	function setTitle($title) {
		$this->post->post_title = $title;
		return $this;
	}
	
	function getSlug() {
		if (isset($this->post->post_name)) {
			return $this->post->post_name;
		}
	}
	
	function setSlug($slug) {
		$this->post->post_name = $slug;
		return $this;
	}
	
	function getContent() {
		if (isset($this->post->post_content)) {
			return do_shortcode($this->post->post_content);
		}
	}
	
	function setContent($desc) {
		$this->post->post_content = $desc;
		return $this;
	}
	
	function save() {
		$this->post->post_type = static::POST_TYPE;
		if ($this->getId()) {
			$result = wp_update_post((array)$this->post, $wp_error = true);
			if (is_numeric($result)) {
				return $result;
			} else {
				return false;
			}
		} else {
			$id = wp_insert_post((array)$this->post);
			$this->post = get_post($id);
			return $id;
		}
	}
	
	function getPermalink() {
		return get_permalink($this->getId());
	}
	
	function getPost() {
		return $this->post;
	}
	
	function getTags($args = array()) {
		return wp_get_post_tags($this->getId(), $args);
	}
	
	function setTags($tags) {
		if (!is_array($tags)) $tags = explode(',', $tags);
		$tags = array_map('trim', $tags);
		$tags = implode(',', $tags);
		return wp_set_post_tags($this->getId(), $tags, $append = false);
	}
	
	function setParent($id) {
		$this->post->post_parent = $id;
		return $this;
	}
	
	function getStatus() {
		if (!empty($this->post->post_status)) {
			return $this->post->post_status;
		}
	}
	
	function setStatus($status) {
		$this->post->post_status = $status;
		return $this;
	}

	function getCommentStatus() {
		if (!empty($this->post->comment_status)) {
			return $this->post->comment_status;
		}
	}
	
	function setCommentStatus($status) {
		$this->post->comment_status = $status;
		return $this;
	}
	
	function setAuthor($userId) {
		$this->post->post_author = $userId;
		return $this;
	}
	
	function getMenuOrder() {
		return $this->post->menu_order;
	}
	
	function setMenuOrder($order) {
		$this->post->menu_order = $order;
		return $this;
	}
	
	function getAuthorId() {
		if (!empty($this->post->post_author)) {
			return $this->post->post_author;
		}
	}
	
	function getAuthor() {
		if (!empty($this->post->post_author) AND $user = get_userdata($this->post->post_author)) {
			return $user;
		}
	}
	
	function getAuthorEmail() {
		if ($user = $this->getAuthor()) {
			return $user->user_email;
		}
	}
	
	function getAuthorDisplayName() {
		if ($user = $this->getAuthor()) {
			return $user->display_name;
		}
	}
	
	function getAuthorLogin() {
		if ($user = $this->getAuthor()) {
			return $user->user_login;
		}
	}
	
	function getCreatedDate() {
		return $this->post->post_date;
	}
	
	function formatCreatedDate() {
		$date = date_i18n(get_option('date_format').' '.get_option('time_format'), get_post_time('U', true, $this->getId()));
		return $date;
	}
	
	function getModifiedDate() {
		return $this->post->post_modified;
	}
	
	function formatModifiedDate() {
		$date = date_i18n(get_option('date_format').' '.get_option('time_format'), get_post_modified_time('U', true, $this->getId()));
		return $date;
	}
	
	function getPostParent() {
		return $this->getParentId();
	}
	
	function getParentId() {
		if (!empty($this->post->post_parent)) {
			return $this->post->post_parent;
		}
	}
	
	function getPostMimeType() {
		return $this->post_mime_type;
	}
	
	function delete() {
		return wp_delete_post($this->getId(), true);
	}

}