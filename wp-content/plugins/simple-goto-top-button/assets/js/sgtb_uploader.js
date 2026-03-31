jQuery(document).ready(function(){
	jQuery('input[name="btnstyle"]').click(function(){
	var style = jQuery(this).val();
	if(style=='style_other'){
		jQuery('.custom_style_arrow').fadeIn(300);
	} else{ 
		jQuery('.custom_style_arrow').fadeOut(300);
	}
	
	})

	jQuery('#sgtb_uploadcustom_ico').click(function() {
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        return false;
    });

    window.send_to_editor = function(html) {
        jQuery('#sgtb_upldbtn').html(html);
		var imgsrc = jQuery('#sgtb_upldbtn img').attr('src');
		jQuery('.custom_arrowbtn').val(imgsrc);
        tb_remove();
    }
	
	jQuery('.sgtbremoveicon').click(function(){
		var cnf = confirm('Are you sure you want to remove this icon ?');
		if(cnf){
			jQuery('.custom_arrowbtn').val('');	
			jQuery('.sgtbuploaded_btn').hide();
		}
	})
	
});