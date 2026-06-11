<?php if(count($data->events)>0):$data_id = 1;foreach($data->events as $ke=>$event): ?>
<li class="events-item " id="event-show-row-<?php echo $event->ID; ?>" data-id="<?php echo $data_id; ?>" data-events-id="<?php echo $event->ID; ?>">
	<div class="events-item-col events-topic">
		<a href="#" class="events-title" onclick="return mec_bp_Events_Load('<?php echo $event->ID; ?>',<?php echo isset( $event->id2 ) ? $event->id2 : 0; ?>);">
			<?php echo $event->data->title; ?>
		</a>
	</div>
	<div class="events-item-col events-meta-wrap">
		<?php
		$date_format = bp_mec_get_settings( 'datetime_format', 'M d Y');
		$date_format = !empty( $date_format ) ? $date_format : 'M d Y';

		$mec_date = $event->date;

		$start_datetime = $mec_date['start']['date'].' '.sprintf('%02d', $mec_date['start']['hour'] ).':'.sprintf('%02d', $mec_date['start']['minutes'] ).' '.$mec_date['start']['ampm'];
		$end_datetime = $mec_date['end']['date'].' '.sprintf('%02d', $mec_date['end']['hour'] ).':'.sprintf('%02d', $mec_date['end']['minutes'] ).' '.$mec_date['end']['ampm'];

		$start_datetime = date_i18n( $date_format, strtotime( $start_datetime ) );
		$end_datetime = date_i18n( $date_format, strtotime( $end_datetime ) );
		?>
		<div class="events-id">
			<?php _e('Start','mec-buddyboss'); ?>: <?php echo $start_datetime; ?></div>
		<div class="events-date">
			<?php _e('End','mec-buddyboss'); ?>: <?php echo $end_datetime; ?></div>
	</div>
</li>
<?php endforeach;endif; ?>