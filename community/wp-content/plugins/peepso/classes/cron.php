<?php

class PeepSoCron 
{
	public static function initialize()
	{
		if(0==PeepSo::get_option('disable_mailqueue', 0)) {
			add_action(PeepSo::CRON_MAILQUEUE, array('PeepSoMailQueue', 'process_mailqueue'));
		}
		
		if(PeepSo::get_option('rebuild_activity_rank') == 1) {
			add_action(PeepSo::CRON_MAINTENANCE_EVENT, array('PeepSoActivityRanking', 'rebuild_rank'), 10, 1);
		}

		if(0==PeepSo::get_option('gdpr_external_cron', 0) && (PeepSo::get_option('gdpr_enable', 1))) {
			add_action(PeepSo::CRON_GDPR_EXPORT_DATA, array('PeepSoGdpr', 'process_export_data'));
			add_action(PeepSo::CRON_GDPR_EXPORT_DATA, array('PeepSoGdpr', 'process_cleanup_data'));
		}

		if(1 == PeepSo::get_option('remove_inactive_users', 0) && 0==PeepSo::get_option('disable_remove_inactive_users_cron')) {
			add_action(PeepSo::CRON_REMOVE_INACTIVE_USERS, array('PeepSoInactiveUsers', 'process_remove_inactive_users'));
		}

		if(1==PeepSo::get_option('superq_enable', 0) && 0==PeepSo::get_option('disable_superq_cron')) {
			add_action(PeepSo::CRON_SUPERQ, array('PeepSoSuperQueue', 'process_queue'));
		}

		do_action('peepso_cron_init');
	}
}

// EOF