<?php
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\Attachment;
use com\cminds\mapsroutesmanager\model\Labels;
use com\cminds\mapsroutesmanager\model\Settings;
$route_form_images = Settings::getOption(Settings::OPTION_ROUTE_FORM_IMAGES);
$route_form_videos = Settings::getOption(Settings::OPTION_ROUTE_FORM_VIDEOS);
$route_form_google_images = Settings::getOption(Settings::OPTION_ROUTE_FORM_GOOGLE_IMAGES);
if($route_form_images == 'optional' || $route_form_videos == 'optional') {
	?>
	<div class="cmmrm-field cmmrm-images">
		<label>
			<img src="<?php echo App::url('asset/img/editor/upload.png'); ?>" class="cmmrm-images-img" style="display:none;" />
			<?php echo Labels::getLocalized('route_images'); ?>
		</label>
		<ul class="cmmrm-images-list">
			<?php
			$template = '<li data-id="%s"%s><a href="%s" target="_blank" title="%s"><img src="%s" alt="Image" /></a>'
				. '<span class="cmmrm-image-delete" title="%s">&times;</span></li>';
			//printf($template, 0, ' style="display:none"', 'about:blank', esc_attr(Labels::getLocalized('dashboard_image_open')), 'about:blank', esc_attr(Labels::getLocalized('dashboard_image_remove')));
			printf($template, 0, ' style="display:none"', '', esc_attr(Labels::getLocalized('dashboard_image_open')), '', esc_attr(Labels::getLocalized('dashboard_image_remove')));
			$imagesIds = array();
			foreach ($route->getImages() as $image):
				if (!$image->isImage() AND !$image->isVideo()) continue;
				$imagesIds[] = $image->getId();
				printf($template,
					$image->getId(),
					'',
					esc_attr($image->getImageUrl(Attachment::IMAGE_SIZE_FULL)),
					esc_attr(Labels::getLocalized('dashboard_image_open')),
					esc_attr($image->getImageUrl(Attachment::IMAGE_SIZE_THUMB)),
					esc_attr(Labels::getLocalized('dashboard_image_remove'))
				);
			endforeach;
			?>
		</ul>
		<div class="cmmrm-field-desc"<?php if (empty($imagesIds)) echo ' style="display:none;"'; ?>>
			<?php echo Labels::getLocalized('dashboard_images_description'); ?>
		</div>
		<div class="cmmrm-images-add">
			<input type="hidden" class="images" name="images" value="<?php echo esc_attr(implode(',', $imagesIds)); ?>" />
			<span class="dashboard_upload_route_image"><?php echo Labels::getLocalized('dashboard_upload_image'); ?></span>
			<?php
			$accept = '';
			if($route_form_images == 'optional') { $accept .= ',image/*'; }
			if($route_form_videos == 'optional') { $accept .= ',video/*'; }
			$accept = substr($accept, 1);
			?>
			<input type="file" class="cmmrm-images-upload" accept="<?php echo $accept; ?>" multiple>
		</div>
		<?php
		if($route_form_videos == 'optional') {
			?>
			<div class="cmmrm-btn cmmrm-add-video-btn">
				<img src="<?php echo App::url('asset/img/editor/addv.png'); ?>" class="imgicon" style="display:none;" />
				<?php echo Labels::getLocalized('dashboard_video_add_btn'); ?>
			</div>
			<?php
		}
		if($route_form_google_images == 'optional') {
			?>
			<div class="cmmrm-google-images-add">
				<div class="googleimages-container"></div>
				<div class="cmmrm-btn cmmrm-load-googleimages-btn" data-index="1"><?php echo Labels::getLocalized('load_google_images'); ?></div>
				<img src="<?php echo App::url('asset/img/ajax-loader.gif'); ?>" />
			</div>
			<?php
		}
	echo '</div>';
}
?>