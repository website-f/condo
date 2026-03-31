<?php

if(get_current_user_id()) {

	$blocked_member_url = PeepSo::get_page('members');
	if(0 == PeepSo::get_option('disable_questionmark_urls', 0)) {
	    $blocked_member_url .= '?';
	}
	$blocked_member_url .= 'blocked/';
	if (PeepSo::get_option('user_blocking_enable', 0) === 1) {
?>

<div class="pso-tabs pso-tabs--boxed pso-tabs--members pso-members__tabs">
	<div class="pso-tabs__inner">
		<a href="<?php echo PeepSo::get_page('members'); ?>" class="pso-btn <?php if (!isset($tab)) echo "pso-active"; ?> pso-tabs__item">
			<i class="pso-i-queue-alt"></i>
			<?php echo esc_attr__('Members', 'peepso-core'); ?>
		</a>
		<a href="<?php echo $blocked_member_url; ?>" class="pso-btn <?php if (isset($tab) && 'blocked' == $tab) echo "pso-active"; ?> pso-tabs__item">
			<i class="pso-i-user-lock"></i>
			<?php echo esc_attr__('Blocked', 'peepso-core'); ?>
		</a>
	</div>
</div>

<?php }
}
