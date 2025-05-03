<?php
/**
 * SMS Functions for Gym Management System
 * This file contains all functions related to SMS functionality
 */

// Define constants if not already defined
if (!defined('API_KEY')) {
    define('API_KEY', '3B853539856F3FD36823E959EF82ABF6'); // Your BIR SMS API key
}
if (!defined('API_URL')) {
    define('API_URL', 'https://user.birasms.com/api/smsapi');
}
if (!defined('ROUTE_ID')) {
    define('ROUTE_ID', 'SI_Alert');
}

/**
 * Send SMS using BIR SMS API
 * 
 * @param string $phoneNumber Phone number to send SMS to
 * @param string $message Message content
 * @return array Result with success status and message
 */
function sendSMS($phoneNumber, $message) {
    // Clean phone number (remove spaces, dashes, etc.)
    $phoneNumber = preg_replace('/\s+/', '', $phoneNumber);
    
    // Prepare POST parameters
    $postData = [
        'key' => API_KEY,
        'campaign' => 'Default',
        'routeid' => ROUTE_ID,
        'type' => 'text',
        'contacts' => $phoneNumber,
        'msg' => $message,
        'responsetype' => 'json'
    ];
    
    // Initialize cURL session
    $ch = curl_init(API_URL);
    
    // Set cURL options for POST request
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Execute cURL session and get response
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        $error = 'cURL Error: ' . curl_error($ch);
        curl_close($ch);
        return ['success' => false, 'message' => $error];
    }
    
    // Close cURL session
    curl_close($ch);
    
    // Process response
    $result = json_decode($response, true);
    
    // Check if the API call was successful
    if (isset($result['status']) && $result['status'] == 'success') {
        return ['success' => true, 'message' => 'SMS sent successfully', 'data' => $result];
    } else {
        $errorMsg = isset($result['message']) ? $result['message'] : 'Unknown error occurred';
        return ['success' => false, 'message' => $errorMsg, 'data' => $result];
    }
}

/**
 * Send welcome SMS to new member
 * 
 * @param object $conn Database connection
 * @param int $memberId Member ID
 * @param string $phone Member's phone number
 * @param string $memberName Member's full name
 * @param string $planName Membership plan name
 * @param string $expiryDate Membership expiry date
 * @return array Result with success status and message
 */
function sendWelcomeSMS($conn, $memberId, $phone, $memberName, $planName, $expiryDate) {
    // Get welcome message template
    $template_query = "SELECT * FROM sms_templates WHERE template_type = 'activation' AND is_active = 1 LIMIT 1";
    $template_result = $conn->query($template_query);
    
    if ($template_result && $template_result->num_rows > 0) {
        $template = $template_result->fetch_assoc();
        $message_template = $template['template_content'];
        
        // Format expiry date
        $formatted_expiry = date('d-m-Y', strtotime($expiryDate));
        
        // Replace placeholders
        $message = str_replace(
            ['{member_name}', '{plan_name}', '{expiry_date}'],
            [$memberName, $planName, $formatted_expiry],
            $message_template
        );
    } else {
        // Default welcome message if no template found
        $message = "Welcome to our gym, $memberName! Your $planName membership is active until " . date('d-m-Y', strtotime($expiryDate)) . ". Thank you for joining us!";
    }
    
    // Send the SMS
    $result = sendSMS($phone, $message);
    
    // Log the SMS
    $status = $result['success'] ? 'sent' : 'failed';
    $error_message = $result['success'] ? null : $result['message'];
    logSmsActivity($conn, $memberId, $phone, $message, $status, $error_message);
    
    return $result;
}

/**
 * Send membership renewal reminder
 * 
 * @param object $conn Database connection
 * @param int $memberId Member ID
 * @param string $phone Member's phone number
 * @param string $memberName Member's full name
 * @param string $planName Membership plan name
 * @param string $expiryDate Membership expiry date
 * @return array Result with success status and message
 */
function sendRenewalReminderSMS($conn, $memberId, $phone, $memberName, $planName, $expiryDate) {
    // Get renewal reminder template
    $template_query = "SELECT * FROM sms_templates WHERE template_type = 'renewal' AND is_active = 1 LIMIT 1";
    $template_result = $conn->query($template_query);
    
    if ($template_result && $template_result->num_rows > 0) {
        $template = $template_result->fetch_assoc();
        $message_template = $template['template_content'];
        
        // Format expiry date
        $formatted_expiry = date('d-m-Y', strtotime($expiryDate));
        
        // Replace placeholders
        $message = str_replace(
            ['{member_name}', '{plan_name}', '{expiry_date}'],
            [$memberName, $planName, $formatted_expiry],
            $message_template
        );
    } else {
        // Default renewal message if no template found
        $message = "Dear $memberName, your $planName membership will expire on " . date('d-m-Y', strtotime($expiryDate)) . ". Please visit our gym to renew your membership. Thank you!";
    }
    
    // Send the SMS
    $result = sendSMS($phone, $message);
    
    // Log the SMS
    $status = $result['success'] ? 'sent' : 'failed';
    $error_message = $result['success'] ? null : $result['message'];
    logSmsActivity($conn, $memberId, $phone, $message, $status, $error_message);
    
    return $result;
}

/**
 * Send custom SMS to a member
 * 
 * @param object $conn Database connection
 * @param int $memberId Member ID
 * @param string $phone Member's phone number
 * @param string $message Custom message
 * @return array Result with success status and message
 */
function sendCustomSMS($conn, $memberId, $phone, $message) {
    // Send the SMS
    $result = sendSMS($phone, $message);
    
    // Log the SMS
    $status = $result['success'] ? 'sent' : 'failed';
    $error_message = $result['success'] ? null : $result['message'];
    logSmsActivity($conn, $memberId, $phone, $message, $status, $error_message);
    
    return $result;
}

/**
 * Send bulk SMS to multiple members
 * 
 * @param object $conn Database connection
 * @param array $memberIds Array of member IDs
 * @param string $messageTemplate Message template with placeholders
 * @return array Result with success status, sent count, failed count, and errors
 */
function sendBulkSMS($conn, $memberIds, $messageTemplate) {
    $sent = 0;
    $failed = 0;
    $errors = [];
    
    // Process each member
    foreach ($memberIds as $memberId) {
        // Get member details
        $query = "SELECT m.id, m.first_name, m.last_name, m.phone, mp.name as plan_name, m.membership_end_date 
                 FROM gym_members m 
                 LEFT JOIN membership_plans mp ON m.membership_id = mp.id 
                 WHERE m.id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $memberId);
        $stmt->execute();
        $result = $stmt->get_result();
        $member = $result->fetch_assoc();
        $stmt->close();
        
        if (!$member || empty($member['phone'])) {
            $failed++;
            continue;
        }
        
        // Replace placeholders in message
        $memberName = $member['first_name'] . ' ' . $member['last_name'];
        $planName = $member['plan_name'] ?? 'N/A';
        $expiryDate = $member['membership_end_date'] ? date('d-m-Y', strtotime($member['membership_end_date'])) : 'N/A';
        
        $message = str_replace(
            ['{member_name}', '{plan_name}', '{expiry_date}'],
            [$memberName, $planName, $expiryDate],
            $messageTemplate
        );
        
        // Send SMS
        $result = sendSMS($member['phone'], $message);
        
        if ($result['success']) {
            $sent++;
            
            // Log the SMS
            $stmt = $conn->prepare("INSERT INTO sms_logs (member_id, phone_number, message, status) VALUES (?, ?, ?, 'sent')");
            $stmt->bind_param("iss", $memberId, $member['phone'], $message);
            $stmt->execute();
            $stmt->close();
        } else {
            $failed++;
            $errors[] = "Failed to send SMS to {$memberName}: {$result['message']}";
            
            // Log the failed SMS
            $errorMsg = $result['message'];
            $stmt = $conn->prepare("INSERT INTO sms_logs (member_id, phone_number, message, status, error_message) VALUES (?, ?, ?, 'failed', ?)");
            $stmt->bind_param("isss", $memberId, $member['phone'], $message, $errorMsg);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    return [
        'success' => $sent > 0,
        'sent' => $sent,
        'failed' => $failed,
        'errors' => $errors,
        'message' => $failed > 0 ? implode("; ", $errors) : ''
    ];
}

/**
 * Log SMS messaging activity
 * 
 * @param object $conn Database connection
 * @param int $memberId Member ID
 * @param string $phone Phone number
 * @param string $message Message content
 * @param string $status Status (sent, failed)
 * @param string $errorMessage Error message if any
 * @return bool Success status
 */
function logSmsActivity($conn, $memberId, $phone, $message, $status, $errorMessage = null) {
    // Check if sms_logs table exists, create it if it doesn't
    $check_table = $conn->query("SHOW TABLES LIKE 'sms_logs'");
    if ($check_table->num_rows == 0) {
        $create_table = "CREATE TABLE IF NOT EXISTS sms_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            member_id INT,
            phone_number VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            template_id INT DEFAULT NULL,
            status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
            error_message TEXT DEFAULT NULL,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (member_id) REFERENCES gym_members(id) ON DELETE SET NULL,
            FOREIGN KEY (template_id) REFERENCES sms_templates(id) ON DELETE SET NULL
        )";
        $conn->query($create_table);
    }
    
    $query = "INSERT INTO sms_logs (member_id, phone_number, message, status, error_message) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issss", $memberId, $phone, $message, $status, $errorMessage);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Check if SMS functionality is enabled
 * 
 * @param object $conn Database connection
 * @return bool True if SMS is enabled, false otherwise
 */
function isSmsEnabled($conn) {
    // Check if sms_config table exists
    $check_table = $conn->query("SHOW TABLES LIKE 'sms_config'");
    if ($check_table->num_rows == 0) {
        // Create default config if table doesn't exist
        createDefaultSmsConfig($conn);
        return true; // Auto-enable SMS
    }
    
    // Check if there's an active configuration
    $query = "SELECT * FROM sms_config WHERE is_active = 1 LIMIT 1";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        return true;
    } else {
        // If no active config found, create and enable one
        createDefaultSmsConfig($conn);
        return true;
    }
}

/**
 * Create default SMS configuration
 * 
 * @param object $conn Database connection
 * @return bool Success status
 */
function createDefaultSmsConfig($conn) {
    // Create sms_config table if it doesn't exist
    $create_table = "CREATE TABLE IF NOT EXISTS sms_config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        api_provider VARCHAR(100) NOT NULL,
        api_endpoint VARCHAR(255) NOT NULL,
        api_key VARCHAR(255) NOT NULL,
        sender_id VARCHAR(50) NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->query($create_table);
    
    // Check if there's already a configuration
    $check_query = "SELECT COUNT(*) as count FROM sms_config";
    $result = $conn->query($check_query);
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        // Update existing configuration to be active
        $update_query = "UPDATE sms_config SET is_active = 1 WHERE id = (SELECT id FROM sms_config LIMIT 1)";
        $conn->query($update_query);
        return true;
    }
    
    // Insert default configuration
    // FIX: Create variables for the values to be passed by reference
    $api_url = API_URL;
    $api_key = API_KEY;
    $route_id = ROUTE_ID;
    $provider = 'BIR SMS';
    
    $query = "INSERT INTO sms_config (api_provider, api_endpoint, api_key, sender_id, is_active) 
              VALUES (?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $provider, $api_url, $api_key, $route_id);
    $result = $stmt->execute();
    $stmt->close();
    
    // Create default templates if they don't exist
    createDefaultSmsTemplates($conn);
    
    return $result;
}

/**
 * Create default SMS templates
 * 
 * @param object $conn Database connection
 * @return bool Success status
 */
function createDefaultSmsTemplates($conn) {
    // Create sms_templates table if it doesn't exist
    $create_table = "CREATE TABLE IF NOT EXISTS sms_templates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        template_name VARCHAR(100) NOT NULL,
        template_type ENUM('activation', 'renewal', 'custom') NOT NULL,
        template_content TEXT NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->query($create_table);
    
    // Check if templates already exist
    $check_query = "SELECT COUNT(*) as count FROM sms_templates";
    $result = $conn->query($check_query);
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        return true; // Templates already exist
    }
    
    // Insert default templates
    $templates = [
        [
            'name' => 'Membership Activation',
            'type' => 'activation',
            'content' => 'Dear {member_name}, welcome to our gym! Your {plan_name} membership is now active until {expiry_date}. Thank you for joining us!'
        ],
        [
            'name' => 'Membership Renewal Reminder',
            'type' => 'renewal',
            'content' => 'Dear {member_name}, your {plan_name} membership will expire on {expiry_date}. Please visit our gym to renew your membership. Thank you!'
        ]
    ];
    
    $query = "INSERT INTO sms_templates (template_name, template_type, template_content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    
    foreach ($templates as $template) {
        $stmt->bind_param("sss", $template['name'], $template['type'], $template['content']);
        $stmt->execute();
    }
    
    $stmt->close();
    return true;
}

/**
 * Get SMS templates
 * 
 * @param object $conn Database connection
 * @param bool $activeOnly Get only active templates
 * @return array Array of templates
 */
function getSmsTemplates($conn, $activeOnly = true) {
    // Ensure templates table exists
    $check_table = $conn->query("SHOW TABLES LIKE 'sms_templates'");
    if ($check_table->num_rows == 0) {
        createDefaultSmsTemplates($conn);
    }
    
    $query = "SELECT * FROM sms_templates";
    if ($activeOnly) {
        $query .= " WHERE is_active = 1";
    }
    $query .= " ORDER BY template_name";
    
    $result = $conn->query($query);
    $templates = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $templates[] = $row;
        }
    }
    
    return $templates;
}

/**
 * Get SMS logs for a specific member
 * 
 * @param object $conn Database connection
 * @param int $memberId Member ID
 * @param int $limit Maximum number of logs to retrieve
 * @return array Array of SMS logs
 */
function getMemberSmsLogs($conn, $memberId, $limit = 10) {
    $query = "SELECT * FROM sms_logs WHERE member_id = ? ORDER BY sent_at DESC LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $memberId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $logs = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
    }
    
    $stmt->close();
    return $logs;
}

/**
 * Send SMS to members with expiring memberships
 * 
 * @param object $conn Database connection
 * @param int $daysBeforeExpiry Number of days before expiry to send reminder
 * @return array Result with success status, sent count, and failed count
 */
function sendExpiryReminderSMS($conn, $daysBeforeExpiry = 7) {
    $today = date('Y-m-d');
    $expiryDate = date('Y-m-d', strtotime("+{$daysBeforeExpiry} days"));
    
    // Get members with memberships expiring in the specified days
    $query = "SELECT m.id, m.first_name, m.last_name, m.phone, mp.name as plan_name, m.membership_end_date 
             FROM gym_members m 
             LEFT JOIN membership_plans mp ON m.membership_id = mp.id 
             WHERE m.status = 'active' AND m.membership_end_date = ? AND m.phone IS NOT NULL AND m.phone != ''";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $expiryDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $memberIds = [];
    
    while ($member = $result->fetch_assoc()) {
        $memberIds[] = $member['id'];
    }
    
    $stmt->close();
    
    if (empty($memberIds)) {
        return ['success' => true, 'sent' => 0, 'failed' => 0, 'message' => 'No members with expiring memberships found'];
    }
    
    // Get renewal template
    $template_query = "SELECT * FROM sms_templates WHERE template_type = 'renewal' AND is_active = 1 LIMIT 1";
    $template_result = $conn->query($template_query);
    
    if ($template_result && $template_result->num_rows > 0) {
        $template = $template_result->fetch_assoc();
        $messageTemplate = $template['template_content'];
    } else {
        $messageTemplate = "Dear {member_name}, your {plan_name} membership will expire on {expiry_date}. Please visit our gym to renew your membership. Thank you!";
    }
    
    // Send bulk SMS
    return sendBulkSMS($conn, $memberIds, $messageTemplate);
}

// Auto-initialize SMS configuration when this file is included
function initializeSmsConfig() {
    global $conn;
    if (isset($conn)) {
        createDefaultSmsConfig($conn);
        createDefaultSmsTemplates($conn);
    }
}

// Call initialization function
initializeSmsConfig();
?>