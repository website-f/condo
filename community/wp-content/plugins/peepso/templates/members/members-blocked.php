<?php if(!get_current_user_id()) { PeepSo::redirect(PeepSo::get_page('members')); } ?>

<div class="peepso">
    <div class="pso-page pso-page--members">
        <?php PeepSoTemplate::exec_template('general', 'navbar'); ?>
        <?php PeepSoTemplate::exec_template('general', 'register-panel'); ?>
        <?php PeepSoTemplate::exec_template('general','wsi'); ?>
        <?php PeepSoTemplate::exec_template('members','members-tabs', array('tab'=>'blocked'));?>

        <div class="pso-members pso-members--grid ps-js-blocked" data-mode="grid"></div>
        <div class="ps-scroll ps-js-blocked-triggerscroll">
            <img class="post-ajax-loader ps-js-blocked-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" style="display:none" />
        </div>
    </div>
</div>

<?php

PeepSoTemplate::exec_template('activity', 'dialogs');
