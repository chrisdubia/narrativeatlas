<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\Attachment;
use com\cminds\mapsroutesmanager\model\Location;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\wp\common\upload_helper\v_1_0_0\controller\UploadController;

class AttachmentController extends Controller {
	
	static $ajax = array('cmmrm_get_image_id');
	
	static $actions = array(
		'cmmrm_route_editor_before_map' => array('args' => 1, 'priority' => 50),
		'cmmrm_route_after_save' => array('args' => 1),
		'cmmrm_route_editor_location_bottom' => array('args' => 1),
		'cmmrm_location_after_save' => array('args' => 2),
	);
	static $filters = array(
		'user_has_cap' => array('args' => 4),
	);
	
	/**
	 * 
	 * @var UploadController
	 */
	static protected $upload;
	
	
	static function bootstrap() {
		parent::bootstrap();
		
// 		static::$upload = new UploadController(App::getPluginFile(), App::PREFIX);
// 		static::$upload->setUploadDir(Attachment::UPLOAD_DIR . DIRECTORY_SEPARATOR . Attachment::UPLOAD_DIR_MEDIA);
// 		static::$upload->setFieldName('route_image');
// 		static::$upload->setupAjaxHandler();
		
	}
	
	
	static function cmmrm_route_editor_before_map(Route $route) {
		//echo static::$upload->getEditView($route->getImagesIds(), 'image/*');
		echo self::loadFrontendView('editor-route-images', compact('route'));
	}
	
	
	static function cmmrm_route_editor_location_bottom(Route $route) {
		echo self::loadFrontendView('editor-location-images', compact('route'));
	}
	
	
	static function cmmrm_route_after_save(Route $route) {
		if (!empty($_POST['images'])) {
			$images = $_POST['images'];
		} else {
			$images = array();
		}
		$route->setImages($images);
	}
	
	
	static function cmmrm_location_after_save(Location $location, $i) {
		if( isset($_POST['locations']['images'][$i]) ) {
			$location->setImages($_POST['locations']['images'][$i]);
		} else {
			$location->setImages(array());
		}
		if( isset($_POST['locations']['icon'][$i]) ) {
			$location->setIcon($_POST['locations']['icon'][$i]);
		} else {
			$location->setIcon('');
		}
		if( isset($_POST['locations']['icon_size'][$i]) ) {
			$location->setIconSize($_POST['locations']['icon_size'][$i]);
		} else {
			$location->setIconSize(Location::ICON_SIZE_NORMAL);
		}
	}
	

	static function cmmrm_get_image_id() {
		$response = array('success' => 0, 'msg' => 'Error');
		if (!empty($_POST['url'])) {
			if (Attachment::isYouTubeUrl($_POST['url'])) { // YouTube
				$attachment = Attachment::createYouTube(0, $_POST['url']);
			} else {
				$url = $_POST['url'];
				$attachment = Attachment::getByUrl($url);
				if (empty($attachment)) {
					$url = preg_replace('~(\-[0-9]+x[0-9]+)(\.\w+)~', '$2', $url);
					$attachment = Attachment::getByUrl($url);
				}
			}
			
			if (!empty($attachment)) {
				$response = array(
					'success' => 1,
					'id' => $attachment->getId(),
					'url' => $attachment->isImage() ? $attachment->getImageUrl(Attachment::IMAGE_SIZE_FULL) : $attachment->getUrl(),
					'thumb' => $attachment->getImageUrl(Attachment::IMAGE_SIZE_THUMB),
				);
			} else {
				$response['msg'] = 'Attachment not found.';
			}
		}
	
		header('Content-type: application/json');
		echo json_encode($response);
		exit;
	
	}
	
	/**
	 * Add capability to edit uploaded file.
	 *
	 * @param array   $allcaps An array of all the user's capabilities.
	 * @param array   $caps    Actual capabilities for meta capability.
	 * @param array   $args    Optional parameters passed to has_cap(), typically object ID.
	 * @param WP_User $user    The user object.
	 */
	static function user_has_cap($allcaps, $caps, $args, $user) {
		
// 		var_dump($allcaps);
// 		var_dump($caps);
// 		var_dump($args);
		
// 		$wantedCap = array_shift($args);
		$currentScript = basename($_SERVER['REQUEST_URI']);
		$attachment_id = filter_input(INPUT_POST, 'attachment_id');

		if ($currentScript == 'async-upload.php') {
			if (!empty($_FILES)) {
				$allcaps['edit_posts'] = true;
			} else {
				if ($attachment_id AND $attachment = get_post($attachment_id) AND $attachment->post_type == Attachment::POST_TYPE) {
					$allcaps['edit_posts'] = true;
				}
			}
			
		}
		return $allcaps;
	}
	
}