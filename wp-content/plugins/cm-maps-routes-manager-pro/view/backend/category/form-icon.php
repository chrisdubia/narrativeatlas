<?php
use com\cminds\mapsroutesmanager\helper\GoogleMapsIcons;
use com\cminds\mapsroutesmanager\model\Category;

if (!empty($term)) $template = <<<HTML
	<tr class="form-field">
		<th scope="row"><label for="cmmrm_category_icon">%s</label></th>
		<td>%s</td>
	</tr>
HTML;
else $template = <<<HTML
	<div class="form-field">
		<label for="cmmrm_category_icon">%s</label>
		%s
	</div>
HTML;

$options = '';
foreach ($icons as $icon) {
	$options .= sprintf('<img src="%s">', esc_attr($icon));
}

if (!empty($currentIcon)) {
	$current = '<img src="'. esc_attr($currentIcon) .'" class="cmmrm_category_icon_image" style="max-width:64px;max-height:64px;" />
		<input type="hidden" name="cmmrm_category_icon" value="'. esc_attr($currentIcon) .'" />';
} else {
	$current = '<img class="cmmrm_category_icon_image" style="max-width:64px;max-height:64px;" /><input type="hidden" name="cmmrm_category_icon" value="" />';
}

$content = <<<HTML
	<div class="cmmrm_category_icon">
		<p id="category-image-wrapper">%s</p>
		<p>
			<input type="button" value="Choose icon" class="cmmrm_category_icon_choose" style="cursor:pointer;" />
			<input type="button" value="Upload icon" class="cmmrm_category_icon_upload" style="cursor:pointer;" />
			<input type="button" value="Remove icon" class="cmmrm_category_icon_remove" style="cursor:pointer;" />
		</p>
		<div class="cmmrm_category_icon_list" style="display:none">%s</div>
		<input type="hidden" name="%s" value="%s" />
	</div>
HTML;

$content = sprintf($content, $current, $options, $nonceField, $nonce);

printf($template, 'Default marker icon', $content);

if (!empty($term)) $template2 = <<<HTML
	<tr class="form-field">
		<th scope="row"><label for="cmmrm_category_icon">%s</label></th>
		<td>%s</td>
	</tr>
HTML;
else $template2 = <<<HTML
	<div class="form-field">
		<label for="cmmrm_category_icon">%s</label>
		%s
	</div>
HTML;

$routecontent = '<div id="cmmrm_category-file-wrapper">';
$files_list = array();
$id = 0;
if ($term AND $route = Category::getInstance($term)) {
	$id = $term->term_id;
	$files_list = $route->getRouteFileList();
}
if ( is_array( $files_list ) ) {
	foreach ( $files_list as $file_list ) {
		$routecontent .= '<div class="cmmrm_added_files_placeholder"><b>' . $file_list['title'] . '</b><a href="' . $file_list['url'] . '" target="_blank">' . basename($file_list['url']) .'</a><input type="hidden" class="cmmrm_remove_file_id"  value="' . $file_list['id'] . '" /><input type="button" class="cmmrm_remove_file_button" title="Remove" value="X" /></div>'; 
	}
}
$routecontent .= '</div>';
$routecontent .= '<div id="cmmrm_category_file_placeholder">';
$routecontent .= '</div>';
$routecontent .= '<p>';
$routecontent .= '<input type="hidden" id ="cmmrm_route_remove_files" name="cmmrm_route_remove_files"  value="">';
$routecontent .= '<input type="button" class="button button-secondary ct_tax_media_file_button" id="cmmrm_media_file_button" name="ct_tax_media_file_button" value="Select Files" />';
$routecontent .= '</p>';

$routecontentscript = <<<HTML
<style>
.cmmrm_added_files_placeholder { clear:both; float:left; width:100%; }
.cmmrm_added_files_placeholder .cmmrm_title_text_input { width:150px !important; float:left; }
.cmmrm_added_files_placeholder .cmmrm_upload_file_name { float: left; margin: 5px 0 0 5px; }
.cmmrm_added_files_placeholder .cmmrm_remove_file_button { float:right; cursor:pointer; }
#cmmrm_category_file_placeholder { margin-bottom:10px; }
.cmmrm_added_files_placeholder { margin-bottom:5px; }
.cmmrm_added_files_placeholder b { margin-right:10px; }
</style>
<script>
jQuery(document).ready( function( $ ) {

	var myfile_upload;

	$('#cmmrm_media_file_button').click(function(e) {
		e.preventDefault();

		if( myfile_upload ) {
			myfile_upload.open();
			return;
		}

		myfile_upload = wp.media.frames.file_frame = wp.media({
			title: "Select the file",
			button: { text: "select" },
			multiple: true

		});

		myfile_upload.on( 'select', function(){
			var attachments = myfile_upload.state().get('selection').map( 
			function( attachments ) {
				attachments.toJSON();
				return attachments;
			});

			for (i = 0; i < attachments.length; ++i) {
				$('#cmmrm_category_file_placeholder').after(
					'<div class="cmmrm_added_files_placeholder">' +
					'<input type="text" class="cmmrm_title_text_input" name="cmmrm_route_add_files_title[' + attachments[i].id + ']" value="" />' + 
					'<input type="hidden" name="cmmrm_route_add_files[]"  value="' + attachments[i].id + '" />' +
					' <input type="button" title="Remove" class="cmmrm_remove_file_button" value="X" onClick="this.parentNode.parentNode.removeChild(this.parentNode);" /><span class="cmmrm_upload_file_name">' +
					attachments[i].attributes.filename  +'</span> </div>'
				);
			}
		});

	myfile_upload.open();

	});

	var holder = $('.cmmrm_added_files_placeholder');
	var removeFileList = $('#cmmrm_route_remove_files');

	if (holder.length) {
		holder.each(function () {
			var thisHolder = $(this),
				deleteButton = thisHolder.find('.cmmrm_remove_file_button'),
				removeFileID = thisHolder.find('.cmmrm_remove_file_id');

				deleteButton.on('click', function (e) {
					e.preventDefault();
					
					removeFileListVal = removeFileList.val();
					removeFileListSeparator = ( removeFileListVal != '' ) ? ',' : '';
					removeFileListVal = removeFileListVal + removeFileListSeparator + removeFileID.val();
					removeFileList.val(removeFileListVal);

					thisHolder.remove();
				})
		})
	}
});
</script>
HTML;

printf($template2, 'Files', $routecontent.$routecontentscript);

/*
use com\cminds\mapsroutesmanager\model\Category;

if (!empty($term)) $template2 = <<<HTML
	<tr class="form-field">
		<th scope="row"><label for="cmmrm_category_icon">%s</label></th>
		<td>%s</td>
	</tr>
HTML;
else $template2 = <<<HTML
	<div class="form-field">
		<label for="cmmrm_category_icon">%s</label>
		%s
	</div>
HTML;

$routecontent = '<div id="cmmrm_category-file-wrapper">';
$files_list = array();
$id = 0;
if ($term AND $route = Category::getInstance($term)) {
	$id = $term->term_id;
	$files_list = $route->getRouteFileList();
}
if ( is_array( $files_list ) ) {
	foreach ( $files_list as $file_list ) {
		$routecontent .= '<div class="cmmrm_added_files_placeholder"><b>' . $file_list['title'] . '</b><a href="' . $file_list['url'] . '" target="_blank">' . basename($file_list['url']) .'</a><input type="hidden" class="cmmrm_remove_file_id"  value="' . $file_list['id'] . '" /><input type="button" class="cmmrm_remove_file_button" title="Remove" value="X" /></div>'; 
	}
}
$routecontent .= '</div>';
$routecontent .= '<div id="cmmrm_category_file_placeholder">';
$routecontent .= '</div>';
$routecontent .= '<p>';
$routecontent .= '<input type="hidden" id ="cmmrm_route_remove_files" name="cmmrm_route_remove_files"  value="">';
$routecontent .= '<input type="button" class="button button-secondary ct_tax_media_file_button" id="cmmrm_media_file_button" name="ct_tax_media_file_button" value="Select Files" />';
$routecontent .= '</p>';

$routecontentscript = <<<HTML
<style>
.cmmrm_added_files_placeholder { clear:both; float:left; width:100%; }
.cmmrm_added_files_placeholder .cmmrm_title_text_input { width:150px !important; float:left; }
.cmmrm_added_files_placeholder .cmmrm_upload_file_name { float: left; margin: 5px 0 0 5px; }
.cmmrm_added_files_placeholder .cmmrm_remove_file_button { float:right; cursor:pointer; }
#cmmrm_category_file_placeholder { margin-bottom:10px; }
.cmmrm_added_files_placeholder { margin-bottom:5px; }
.cmmrm_added_files_placeholder b { margin-right:10px; }
</style>
<script>
jQuery(document).ready( function( $ ) {

	var myfile_upload;

	$('#cmmrm_media_file_button').click(function(e) {
		e.preventDefault();

		if( myfile_upload ) {
			myfile_upload.open();
			return;
		}

		myfile_upload = wp.media.frames.file_frame = wp.media({
			title: "Select the file",
			button: { text: "select" },
			multiple: true

		});

		myfile_upload.on( 'select', function(){
			var attachments = myfile_upload.state().get('selection').map( 
			function( attachments ) {
				attachments.toJSON();
				return attachments;
			});

			for (i = 0; i < attachments.length; ++i) {
				$('#cmmrm_category_file_placeholder').after(
					'<div class="cmmrm_added_files_placeholder">' +
					'<input type="text" class="cmmrm_title_text_input" name="cmmrm_route_add_files_title[' + attachments[i].id + ']" value="" />' + 
					'<input type="hidden" name="cmmrm_route_add_files[]"  value="' + attachments[i].id + '" />' +
					' <input type="button" title="Remove" class="cmmrm_remove_file_button" value="X" onClick="this.parentNode.parentNode.removeChild(this.parentNode);" /><span class="cmmrm_upload_file_name">' +
					attachments[i].attributes.filename  +'</span> </div>'
				);
			}
		});

	myfile_upload.open();

	});

	var holder = $('.cmmrm_added_files_placeholder');
	var removeFileList = $('#cmmrm_route_remove_files');

	if (holder.length) {
		holder.each(function () {
			var thisHolder = $(this),
				deleteButton = thisHolder.find('.cmmrm_remove_file_button'),
				removeFileID = thisHolder.find('.cmmrm_remove_file_id');

				deleteButton.on('click', function (e) {
					e.preventDefault();
					
					removeFileListVal = removeFileList.val();
					removeFileListSeparator = ( removeFileListVal != '' ) ? ',' : '';
					removeFileListVal = removeFileListVal + removeFileListSeparator + removeFileID.val();
					removeFileList.val(removeFileListVal);

					thisHolder.remove();
				})
		})
	}
});
</script>
HTML;

printf($template2, 'Files', $routecontent.$routecontentscript);
*/