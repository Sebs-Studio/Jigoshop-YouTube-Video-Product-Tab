/**
 * Jigoshop YouTube Video Product Tab
 *
 * Author: Seb's Studio
 * Version: 1.0
 */

jQuery(document).ready(function(){
	var sizeWidth = 480; // Placeholder for width.
	var sizeHeight = 320; // Placeholder for height.
	var skinURL = ''; // Placeholder for custom skin url.
	var hideSize = 0; // Hide custom size fields.

	if(jQuery("#_tab_youtube_video_size.select").val() == 'custom'){
		hideSize = 1;
	}

	if(jQuery("#_tab_youtube_video_skin.select").val() == 'custom-skin'){
		jQuery('._tab_youtube_video_skin_custom_field').show();
	}

	jQuery('input[name="_tab_youtube_video_width"]').keyup(function(){
		var value = jQuery(this).val();
		jQuery(this).text(value);
		sizeWidth = value;
	});

	jQuery('input[name="_tab_youtube_video_height"]').keyup(function(){
		var value = jQuery(this).val();
		jQuery(this).text(value);
		sizeHeight = value;
	});

	jQuery('input[name="_tab_youtube_video_skin_custom"]').keyup(function(){
		var value = jQuery(this).val();
		jQuery(this).text(value);
		skinURL = value;
	});

	jQuery("#_tab_youtube_video_size.select").change(function(){
		var vsize = jQuery(this).val();
		var width = youtube_video_product_tab.vwidth;
		var height = youtube_video_product_tab.vheight;

		if(vsize == 'custom'){
			if(hideSize == 0){
				jQuery('.form-field._tab_youtube_video_size_field').after('<p class="form-field _tab_youtube_video_width_field"><label for="_tab_youtube_video_width">' + width + '</label><input type="text" class="short" name="_tab_youtube_video_width" id="_tab_youtube_video_width" value="" placeholder="' + sizeWidth + '" style="width:60px;"></p><p class="form-field _tab_youtube_video_height_field"><label for="_tab_youtube_video_height">' + height + '</label><input type="text" class="short" name="_tab_youtube_video_height" id="_tab_youtube_video_height" value="" placeholder="' + sizeHeight + '" style="width:60px;"></p>');
				jQuery('input[name="_tab_youtube_video_width"]').val(sizeWidth); // Sets the custom width saved.
				jQuery('input[name="_tab_youtube_video_height"]').val(sizeHeight); // Sets the custom height saved.
				hideSize = 1;
			}
			else{
				jQuery('._tab_youtube_video_width_field').show();
				jQuery('._tab_youtube_video_height_field').show();
			}
		}
		else{
			jQuery('._tab_youtube_video_width_field').hide();
			jQuery('._tab_youtube_video_height_field').hide();
		}
		return false;
	});

	jQuery("#_tab_youtube_video_skin.select").change(function(){
		var vskin = jQuery(this).val();
		var custom_skin = youtube_video_product_tab.vskinloc;
		var post_id = youtube_video_product_tab.post_id;
		var upload = youtube_video_product_tab.vupload;
		var insert = youtube_video_product_tab.insertURL;
		var input_placeholder = youtube_video_product_tab.skin_url_input_placeholder;

		if(vskin == 'custom-skin'){
			jQuery('._tab_youtube_video_skin_custom_field').show();
		}
		else{
			jQuery('._tab_youtube_video_skin_custom_field').hide();
		}
		return false;
	});

	jQuery('.upload_skin').click(function(e){
		// Disable default action.
		e.preventDefault();
		// Set up variables.
		file	= jQuery(this).prev();
		post_id = jQuery(this).data('postid');
		button_text = jQuery(this).data('update');
		window.send_to_editor = function(html){
			// Attach the file URI to the relevant.
			file.val(jQuery(html).attr('href'));
			// Hide thickbox.
			tb_remove();
		}
		// Show thickbox.
		tb_show(button_text, 'media-upload.php?post_id=' + post_id + '&from=jigoshop_product&tab=type&TB_iframe=true');
	});

});