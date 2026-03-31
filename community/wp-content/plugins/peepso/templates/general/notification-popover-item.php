<?php
$PeepSoProfile	= PeepSoProfile::get_instance();
$user_id = $PeepSoProfile->notification_user();
$PeepSoUser		= PeepSoUser::get_instance($user_id);

$notification_id = $PeepSoProfile->notification_id(FALSE);
$readstatus = $PeepSoProfile->notification_readstatus();

$className = 'pso-notification';
if ($readstatus === FALSE) {
	$className .= ' pso-notification--unread';
}
$className .= ' ps-js-notification ps-js-notification--' . $notification_id;

?>
<div class="pso-notification__wrap">
	<div class="<?php echo $className; ?>" data-id="<?php echo $notification_id; ?>"
		<?php echo $readstatus === FALSE ? 'data-unread="1"' : '' ?>>
		<a class="pso-notification__inner" href="<?php echo $PeepSoProfile->notification_link(false); ?>">
			<div class="pso-notification__avatar">
				<div class="pso-avatar pso-avatar--sm">
					<?php $PeepSoProfile->notification_avatar($notification_id, $PeepSoUser); ?>
				</div>
			</div>

			<div class="pso-notification__body">
				<div class="pso-notification__desc">
					<span class="pso-notification__user"><?php $PeepSoProfile->notification_user_firstname($notification_id, $PeepSoUser); ?></span>
					<span>
						<?php $PeepSoProfile->notification_message(); ?><?php $PeepSoProfile->notification_link(); ?>
						<?php $PeepSoProfile->notification_human_friendly();?>
					</span>
				</div>

				<div class="pso-notification__meta">
					<span
					class="activity-post-age"
					data-timestamp="<?php $PeepSoProfile->notification_timestamp(); ?>"><?php $PeepSoProfile->notification_age(); ?></span>

					<?php if (!$readstatus) { ?>
					<span class="pso-notification__status pso-tip pso-tip--left ps-js-mark-as-read"
							aria-label="<?php echo esc_attr__('Mark as read', 'peepso-core');?>">
						<i class="pso-i-circle"></i>
					</span>
					<?php } ?>
				</div>
			</div>
		</a>
	</div>
</div>
