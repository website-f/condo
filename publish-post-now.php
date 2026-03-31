<?php

/**
 * FS Poster Schedule Creator API
 *
 * Simple, secure endpoint to create schedules with default settings
 *
 * Endpoint: /publish-post-now.php
 * Method: POST
 * Headers: X-API-Key: your-secret-key
 * Body: { "post_id": 123 }
 *
 * What it does:
 * 1. Validates post exists and is publishable
 * 2. Creates schedules for all active channels (no duplicate check)
 * 3. Uses default schedule settings (time, channels, etc.)
 * 4. Returns group_id and schedule count
 * 5. Actual publishing happens via WordPress cron
 */

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// JSON response header
header('Content-Type: application/json');

// Load WordPress
require_once('wp-load.php');

use FSPoster\App\Providers\Schedules\ScheduleService;
use FSPoster\App\Models\Schedule;

// ============================================
// SECURITY CONFIGURATION
// ============================================

/**
 * IMPORTANT: Generate a strong API key and replace this!
 *
 * Generate command: openssl rand -hex 32
 * Or use: https://www.random.org/strings/
 */
define('FSP_API_KEY', 'your-secret-api-key-change-this-immediately');

/**
 * Optional: IP Whitelist (empty = allow all)
 */
$allowed_ips = [
    // '192.168.1.100',
    // '10.0.0.50',
];
$valid_api_keys = ['charles-test-ipp-auto-send-001'];

/**
 * Optional: Enable request logging
 */
define('ENABLE_LOGGING', false);

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Send JSON response and exit
 */
function sendResponse($success, $message, $data = [], $http_code = 200)
{
    http_response_code($http_code);

    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => current_time('mysql')
    ];

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    // Log response if enabled
    if (ENABLE_LOGGING) {
        error_log('[FS Poster Schedule] Response: ' . json_encode([
            'success' => $success,
            'message' => $message,
            'post_id' => $data['post_id'] ?? null
        ]));
    }

    exit;
}

/**
 * Get client IP address
 */
function getClientIP()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

/**
 * Log request details
 */
function logRequest($post_id = null)
{
    if (!ENABLE_LOGGING) return;

    error_log(sprintf(
        '[FS Poster Schedule] Request from IP: %s, Post ID: %s, Time: %s',
        getClientIP(),
        $post_id ?? 'not provided',
        current_time('mysql')
    ));
}

// ============================================
// SECURITY CHECKS
// ============================================

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed. Only POST requests accepted.', [
        'received_method' => $_SERVER['REQUEST_METHOD']
    ], 405);
}

// Check IP whitelist
if (!empty($allowed_ips)) {
    $client_ip = getClientIP();
    if (!in_array($client_ip, $allowed_ips)) {
        error_log('[FS Poster Schedule] BLOCKED IP: ' . $client_ip);
        sendResponse(false, 'Access forbidden: IP address not allowed', [
            'your_ip' => $client_ip
        ], 403);
    }
}


$provided_api_key = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (!in_array($provided_api_key, $valid_api_keys)) {
    sendResponse(false, 'Unauthorized: Invalid or missing API key', [], 401);
}


// ============================================
// GET REQUEST DATA
// ============================================

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Fallback to form-data if JSON parsing fails
if (json_last_error() !== JSON_ERROR_NONE) {
    $data = $_POST;
}

if (empty($data)) {
    sendResponse(false, 'No data received in request body', [
        'hint' => 'Send JSON: {"post_id": 123}',
        'json_error' => json_last_error_msg()
    ], 400);
}

// Get post_id
if (!isset($data['post_id'])) {
    sendResponse(false, 'Missing required parameter: post_id', [
        'hint' => 'Send JSON: {"post_id": 123}',
        'received_data' => array_keys($data)
    ], 400);
}

$post_id = $data['post_id'];

// Log request
logRequest($post_id);

// ============================================
// VALIDATE POST ID
// ============================================

if (!is_numeric($post_id) || $post_id <= 0) {
    sendResponse(false, 'Invalid post_id format', [
        'post_id' => $post_id,
        'hint' => 'post_id must be a positive integer'
    ], 400);
}

$post_id = (int) $post_id;

// ============================================
// CHECK POST EXISTS
// ============================================

$post = get_post($post_id);

if (!$post) {
    sendResponse(false, 'Post not found', [
        'post_id' => $post_id
    ], 404);
}

// ============================================
// VALIDATE POST STATUS
// ============================================

$allowed_statuses = ['publish'];

if (!in_array($post->post_status, $allowed_statuses)) {
    sendResponse(false, 'Post status not allowed for scheduling', [
        'post_id' => $post_id,
        'post_status' => $post->post_status,
        'allowed_statuses' => $allowed_statuses
    ], 400);
}

// ============================================
// VALIDATE POST TYPE
// ============================================

$allowed_post_types = ['post', 'page', 'attachment', 'product'];

if (!in_array($post->post_type, $allowed_post_types)) {
    sendResponse(false, 'Post type not enabled in FS Poster settings', [
        'post_id' => $post_id,
        'post_type' => $post->post_type,
        'allowed_post_types' => $allowed_post_types,
        'hint' => 'Enable this post type in FS Poster > Settings > Auto-Share'
    ], 400);
}

// ============================================
// CREATE SCHEDULES
// ============================================

try {
    // Create schedules with default settings
    // - Generates new group_id
    // - Uses all active channels
    // - Calculates send_time automatically
    // - No duplicate check (creates fresh schedules)
    $group_id = ScheduleService::createSchedulesFromWpPost(
        $post_id,
        '',         // Empty = generate new group_id
        [],         // Empty = use all active channels with default settings
        null        // null = calculate send_time automatically
    );

    if (!$group_id) {
        sendResponse(false, 'Failed to create schedules', [
            'post_id' => $post_id,
            'error' => 'ScheduleService returned empty group_id'
        ], 500);
    }

    // ============================================
    // UPDATE POST META
    // ============================================

    update_post_meta($post_id, 'fsp_schedule_group_id', $group_id);
    update_post_meta($post_id, 'fsp_enable_auto_share', 1);
    update_post_meta($post_id, 'fsp_schedule_created_manually', 1);

    // ============================================
    // VERIFY SCHEDULES CREATED
    // ============================================

    $created_schedules = Schedule::where('group_id', $group_id)
        ->where('wp_post_id', $post_id)
        ->fetchAll();

    if (empty($created_schedules)) {
        sendResponse(false, 'Schedules created but not found in database', [
            'post_id' => $post_id,
            'group_id' => $group_id,
            'error' => 'Database verification failed'
        ], 500);
    }

    // ============================================
    // BUILD SUCCESS RESPONSE
    // ============================================

    $response_data = [
        'post_id' => $post_id,
        'post_title' => $post->post_title,
        'post_url' => get_permalink($post_id),
        'post_status' => $post->post_status,
        'post_type' => $post->post_type,
        'group_id' => $group_id,
        'schedules_created' => count($created_schedules),
        'post_meta' => [
            'fsp_schedule_group_id' => $group_id,
            'fsp_enable_auto_share' => '1',
            'fsp_schedule_created_manually' => '1'
        ],
        'note' => 'Schedules created successfully. Posts will be published by WordPress cron job.'
    ];

    if (ENABLE_LOGGING) {
        error_log(sprintf(
            '[FS Poster Schedule] Created %d schedules for Post ID: %d, Group ID: %s',
            count($created_schedules),
            $post_id,
            $group_id
        ));
    }

    sendResponse(true, 'Schedules created successfully', $response_data, 200);
} catch (\Exception $e) {
    error_log('[FS Poster Schedule] EXCEPTION: ' . $e->getMessage());
    error_log('[FS Poster Schedule] TRACE: ' . $e->getTraceAsString());

    sendResponse(false, 'Exception occurred during schedule creation', [
        'post_id' => $post_id,
        'error' => $e->getMessage(),
        'trace' => explode("\n", $e->getTraceAsString())
    ], 500);
}