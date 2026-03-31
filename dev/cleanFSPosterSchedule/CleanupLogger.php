<?php
/**
 * Cleanup Logger Class
 *
 * Handles logging for schedule cleanup operations
 * Creates daily log files in dev/logs/ directory
 */

class CleanupLogger
{
    private $log_dir;
    private $log_file;
    private $file_handle;
    private $start_time;
    private $console_output;

    /**
     * Constructor
     *
     * @param bool $console_output Whether to also output to console (default: true)
     */
    public function __construct($console_output = true)
    {
        $this->log_dir = __DIR__ . '/logs';
        $this->console_output = $console_output;
        $this->start_time = microtime(true);

        // Create logs directory if it doesn't exist
        if (!file_exists($this->log_dir)) {
            mkdir($this->log_dir, 0755, true);
        }

        // Set log file path (daily files)
        $date = date('Y-m-d');
        $this->log_file = $this->log_dir . '/cleanup-' . $date . '.log';

        // Open file handle
        $this->file_handle = fopen($this->log_file, 'a');

        if (!$this->file_handle) {
            throw new Exception("Could not open log file: {$this->log_file}");
        }

        // Write session start
        $this->writeHeader();
    }

    /**
     * Write log header
     */
    private function writeHeader()
    {
        $this->writeLine(str_repeat('=', 80));
        $this->writeLine('SCHEDULE CLEANUP - ' . date('Y-m-d H:i:s'));
        $this->writeLine(str_repeat('=', 80));
        $this->writeLine('');
    }

    /**
     * Write a line to log
     *
     * @param string $message
     * @param string $level Log level (INFO, WARNING, ERROR, SUCCESS)
     */
    public function log($message, $level = 'INFO')
    {
        $timestamp = date('H:i:s');
        $formatted = "[{$timestamp}] [{$level}] {$message}";

        $this->writeLine($formatted);
    }

    /**
     * Write line to both file and console
     *
     * @param string $line
     */
    private function writeLine($line)
    {
        // Write to file
        if ($this->file_handle) {
            fwrite($this->file_handle, $line . "\n");
        }

        // Write to console if enabled
        if ($this->console_output) {
            echo $line . "\n";
        }
    }

    /**
     * Log info message
     *
     * @param string $message
     */
    public function info($message)
    {
        $this->log($message, 'INFO');
    }

    /**
     * Log success message
     *
     * @param string $message
     */
    public function success($message)
    {
        $this->log($message, 'SUCCESS');
    }

    /**
     * Log warning message
     *
     * @param string $message
     */
    public function warning($message)
    {
        $this->log($message, 'WARNING');
    }

    /**
     * Log error message
     *
     * @param string $message
     */
    public function error($message)
    {
        $this->log($message, 'ERROR');
    }

    /**
     * Log section header
     *
     * @param string $title
     */
    public function section($title)
    {
        $this->writeLine('');
        $this->writeLine(str_repeat('-', 80));
        $this->writeLine($title);
        $this->writeLine(str_repeat('-', 80));
    }

    /**
     * Log statistics in table format
     *
     * @param array $stats Key-value pairs
     */
    public function logStats($stats)
    {
        foreach ($stats as $key => $value) {
            $this->writeLine(sprintf("  %-30s : %s", $key, $value));
        }
    }

    /**
     * Log array data
     *
     * @param array $data
     * @param string $title Optional title
     */
    public function logArray($data, $title = null)
    {
        if ($title) {
            $this->info($title);
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->writeLine("  {$key}:");
                foreach ($value as $k => $v) {
                    $this->writeLine("    - {$k}: {$v}");
                }
            } else {
                $this->writeLine("  - {$key}: {$value}");
            }
        }
    }

    /**
     * Log execution summary
     *
     * @param array $result Cleanup result array
     */
    public function logSummary($result)
    {
        $this->section('EXECUTION SUMMARY');

        $elapsed = round(microtime(true) - $this->start_time, 2);

        $summary = [
            'Status' => $result['success'] ? '✅ SUCCESS' : '❌ FAILED',
            'Mode' => $result['dry_run'] ? 'DRY RUN (No Deletions)' : 'LIVE (Actual Deletions)',
            'Keep Limit' => $result['keep_latest'],
            'Initial Count' => $result['initial_count'],
            'Final Count' => $result['final_count'],
            'Deleted Count' => $result['deleted_count'],
            'Orphaned Posts Removed' => $result['orphaned_posts_deleted'] ?? 0,
            'Execution Time' => "{$elapsed} seconds",
        ];

        $this->logStats($summary);

        // Log analysis if available
        if (isset($result['analysis'])) {
            $this->writeLine('');
            $this->info('Deletion Analysis:');

            if (isset($result['analysis']['by_status'])) {
                $this->writeLine('  By Status:');
                foreach ($result['analysis']['by_status'] as $status => $count) {
                    $this->writeLine("    - {$status}: {$count}");
                }
            }

            if (isset($result['analysis']['wp_post_ids'])) {
                $wp_count = count($result['analysis']['wp_post_ids']);
                $this->writeLine("  Affected WordPress Posts: {$wp_count}");
            }

            if (isset($result['analysis']['fsp_post_ids'])) {
                $fsp_count = count($result['analysis']['fsp_post_ids']);
                $this->writeLine("  Affected FSP Posts: {$fsp_count}");
            }
        }

        // Log errors if any
        if (!empty($result['errors'])) {
            $this->writeLine('');
            $this->error('Errors Encountered:');
            foreach ($result['errors'] as $error) {
                $this->writeLine("  - {$error}");
            }
        }

        $this->writeLine('');
        $this->writeLine(str_repeat('=', 80));
        $this->writeLine('');
    }

    /**
     * Close log file
     */
    public function close()
    {
        if ($this->file_handle) {
            fclose($this->file_handle);
            $this->file_handle = null;
        }
    }

    /**
     * Get log file path
     *
     * @return string
     */
    public function getLogFile()
    {
        return $this->log_file;
    }

    /**
     * Get all log files
     *
     * @return array
     */
    public function getAllLogFiles()
    {
        $files = glob($this->log_dir . '/cleanup-*.log');
        rsort($files); // Most recent first
        return $files;
    }

    /**
     * Get log file size
     *
     * @return string Formatted size
     */
    public function getLogSize()
    {
        if (file_exists($this->log_file)) {
            $bytes = filesize($this->log_file);
            return $this->formatBytes($bytes);
        }
        return '0 B';
    }

    /**
     * Format bytes to human-readable size
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Clean up old log files (keep last N days)
     *
     * @param int $keep_days Number of days to keep (default: 30)
     * @return int Number of files deleted
     */
    public function cleanOldLogs($keep_days = 30)
    {
        $cutoff_date = date('Y-m-d', strtotime("-{$keep_days} days"));
        $files = glob($this->log_dir . '/cleanup-*.log');
        $deleted = 0;

        foreach ($files as $file) {
            // Extract date from filename: cleanup-2026-01-15.log
            if (preg_match('/cleanup-(\d{4}-\d{2}-\d{2})\.log$/', $file, $matches)) {
                $file_date = $matches[1];

                if ($file_date < $cutoff_date) {
                    if (unlink($file)) {
                        $deleted++;
                        $this->info("Deleted old log: " . basename($file));
                    }
                }
            }
        }

        return $deleted;
    }

    /**
     * Destructor - ensure file is closed
     */
    public function __destruct()
    {
        $this->close();
    }
}
