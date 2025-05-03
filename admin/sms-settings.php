<?php
// admin/sms-settings.php

// Include necessary files and configurations
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/sms_functions.php';

// Check if admin is logged in
if (!isLoggedIn()) {
    header("Location: ../index.php");
    exit;
}

/**
 * Sanitize input data to prevent XSS and SQL injection
 * 
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    if ($conn) {
        $data = $conn->real_escape_string($data);
    }
    return $data;
}

// Initialize variables
$error = '';
$success = '';

// Get current SMS configuration
$config_query = "SELECT * FROM sms_config LIMIT 1";
$config_result = $conn->query($config_query);
$config = ($config_result && $config_result->num_rows > 0) ? $config_result->fetch_assoc() : null;

// If no configuration exists, create default
if (!$config) {
    createDefaultSmsConfig($conn);
    $config_result = $conn->query($config_query);
    $config = ($config_result && $config_result->num_rows > 0) ? $config_result->fetch_assoc() : null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update SMS configuration
    if (isset($_POST['update_config'])) {
        $api_provider = sanitizeInput($_POST['api_provider']);
        $api_endpoint = sanitizeInput($_POST['api_endpoint']);
        $api_key = sanitizeInput($_POST['api_key']);
        $sender_id = sanitizeInput($_POST['sender_id']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($api_provider) || empty($api_endpoint) || empty($api_key)) {
            $error = "Please fill in all required fields";
        } else {
            $update_query = "UPDATE sms_config SET 
                            api_provider = ?, 
                            api_endpoint = ?, 
                            api_key = ?, 
                            sender_id = ?, 
                            is_active = ?, 
                            updated_at = NOW() 
                            WHERE id = ?";
            
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssssii", $api_provider, $api_endpoint, $api_key, $sender_id, $is_active, $config['id']);
            
            if ($stmt->execute()) {
                $success = "SMS configuration updated successfully";
                // Check if logActivity function exists before calling it
                if (function_exists('logActivity')) {
                    logActivity($conn, $_SESSION['admin_id'], "Updated SMS configuration");
                }
                
                // Refresh configuration
                $config_result = $conn->query($config_query);
                $config = $config_result->fetch_assoc();
            } else {
                $error = "Error updating SMS configuration: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
    
    // Test SMS functionality
    if (isset($_POST['test_sms'])) {
        $test_phone = sanitizeInput($_POST['test_phone']);
        $test_message = sanitizeInput($_POST['test_message']);
        
        if (empty($test_phone)) {
            $error = "Test phone number is required";
        } elseif (empty($test_message)) {
            $error = "Test message is required";
        } else {
            // Send test SMS
            $result = sendSMS($test_phone, $test_message);
            
            if ($result['success']) {
                $success = "Test SMS sent successfully";
                // Check if logActivity function exists before calling it
                if (function_exists('logActivity')) {
                    logActivity($conn, $_SESSION['admin_id'], "Sent test SMS to {$test_phone}");
                }
                
                // Log the SMS
                $stmt = $conn->prepare("INSERT INTO sms_logs (phone_number, message, status) VALUES (?, ?, 'sent')");
                $stmt->bind_param("ss", $test_phone, $test_message);
                $stmt->execute();
                $stmt->close();
            } else {
                $error = "Failed to send test SMS: " . $result['message'];
            }
        }
    }
}

// Get SMS templates
$templates_query = "SELECT * FROM sms_templates ORDER BY template_name";
$templates_result = $conn->query($templates_query);
$templates = [];

if ($templates_result && $templates_result->num_rows > 0) {
    while ($template = $templates_result->fetch_assoc()) {
        $templates[] = $template;
    }
}

// Get recent SMS logs
$logs_query = "SELECT sl.*, CONCAT(gm.first_name, ' ', gm.last_name) as member_name 
              FROM sms_logs sl
              LEFT JOIN gym_members gm ON sl.member_id = gm.id
              ORDER BY sl.sent_at DESC
              LIMIT 10";
$logs_result = $conn->query($logs_query);
$logs = [];

if ($logs_result && $logs_result->num_rows > 0) {
    while ($log = $logs_result->fetch_assoc()) {
        $logs[] = $log;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Settings - Gym Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">SMS Settings</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="send-sms.php" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-send me-1"></i> Send SMS
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- SMS Configuration -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">SMS Configuration</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="api_provider" class="form-label">API Provider <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="api_provider" name="api_provider" value="<?php echo $config ? $config['api_provider'] : 'BIR SMS'; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="api_endpoint" class="form-label">API Endpoint <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="api_endpoint" name="api_endpoint" value="<?php echo $config ? $config['api_endpoint'] : API_URL; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="api_key" class="form-label">API Key <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="api_key" name="api_key" value="<?php echo $config ? $config['api_key'] : API_KEY; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="sender_id" class="form-label">Sender ID / Route ID</label>
                                    <input type="text" class="form-control" id="sender_id" name="sender_id" value="<?php echo $config ? $config['sender_id'] : ROUTE_ID; ?>">
                                </div>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo ($config && $config['is_active']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">Enable SMS Notifications</label>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" name="update_config" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Save Configuration
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Test SMS -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Test SMS</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="test_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="test_phone" name="test_phone" placeholder="Enter phone number" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="test_message" class="form-label">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="test_message" name="test_message" rows="3" required>This is a test message from Gym Management System.</textarea>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" name="test_sms" class="btn btn-success">
                                    <i class="bi bi-send me-1"></i> Send Test SMS
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- SMS Templates -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">SMS Templates</h5>
                        <a href="sms-templates.php" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i> Manage Templates
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Template Name</th>
                                        <th>Type</th>
                                        <th>Content</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($templates) > 0): ?>
                                        <?php foreach ($templates as $template): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($template['template_name']); ?></td>
                                                <td>
                                                    <span class="badge <?php 
                                                        echo $template['template_type'] === 'activation' ? 'bg-success' : 
                                                            ($template['template_type'] === 'renewal' ? 'bg-warning' : 'bg-info'); 
                                                    ?>">
                                                        <?php echo ucfirst($template['template_type']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars(substr($template['template_content'], 0, 50)) . '...'; ?></td>
                                                <td>
                                                    <span class="badge <?php echo $template['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo $template['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No templates found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Recent SMS Logs -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Recent SMS Logs</h5>
                        <a href="sms-logs.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-list-ul me-1"></i> View All Logs
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Date/Time</th>
                                        <th>Member</th>
                                        <th>Phone</th>
                                        <th>Message</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($logs) > 0): ?>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td><?php echo date('d-m-Y H:i', strtotime($log['sent_at'])); ?></td>
                                                <td><?php echo $log['member_name'] ? htmlspecialchars($log['member_name']) : 'N/A'; ?></td>
                                                <td><?php echo htmlspecialchars($log['phone_number']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($log['message'], 0, 30)) . '...'; ?></td>
                                                <td>
                                                    <span class="badge <?php 
                                                        echo $log['status'] === 'sent' ? 'bg-success' : 
                                                            ($log['status'] === 'pending' ? 'bg-warning' : 'bg-danger'); 
                                                    ?>">
                                                        <?php echo ucfirst($log['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No SMS logs found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>