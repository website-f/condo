<div id="gs_menu" class="psa-starter__menu">
    <div class="psa-starter__logo">
        <img width="130" src="<?php echo esc_url(PeepSo::get_asset('images/admin/logo_red.png'));?>" />
    </div>
    <?php foreach($steps as $step_id=>$label) { ?>
        <a class="psa-starter__menu-item <?php if($step_id==$step) echo 'active';?>" href="<?php echo admin_url('admin.php?page=peepso-getting-started&section=peepso&step='.$step_id);?>">
            <?php echo $label;?>
        </a>
    <?php } ?>
</div>

<div id="gs_container" class="psa-starter__content">
