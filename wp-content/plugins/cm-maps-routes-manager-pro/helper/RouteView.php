<?php
namespace com\cminds\mapsroutesmanager\helper;

use com\cminds\mapsroutesmanager\shortcode\RouteSnippetShortcode;
use com\cminds\mapsroutesmanager\controller\RouteController;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\controller\FrontendController;
use com\cminds\mapsroutesmanager\model\Category;
use com\cminds\mapsroutesmanager\model\Route;
use com\cminds\mapsroutesmanager\model\Attachment;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\App;

class RouteView {

	static protected $travelModeIcons = array(
		'WALKING' => 'travelModeIcons walking dashicons dashicons-universal-access',
		'BICYCLING' => 'travelModeIcons bicycling fa fa-bicycle',
		'DRIVING' => 'travelModeIcons driving fa fa-car',
		'DIRECT' => 'travelModeIcons straight dashicons dashicons-sticky'
	);
	
	static function displayTermsInlineNav($title, $class, array $items) {
		?><div class="cmmrm-route-<?php echo $class; ?>">
			<strong><?php echo $title; ?>:</strong>
			<ul class="cmmrm-route-<?php echo $class; ?>-list cmmrm-inline-nav"><?php
				foreach ($items as $item):
					printf('<li><a href="%s">%s</a></li>',
						esc_attr($item->getPermalink()),
						esc_html($item->getName())
					);
				endforeach; ?>
			</ul>
		</div><?php
	}
	
	static function displayImages(array $images, $class, $id) {
		?><div class="cmmrm-inline-gallery" id="cmmrm-inline-gallery-<?php echo rand(); ?>">
        <?php foreach ($images as $image):
			$extra = '';
			if ($image->isVideo()) {
				$extra = sprintf(' data-type="youtube" data-videoid="%s"', esc_attr($image->getYoutubeId()));
			}
			printf('<a href="%s" class="cmmrm-gallery" rel="gallery-%s-%d"><img src="%s" data-image="%s" alt="%s"%s /></a>',
				esc_attr($image->isImage() ? $image->getImageUrl(Attachment::IMAGE_SIZE_LARGE) : $image->getUrl()),
				$class,
				$id,
				esc_attr($image->getImageUrl((wp_is_mobile())?Attachment::IMAGE_SIZE_LARGE:Attachment::IMAGE_SIZE_THUMB)),
				esc_attr($image->isImage() ? $image->getImageUrl(Attachment::IMAGE_SIZE_LARGE) : $image->getUrl()),
				esc_attr('Image'),
				$extra
			);
		endforeach; ?></div><?php
	}
	
	static function displayRating(Route $route) {
		return '<div class="cmmrm-route-rating" data-route-id="'. $route->getId() .'">' . static::displayRatingInner($route) . '</div>';
	}
	
	static function displayRatingInner(Route $route) {
		$canRate = ($route->canRate() AND !$route->didUserRate());
		$rateTitle = Labels::getLocalized('rate_btn_title');
		$out = sprintf('<ul class="cmmrm-rating" data-rating="%d" data-can-rate="%d">',
			round($route->getRate()),
			($canRate ? 1 : 0)
		);
		for ($i=1; $i<=5; $i++) {
			$out .= sprintf('<li data-rate="%d"%s></li>',
				$i,
				($canRate ? ' title="'. esc_attr(sprintf($rateTitle, $i)) .'"' : '')
			);
		}
		$out .= '</ul>';
		$out .= '<span class="cmmrm-votes-number">('. intval($route->getVotesNumber()) .')</span>';
		return $out;
	}
	
	static function categoriesFilter($baseUrl, $currentCategoryId, array $categories, $parent = 0, $depth = 0) {
		$out = '';
		if (!empty($categories[$parent])) {
			foreach ($categories[$parent] as $categoryId => $category) {
				//$url = home_url('/' . Category::getUrlPart()) . '/' . $category->getSlug() .'/';
				$url = add_query_arg(Category::TAXONOMY, $category->getSlug(), $baseUrl);
				$out .= sprintf('<option value="%s" data-url="%s"%s>%s</option>',
					esc_attr($category->getSlug()),
					esc_attr($url),
					selected($currentCategoryId, $categoryId, $echo = false),
					str_repeat('&ndash;', $depth) . ' ' . esc_html($category->getName())
				);
				$out .= self::categoriesFilter($baseUrl, $currentCategoryId, $categories, $categoryId, $depth+1);
			}
		}
		return $out;
	}

	static function mergecategoriesFilter($rlbaseUrl, $rlcurrentCategoryId, array $rlcategories, $rparent = 0, $rldepth = 0) {
		$out = '';
		if (!empty($rlcategories[$rparent])) {
			foreach ($rlcategories[$rparent] as $rlcategoryId => $rlcategory) {
				if($rlcategory['slug'] == 'route') {
					$url = add_query_arg(Category::TAXONOMY, $rlcategory['slug'], $rlbaseUrl);
					$out .= sprintf('<option value="%s" data-url="%s"%s>%s</option>',
						esc_attr($rlcategory['slug']),
						esc_attr($url),
						selected($rlcurrentCategoryId, $rlcategoryId, $echo = false),
						str_repeat('&ndash;', $rldepth) . ' ' . esc_html($rlcategory['name'])
					);
					$out .= self::mergecategoriesFilter($rlbaseUrl, $rlcurrentCategoryId, $rlcategories, $rlcategoryId, $rldepth+1);
				}
				if($rlcategory['slug'] == 'location') {

				}
			}
		}
		return $out;
	}

	static function getRefererUrl() {
		$isTheSameHost = function($a, $b) {
			return parse_url($a, PHP_URL_HOST) == parse_url($b, PHP_URL_HOST);
		};
		$canUseReferer = (!empty($_SERVER['HTTP_REFERER'])
			AND $isTheSameHost($_SERVER['HTTP_REFERER'], site_url())
		);
		if (!empty($_GET['backlink'])) { // GET backlink param
			return base64_decode(urldecode($_GET['backlink']));
		}
		else if (!empty($_POST['backlink'])) { // POST backlink param
			return $_POST['backlink'];
		}
		else if ($canUseReferer) { // HTTP referer
			return $_SERVER['HTTP_REFERER'];
		} else { // index page
    		return FrontendController::getUrl();
    	}
	}
	
	static function getDisplayParams(array $displayParams) {
		$out = '';
		foreach ($displayParams as $param) {
			$out .= ' data-show-param-'. str_replace('_', '-', str_replace('_cmmrm_', '', $param)) .'="1"';
		}
		return $out;
	}
	
	static function getTravelModeMenu($currentTravelMode, $showTitle = true, $labelsAsTooltip = false) {
		$currentTravelMode_arr = array();
		if($currentTravelMode != '' && !is_array($currentTravelMode)) {
			$currentTravelMode_arr = explode(',', $currentTravelMode);
		}
		if(count($currentTravelMode_arr) > 0) {
			$out = '';
			if ($showTitle) $out .= sprintf('<li><strong>%s</strong></li>', Labels::getLocalized('travel_mode'));
			
			$modes = Route::$travelModes;
			if(Settings::getOption(Settings::OPTION_TRAVEL_MODE_MULTIPLE_SHOW) == 1) {
				$modes = $currentTravelMode_arr;
			}

			foreach ($modes as $mode) {
				if($mode == 'WALKING') {
					$iconImg = '<img class="travelModeImgs walking" src="'.App::url('asset/img/editor/walking.png').'" alt="Walking" title="Walking" style="display:none;" />';
				} else if($mode == 'BICYCLING') {
					$iconImg = '<img class="travelModeImgs bicycling" src="'.App::url('asset/img/editor/bicycling.png').'" alt="Bicycling" title="Bicycling" style="display:none;" />';
				} else if($mode == 'DRIVING') {
					$iconImg = '<img class="travelModeImgs driving" src="'.App::url('asset/img/editor/driving.png').'" alt="Driving" title="Driving" style="display:none;" />';
				} else if($mode == 'DIRECT') {
					$iconImg = '<img class="travelModeImgs straight" src="'.App::url('asset/img/editor/straight.png').'" alt="Straight Line" title="Straight Line" style="display:none;" />';
				} else {
					$iconImg = '';
				}
				if (!empty(self::$travelModeIcons[$mode])) {
					$iconClass = self::$travelModeIcons[$mode];
				} else {
					$iconClass = '';
				}
				$title = Labels::getLocalized('travel_mode_'. strtolower($mode));
				$out .= sprintf('<li><a href="" data-mode="%s"%s>%s<i class="%s"></i><span>%s</span></a></li>',
					esc_attr(strtoupper($mode)),	
					($currentTravelMode_arr[0] == $mode ? ' class="current"' : ''),
					//($labelsAsTooltip ? ' title="Travel mode: '. esc_attr($title) .'"' : ''),
					$iconImg,
					$iconClass,
					($labelsAsTooltip ? '' : ' '. $title)
				);
			}
			return sprintf('<ul class="cmmrm-inline-nav cmmrm-route-travel-mode">%s</ul>', $out);
		}
	}
	
	static function getTileWidth() {
		return Settings::getOption(Settings::OPTION_INDEX_TILE_WIDTH);
	}
	
	static function getTileImageMaxHeight() {
		return round(Settings::getOption(Settings::OPTION_INDEX_TILE_WIDTH) / 16 * 11);
	}
	
	static function getFeaturedImageInfo(Route $route, array $atts, $width, $height) {
		if (isset($atts['featured']) AND RouteSnippetShortcode::FEATURED_MAP == $atts['featured'] AND strlen($route->getOverviewPath()) > 0) {
			$size = $width . 'x' . $height;
			$imageUrl = $route->getMapThumbUrl($size);
		}
		else if ($images = $route->getImages()) {
			if ($firstImage = reset($images) AND is_object($firstImage) AND $firstImage instanceof Attachment) {
				if ($imageInfo = $firstImage->getImageInfo(array($width, $height))) {
					$imageUrl = $imageInfo[0];
					$width = $imageInfo[1];
					$height = $imageInfo[2];
				}
			}
		} else {
			$imageUrl = Settings::getOption(Settings::OPTION_ROUTE_DEFAULT_IMAGE);
		}
		if (!empty($imageUrl)) {
			return array($imageUrl, $width, $height);
		}
	}
	
	static function getFeaturedImageLarge(Route $route, array $atts) {
		$width = self::getTileWidth();
		$height = self::getTileImageMaxHeight();
		$imageInfo = self::getFeaturedImageInfo($route, $atts, $width, $height);
		if ($imageInfo) {
			$imageUrl = reset($imageInfo);
			return sprintf('<a href="%s" class="cmmrm-route-featured-image-large" style="background-image:url(\'%s\');" /></a>',
				esc_attr($route->getPermalink()),
				esc_attr($imageUrl)
			);
		}
	}
	
	static function getFeaturedImageThumb(Route $route, array $atts) {
		$width = $height = 80;
		$imageInfo = self::getFeaturedImageInfo($route, $atts, $width, $height);
		if ($imageInfo) {
			$imageUrl = reset($imageInfo);
			return sprintf('<a href="%s"><img src="%s" alt="Image" style="width:'. $width .'px;height:'. $height .'px;" /></a>',
				esc_attr($route->getPermalink()),
				esc_attr($imageUrl)
			);
		}
	}
	
	static function getFullMap(Route $route, $atts) {
		echo RouteController::loadFrontendView('single-map', compact('route', 'atts'));
	}

	static function processDescription($content) {
		$content = trim(wpautop($content));
		
		if (Settings::getOption(Settings::OPTION_SINGLE_ROUTE_PROCESS_SHORTCODES_IN_DESC)) {
			$whitelist = Settings::getOption(Settings::OPTION_SINGLE_ROUTE_SHORTCODES_WHITELIST);
			if ($whitelist) {
				$content = ShortcodeHelper::filterShortcodes($content, $whitelist);
			}
			$content = do_shortcode($content);
		}
		return $content;
	}	
	
}