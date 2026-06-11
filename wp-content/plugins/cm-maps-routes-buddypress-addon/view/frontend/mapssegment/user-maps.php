<?php
use com\cminds\mapsroutesmanager\addon\buddypress\model\Settings;
if(isset($userId)) {
$bp_routes_layout = Settings::getOption(Settings::OPTION_USER_PROFILE_MAPS_LAYOUT);
?>
<div class="cmmrm-buddypress-profile-user-maps">
    <div class="cmmrm-buddypress-btn cmmrm-buddypress-show-map-btn">
		<?php echo isset($showMapText) ? $showMapText : 'Show Map'; ?>
	</div>
    <?php
	if($showManage) {
		?>
        <div class="cmmrm-buddypress-btn cmmrm-buddypress-manage-btn">
			<?php echo isset($manageMapsText) ? $manageMapsText : 'Manage Maps'; ?>
		</div>
		<?php
	}
	if($showManage) {
		?>
        <div class="cmmrm-buddypress-manage-routes">
            <?php echo do_shortcode('[my-routes-table]'); ?>
        </div>
		<?php
	}
	echo do_shortcode('[cm-routes-map author='. $userId .']');
	?>
    <div class="cmmrm-buddypress-routes-list cmmrm-routes-archive-<?php echo $bp_routes_layout;?>">
        <?php
		foreach($routesIds as $id) {
            $shortcode = sprintf('[route-snippet id='. $id .' params=%d layout=%s featured=%s]',
                intval(Settings::getOption(Settings::OPTION_USER_PROFILE_MAPS_SHOW_ROUTE_PARAMS)),
                Settings::getOption(Settings::OPTION_USER_PROFILE_MAPS_LAYOUT),
                Settings::getOption(Settings::OPTION_USER_PROFILE_MAPS_FEATURED)
            );
            echo str_replace('[', '&lsqb;', do_shortcode($shortcode));
            if($bp_routes_layout == 'list') { ?>
                <div class="clearfix mb-20"></div>
				<?php
			}
		}
		?>
    </div>
    <div class="clearfix mb-20"></div>
</div>
<?php
}
?>