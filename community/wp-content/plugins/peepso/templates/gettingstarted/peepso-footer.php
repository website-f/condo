<?php

if(isset($_GET['peepso_debug'])) {
    PeepSo3_Mayfly::del('peepso_config_licenses_bundle');
    update_option('peepso_register', 0);
}
?>

<div class="psa-starter__column">
    <div class="psa-starter__column-inner">

    <!-- REGISTER YOUR COPY -->
    
    <?php
        $optionName = 'peepso_register';
        
        $registrationHide	 = filter_input(INPUT_GET, 'peepso_registration_hide' );
        if ( $registrationHide ) {
            update_option($optionName, 1);
        }
        
        $post = filter_input_array(INPUT_POST);
        $domain = 'https://peepso.com';
        
        if (!empty($post) && !empty($post['register_nonce'])) {
            
            $nonceCheck = wp_verify_nonce($post['register_nonce'], 'peepso_register');
            if ($nonceCheck) {
                
                if (!PeepSo::get_option('optin_stats', 0)) {
                    if(isset($post['optin_stats'])) {
                        $PeepSoConfigSettings = PeepSoConfigSettings::get_instance();
                        $PeepSoConfigSettings->set_option('optin_stats', 1);
                    }
                }
                
                if(isset($post['optin_stats'])) {
                    unset($post['optin_stats']);
                }
                unset($post['register_nonce']);
                
                $jsonData = wp_json_encode(array($post));
                
                $args = array(
                    'body' => array(
                        'jsonData' => $jsonData
                    )
                );
                
                $href		 = str_replace('http', 'https', $domain) . '/wp-admin/admin-ajax.php?action=add_user&cminds_json_api=add_user';
                $response	 = wp_remote_post($href, $args);
                
                if (!is_wp_error($response))
                    {
                        $result = json_decode(wp_remote_retrieve_body($response), true);
                        if ($result && 1 === $result['result'])
                            {
                                update_option($optionName, 1);
                            }
                    } else {
                        $args['sslverify'] = false;
                        $href				 = $domain . '/wp-admin/admin-ajax.php?action=add_user&cminds_json_api=add_user';
                        $response			 = wp_remote_post($href, $args);
                        
                        if (!is_wp_error($response))
                            {
                                $result = json_decode(wp_remote_retrieve_body($response), true);
                                if ($result && 1 === $result['result'])
                                    {
                                        update_option($optionName, 1);
                                    }
                            } else {
                                $message = 'Registered fields: <br/><table>';
                                foreach ($post as $key => $value) {
                                    if (!in_array($key, array('product_name', 'email', 'hostname'))) {
                                        continue;
                                    }
                                    $message .= '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
                                }
                                $message .= '</table>';
                                
                                add_filter('wp_mail_content_type', array(&$this, 'set_mail_content_type'));
                                wp_mail('info@peepso.com', 'PeepSo Product Registration', $message);
                                remove_filter('wp_mail_content_type', array(&$this, 'set_mail_content_type'));
                            }
                    }
            }
        }
        
        
        
        $fields = array(
            'product_name'   => 'peepso',
            'remote_url'     => get_bloginfo('wpurl'),
            'remote_ip'      => $_SERVER['SERVER_ADDR'],
            'remote_country' => '',
            'remote_city'    => '',
            'email'          => get_bloginfo('admin_email'),
            'hostname'       => get_bloginfo('wpurl'),
            'username'       => '',
        );
        
        $output = '';
        foreach ($fields as $key => $value) {
            $output .= sprintf( '<input type="hidden" name="%s" value="%s" />', $key, $value );
        }
        
        $registrationHidden = get_option($optionName);
        
        if (!$registrationHidden)
            {
                $dashboard_main = __('Once registered, you will receive updates and special offers from PeepSo. We will send your administrator\'s e-mail and site URL to PeepSo server.','peepso');
                
    ?>
    <div class="psa-starter__registercopy">
        <h2>Register Your Copy</h2>
        
        <form method="post" action="">
            
            <div class="cminds_registration_wrapper">
                <div class="cminds_registration">
                    <div class="cminds_registration_text">
                        <span>
                            <?php echo $dashboard_main; ?>
                        </span>
                        <span>
                            <?php if(!PeepSo::get_option('optin_stats', 0)) { ?>
                            
                            <p><input name="optin_stats" type="checkbox" checked="checked" /> <?php echo __('Enable additional statistics'); ?> <a target="_blank" href="<?php echo admin_url('admin.php?page=peepso_config&tab=advanced&stats');?>"><i class="infobox-icon dashicons dashicons-editor-help"></i></a></p>
                            
                            <?php } ?>
                        </span>
                    </div>
                    <div class="cminds_registration_action">
                        
                        <?php
                            wp_nonce_field('peepso_register', 'register_nonce');
                            echo $output;
                        ?>
                        <input class="button button-primary" type="submit" value="Register Your Copy" />
                        
                    </div>
                </div>
            </div>
        </form>
    </div>
<hr>

    <?php } ?>

    <div class="psa-starter__header">
        <h2 class="psa-starter__header-title"><?php echo  __('More','peepso-core');?></h2>
        <p><?php echo  sprintf(__('Want to learn more? Check out our %s and our %s. Need support? You can always %s - we are happy to help!','peepso-core'),
                '<a href="https://peep.so/documentation" target="_blank">'.__('documentation','peepso-core').' <i class="fa fa-external-link"></i></a>',
                '<a href="https://www.youtube.com/c/peepso" target="_blank">'.__('YouTube Channel','peepso-core').' <i class="fa fa-external-link"></i></a>',
                '<a href="https://peepso.com/contact" target="_blank">'.__('contact us','peepso-core').' <i class="fa fa-external-link"></i></a>'
            );

            ?>
        </p>
        <p><?php echo  sprintf(__('Join our %s to meet other people building their communities with PeepSo, exchange experiences, support and inspire each other.','peepso-core'), '<a href="https://peepso.com/community" target="_blank">'.__('PeepSo Community','peepso-core').' <i class="fa fa-external-link"></i></a>');?></p>
        <p><?php echo  sprintf(__('Are you a developer? You might be interested in %s.','peepso-core'),'<a href="https://peep.so/helloworld" target="_blank">'.__('PeepSo Developer Resources','peepso-core').' <i class="fa fa-external-link"></i></a>');?></p>
    </div>

    <hr>
    
    <div class="psa-starter__header">
        <h2 class="psa-starter__header-title"><?php echo  __('See how it`s done','peepso-core');?></h2>
        <p><?php echo  sprintf(__('The video below will guide you through the very basics. Longer more detailed videos are available on our %s','peepso-core'),
        '<a href="https://www.youtube.com/c/peepso" target="_blank">'.__('YouTube Channel','peepso-core').' <i class="fa fa-external-link"></i></a>'
        
        );
            
        ?>
        </p>
        <iframe width="560" height="315" src="<?php echo PeepSoGettingStarted::get_youtube_video();?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
    </div>

    <hr>
    
    <div class="psa-starter__header">
        <h2 class="psa-starter__header-title"><?php echo  __('Make Your Community Mobile','peepso-core');?></h2>
        <p><?php echo  sprintf(__('Take your online community to the next level by going mobile with PeepSo! With your own branded mobile app, your members can connect, share, and engage anytime, anywhere - right from their phones. Whether you`re running a social group, professional network, or niche community, a dedicated app boosts visibility, increases user retention and keeps your community active on the go. Don’t miss out - %s','peepso-core'),
            '<a href="https://peepso.com/app" target="_blank">'.__('make your community mobile.','peepso-core').' <i class="fa fa-external-link"></i></a>',
        );
            
        ?>
        </p>
        <a href="https://peepso.com/app" target="_blank"><img src="https://peepso.com/wp-content/uploads/2024/09/app-peepso.webp" alt="your mobile community" width="550px"></a>
    </div>



    </div>
    </div>
    </div>

</div> <!-- EOF container -->


<?php
$prev_step = $step-1;
$next_step = $step+1;

$back_label = '<i class="fa fa-angle-left" aria-hidden="true"></i> ' . __('Back','peepso-core');
$prev_label = __('Next','peepso-core') . ' <i class="fa fa-angle-right" aria-hidden="true"></i>';

?>

<div id="gs_prevnext" class="psa-starter__footer">
    <div class="psa-starter__footer-navi">
        <?php if($prev_step>0) { ?>
            <a href="<?php echo admin_url('admin.php?page=peepso-getting-started&section=peepso&step='.$prev_step);?>"><?php echo $back_label;?></a>
        <?php } else { echo "<span></span>"; } ?>

        <?php if($next_step<=count($steps)) { ?>
            <a href="<?php echo admin_url('admin.php?page=peepso-getting-started&section=peepso&step='.$next_step);?>"><?php echo $prev_label;?></a>
        <?php } else { echo "<span></span>"; } ?>
    </div>
</div>
