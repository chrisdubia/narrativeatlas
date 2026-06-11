<?php
namespace com\cminds\mapsroutesmanager\controller;

use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Category;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\helper\GoogleMapsIcons;

class CategoryController extends Controller {
	
	const ICON_NONCE_NAME = 'cmmrm_category_icon_nonce';

	protected static $filters = array(
		'cmmrm_category_term_args',
		'term-ordering-default-taxonomies' => array('method' => 'geckaTermsOrderingTaxonomies'), // support gecka terms ordering
		'term_link' => array('args' => 2),
	);
	protected static $actions = array(
		array('name' => 'admin_menu', 'priority' => 11),
		'cmmrm_category_edit_form_fields' => array('method' => 'formFields', 'args' => 1),
		'cmmrm_category_add_form_fields' => array('method' => 'formFields', 'args' => 1),
		'edited_cmmrm_category' => array('method' => 'categoryAfterSave', 'args' => 2),
		'created_cmmrm_category' => array('method' => 'categoryAfterSave', 'args' => 2),
		'cmmrm_categories_filter' => array('args' => 1),
		'cmmrm_route_editor_middle' => array('args' => 1),
		'cmmrm_route_after_save' => array('args' => 1),
	);
	
	static function formFields($term = null) {
		if (!empty($term) AND is_object($term) AND $category = Category::getInstance($term)) {
			$currentIcon = $category->getIcon();
		} else {
			$term = null;
			$currentIcon = null;
		}
		
		$nonceField = self::ICON_NONCE_NAME;
		$nonce = wp_create_nonce(self::ICON_NONCE_NAME);
		$icons = GoogleMapsIcons::getAll();
		
		wp_enqueue_media();
		wp_enqueue_script('cmmrm-backend');
		
		echo self::loadBackendView('form-icon', compact('term', 'icons', 'currentIcon', 'nonce', 'nonceField'));
	}
	
	static function categoryAfterSave($term_id, $term_taxonomy_id = null) {
		// Get category object
		$category = Category::getInstance($term_id);
		if (empty($category)) return;
		
		if (isset($_POST[self::ICON_NONCE_NAME]) AND wp_verify_nonce($_POST[self::ICON_NONCE_NAME], self::ICON_NONCE_NAME) AND !empty($_POST[self::ICON_NONCE_NAME])) {
			$category->setIcon($_POST['cmmrm_category_icon']);
		}

		if ($route = Category::getInstance($term_id)) {
			$add_files = $_POST['cmmrm_route_add_files'];
			$add_files_title = $_POST['cmmrm_route_add_files_title'];
			$route->setRouteFileList($add_files, $add_files_title);
			$remove_files = $_POST['cmmrm_route_remove_files'];
			if(!empty($remove_files)) $route->removeRouteFileList($remove_files);
		}
	}

	static function admin_menu() {
		$url = htmlspecialchars(add_query_arg(urlencode_deep(array(
			'taxonomy' => Category::TAXONOMY,
			'post_type' => Route::POST_TYPE
		)), 'edit-tags.php'));
		add_submenu_page(App::PREFIX, App::getPluginName() . ' Categories', 'Categories', 'manage_options', $url);
		if( isset($_GET['taxonomy']) && $_GET['taxonomy'] == Category::TAXONOMY && isset($_GET['post_type']) && $_GET['post_type'] == Route::POST_TYPE ) {
			add_filter('parent_file', function($q) { return App::PREFIX; }, 999);
		}
	}
	
	static function cmmrm_category_term_args($args) {
		$args['show_ui'] = true;
		return $args;
	}
	
	static function cmmrm_categories_filter($atts) {
		if (empty($atts['categories'])) return;
		$categories = Category::mapByParent(Category::getAll());
		
		$mergecategories = '0';
		if((is_plugin_active('cm-map-locations/cm-map-locations-pro.php') || is_plugin_active('cm-map-locations-pro/cm-map-locations-pro.php')) && Settings::getOption(Settings::OPTION_INDEX_MAP_LOCATIONS_INTEGRATION) == '1' && Settings::getOption(Settings::OPTION_INDEX_MAP_LOCATIONS_MERGE_CATEGORIES) == '1') {
			$mergecategories = '1';
		}
		
		$currentCategoryId = 0;
		if(isset(FrontendController::$query)) {
			$currentCategorySlug = FrontendController::$query->get(Category::TAXONOMY);
			if ($term = get_term_by('slug', $currentCategorySlug, Category::TAXONOMY)) {
				$currentCategoryId = $term->term_id;
			}
		}
		$baseUrl = FrontendController::getFilterUrl($includeCategory = false);
		//echo "<pre>"; print_r($categories); echo "</pre>"; die;
		echo self::loadFrontendView('filter', compact('categories', 'currentCategoryId', 'baseUrl', 'mergecategories'));
	}
	
	static function cmmrm_route_editor_middle(Route $route) {
		$categoriesTree = Category::getTreeArray(array(), 0, Category::FIELDS_ID_NAME);
		$route_form_category = Settings::getOption(Settings::OPTION_ROUTE_FORM_CATEGORY);
		if ($route_form_category != 'none') {
			echo self::loadFrontendView('editor', compact('categoriesTree', 'route'));
		}
	}
	
	static function cmmrm_route_after_save(Route $route) {
		if (!empty($_POST['categories']) AND is_array($_POST['categories'])) {
			$categories = $_POST['categories'];
		} else {
			$categories = array();
		}
		$route->setCategories($categories);
	}
	
	static function geckaTermsOrderingTaxonomies(array $taxonomies) {
		$taxonomies[] = Category::TAXONOMY;
		return $taxonomies;
	}
	
	static function term_link($link, $term) {
		if (Category::TAXONOMY == $term->taxonomy) {
			$link = FrontendController::getUrl('', array($term->taxonomy => $term->slug));
		}
		return $link;
	}

}

/**
 * Plugin class
 * https://catapultthemes.com/adding-an-image-upload-field-to-categories/
 **/
if ( ! class_exists( 'CMMRM_CATEGORIES_META' ) ) {

class CMMRM_CATEGORIES_META {

 public function __construct() {
  //
 }

 public function init() {
   add_action( 'admin_enqueue_scripts', array( $this, 'cmmrm_category_load_media' ) );
   add_action( 'admin_footer', array ( $this, 'cmmrm_category_add_script' ) );
 }

 public function cmmrm_category_load_media() {
  wp_enqueue_media();
 }

 public function cmmrm_category_add_script() { ?>
   <script>
     jQuery(document).ready( function($) {
       function ct_media_upload(button_class) {
         var _custom_media = true,
         _orig_send_attachment = wp.media.editor.send.attachment;
         $('body').on('click', button_class, function(e) {
           var button_id = '#'+$(this).attr('id');
           var send_attachment_bkp = wp.media.editor.send.attachment;
           var button = $(button_id);
           _custom_media = true;
           wp.media.editor.send.attachment = function(props, attachment){
             if ( _custom_media ) {               
			   $('#category-image-wrapper').html('<img class="cmmrm_category_icon_image" src="'+attachment.url+'" style="max-width:64px;max-height:64px;"><input name="cmmrm_category_icon" value="'+attachment.url+'" type="hidden">');
             } else {
               return _orig_send_attachment.apply( button_id, [props, attachment] );
             }
            }
         wp.media.editor.open(button);
         return false;
       });
     }
     ct_media_upload('.cmmrm_category_icon_upload'); 
     $('body').on('click','.cmmrm_category_icon_remove',function(){
	   $('#category-image-wrapper').html('<img class="cmmrm_category_icon_image" src="" style="max-width:64px;max-height:64px;"><input name="cmmrm_category_icon" value="" type="hidden">');
     });
     $(document).ajaxComplete(function(event, xhr, settings) {
	   if (typeof settings.data !== 'undefined') {
	     var queryStringArr = settings.data.split('&');
         if( $.inArray('action=add-tag', queryStringArr) !== -1 ){
           var xml = xhr.responseXML;
           $response = $(xml).find('term_id').text();
           if($response!=""){
             // Clear the thumb image
             $('#category-image-wrapper').html('');
           }
         }
	   }
     });
   });
 </script>
 <?php }
 }
 
 $CMMRM_CATEGORIES_META = new CMMRM_CATEGORIES_META();
 $CMMRM_CATEGORIES_META->init();

}