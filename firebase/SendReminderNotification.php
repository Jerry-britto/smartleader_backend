<?php
include(__DIR__ . '/../common/config.php');
include("FirebaseNotificationService.php");

$fcm = new FirebaseNotificationService(); 

date_default_timezone_set('Asia/Kolkata');
$currentTime = date('Y-m-d H:i');

$query = "SELECT ae.*, u.fcm_token 
          FROM add_event ae 
          JOIN signup u ON u.id = ae.user_id 
          WHERE ae.remind_me IS NOT NULL 
          AND u.fcm_token IS NOT NULL 
          AND u.fcm_token != ''";

$result = mysqli_query($conn, $query);
$sent = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $eventDate = $row['date'];          // e.g., 02-08-2025
        $eventTime = trim($row['time']);    // e.g., "5:00 PM" OR "17:00"
        $remindBefore = (int)$row['remind_me']; // in minutes
    
        // Supported formats
        $formats = [
            'd-m-Y h:i A', // 12-hour (with AM/PM) e.g. 02-08-2025 5:00 PM
            'd-m-Y H:i',   // 24-hour e.g. 02-08-2025 17:00
            'Y-m-d H:i',   // DB में अगर Y-m-d हो
            'Y-m-d h:i A',
        ];
    
        $eventDateTime = null;
        foreach ($formats as $format) {
            $eventDateTime = DateTime::createFromFormat($format, $eventDate . ' ' . $eventTime);
            if ($eventDateTime !== false) {
                break;
            }
        }
    
        if (!$eventDateTime) {
            continue; // Skip invalid rows
        }
    
        // Subtract the reminder minutes
        $reminderTime = clone $eventDateTime;
        $reminderTime->modify("-{$remindBefore} minutes");
    
        // Compare with current time in 24-hour format
        if ($reminderTime->format('Y-m-d H:i') === $currentTime) {
            if (!empty($row['fcm_token'])) {
                $fcm->send(
                    $row['fcm_token'],
                    "ðŸ”” Reminder: " . $row['title'],
                    $row['description']
                );
                $sent++;
            }
        }
    }


echo "[" . date('Y-m-d H:i:s') . "] Cron ran successfully. Notifications sent: {$sent}\n";
