<?php
$online = '';
if (PeepSo3_Mayfly::get('peepso_cache_'.$member->get_id().'_online')) {
    $online = PeepSoTemplate::exec_template('profile', 'online', array('PeepSoUser' => $member, 'class' => 'ps-online--md ps-online--static'), TRUE);
}

$FollowingCount = PeepSoUserFollower::count_following($member->get_id(), true);
$FollowersCount = PeepSoUserFollower::count_followers($member->get_id(), true);
$Followers = _n('Follower', 'Followers', $FollowersCount, 'peepso-core');
if(!$FollowersCount) {
    $Followers = __('No followers', 'peepso-core');
}
?>

<div class="pso-member__header">
    <div class="pso-member__cover" style="background-image:url('<?php echo $member->get_cover(750); ?>')"></div>
    <a class="pso-avatar pso-avatar--lg pso-member__avatar" href="<?php echo $member->get_profileurl(); ?>">
        <img alt="<?php echo strip_tags($member->get_fullname()); ?> avatar" src="<?php echo $member->get_avatar(); ?>">
        <?php echo $online; ?>
    </a>
</div>
<div class="pso-member__body">
    <div class="pso-member__data">
        <a class="pso-member__meta" href="<?php echo $member->get_profileurl() . 'followers'; ?>">
            <span><?php echo $FollowersCount; ?></span>
            <span><?php echo $Followers; ?></span>
        </a>
        <a class="pso-member__meta" href="<?php echo $member->get_profileurl() . 'followers/following'; ?>">
            <span><?php echo $FollowingCount; ?></span>
            <span><?php echo __('Following', 'peepso-core'); ?></span>
        </a>
    </div>
    <div class="pso-member__name">
        <a href="<?php echo $member->get_profileurl(); ?>" class="pso-member-name__link" data-title="<?php echo strip_tags($member->get_fullname()); ?>">
            <?php 
            do_action('peepso_action_render_user_name_before', $member->get_id());
            echo $member->get_fullname();
            do_action('peepso_action_render_user_name_after', $member->get_id()); 
            ?>
        </a>
        <?php do_action('peepso_after_member_thumb', $member->get_id()); ?>
    </div>
    <?php
    if (!isset($hide_member_buttons_extra)) {
        PeepSoMemberSearch::member_buttons_extra($member->get_id());
    }
    ?>
</div>
<div class="pso-member__bottom">
    <?php
    if (!isset($hide_member_actions)) {
        PeepSoMemberSearch::member_buttons($member->get_id());
    }
    ?>
    <?php PeepSoMemberSearch::member_options($member->get_id()); ?>
</div>
