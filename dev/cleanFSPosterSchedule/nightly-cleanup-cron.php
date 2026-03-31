<?php

/**
 * Nightly Schedule Cleanup Cron Job
 *
 * This script is designed to run every night via cron
 * It cleans up old schedules and logs all details to daily log files
 *
 * Setup Cron Job (Linux/Mac):
 *   # Run every night at 2:00 AM
 *   0 2 * * * /usr/bin/php /path/to/fs-poster/dev/nightly-cleanup-cron.php >> /dev/null 2>&1
 *
 * Setup Cron Job (Windows Task Scheduler):
 *   Program: C:\php\php.exe
 *   Arguments: C:\path\to\fs-poster\dev\nightly-cleanup-cron.php
 *   Trigger: Daily at 2:00 AM
 *
 * Or via WordPress Cron:
 *   See setup instructions at the bottom of this file
 */

function NightlyFSPosterScheduleCleanUp()
{

    // Load WordPress
    require_once(dirname(dirname(__DIR__)) . '/wp-load.php');

    // Load cleanup classes
    require_once(__DIR__ . '/CleanupLogger.php');
    require_once(__DIR__ . '/ScheduleCleanup.php');

    // ============================================
    // CONFIGURATION
    // ============================================

    // Number of schedules to keep
    $keep_latest = 300;

    // Dry run mode (set to false for actual deletion)
    $dry_run = false;

    // Keep log files for N days (older logs will be deleted)
    // $keep_logs_days = 30;

    // ============================================
    // INITIALIZE LOGGER
    // ============================================

    try {
        // Create logger (console_output = false for cron, true for testing)
        $logger = new CleanupLogger(false);

        $logger->info('Nightly cleanup cron job started');
        $logger->info('Configuration:');
        $logger->info("  - Keep Latest: {$keep_latest} schedules");
        $logger->info("  - Dry Run: " . ($dry_run ? 'Yes' : 'No'));
        // $logger->info("  - Log Retention: {$keep_logs_days} days");

        // ============================================
        // GET PRE-CLEANUP STATISTICS
        // ============================================

        $cleanup = new ScheduleCleanup();
        $cleanup->setLogger($logger);

        $logger->section('PRE-CLEANUP STATISTICS');

        $pre_stats = $cleanup->getStatistics();
        $range = $cleanup->getScheduleRange();

        $logger->logStats([
            'Total Schedules' => $pre_stats['total'],
            'Not Sent' => $pre_stats['by_status']['not_sent'],
            'Sending' => $pre_stats['by_status']['sending'],
            'Success' => $pre_stats['by_status']['success'],
            'Error' => $pre_stats['by_status']['error'],
            'Draft' => $pre_stats['by_status']['draft'],
            'Oldest Schedule ID' => $range['oldest'],
            'Newest Schedule ID' => $range['newest']
        ]);

        // ============================================
        // PERFORM CLEANUP
        // ============================================

        $result = $cleanup->cleanupOldSchedules($keep_latest, $dry_run, false);

        // ============================================
        // LOG SUMMARY
        // ============================================

        $logger->logSummary($result);

        // ============================================
        // GET POST-CLEANUP STATISTICS
        // ============================================

        $logger->section('POST-CLEANUP STATISTICS');

        $post_stats = $cleanup->getStatistics();

        $logger->logStats([
            'Total Schedules' => $post_stats['total'],
            'Not Sent' => $post_stats['by_status']['not_sent'],
            'Sending' => $post_stats['by_status']['sending'],
            'Success' => $post_stats['by_status']['success'],
            'Error' => $post_stats['by_status']['error'],
            'Draft' => $post_stats['by_status']['draft']
        ]);

        // ============================================
        // CLEANUP OLD LOG FILES
        // ============================================

        // $logger->section('LOG FILE MAINTENANCE');

        // $deleted_logs = $logger->cleanOldLogs($keep_logs_days);

        // if ($deleted_logs > 0) {
        //     $logger->info("Deleted {$deleted_logs} old log files");
        // } else {
        //     $logger->info("No old log files to delete");
        // }

        // $all_logs = $logger->getAllLogFiles();
        // $logger->info("Current log files: " . count($all_logs));
        // $logger->info("Current log size: " . $logger->getLogSize());

        // ============================================
        // COMPLETION
        // ============================================

        if ($result['success']) {
            $logger->success('Nightly cleanup completed successfully');
        } else {
            $logger->error('Nightly cleanup completed with errors');
        }

        $logger->info('Log file: ' . $logger->getLogFile());
    } catch (\Exception $e) {
        // If logger failed, fall back to error_log
        if (isset($logger)) {
            $logger->error('FATAL ERROR: ' . $e->getMessage());
            $logger->error('Trace: ' . $e->getTraceAsString());
        } else {
            error_log('[FS Poster Cleanup] FATAL ERROR: ' . $e->getMessage());
        }

        exit(1);
    }
}

/*
 * ============================================
 * WORDPRESS CRON SETUP INSTRUCTIONS
 * ============================================
 *
 * Add to your theme's functions.php or a custom plugin:

// Schedule the nightly cleanup
add_action('wp', function() {
    if (!wp_next_scheduled('fsp_nightly_cleanup')) {
        // Schedule for 2:00 AM every night
        wp_schedule_event(strtotime('tomorrow 2:00 AM'), 'daily', 'fsp_nightly_cleanup');
    }
});

// Handle the cleanup
add_action('fsp_nightly_cleanup', function() {
    $script = WP_CONTENT_DIR . '/plugins/fs-poster/dev/nightly-cleanup-cron.php';

    if (file_exists($script)) {
        include $script;
    } else {
        error_log('[FS Poster] Cleanup script not found: ' . $script);
    }
});

 *
 * To manually trigger (for testing):
 *   do_action('fsp_nightly_cleanup');
 *
 * To unschedule:
 *   $timestamp = wp_next_scheduled('fsp_nightly_cleanup');
 *   wp_unschedule_event($timestamp, 'fsp_nightly_cleanup');
 *
 * ============================================
 */