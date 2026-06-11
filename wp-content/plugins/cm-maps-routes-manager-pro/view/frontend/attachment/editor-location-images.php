<?php
use com\cminds\mapsroutesmanager\App;
use com\cminds\mapsroutesmanager\model\Settings;
use com\cminds\mapsroutesmanager\model\Location;
use com\cminds\mapsroutesmanager\model\Labels;
?>
<?php if (Settings::getOption(Settings::OPTION_LOCATION_ICON_ENABLE)): ?>
	<div class="cmmrm-location-icon">
		<input class="location-icon" type="hidden" name="locations[icon][]" value="" />
		<input type="button" class="cmmrm-btn cmmrm-location-choose-icon" value="<?php echo esc_attr(Labels::getLocalized('dashboard_location_icon_choose_btn')); ?>" />
		<label><span><?php echo Labels::getLocalized('dashboard_location_icon_size'); ?></span>
			<select name="locations[icon_size][]" class="cmmrm-location-icon-size">
				<option value="<?php echo Location::ICON_SIZE_LARGE; ?>"><?php echo Labels::getLocalized('location_icon_size_large'); ?></option>
				<option value="<?php echo Location::ICON_SIZE_NORMAL; ?>"><?php echo Labels::getLocalized('location_icon_size_normal'); ?></option>
				<option value="<?php echo Location::ICON_SIZE_SMALL; ?>"><?php echo Labels::getLocalized('location_icon_size_small'); ?></option>
			</select>
		</label>
		<input type="button" class="cmmrm-btn cmmrm-location-remove-icon" value="<?php echo esc_attr(Labels::getLocalized('dashboard_location_icon_remove_btn')); ?>" />
	</div>
<?php endif; ?>
<?php
$route_form_images = Settings::getOption(Settings::OPTION_ROUTE_FORM_IMAGES);
$route_form_videos = Settings::getOption(Settings::OPTION_ROUTE_FORM_VIDEOS);
$route_form_google_images = Settings::getOption(Settings::OPTION_ROUTE_FORM_GOOGLE_IMAGES);
if($route_form_images == 'optional' || $route_form_videos == 'optional') {
	?>
	<div class="cmmrm-images">
		<img src="<?php echo App::url('asset/img/editor/upload.png'); ?>" class="imgicon" style="display:none;" />
		<input type="hidden" class="images" name="locations[images][]" value="" />
		<ul class="cmmrm-images-list">
			<li data-id="0" style="display:none"><a href="" target="_blank" title="<?php
				echo esc_attr(Labels::getLocalized('dashboard_image_open')); ?>"><img src="" alt="Image" /></a>
			<span class="cmmrm-image-delete" title="<?php
				echo esc_attr(Labels::getLocalized('dashboard_image_remove')); ?>">&times;</span></li>
		</ul>
		<span class="dashboard_upload_location_image"><?php echo Labels::getLocalized('dashboard_upload_image'); ?></span>
		<?php
		$accept = '';
		if($route_form_images == 'optional') { $accept .= ',image/*'; }
		if($route_form_videos == 'optional') { $accept .= ',video/*'; }
		$accept = substr($accept, 1);
		?>
		<input type="file" class="cmmrm-images-upload" accept="<?php echo $accept; ?>" multiple>
		<?php
		if($route_form_videos == 'optional') {
			?>
			<div class="cmmrm-video">
				<img src="<?php echo App::url('asset/img/editor/addv.png'); ?>" class="imgicon" style="display:none;" />
				<div class="cmmrm-btn cmmrm-add-video-btn"><?php echo Labels::getLocalized('dashboard_video_add_btn'); ?></div>
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