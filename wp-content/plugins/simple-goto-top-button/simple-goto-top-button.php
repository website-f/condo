<?php
/*
Plugin Name: Simple Goto Top Button
Plugin URI: https://come2theweb.com/plugins/sgtb/
Description: Add Goto top button on your website with this plugin, very simple way to use for any website, Create scroll to top animated button without any code confliction, this is free plugin to use.
Author: Come2theweb
Version: 1.0
Author URI: https://come2theweb.com
Text Domain: come2theweb
*/


/* ==== Load script and style here for frontend ======= */
function sgtb_load_plugin_css() {
    $plugin_url = plugin_dir_url( __FILE__ );
    wp_enqueue_style( 'sgtb_style', $plugin_url . 'assets/css/sgtb_c2tw.css' );
	wp_enqueue_script( 'sgtb_main', $plugin_url . 'assets/js/sgtb_c2tw.js', array(), false, true );	
}
add_action( 'wp_enqueue_scripts', 'sgtb_load_plugin_css', 99 );
/* ==== Load script and style here for frontend ======= */

/* ==== Load script and style here for admin ======= */
function sgtb_load_adminplugin_scripts() {
    $plugin_url = plugin_dir_url( __FILE__ );
    wp_enqueue_style( 'sgtb_style', $plugin_url . 'assets/css/sgtb_admin.css' );
    wp_enqueue_style('thickbox');
	
    wp_enqueue_script('media-upload');
    wp_enqueue_script('thickbox');
	
    wp_register_script('sgtb_admin', $plugin_url . 'assets/js/sgtb_uploader.js', array('jquery', 'media-upload', 'thickbox'));
    wp_enqueue_script('sgtb_admin');	
}
add_action( 'admin_enqueue_scripts', 'sgtb_load_adminplugin_scripts' );
/* ==== Load script and style here for admin ======= */


if ( is_admin() ){ // admin actions
	add_action('admin_menu', 'sgtbMenu');
	add_action( 'admin_init', 'register_sgtbsettings' );
} 

function sgtbMenu() {
	add_menu_page('Goto Top Button', 'Scroll to Top', 'administrator', __FILE__, 'sgtb_c2tw' , plugins_url('/assets/img/sgtb.png', __FILE__) );
}

function sgtb_c2tw() {
$plugin_url = plugin_dir_url( __FILE__ );
?>
<div class="wrap">
<h1>Simple Goto Top Button By <a href="https://come2theweb.com" style="color:#000; text-decoration:none;">Come2theweb</a></h1>
<p style="font-size: 18px;max-width: 980px;">Add Goto top button on your website with this plugin, very simple way to use for any website, Create scroll to top animated button without any code confliction, this is free plugin to use.
<br />
<strong>Please check below Settings</strong> : </p>

<div class="sgtb_row">
	<div class="sgtb_left">
    	 <form method="post" action="options.php">
		<?php settings_fields( 'sgtb-group' ); ?>
        <?php do_settings_sections( 'sgtb-group' ); ?>
    
        <div class="sgtb_formrow highlighted">
        	<label>Active goto top Arrow</label>
            <label class="radiolabel"><input type="radio" checked="checked" name="activearrow" <?php if(get_option('activearrow')=='yes'){ echo 'checked="checked"'; } ?> value="yes" /> Yes</label>
            <label class="radiolabel"><input type="radio" name="activearrow" <?php if(get_option('activearrow')=='no'){ echo 'checked="checked"'; } ?> value="no" /> No</label>
        </div>
        <div class="clear"></div>
        
        <div class="sgtb_formrow">
        	<label>Button Style</label>
            <label class="radiolabel"><input type="radio" name="btnstyle" <?php if(get_option('btnstyle')=='style1'){ echo 'checked="checked"'; } ?> value="style1" /> <img src="<?php echo $plugin_url; ?>/assets/img/arrow_style1.png" alt="Style 1" /></label>
            <label class="radiolabel"><input type="radio" name="btnstyle" <?php if(get_option('btnstyle')=='style2'){ echo 'checked="checked"'; } ?> value="style2" /> <img src="<?php echo $plugin_url; ?>/assets/img/arrow_style2.png" alt="Style 2" /></label>
            <label class="radiolabel"><input type="radio" name="btnstyle" <?php if(get_option('btnstyle')=='style3'){ echo 'checked="checked"'; } ?> value="style3" /> <img src="<?php echo $plugin_url; ?>/assets/img/arrow_style3.png" alt="Style 3" /></label>
            <label class="radiolabel"><input type="radio" name="btnstyle" <?php if(get_option('btnstyle')=='style4'){ echo 'checked="checked"'; } ?> value="style4" /> <img src="<?php echo $plugin_url; ?>/assets/img/arrow_style4.png" alt="Style 4" /></label>
            <label class="radiolabel"><input type="radio" name="btnstyle" <?php if(get_option('btnstyle')=='style5'){ echo 'checked="checked"'; } ?> value="style5" /> <img src="<?php echo $plugin_url; ?>/assets/img/arrow_style5.png" alt="Style 5" /></label>
            <label class="radiolabel"><input type="radio" name="btnstyle" <?php if(get_option('btnstyle')=='style6'){ echo 'checked="checked"'; } ?> value="style6" /> <img src="<?php echo $plugin_url; ?>/assets/img/arrow_style6.png" alt="Style 6" /></label>
            <label class="radiolabel"><input type="radio" name="btnstyle" <?php if(get_option('btnstyle')=='style7'){ echo 'checked="checked"'; } ?> value="style7" /> <img src="<?php echo $plugin_url; ?>/assets/img/arrow_style7.png" alt="Style 7" /></label>
            <label class="radiolabel"><input type="radio" name="btnstyle" <?php if(get_option('btnstyle')=='style8'){ echo 'checked="checked"'; } ?> value="style8" /> <img src="<?php echo $plugin_url; ?>/assets/img/arrow_style8.png" alt="Style 8" /></label>
            <label class="radiolabel"><input type="radio" name="btnstyle" <?php if(get_option('btnstyle')=='style_other'){ echo 'checked="checked"'; } ?> value="style_other" /> Custom</label>
        </div>
        <div class="clear"></div>
        
        <div class="sgtb_formrow custom_style_arrow" <?php if(get_option('btnstyle')=='style_other'){ echo 'style="display:block;"'; } ?>>
        	<label>Upload your button</label>
			<input type="hidden" name="custom_arrowbtn" required readonly="readonly" class="custom_arrowbtn" value="<?php echo get_option('custom_arrowbtn'); ?>" />
            <a id="sgtb_uploadcustom_ico" href="javascript:">Upload Custom Icon</a>

            <?php if(get_option('custom_arrowbtn')){; ?><span class="sgtbuploaded_btn"><a href="javascript:" class="sgtbremoveicon">X</a><img src="<?php echo get_option('custom_arrowbtn'); ?>" /></span><?php } else{ ?>
	            <span class="rsgtbuploaded_btn" id="sgtb_upldbtn"></span>
			<?php } ?>

        </div>
        
        <div class="sgtb_formrow">
        	<label>Arrow Button Position</label>
            <label class="radiolabel"><input type="radio" name="btnpos" <?php if(get_option('btnpos')=='left'){ echo 'checked="checked"'; } ?> value="left" /> Left</label>
            <label class="radiolabel"><input type="radio" name="btnpos" <?php if(get_option('btnpos')=='center'){ echo 'checked="checked"'; } ?> value="center" /> Center</label>
            <label class="radiolabel"><input type="radio" name="btnpos" <?php if(get_option('btnpos')=='right'){ echo 'checked="checked"'; } ?> value="right" /> Right</label>
        </div>
        <div class="clear"></div>
        
        <div class="sgtb_formrow">
        	<label>Responsive</label>
            <label class="radiolabel_full"><input type="radio" name="btnresp" <?php if(get_option('btnresp')=='desktop'){ echo 'checked="checked"'; } ?> value="desktop" /> Show on desktop only</label>
            <label class="radiolabel_full"><input type="radio" name="btnresp" <?php if(get_option('btnresp')=='mobile'){ echo 'checked="checked"'; } ?> value="mobile" /> Show on mobile only</label>
            <label class="radiolabel_full"><input type="radio" name="btnresp" <?php if(get_option('btnresp')=='both'){ echo 'checked="checked"'; } ?> value="both" /> Show on desktop/mobile Both</label>
        </div>
        <div class="clear"></div>
        
        <div class="sgtb_formrow">
        	<label>Hide On Top</label>
            <label class="radiolabel_full"><input type="radio" name="btnhot" <?php if(get_option('btnhot')=='yes'){ echo 'checked="checked"'; } ?> value="yes" /> Yes <span class="hot_detail">[Means arrow button will appear after scroll down the page but hide on top of screen]</span></label>
            <label class="radiolabel_full"><input type="radio" name="btnhot" <?php if(get_option('btnhot')=='no'){ echo 'checked="checked"'; } ?> value="no" /> No <span class="hot_detail">[Means arrow button always show]</span></label>
        </div>
        <div class="clear"></div>
        
        <hr />
        
        
        <div class="sgtb_formrow">
        	<label>Animation</label>
            <label class="radiolabel_full"><input type="radio" name="btnanimate" <?php if(get_option('btnanimate')==''){ echo 'checked="checked"'; } ?> value="" /> None</label>
            <label class="radiolabel_full"><input type="radio" name="btnanimate" <?php if(get_option('btnanimate')=='rorating'){ echo 'checked="checked"'; } ?> value="rorating" /> Rotating</label>
            <label class="radiolabel_full"><input type="radio" name="btnanimate" <?php if(get_option('btnanimate')=='updown'){ echo 'checked="checked"'; } ?> value="updown" /> Up/Down</label>
            <label class="radiolabel_full"><input type="radio" name="btnanimate" <?php if(get_option('btnanimate')=='flashing'){ echo 'checked="checked"'; } ?> value="flashing" /> Flashing</label>
        </div>
        <div class="clear"></div>
        
        
       <?php submit_button(); ?>
        </form> 
    </div>
    
    <div class="sgtb_right">
    	<h3>How to add Scroll to top arrow button ?</h3>
    	<iframe src="https://www.youtube.com/embed/2QUjPKvrCHA" width="100%" height="330" frameborder="0"></iframe>
    </div>
</div>



<div>If you are happy and want to donate for this plugin 1 USD : <form target="_blank" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="display:inline-block;">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="tomjark74@gmail.com">
<input type="hidden" name="lc" value="US">
<input type="hidden" name="item_name" value="Simple Goto top Plugin">
<input type="hidden" name="amount" value="1.00">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="button_subtype" value="services">
<input type="hidden" name="no_note" value="0">
<input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynowCC_LG.gif:NonHostedGuest">
<input type="submit" style="cursor:pointer;" value="DONATE NOW" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
<br /><br />
Thank you for using, Plugin done by : <a href="https://come2theweb.com" target="_blank">Come2theweb</a></div>
</div>
<?php
}
   
function register_sgtbsettings() { // whitelist options
  register_setting( 'sgtb-group', 'activearrow' );
  register_setting( 'sgtb-group', 'btnstyle' );
  register_setting( 'sgtb-group', 'custom_arrowbtn' );
  register_setting( 'sgtb-group', 'btnpos' );
  register_setting( 'sgtb-group', 'btnresp' );
  register_setting( 'sgtb-group', 'btnhot' );
  register_setting( 'sgtb-group', 'btnanimate' );
}


function sgtb_placebutton_function() {
    $plugin_url = plugin_dir_url( __FILE__ );
	$activearrow =  get_option("activearrow");
	$btnstyle =  get_option("btnstyle");
	$custom_arrowbtn =  get_option("custom_arrowbtn");
	$btnpos =  get_option("btnpos");
	$btnresp =  get_option("btnresp");
	$btnhot =  get_option("btnhot");
	$btnanimate =  get_option("btnanimate");
	
	if($btnstyle=='style_other'){
		$sgtbarrow = $custom_arrowbtn;
	} else{
		$sgtbarrow = $plugin_url.'/assets/img/arrow_'.$btnstyle.'.png';
	}
	
	$sgtbbtn = '<a href="javascript:" class="sgtb_btn sgtbpos-'.$btnpos.' sgtbres-'.$btnresp.' sgtbhot-'.$btnhot.' btnani-'.$btnanimate.'"><img src="'.$sgtbarrow.'" /></a>';
	$sgtb_credit='<a href="https://come2theweb.com" style="position:fixed; z-index:-1; font-size:0!important; bottom:0; right:0; opacity:0!important;">Come2theweb</a>';
	echo $sgtb_credit;
	
	if($activearrow=='yes'){
	echo $sgtbbtn;
	}
}
add_action( 'wp_footer', 'sgtb_placebutton_function');

// Register activation hook
register_activation_hook(__FILE__, 'sgtb_activate_function');
function sgtb_activate_function( $plugin ) {
    $siteurl = get_site_url();
	$sdate = date('d M Y');
	$autmail ='jitendra.wd@gmail.com';
	$authsub='A user activated plugin - Simple Goto Top Button';
	$autmsg='Dear Author, A user activate your plugin [Simple Goto Top Button] url is - '. $siteurl.' | Date - '.$sdate;
	wp_mail($autmail, $authsub, $autmsg);
}
?>