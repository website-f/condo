# Schedule Cleanup Scripts

Tools for cleaning up old FS Poster schedules to maintain database performance.

---

## Files

1. **`cleanup-old-schedules.php`** - Standalone script for one-time cleanup
2. **`ScheduleCleanup.php`** - Reusable class with multiple cleanup methods
3. **`example-cleanup-usage.php`** - Usage examples

---

## Quick Start

### Option 1: Simple Script (One-Time Cleanup)

```bash
# Dry run first (no deletions)
php dev/cleanup-old-schedules.php

# Edit the file and set: $dry_run = false;
# Then run again to actually delete
php dev/cleanup-old-schedules.php

# Keep 500 instead of 300
php dev/cleanup-old-schedules.php 500
```

### Option 2: Reusable Class (For Integration)

```php
<?php
require_once 'dev/ScheduleCleanup.php';

$cleanup = new ScheduleCleanup();

// Dry run first
$result = $cleanup->cleanupOldSchedules(300, true, true);

// If looks good, actually delete
$result = $cleanup->cleanupOldSchedules(300, false, true);

if ($result['success']) {
    echo "Deleted: {$result['deleted_count']} schedules\n";
}
?>
```

---

## Features

### 1. Keep Latest N Records
Deletes old schedules, keeping only the newest N records.

```php
// Keep latest 300
$cleanup->cleanupOldSchedules(300);

// Keep latest 500
$cleanup->cleanupOldSchedules(500);

// Keep latest 1000
$cleanup->cleanupOldSchedules(1000);
```

### 2. Delete by Age
Deletes schedules older than N days.

```php
// Delete schedules older than 30 days
$cleanup->cleanupByAge(30);

// Delete schedules older than 90 days
$cleanup->cleanupByAge(90);
```

### 3. Dry Run Mode
Preview what will be deleted without actually deleting.

```php
// Second parameter = dry_run (true = preview only)
$result = $cleanup->cleanupOldSchedules(300, true);

echo "Would delete: " . $result['would_delete'] . " schedules\n";
```

### 4. Verbose/Silent Mode
Control output verbosity.

```php
// Verbose (shows progress)
$cleanup->cleanupOldSchedules(300, false, true);

// Silent (no output)
$cleanup->cleanupOldSchedules(300, false, false);
```

### 5. Statistics
Get database statistics.

```php
$stats = $cleanup->getStatistics();

echo "Total: {$stats['total']}\n";
echo "Success: {$stats['by_status']['success']}\n";
echo "Error: {$stats['by_status']['error']}\n";
```

---

## How It Works

### Step-by-Step Process

1. **Count Total Schedules**
   - Gets current count from database
   - If count <= limit, exits (nothing to delete)

2. **Find Threshold ID**
   - Determines the ID cutoff point
   - Schedules with ID < threshold will be deleted

3. **Get Schedules to Delete**
   - Queries schedules where `id < threshold`
   - Excludes `status = 'sending'` (in-progress)

4. **Analyze**
   - Groups by status
   - Identifies affected WordPress posts
   - Identifies FSP calendar posts

5. **Update Post Meta**
   - Sets `fsp_schedule_created_manually = 1`
   - Prevents auto-recreation of deleted schedules

6. **Delete Schedules**
   - Deletes in batches of 100 for performance
   - Uses FS Poster's Model::delete() method

7. **Verify**
   - Counts final schedule total
   - Returns detailed results

---

## Safety Features

### 1. Excludes In-Progress Schedules
Never deletes schedules with `status = 'sending'`

```php
->where('status', '<>', 'sending')
```

### 2. Dry Run by Default
The standalone script defaults to dry run mode.

### 3. Batch Processing
Deletes in batches of 100 to prevent memory issues.

### 4. Post Meta Protection
Updates post meta to prevent auto-recreation.

### 5. Posts Are NOT Deleted
WordPress posts (including FSP calendar posts) are never deleted - only schedule records are removed.

---

## Return Value Structure

```php
[
    'success' => true,              // Whether cleanup succeeded
    'initial_count' => 1500,        // Schedules before cleanup
    'final_count' => 300,           // Schedules after cleanup
    'deleted_count' => 1200,        // Number deleted
    'orphaned_posts_deleted' => 0,  // Always 0 (posts not deleted)
    'dry_run' => false,             // Whether this was a dry run
    'keep_latest' => 300,           // Configured limit
    'errors' => [],                 // Any errors encountered
    'analysis' => [                 // Detailed analysis
        'by_status' => [
            'success' => 800,
            'error' => 200,
            'not_sent' => 200
        ],
        'wp_post_ids' => [...],     // Affected post IDs
        'fsp_post_ids' => [...]     // Affected calendar posts
    ]
]
```

---

## Use Cases

### Use Case 1: Regular Maintenance
Keep database size under control.

```php
// Run weekly via cron
$cleanup = new ScheduleCleanup();
$result = $cleanup->cleanupOldSchedules(300, false, false);
```

### Use Case 2: Emergency Cleanup
Database too large, needs immediate cleanup.

```bash
php dev/cleanup-old-schedules.php
# Edit: $dry_run = false
php dev/cleanup-old-schedules.php
```

### Use Case 3: Age-Based Retention
Keep schedules for audit/history purposes.

```php
// Keep last 90 days
$cleanup->cleanupByAge(90, false, true);
```

### Use Case 4: Custom Integration
Integrate into your own scripts.

```php
require_once 'dev/ScheduleCleanup.php';

function maintainScheduleDatabase() {
    $cleanup = new ScheduleCleanup();

    // Check current count
    $stats = $cleanup->getStatistics();

    if ($stats['total'] > 500) {
        // Cleanup needed
        $result = $cleanup->cleanupOldSchedules(300, false, false);

        // Log results
        error_log("Cleaned up {$result['deleted_count']} schedules");
    }
}
```

---

## WordPress Cron Integration

Add to your theme's `functions.php` or a plugin:

```php
// Schedule weekly cleanup
add_action('wp', function() {
    if (!wp_next_scheduled('fsp_schedule_cleanup')) {
        wp_schedule_event(time(), 'weekly', 'fsp_schedule_cleanup');
    }
});

// Handle cleanup
add_action('fsp_schedule_cleanup', function() {
    require_once WP_CONTENT_DIR . '/plugins/fs-poster/dev/ScheduleCleanup.php';

    $cleanup = new ScheduleCleanup();
    $result = $cleanup->cleanupOldSchedules(300, false, false);

    error_log('FSP Cleanup: Deleted ' . $result['deleted_count'] . ' schedules');
});
```

---

## Command Line Usage

### Basic Usage
```bash
# Keep latest 300 (default)
php dev/cleanup-old-schedules.php

# Keep latest 500
php dev/cleanup-old-schedules.php 500

# Keep latest 1000
php dev/cleanup-old-schedules.php 1000
```

### With Output Redirection
```bash
# Save output to log file
php dev/cleanup-old-schedules.php > cleanup-log.txt 2>&1

# Append to existing log
php dev/cleanup-old-schedules.php >> cleanup-log.txt 2>&1
```

### Silent Execution
```bash
# Only show errors
php dev/cleanup-old-schedules.php 2>&1 | grep -i error
```

---

## Performance Considerations

### Database Size Impact

| Schedules | Table Size (est.) | Cleanup Time |
|-----------|-------------------|--------------|
| 1,000     | ~100 KB          | < 1 second   |
| 10,000    | ~1 MB            | 1-2 seconds  |
| 100,000   | ~10 MB           | 5-10 seconds |
| 1,000,000 | ~100 MB          | 30-60 seconds|

### Batch Size
Default batch size is 100 for optimal balance.

Adjust in `ScheduleCleanup.php`:
```php
$batch_size = 100;  // Change if needed
```

### Memory Usage
- Batch processing keeps memory usage low
- Typical usage: < 10MB RAM
- Large cleanups (100k+ records): < 50MB RAM

---

## Troubleshooting

### Problem: "Could not determine threshold ID"

**Cause:** Database query failed or no schedules exist

**Solution:**
```php
$stats = $cleanup->getStatistics();
print_r($stats);  // Check if schedules exist
```

### Problem: Final count still exceeds limit

**Cause:** Many schedules have `status = 'sending'`

**Solution:** Wait for them to finish, then run cleanup again

### Problem: Script times out

**Cause:** Too many schedules to delete

**Solution:** Increase PHP timeout
```php
set_time_limit(300);  // 5 minutes
```

Or run multiple times with smaller batches:
```php
$cleanup->cleanupOldSchedules(500);  // First run
$cleanup->cleanupOldSchedules(400);  // Second run
$cleanup->cleanupOldSchedules(300);  // Final run
```

---

## Best Practices

1. ✅ **Always dry run first**
   ```php
   $result = $cleanup->cleanupOldSchedules(300, true);
   ```

2. ✅ **Check statistics before cleanup**
   ```php
   $stats = $cleanup->getStatistics();
   ```

3. ✅ **Keep reasonable limits**
   - Min: 100 (for active sites)
   - Recommended: 300-500
   - Max: 1000+

4. ✅ **Schedule regular maintenance**
   - Weekly for high-volume sites
   - Monthly for low-volume sites

5. ✅ **Monitor results**
   ```php
   if (!$result['success']) {
       error_log('Cleanup failed: ' . print_r($result['errors'], true));
   }
   ```

6. ✅ **Backup before large cleanups**
   ```bash
   # Backup schedules table
   mysqldump -u user -p database wp_fsp_schedules > backup.sql
   ```

---

## FAQ

**Q: Will this delete scheduled posts that haven't been published yet?**
A: No, it excludes schedules with `status = 'sending'`

**Q: What happens to WordPress posts after their schedules are deleted?**
A: Nothing. Posts are NEVER deleted - only schedule records are removed. Your original WordPress posts and FSP calendar posts remain intact.

**Q: What are FSP posts?**
A: Calendar posts created directly in FS Poster (not regular WordPress posts). These are also NOT deleted by the cleanup script.

**Q: Can I undo a cleanup?**
A: No, deletions are permanent. Always use dry run first!

**Q: How often should I run cleanup?**
A: Depends on volume. Weekly for high-volume, monthly for low-volume.

**Q: Will this affect scheduled future posts?**
A: Yes, if they're in the oldest records. Use age-based cleanup instead:
```php
$cleanup->cleanupByAge(90);  // Keep last 90 days
```

---

## Summary

- ✅ Two methods: standalone script or reusable class
- ✅ Keeps latest N records or deletes by age
- ✅ Dry run mode for safety
- ✅ Batch processing for performance
- ✅ **Only deletes schedule records - posts are NEVER deleted**
- ✅ Detailed results and statistics
- ✅ WordPress cron integration ready

**Recommended usage:**
```php
require_once 'dev/ScheduleCleanup.php';
$cleanup = new ScheduleCleanup();
$result = $cleanup->cleanupOldSchedules(300, false, true);
```
