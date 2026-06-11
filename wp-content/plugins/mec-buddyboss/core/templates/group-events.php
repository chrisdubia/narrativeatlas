<?php
/**
* BuddyBoss - Groups mec eventss
*
* @package BuddyBossPro/Integration/mec/Template
* @since 1.0.0
*/
?>
<style type="text/css">
	.mec-events-button{
		padding: 2px;
	}
	.mec-single-event .mec-event-content{
		padding: 6px 0 4px;
	}
</style>
<input type="hidden" name="mec-bp-area" id="mec-bp-area" value="<?php echo $area; ?>">
<input type="hidden" name="mec-bp-group_id" id="mec-bp-group_id" value="<?php echo $group_id; ?>">
<input type="hidden" name="mec-bp-status_load" id="mec-bp-status_load" value="<?php echo $data->created == true?'created':'booked'; ?>">
<div id="bp-mec-events-container" class="bp-mec-events-container">

	<div class="bp-mec-events-left	<?php if ($data->created == false ) {echo 'bp-full';} ?>">
		<div class="bp-mec-events-left-inner">
			<div class="bb-panel-head">
				<div class="bb-panel-subhead">
					<?php if ( $data->created == true && $group_id==null) { ?>
					<h4 class="total-members-text"><?php esc_html_e( 'Event List', 'mec-buddyboss' ); ?></h4>
					<?php } elseif($data->booked == true && $group_id==null) { ?>
					<h4 class="total-members-text"><?php esc_html_e( 'Booked Events', 'mec-buddyboss' ); ?></h4>
					<?php }else if($group_id!==null){ ?>
					<h4 class="total-members-text"><?php esc_html_e( 'Events', 'mec-buddyboss' ); ?></h4>
					<?php } ?>
					<div id="bp-mec-dropdown-options-loader" class="bp-mec-dropdown-options-loader-hide">
						<i class="bb-icon-loader animate-spin"></i>
					</div>
					<div class="bp-group-message-wrap">

						<?php if ( $data->created == true && $access_create===true && BP_MEC_Frontend::current_user_can_submit_event() )  : ?>
						<a href="<?php echo $url;?>" id="bp-mec-create-events-button" data-group-id="<?php echo esc_attr( $group_id ); ?>">
							<i class="bb-icon-edit-square"></i><?php _e( 'Create New', 'mec-buddyboss' ); ?>
						</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php if(bp_mec_is_mec_filters_enabled(false)): ?>
			<div class="bp-mec-events-search subnav-search mec-left-right clearfix" role="search">
				<section class="container">
					<div class="left-half">
						<article>
							<div class="bp-search">
								<form action="" method="get" id="bp_mec_events_search_form" class="bp-mec-events-search-form" data-bp-search="mec-events">
									<label for="bp_mec_events_search" class="bp-screen-reader-text"><?php bp_nouveau_search_default_text( __( 'Search Events', 'mec-buddyboss' ), false ); ?></label>
									<input type="search" id="bp_mec_events_search" placeholder="<?php esc_attr_e( 'Search Events', 'mec-buddyboss' ); ?>" />
									<button type="submit" id="bp_mec_events_search_submit" class="nouveau-search-submit">
									<span class="dashicons dashicons-search" aria-hidden="true"></span>
									<span id="button-text" class="bp-screen-reader-text"><?php esc_html_e( 'Search Events', 'mec-buddyboss' ); ?></span>
									</button>
								</form>
							</div>
						</article>
					</div>
					<div class="right-half">
						<article>
							<?php
							$categories = get_categories('taxonomy=mec_category&post_type=mec-events');
							?>
							<div class="select-wrap">
								<select id="mec-bp-events-category">
									<option value=""><?php esc_html_e( 'Select Category', 'mec-buddyboss' ); ?></option>
									<?php
									foreach ($categories as $key => $cat) {
										echo '<option value="'.$cat->term_id.'">'.$cat->name.'</option>';
									}
									?>
								</select>
								<span class="select-arrow" aria-hidden="true"></span>
							</div>
						</article>
					</div>
				</section>
			</div>
			<?php endif; ?>
			<div class="bp-mec-events-members-listing">
				<ul id="eventss-list" class="item-list bp-list all-events">
					<span class="events-timezone">
					</span>
				</ul>
			</div>
		</div>
	</div>
	<div class="bp-mec-events-right">
		<div class="bp-mec-events-right-top">
			<div id="bp-mec-events-content">
				<div id="bp-mec-single-events-wrapper">
					<?php
					if($data->create_mode==true && $access_create==true){

						bp_mec_echo_fes_form_styles_and_scripts();
						$fes = new MEC_feature_fes();
						echo $fes->vform();
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
