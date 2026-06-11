<?php

$event_id = $event->ID;
?>
<div class="events-item-container" data-id="<?php echo $event_id; ?>" data-events-id="<?php echo $event_id; ?>" data-is-recurring="0">
	<div class="bb-title-wrap">
		<a href="#" class="bp-back-to-events-list"><span class="bb-icon-chevron-left"></span></a>
		<div>
			<h2 class="bb-title">
			<?php echo $event->title; ?>							</h2>
			<div class="bb-timezone">
			</div>
		</div>
	</div>
	<div id="bp-mec-single-events" class="events-item events-item-table single-events-item-table" >
		<?php if( bp_is_group() && bp_mec_is_mec_groups_enabled() && function_exists('groups_get_groups') &&bp_mec_is_mec_assign_groups_enabled(false)==true && BP_MEC_Frontend::current_user_can_submit_event() ): ?>
			<?php if( mec_bb_current_user_can_edit_event( $event_id ) ): ?>
				<div class="single-events-item">
					<div class="events-item-head">
						<?php _e('Assign To Group','mec-buddyboss'); ?>
					</div>
					<div class="events-item-col">
						<?php
						$groups = bp_mec_get_groups();
						if($groups != null):
						?>
						<select id="mec-bp-groups" class="mec-bp-groups" data-bp-filter="friends">
							<option value="">
								<?php _e('Select Group','mec-buddyboss'); ?>
							</option>
							<?php
							foreach ($groups as $kg => $group) {
								if( bp_mec_is_user_can_event_change($group->id)){
								echo '<option value="'.$group->id.'">'.$group->name.'</option>';
								}
							}
							?>
						</select>
						<button class="button" onclick="return mec_bp_Events_Assign('<?php echo $event_id; ?>',null,'add')" id="mec-bp-groups-assign" type="button">
						<?php _e('Assign','mec-buddyboss'); ?>
						</button>
						<?php else: ?>
						<b>
						<?php _e('Group not found!','mec-buddyboss'); ?>
						</b>
						<?php endif; ?>
						<div id="mec-bp-groups-assign-list-area">
							<?php
							echo mec_bp_get_all_groups_formated( $event_id );
							?>
						</div>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>
		<div class="single-events-item">
			<div class="events-item-head">
				<?php _e('Start','mec-buddyboss'); ?>
			</div>
			<?php
				$date_format = bp_mec_get_settings( 'datetime_format', 'M d Y');
				$date_format = !empty( $date_format ) ? $date_format : 'M d Y';

				$mec_date = $event->date;

				$start_datetime = $mec_date['start']['date'].' '.sprintf('%02d', $mec_date['start']['hour'] ).':'.sprintf('%02d', $mec_date['start']['minutes'] ).' '.$mec_date['start']['ampm'];
				$end_datetime = $mec_date['end']['date'].' '.sprintf('%02d', $mec_date['end']['hour'] ).':'.sprintf('%02d', $mec_date['end']['minutes'] ).' '.$mec_date['end']['ampm'];

				$start_datetime = date_i18n( $date_format, strtotime( $start_datetime ) );
				$end_datetime = date_i18n( $date_format, strtotime( $end_datetime ) );
			?>
			<div class="events-item-col">
				<span class="events-id">
					<?php echo $start_datetime ?>
				</span>
			</div>
		</div>
		<div class="single-events-item">
			<div class="events-item-head">
				<?php _e('End','mec-buddyboss'); ?>
			</div>
			<div class="events-item-col">
				<?php echo $end_datetime ?>
			</div>
		</div>

		<div class="single-events-item">
			<div class="events-item-head">
				<?php _e('Event Link','mec-buddyboss'); ?>
			</div>
			<div class="events-item-col">
				<div class="copy-link-wrap">
					<a class="bb-invitation-url" href="<?php echo $event->permalink; ?>">
						<?php echo $event->permalink; ?>
					</a>
				</div>
			</div>
		</div>
		<?php
			$url= mec_bb_get_edit_or_create_event_link( $event_id );
			if( !empty( $url ) ):
		?>
			<div class="single-events-item">
				<div class="events-item-head">
					<?php _e('Edit Link','mec-buddyboss'); ?>
				</div>
				<div class="events-item-col">
					<div class="copy-link-wrap">
						<a class="bb-edit-url" href="<?php echo mec_bb_get_edit_or_create_event_link( $event_id ); ?>">
							<?php echo __( 'Edit', 'mec-buddyboss' ); ?>
						</a>
					</div>
				</div>
			</div>
		<?php
			endif;

		$book_or_event_id = isset( $event->id2 ) ? $event->id2 : false;
		do_action( 'mec_buddyboss_event_or_booking_detail', $event_id, $book_or_event_id, $event );
		?>

	</div>
</div>