<?php
$PeepSoUser = PeepSoUser::get_instance($view_user_id);
?>
<div class="pso-tabs pso-tabs--boxed pso-tabs--members pso-members__tabs ps-followers__tabs">
	<div class="pso-tabs__inner">
		<a href="<?php echo $PeepSoUser->get_profileurl() . 'followers/'; ?>" class="pso-btn <?php if('followers' === $current) echo "pso-active"; ?> pso-tabs__item">
			<?php echo esc_attr__('Followers', 'peepso-core'); ?>
		</a>
		<a href="<?php echo $PeepSoUser->get_profileurl() . 'followers/following'; ?>" class="pso-btn <?php if('following' === $current) echo "pso-active"; ?> pso-tabs__item">
			<?php echo esc_attr__('Following', 'peepso-core'); ?>
		</a>
	</div>
</div>
