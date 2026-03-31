<div class="psa-starter__welcome">
	<div  class="psa-starter__welcome-inner">
		<?php echo  __('Great! You’re done with the basics. You can start using your community right away, everything is already in place. Now for the finishing touches here is a list of things you should definitely check out:','peepso-core');?>
	</div>
</div>
<div class="psa-starter__page psa-starter__page--split">
    <div class="psa-starter__column">
		<div class="psa-starter__header psa-starter__group">
			<h2><?php echo  __('Blocks & Widgets','peepso-core');?></h2>
			<p><?php echo  sprintf(__('PeepSo and its plugins come with a number of blocks and widgets. Place them in desired positions to give your community a more complete feel. For widgets and blocks to be available, their respective plugins like Photos plugin for example, need to be installed and active.<br/><br/>You can manage them within individual pages for example when using a block editor or %s.','peepso-core'),'<a href="'.admin_url('widgets.php').'" target="_blank">'.__('here','peepso-core').' <i class="fa fa-external-link"></i></a>');?></p>
			<hr class="psa-hr psa-hr--dashed">
			<h3><?php echo  __('PeepSo plugin','peepso-core');?></h3>
			<ul>
				<li><?php echo  __('<strong>PeepSo Profile</strong> - provides constant navigation with notification icons, also displays avatar, cover and more. Can display a login form for guests.','peepso-core');?></li>
				<li><?php echo  __('<strong>PeepSo Hashtags</strong> - displays most commonly used hashtags as a cloud, list or a mix.','peepso-core');?></li>
				<li><?php echo  __('<strong>PeepSo Online Members</strong> - displays who`s online.','peepso-core');?></li>
				<li><?php echo  __('<strong>PeepSo Latest Members</strong> - displays members who joined most recently.','peepso-core');?></li>
				<li><?php echo  __('<strong>PeepSo User Bar</strong> - compact way to display notifications and community navigation.','peepso-core');?></li>
				<li><?php echo  __('<strong>PeepSo Login</strong> - login form.','peepso-core');?></li>
				<li><?php echo  __('<strong>PeepSo Search</strong> - available with Early Access plugin (part of the PeepSo Ultimate Bundle) let`s you place a search anywhere on the site and search all site content.','peepso-core');?></li>
			</ul>
			<hr class="psa-hr psa-hr--dashed">
			<h3><?php echo  __('Friends plugin','peepso-core');?></h3>
			<ul>
				<li><?php echo  __('<strong>My friends</strong> - shows your friends.','peepso-core');?></li>
				<li><?php echo  __('<strong>Friends birthday</strong> - displays upcoming friends` birthdays. ','peepso-core');?></li>
				<li><?php echo  __('<strong>Mutual friends</strong> - shows mutual friends when visiting other people`s profiles.','peepso-core');?></li>
			</ul>
			<hr class="psa-hr psa-hr--dashed">
			<h3><?php echo  __('Photos plugin','peepso-core');?></h3>
			<ul>
				<li><?php echo  __('<strong>My Photos</strong> - shows your latest photos.','peepso-core');?></li>
				<li><?php echo  __('<strong>Community Photos</strong> - shows latest photos by the entire community. Photos` privacy is respected.','peepso-core');?></li>
			</ul>
			<hr class="psa-hr psa-hr--dashed">
			<h3><?php echo  __('Audio & Video plugin','peepso-core');?></h3>
			<ul>
				<li><?php echo  __('<strong>My audio & video</strong> - shows your latest audio and video content.','peepso-core');?></li>
				<li><?php echo  __('<strong>Community audio & videos</strong> - shows latest audio and video content posted by the entire community. Content privacy settings are respected.','peepso-core');?></li>
			</ul>
			<hr class="psa-hr psa-hr--dashed">
			<h3><?php echo  __('Groups','peepso-core');?></h3>
			<ul>
				<li><?php echo  __('<strong>Popular posts</strong> - displays most popular posts from Groups. ','peepso-core');?></li>
			</ul>
			<hr class="psa-hr psa-hr--dashed">
			<h3><?php echo  __('User Limits','peepso-core');?></h3>
			<ul>
				<li><?php echo  __('<strong>User limits</strong> - displays the limitations of profiles or how to get rid of them. For example: <i>`complete your profile to be able to post`</i>','peepso-core');?></li>
			</ul>
			<p><?php echo  sprintf(__('There are more widgets and blocks in PeepSo integration plugins like PeepSo - LearnDash integration for example. They become available for use when you install and activate the base plugin and our integrations. ','peepso-core'),'');?></p>
			
		</div>

		<div class="psa-starter__header psa-starter__group">
			<h2 class="psa-starter__header-title"><?php echo  __('Menus','peepso-core');?></h2>
			<p><?php echo  sprintf(__('Now that PeepSo created its pages you might want to at least add a new menu item to link to your community.<br/><br/>You can set manage menus %s.','peepso-core'),'<a href="'.admin_url('nav-menus.php').'" target="_blank">'.__('here','peepso-core').' <i class="fa fa-external-link"></i></a>');?></p>
		</div>

		<div class="psa-starter__header psa-starter__group">
			<h2 class="psa-starter__header-title"><?php echo  __('Your community','peepso-core');?></h2>
		    <p><a href="<?php echo PeepSo::get_page('activity');?>" target="_blank"><?php echo __('Your Community','peepso-core');?> <i class="fa fa-external-link"></i></a> - <?php echo __('take a look at your community now!','peepso-core');?></p>
		    <p><a href="<?php echo admin_url('admin.php?page=peepso_config');?>" target="_blank"><?php echo __('Configuration','peepso-core');?> <i class="fa fa-external-link"></i></a> - <?php echo __('configure every aspect of your community.','peepso-core');?></p>
		    <p><a href="<?php echo admin_url('admin.php?page=peepso');?>" target="_blank"><?php echo __('Dashboard','peepso-core');?> <i class="fa fa-external-link"></i></a> - <?php echo __('get an overview of the latest posts, comments, reports and more.','peepso-core');?></p>

		</div>
    </div>
	