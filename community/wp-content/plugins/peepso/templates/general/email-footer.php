<?php
$i18n = __('This email was sent to {currentuserfullname} ({useremail}).', 'peepso-core');
$message = 'This email was sent to {currentuserfullname} ({useremail}).';

$message = PeepSo3_MultiLang__($message,'peepso-core', $user_id);
?>
<p style="margin: 0; word-break: break-word;"><span style="word-break: break-word;"><?php echo $message; ?></span></p>

<?php
$i18n = __('If you do not wish to receive these emails from {sitename}, you can <a href="{unsubscribeurl}" target="_blank" style="text-decoration: underline; color: #3d3d3d;" rel="noopener">manage your preferences</a> here.', 'peepso-core');
$message = 'If you do not wish to receive these emails from {sitename}, you can <a href="{unsubscribeurl}" target="_blank" style="text-decoration: underline; color: #3d3d3d;" rel="noopener">manage your preferences</a> here.';

$message = PeepSo3_MultiLang__($message,'peepso-core', $user_id);
?>
<p style="margin: 0; word-break: break-word;"><span style="word-break: break-word;"><?php echo $message; ?></span></p>
