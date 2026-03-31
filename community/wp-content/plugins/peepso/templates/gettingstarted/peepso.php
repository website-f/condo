<?php
$steps = array(
    '1' =>   '<i class="fa fa-child" aria-hidden="true"></i><span>' . __('Welcome to PeepSo!', 'peepso-core') . '</span>', // shortcodes & optin
    '2' =>   '<i class="fa fa-key" aria-hidden="true"></i><span>' . __('License Keys', 'peepso-core') . '</span>', // shortcodes & optin
    '3' =>   '<i class="fa fa-sliders" aria-hidden="true"></i><span>' . __('Customize', 'peepso-core') . '</span>', // config options
    '4' =>   '<i class="fa fa-plus" aria-hidden="true"></i><span>' . __('Next steps', 'peepso-core') . '</span>', // thanks, upsell
);

$data = array('step'=>$step,'steps'=>$steps,'license'=>$license);
PeepSoTemplate::exec_template('gettingstarted','peepso-header',   $data);
PeepSoTemplate::exec_template('gettingstarted','peepso-'.$step, $data);
PeepSoTemplate::exec_template('gettingstarted','peepso-footer', $data);
