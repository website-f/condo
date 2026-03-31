jQuery(document).ready(function(){


jQuery(window).scroll(function(){
  scroll = jQuery(window).scrollTop();
  if (scroll >= 150) jQuery('.sgtbhot-yes').addClass('sgtbvis');
  else jQuery('.sgtbhot-yes').removeClass('sgtbvis');
});

jQuery(document).on('click', '.sgtb_btn', function(){
    jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
});

})

