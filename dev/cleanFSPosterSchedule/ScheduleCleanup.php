<?php
/**
 * Schedule Cleanup Class
 *
 * Provides reusable functions for cleaning up old schedules
 * Now includes comprehensive logging support
 *
 * Usage:
 *   require_once 'dev/ScheduleCleanup.php';
 *   $cleanup = new ScheduleCleanup();
 *   $result = $cleanup->cleanupOldSchedules(300);
 */

use FSPoster\App\Models\Schedule;
use FSPoster\App\Providers\DB\DB;

class ScheduleCleanup
{
    private $logger = null;

    /**
     * Set logger instance
     *
     * @param CleanupLogger $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Clean up old schedules, keeping only the latest N records
     *
     * @param int $keep_latest Number of latest schedules to keep (default: 300)
     * @param bool $dry_run If true, only analyze without deleting (default: false)
     * @param bool $verbose If true, output detailed progress (default: true)
     * @return array Results array with statistics
     */
    public function cleanupOldSchedules($keep_latest = 300, $dry_run = false, $verbose = true)
    {
        $result = [
            'success' => false,
            'initial_count' => 0,
            'final_count' => 0,
            'deleted_count' => 0,
            'orphaned_posts_deleted' => 0,
            'dry_run' => $dry_run,
            'keep_latest' => $keep_latest,
            'errors' => []
        ];

        try {
            // Step 1: Count total schedules
            if ($this->logger) {
                $this->logger->section('COUNTING SCHEDULES');
            }

            $total_count = Schedule::count();
            $result['initial_count'] = $total_count;

            if ($verbose || $this->logger) {
                $msg = "Total Schedules: {$total_count}";
                if ($this->logger) {
                    $this->logger->info($msg);
                } else {
                    echo $msg . "\n";
                }
            }

            // Check if cleanup is needed
            if ($total_count <= $keep_latest) {
                $result['success'] = true;
                $result['final_count'] = $total_count;

                $msg = "Nothing to clean up (current: {$total_count}, limit: {$keep_latest})";
                if ($this->logger) {
                    $this->logger->success($msg);
                } elseif ($verbose) {
                    echo $msg . "\n";
                }

                return $result;
            }

            $to_delete_count = $total_count - $keep_latest;

            if ($verbose || $this->logger) {
                $msg = "To Delete: {$to_delete_count}";
                if ($this->logger) {
                    $this->logger->info($msg);
                } else {
                    echo $msg . "\n";
                }
            }

            // Step 2: Find threshold ID
            if ($this->logger) {
                $this->logger->section('FINDING THRESHOLD');
            }

            global $wpdb;
            $table = DB::table('schedules');

            $threshold_query = "
                SELECT id
                FROM {$table}
                ORDER BY id DESC
                LIMIT 1 OFFSET {$keep_latest}
            ";

            $threshold_id = $wpdb->get_var($threshold_query);

            if (!$threshold_id) {
                $result['errors'][] = 'Could not determine threshold ID';
                if ($this->logger) {
                    $this->logger->error('Could not determine threshold ID');
                }
                return $result;
            }

            if ($verbose || $this->logger) {
                $msg = "Threshold ID: {$threshold_id}";
                if ($this->logger) {
                    $this->logger->info($msg);
                    $this->logger->info("Will delete all schedules with ID < {$threshold_id}");
                } else {
                    echo $msg . "\n";
                }
            }

            // Step 3: Get schedules to delete
            if ($this->logger) {
                $this->logger->section('RETRIEVING SCHEDULES TO DELETE');
            }

            $schedules_to_delete = Schedule::where('id', '<', $threshold_id)
                                          ->where('status', '<>', 'sending')
                                          ->fetchAll();

            $actual_delete_count = count($schedules_to_delete);

            if ($actual_delete_count === 0) {
                $result['success'] = true;
                $result['final_count'] = $total_count;

                $msg = "No schedules to delete";
                if ($this->logger) {
                    $this->logger->info($msg);
                } elseif ($verbose) {
                    echo $msg . "\n";
                }

                return $result;
            }

            $msg = "Found {$actual_delete_count} schedules to delete (excluding 'sending' status)";
            if ($this->logger) {
                $this->logger->info($msg);
            } elseif ($verbose) {
                echo $msg . "\n";
            }

            // Step 4: Analyze
            if ($this->logger) {
                $this->logger->section('ANALYSIS');
            }

            $analysis = $this->analyzeSchedules($schedules_to_delete);
            $result['analysis'] = $analysis;

            if ($verbose || $this->logger) {
                if ($this->logger) {
                    $this->logger->info('By Status:');
                    foreach ($analysis['by_status'] as $status => $count) {
                        $this->logger->info("  - {$status}: {$count}");
                    }
                    $this->logger->info('Affected WordPress Posts: ' . count($analysis['wp_post_ids']));
                    $this->logger->info('Affected FSP Posts: ' . count($analysis['fsp_post_ids']));
                    $this->logger->info('NOTE: Posts will NOT be deleted, only schedule records');
                } else {
                    echo "By Status:\n";
                    foreach ($analysis['by_status'] as $status => $count) {
                        echo "  - {$status}: {$count}\n";
                    }
                }
            }

            // Step 5: Dry run or actual delete
            if ($dry_run) {
                $result['success'] = true;
                $result['final_count'] = $total_count;
                $result['would_delete'] = $actual_delete_count;

                if ($this->logger) {
                    $this->logger->section('DRY RUN - NO DELETIONS PERFORMED');
                    $this->logger->warning("Would delete {$actual_delete_count} schedules");
                    $this->logger->warning("Would check " . count($analysis['fsp_post_ids']) . " FSP posts for orphans");
                } elseif ($verbose) {
                    echo "DRY RUN - No deletions performed\n";
                }

                return $result;
            }

            // Step 6: Perform deletion
            if ($this->logger) {
                $this->logger->section('STARTING DELETION');
            }

            $deleted = $this->deleteSchedules($schedules_to_delete, $verbose);
            $result['deleted_count'] = $deleted;

            if ($this->logger) {
                $this->logger->success("Deleted {$deleted} schedule records");
            }

            // Step 7: Skip post cleanup (only clean schedules, not posts)
            // Posts may still be used elsewhere, so we don't delete them
            $result['orphaned_posts_deleted'] = 0;

            if ($this->logger) {
                $this->logger->info("Skipping post cleanup - only schedule records deleted");
            }

            // Step 8: Verify
            if ($this->logger) {
                $this->logger->section('VERIFICATION');
            }

            $final_count = Schedule::count();
            $result['final_count'] = $final_count;
            $result['success'] = true;

            if ($verbose || $this->logger) {
                if ($this->logger) {
                    $this->logger->info("Before: {$total_count}");
                    $this->logger->info("After: {$final_count}");
                    $this->logger->info("Deleted: {$deleted}");

                    if ($final_count <= $keep_latest) {
                        $this->logger->success("Schedule count is now within limit");
                    } else {
                        $this->logger->warning("Final count ({$final_count}) still exceeds target ({$keep_latest})");
                        $this->logger->info("This may be due to 'sending' status schedules being excluded");
                    }
                } else {
                    echo "Before: {$total_count}\n";
                    echo "After: {$final_count}\n";
                    echo "Deleted: {$deleted}\n";
                }
            }

        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();

            if ($this->logger) {
                $this->logger->error('Exception: ' . $e->getMessage());
                $this->logger->error('Trace: ' . $e->getTraceAsString());
            } elseif ($verbose) {
                echo "Error: " . $e->getMessage() . "\n";
            }
        }

        return $result;
    }

    /**
     * Analyze schedules to be deleted
     *
     * @param array $schedules
     * @return array Analysis results
     */
    private function analyzeSchedules($schedules)
    {
        $analysis = [
            'by_status' => [],
            'wp_post_ids' => [],
            'fsp_post_ids' => []
        ];

        foreach ($schedules as $schedule) {
            // Count by status
            $status = $schedule->status;
            $analysis['by_status'][$status] = ($analysis['by_status'][$status] ?? 0) + 1;

            // Track posts
            $wp_post_id = $schedule->wp_post_id;

            if (get_post_type($wp_post_id) === 'fsp_post') {
                $analysis['fsp_post_ids'][$wp_post_id] = true;
            } else {
                $analysis['wp_post_ids'][$wp_post_id] = true;
            }
        }

        return $analysis;
    }

    /**
     * Delete schedules
     *
     * @param array $schedules_to_delete
     * @param bool $verbose
     * @return int Number of schedules deleted
     */
    private function deleteSchedules($schedules_to_delete, $verbose = false)
    {
        $schedule_ids = [];

        // Update post meta for affected posts
        foreach ($schedules_to_delete as $schedule) {
            $schedule_ids[] = $schedule->id;

            $wp_post_id = $schedule->wp_post_id;

            // Mark as manually created to prevent auto-recreation
            if (get_post_type($wp_post_id) !== 'fsp_post') {
                update_post_meta($wp_post_id, 'fsp_schedule_created_manually', 1);
            }
        }

        // Delete in batches
        $batch_size = 100;
        $batches = array_chunk($schedule_ids, $batch_size);
        $deleted_count = 0;

        foreach ($batches as $batch_num => $batch) {
            Schedule::where('id', 'in', $batch)->delete();
            $deleted_count += count($batch);

            if ($verbose || $this->logger) {
                $current = $batch_num + 1;
                $total = count($batches);
                $msg = "Batch {$current}/{$total}: Deleted " . count($batch) . " schedules (Total: {$deleted_count})";

                if ($this->logger) {
                    $this->logger->info($msg);
                } else {
                    echo "  {$msg}\n";
                }
            }
        }

        return $deleted_count;
    }

    /**
     * Cleanup orphaned FSP calendar posts
     *
     * DEPRECATED: This method is no longer used.
     * Posts are NOT deleted even if they have no schedules,
     * as they may be used elsewhere.
     *
     * @param array $fsp_post_ids
     * @param bool $verbose
     * @return int Number of posts deleted (always 0)
     */
    private function cleanupOrphanedPosts($fsp_post_ids, $verbose = false)
    {
        // DISABLED: We only clean schedules, not posts
        // Posts may still be in use elsewhere
        return 0;
    }

    /**
     * Get schedule statistics
     *
     * @return array Statistics
     */
    public function getStatistics()
    {
        $total = Schedule::count();

        $stats = [
            'total' => $total,
            'by_status' => [
                'not_sent' => Schedule::where('status', 'not_sent')->count(),
                'sending' => Schedule::where('status', 'sending')->count(),
                'success' => Schedule::where('status', 'success')->count(),
                'error' => Schedule::where('status', 'error')->count(),
                'draft' => Schedule::where('status', 'draft')->count(),
            ]
        ];

        return $stats;
    }

    /**
     * Get oldest and newest schedule IDs
     *
     * @return array ['oldest' => int, 'newest' => int]
     */
    public function getScheduleRange()
    {
        global $wpdb;
        $table = DB::table('schedules');

        $query = "SELECT MIN(id) as oldest, MAX(id) as newest FROM {$table}";
        $result = $wpdb->get_row($query, ARRAY_A);

        return [
            'oldest' => (int)$result['oldest'],
            'newest' => (int)$result['newest']
        ];
    }

    /**
     * Delete schedules older than N days
     *
     * @param int $days Number of days to keep
     * @param bool $dry_run
     * @param bool $verbose
     * @return array Results
     */
    public function cleanupByAge($days = 30, $dry_run = false, $verbose = true)
    {
        $result = [
            'success' => false,
            'deleted_count' => 0,
            'dry_run' => $dry_run,
            'days' => $days,
            'errors' => []
        ];

        try {
            $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

            if ($verbose || $this->logger) {
                $msg = "Deleting schedules older than {$days} days (before {$cutoff_date})";

                if ($this->logger) {
                    $this->logger->section('CLEANUP BY AGE');
                    $this->logger->info($msg);
                } else {
                    echo $msg . "\n";
                }
            }

            $schedules_to_delete = Schedule::where('send_time', '<', $cutoff_date)
                                          ->where('status', '<>', 'sending')
                                          ->fetchAll();

            $count = count($schedules_to_delete);

            if ($count === 0) {
                $result['success'] = true;

                $msg = "No old schedules found";
                if ($this->logger) {
                    $this->logger->info($msg);
                } elseif ($verbose) {
                    echo $msg . "\n";
                }

                return $result;
            }

            if ($verbose || $this->logger) {
                $msg = "Found {$count} schedules to delete";

                if ($this->logger) {
                    $this->logger->info($msg);
                } else {
                    echo $msg . "\n";
                }
            }

            if ($dry_run) {
                $result['success'] = true;
                $result['would_delete'] = $count;
                return $result;
            }

            $deleted = $this->deleteSchedules($schedules_to_delete, $verbose);
            $result['deleted_count'] = $deleted;
            $result['success'] = true;

        } catch (\Exception $e) {
            $result['errors'][] = $e->getMessage();

            if ($this->logger) {
                $this->logger->error($e->getMessage());
            }
        }

        return $result;
    }
}
