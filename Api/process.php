<?php
// Critical: suppress notices/warnings from leaking into output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
while (ob_get_level()) { ob_end_clean(); }
// Enable detailed error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/home/smartleader/public_html/administrator/Api/api_debug.log');

// Basic CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Log incoming request snapshot
$headers = function_exists('getallheaders') ? getallheaders() : [];
$rawInput = file_get_contents('php://input');
$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
    'post_data' => $_POST,
    'request_data' => $_REQUEST,
    'raw_input' => $rawInput,
    'headers' => $headers
];
error_log("=== API REQUEST LOG ===\n" . json_encode($logData));

// Handle JSON request body (merge into POST/REQUEST for app compatibility)
if (!empty($rawInput)) {
    $json_data = json_decode($rawInput, true);
    if (is_array($json_data)) {
        $_POST = array_merge($_POST, $json_data);
        $_REQUEST = array_merge($_REQUEST, $json_data);
        error_log("JSON data merged into POST: " . json_encode($_POST));
    }
}

include('../common/config.php');
include("function.php");

$obj = new smart_leader($conn);

// Determine action once and log it
$action = $_POST['action'] ?? $_REQUEST['action'] ?? '';
error_log("Action called: " . ($action !== '' ? $action : 'none'));

// Utility: view logs quickly
if ($action === 'viewLogs') {
    $logFile = '/home/smartleader/public_html/administrator/Api/api_debug.log';
    if (file_exists($logFile)) {
        header('Content-Type: text/plain');
        echo file_get_contents($logFile);
    } else {
        echo "Log file not found";
    }
    exit;
}

// Handle Razorpay/subscription endpoints first, then fall through to others
if ($action === 'createSubscription') {
    error_log("Calling createSubscription()...");
    $obj->createSubscription();
    exit;
}

// Aliases for mobile client compatibility
if ($action === 'createRazorpaySubscription') {
    error_log("Alias route: createRazorpaySubscription -> createSubscription");
    $obj->createSubscription();
    exit;
}

if ($action === 'capturePaymentStatus') {
    $obj->capturePaymentStatus();
    exit;
}

if ($action == "get_user_subscription"){
	$obj->get_active_subscription();
}

if ($action === 'handleWebhook') {
    $obj->handleWebhook();
    exit;
}

if ($action === 'notification') {
    $result = $obj->notification();
    echo json_encode($result);
    exit;
}

// All other endpoints
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'lock_target_amount') {
$obj->lock_target_amount();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_analytics') {
$obj->show_analytics();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'copy_team') {
$obj->copy_team();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_team_graph') {
$obj->show_team_graph();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_branch_team') {
$obj->show_branch_team();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_branch') {
$obj->delete_branch();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit_branch') {
$obj->edit_branch();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_branch') {
$obj->show_branch();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_branch') {
$obj->add_branch();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_individual_team') {
$obj->delete_individual_team();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_individual_team') {
$obj->show_individual_team();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'individual_target_update') {
$obj->individual_target_update();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit_individual_team') {
$obj->edit_individual_team();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_individual_team') {
$obj->add_individual_team();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_sub_team') {
$obj->delete_sub_team();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_team') {
$obj->delete_team();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'member_target_team') {
$obj->member_target_team();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'target_update_team') {
$obj->target_update_team();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'update_actual_business_amount') {
$obj->update_actual_business_amount();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'search_book') {
$obj->search_book();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'new_update_sub_team') {
$obj->new_update_sub_team();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'new_show_sub_team') {
$obj->new_show_sub_team();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_sub_team_member') {
$obj->show_sub_team_member();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_sub_team') {
$obj->show_sub_team();
}
if ($action === 'updateSubscription') {
    $obj->updateSubscription();
    exit;
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'sub_team_completed') {
$obj->sub_team_completed();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit_sub_team') {
$obj->edit_sub_team();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_sub_team') {
$obj->add_sub_team();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_team') {
$obj->show_team();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit_team') {
$obj->edit_team();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_team') {
$obj->add_team();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_expense') {
$obj->show_expense();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_expense') {
$obj->add_expense();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_video_download') {
$obj->delete_video_download();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_video_download') {
$obj->show_video_download();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'video_download') {
$obj->video_download();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'order_history') {
$obj->order_history();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'order_now') {
$obj->order_now();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_cart') {
$obj->delete_cart();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_cart') {
$obj->show_cart();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_cart') {
$obj->add_cart();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'ebook_detail') {
$obj->ebook_detail();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'multipal_delete_event') {
$obj->multipal_delete_event();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'event_delete') {
$obj->event_delete();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_event') {
$obj->show_event();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_event_type') {
$obj->add_event_type();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit_event') {
$obj->edit_event();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_event') {
$obj->add_event();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'multipal_delete_task') {
$obj->multipal_delete_task();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'task_delete') {
$obj->task_delete();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit_task') {
$obj->edit_task();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_task') {
$obj->show_task();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_task') {
$obj->add_task();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_note') {
$obj->show_note();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'notes_move') {
$obj->notes_move();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'multipal_delete_note') {
$obj->multipal_delete_note();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_note') {
$obj->delete_note();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit_note') {
$obj->edit_note();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_note') {
$obj->add_note();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_static_folder') {
$obj->show_static_folder();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_folder') {
$obj->delete_folder();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_folder') {
$obj->show_folder();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_folder') {
$obj->add_folder();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'multipal_delete_meeting') {
$obj->multipal_delete_meeting();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'search_meeting') {
$obj->search_meeting();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_meeting') {
$obj->show_meeting();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_meeting') {
$obj->add_meeting();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'video_detail') {
$obj->video_detail();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_video') {
$obj->show_video();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_free_video') {
$obj->show_free_video();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'getFreeVideosReal') {
    error_log("=== process.php: getFreeVideosReal action triggered ===");
    error_log("Object exists: " . (isset($obj) ? "Yes" : "No"));
    error_log("Method exists: " . (method_exists($obj, 'getFreeVideosReal') ? "Yes" : "No"));
    
    if (method_exists($obj, 'getFreeVideosReal')) {
        $obj->getFreeVideosReal();
    } else {
        error_log("ERROR: Method getFreeVideosReal does not exist!");
        echo json_encode([
            'status' => 'error',
            'message' => 'Method getFreeVideosReal not found',
            'count' => 0,
            'data' => []
        ]);
    }
    exit;
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'get_video_names') {
$obj->get_video_names();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_tags') {
$obj->show_tags();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_book') {
$obj->show_book();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'New_edit_book') {
$obj->New_edit_book();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'connection_delete') {
$obj->connection_delete();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit_connection') {
$obj->edit_connection();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_connection') {
$obj->show_connection();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'connection') {
$obj->connection();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'connection_type_delete') {
$obj->connection_type_delete();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_connection_type') {
$obj->show_connection_type();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_connection_type') {
$obj->add_connection_type();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'contact_us') {
$obj->contact_us();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'privacy_policy') {
$obj->privacy_policy();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'terms_condition') {
$obj->terms_condition();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'About_us') {
$obj->About_us();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'banner') {
$obj->banner();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'update_name') {
$obj->update_name();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'show_profile') {
$obj->show_profile();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'get_profile') {
$obj->get_profile();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'update_profile') {
$obj->update_profile();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'social_login') {
$obj->social_login();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_subteam') {
$obj->delete_subteam();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'user_signup') {
$obj->user_signup();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'forgot_password_request') {
$obj->forgot_password_request();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'reset_password') {
$obj->reset_password();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'user_login') {
$obj->user_login();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'verify_otp') {
$obj->verify_otp();
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'language_list') { 
$obj->LanguageList();
}


//   Search   //

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'video_search') { 
$obj->videoSearch();
}


if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'book_search') { 
$obj->bookSearch();
}

//   Plan   //

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'get_plan') { 
  $obj->get_plan();
}


