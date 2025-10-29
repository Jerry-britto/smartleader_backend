<?php
// Include PHPMailer classes
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . "/../firebase/FirebaseNotificationService.php";
use App\Razorpay\RazorpayService;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class smart_leader

{
	public $conn;
	public $path;
	function __construct($conn)
	{
		$this->conn = $conn;
		$this->path = "https://smartleader.info/administrator/images/";
	}

function get_active_subscription()
    {
        header('Content-Type: application/json');

        // 1. Extract User ID
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : (isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : null);

        // 2. Basic Validation
        if (empty($user_id)) {
            echo json_encode([
                'status' => false,
                'message' => 'User ID is required.'
            ]);
            exit;
        }

        $userIdInt = (int)$user_id;

        // 3. Check User Existence (Securing the query)
        $userCheckStmt = mysqli_prepare($this->conn, "SELECT id FROM signup WHERE id = ? LIMIT 1");
        if (!$userCheckStmt) {
            error_log("DB Prepare Error (User Check): " . mysqli_error($this->conn));
            echo json_encode(['status' => false, 'message' => 'Database error (code 1).']);
            exit;
        }
        mysqli_stmt_bind_param($userCheckStmt, "i", $userIdInt);
        mysqli_stmt_execute($userCheckStmt);
        $userResult = mysqli_stmt_get_result($userCheckStmt);

        if (mysqli_num_rows($userResult) === 0) {
            echo json_encode([
                'status' => false,
                'message' => 'User not found.'
            ]);
            exit;
        }

        // 4. Retrieve THE Active Subscription and Plan Details
        // Active statuses: 'active', 'resumed', 'authenticated'
        $activeStatuses = ['active', 'resumed', 'authenticated'];
        $statusString = "'" . implode("','", $activeStatuses) . "'";

        // Use LIMIT 1 because you only expect one active plan
        $sql = "SELECT s.subscription_id, s.status, s.start_at, s.end_at, s.next_charge_at, 
                   p.name AS plan_name, p.amount AS plan_amount_paise, p.currency, p.id
            FROM subscriptions s
            JOIN plans p ON s.plan_id = p.id
            WHERE s.user_id = ? AND s.status IN ($statusString)
            LIMIT 1";

        $stmt = mysqli_prepare($this->conn, $sql);

        if (!$stmt) {
            error_log("DB Prepare Error (Subscription Fetch): " . mysqli_error($this->conn));
            echo json_encode(['status' => false, 'message' => 'Database error (code 2).']);
            exit;
        }

        mysqli_stmt_bind_param($stmt, "i", $userIdInt);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $activePlan = mysqli_fetch_assoc($result); // Fetching only one row

        // 5. Return Results
        if (empty($activePlan)) {
            echo json_encode([
                'status' => true,
                'message' => 'No active subscription found.',
                'data' => null // Return null or an empty object/array for a single result
            ]);
        } else {
            // Process the single result
            $activePlan['plan_amount'] = (int)$activePlan['plan_amount_paise'];
            unset($activePlan['plan_amount_paise']);

            echo json_encode([
                'status' => true,
                'message' => 'Active subscription retrieved successfully.',
                'data' => $activePlan // Returns a single object, not an array
            ]);
        }
        exit;
    }

        public function updateSubscription()
    {
        header('Content-Type: application/json');

        $userId = $_POST['user_id'] ?? null;
        $subscriptionId = $_POST['subscription_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $startAt = $_POST['start_at'] ?? null;
        $endAt = $_POST['end_at'] ?? null;
        $nextChargeAt = $_POST['next_charge_at'] ?? null;

        if (!$userId || !$subscriptionId) {
            http_response_code(400);
            echo json_encode(["error" => "Missing user_id or subscription_id"]);
            return;
        }

        // Build dynamic SQL update based on provided fields
        $fields = [];
        $params = [];
        $types = '';

        if ($status) {
            $fields[] = "status = ?";
            $params[] = $status;
            $types .= 's';
        }
        if ($startAt) {
            $fields[] = "start_at = ?";
            $params[] = $startAt;
            $types .= 's';
        }
        if ($endAt) {
            $fields[] = "end_at = ?";
            $params[] = $endAt;
            $types .= 's';
        }
        if ($nextChargeAt) {
            $fields[] = "next_charge_at = ?";
            $params[] = $nextChargeAt;
            $types .= 's';
        }

        if (empty($fields)) {
            echo json_encode(["message" => "Nothing to update"]);
            return;
        }

        $sql = "UPDATE subscriptions SET " . implode(', ', $fields) . ", updated_at = NOW() 
            WHERE subscription_id = ? AND user_id = ?";
        $params[] = $subscriptionId;
        $params[] = $userId;
        $types .= 'ss';

        $stmt = mysqli_prepare($this->conn, $sql);
        if (!$stmt) {
            error_log("Failed to prepare: " . mysqli_error($this->conn));
            http_response_code(500);
            echo json_encode(["error" => "SQL prepare failed"]);
            return;
        }

        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);

        echo json_encode(["message" => "Subscription updated successfully"]);
    }
	//--------------------------------------lock_target_amount--------------------------------------------------------------------------------------
	function lock_target_amount()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,

		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {


			$update_note = mysqli_query($this->conn, "UPDATE `add_team` SET `target_status`='1' WHERE `id`='$id'");
			if ($update_note) {
				$update_note1 = mysqli_query($this->conn, "UPDATE `add_sub_team` SET `target_status`='1' WHERE `add_team_id`='$id'");
			}
		}
		if ($update_note) {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_team` WHERE `id`='$id'"));
			$select['path'] = $this->path;
			$select['message'] = "Update Target Amount Lock Successfully";
		} else {
			$select['message'] = "Failed";
		}
		echo json_encode($select, JSON_UNESCAPED_SLASHES);
	}
	//-----------------------------------------------show_analytics---------------------------------
	function show_analytics()
	{
		// retrieve POST data
		extract($_POST);
		// validate input values
		$validation = [
			'user_id' => $user_id,
			'branch_id' => $branch_id,
		];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is Empty";
			echo json_encode($msz);
			die();
		} else {
			// initialize arrays for storing data
			$analytics_data = array();
			$month_years = array();
			// retrieve month_year values for given user and branch
			$fetch = mysqli_query($this->conn, "SELECT DISTINCT `month_year` FROM `add_team` WHERE `user_id`='$user_id' and `branch_id`='$branch_id'");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				// initialize array for each month_year value
				$month_year_data = array();
				$month_year_data['month_year'] = $fetch_cate['month_year'];

				// retrieve team data for given month_year value
				$fetch1 = mysqli_query($this->conn, "SELECT * FROM `add_team` where `month_year`='" . $fetch_cate['month_year'] . "' and `user_id`='$user_id'");
				$team_data = array();
				while ($fetch_cate1 = mysqli_fetch_assoc($fetch1)) {
					$target_amount = $fetch_cate1['target_amount'];
					$amount = $fetch_cate1['amount'];
					$percentage = ($amount / $target_amount) * 100;
					$rounded_percentage = number_format($percentage, 2);;
					$gaps = $target_amount - $amount;
					// add team data to team_data array
					$team_data[] = array(
						'id' => $fetch_cate1['id'],
						'branch_id' => $fetch_cate1['branch_id'],
						'user_id' => $fetch_cate1['user_id'],
						'team_id' => $fetch_cate1['team_id'],
						'team_name' => $fetch_cate1['team_name'],
						'target_amount' => $fetch_cate1['target_amount'],
						'amount' => $fetch_cate1['amount'],
						'month_year' => $fetch_cate1['month_year'],
						'status' => $fetch_cate1['status'],
						'percentage' => $rounded_percentage,
						'gaps' => $gaps,
						'path' => $this->path
					);
				}
				// add team_data array to month_year_data array
				$month_year_data['team_data'] = $team_data;
				// add month_year_data array to analytics_data array
				$analytics_data[] = $month_year_data;
			}
			// return data in JSON format
			if (!empty($analytics_data)) {
				$msz['data'] = $analytics_data;
				$msz['message'] = "Analytics Team showing is successful";
			} else {
				$msz['message'] = "Failed to show analytics";
			}
			echo json_encode($msz);
		}
	}
	//-------------------------------copy_team-----------------------------------------
	function copy_team()
	{
		extract($_POST);
		$valid = array(
			'team_id' => $team_id,
			);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$branch_count = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_team` WHERE `team_id`='$team_id'"));

			$dateString = $branch_count['month_year'];
			$date = DateTime::createFromFormat('M-Y', $dateString);
			$formattedDate = $date->format('Y-m-d');

			$dateString1 = $formattedDate;
			$date1 = new DateTime($dateString1);
			$date1->modify('+1 month');
			$formattedDate1 = $date1->format('Y-m-d');
			$dateString2 = $formattedDate1;
			$date2 = new DateTime($dateString2);
			$formattedDate2 = $date2->format('M-Y');


			$team_user_id = rand(1000000, 9999999);
			$booking = mysqli_query($this->conn, "INSERT INTO `add_team` SET `branch_id`='" . $branch_count['branch_id'] . "',
			`user_id`='" . $branch_count['user_id'] . "',`team_id`='$team_user_id',`team_name`='" . $branch_count['team_name'] . "',
			`target_amount`='0',`amount`='0',`month_year`='$formattedDate2'");
			$newTeamID = mysqli_insert_id($this->conn);


			$add_team_id = $branch_count['id'];
			$getOldTeams = (mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `add_team_id`='$add_team_id'"));

			$index = 0;
			while ($new = mysqli_fetch_assoc($getOldTeams)) {
				// $sub_team = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `team_id`='$team_id'"));
				$member_unqiue = rand(1000000, 9999999);
				if ($index == 0) {
					$sub = mysqli_query($this->conn, "INSERT INTO `add_sub_team` SET `add_team_id`='" . $newTeamID . "',`user_id`='" . $new['user_id'] . "',`team_id`='$team_user_id',
				`team_name`='" . $new['team_name'] . "',`member_name`='" . $new['member_name'] . "',`member_unqiue_id`='$member_unqiue',
				`member_target`='0',`member_completed`='0',`month_year`='$formattedDate2'");
					$insert_id1 = mysqli_insert_id($this->conn);
				} else {
					
					$getLastRecord = (mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `add_team_id`='$newTeamID' ORDER BY id DESC LIMIT 1"));
					$getLastData = mysqli_fetch_assoc($getLastRecord);
					$lastTeamID =  $getLastData['member_unqiue_id'];
					
					$sub = mysqli_query($this->conn, "INSERT INTO `add_sub_team` SET `add_team_id`='" . $newTeamID . "',`user_id`='" . $new['user_id'] . "',`team_id`='$lastTeamID',
						`team_name`='" . $new['team_name'] . "',`member_name`='" . $new['member_name'] . "',`member_unqiue_id`='$member_unqiue',
						`member_target`='0',`member_completed`='0',`month_year`='$formattedDate2'");
					$insert_id1 = mysqli_insert_id($this->conn);
				}
				$index++;
			}



			$insert_id = mysqli_insert_id($this->conn);
			if ($insert_id != '') {
				$vals = mysqli_query($this->conn, "SELECT * FROM `add_team` where id='$insert_id'");
				$msz = mysqli_fetch_assoc($vals);
				$msz['message'] = "Team Coupy Successfully ";
			} else {
				$msz['message'] = "FAILD";
			}
		}
		echo json_encode($msz);
	}
	//----------------------------------------show_branch_team----------------------------------
	function show_team_graph()
	{
		extract($_POST);
		$validation = [
			'user_id' => $user_id
		];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is Empty";
			echo json_encode($msz);
			die();
		} else {
			$result = array();
			$fetch = mysqli_query($this->conn, "SELECT * FROM `add_branch` WHERE `user_id`='$user_id'");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$team_data = array();
				$fetch1 = mysqli_query($this->conn, "SELECT * FROM `add_team` WHERE branch_id='" . $fetch_cate['id'] . "'");
				while ($fetch_cate1 = mysqli_fetch_assoc($fetch1)) {

					$fetch_cate1['path'] = $this->path;
					array_push($team_data, $fetch_cate1);
				}
				$fetch_cate['team_data'] = $team_data;
				array_push($result, $fetch_cate);
			}
			if (count($result) > 0) {
				$msz['data'] = $result;
				$msz['message'] = "Team Graph Showing is Successful";
			} else {
				$msz['message'] = "Failed to show branch team data";
			}
			echo json_encode($msz);
		}
	}
	//----------------------------------------show_branch_team----------------------------------
	function show_branch_team()
	{
		extract($_POST);
		$validation = [
			'branch_id' => $branch_id,
		];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is Empty";
			echo json_encode($msz);
			die();
		} else {
			$deep = array();
			$fetch = mysqli_query($this->conn, "SELECT * FROM `add_team` WHERE `branch_id`='$branch_id' ");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$fetch_cate['path'] = $this->path;
				array_push($deep, $fetch_cate);
			}
			if ($fetch) {
				$msz['data'] = $deep;
				$msz['message'] = " Branch Team showing Is Successfully";
			} else {
				$msz['message'] = "Faild To Show ";
			}
			echo json_encode($msz);
		}
	}

	//------------------------------------------------delete_branch------------------------------
	function delete_branch()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(
			null,
			$valid
		);
		if ($valid_check) {
			$message['message'] = $valid_check . " is empty";
		} else {
			$booking = mysqli_query($this->conn, "Delete from `add_branch` where `id` ='$id'");
			if ($booking) {
				$message['message'] = " Successfully Deleted";
			} else {
				$message['message'] = "FAILD";
			}
		}
		echo json_encode($message);
	}
	//---------------------------------------------------edit_branch---------------------------------
	function edit_branch()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_branch` WHERE `id`='$id' "));
			if ($btanch_name == "") {
				$btanch_name = $select['btanch_name'];
			}

			$update_note = mysqli_query($this->conn, "UPDATE `add_branch` SET  `btanch_name`='$btanch_name' WHERE `id`='$id'");
		}
		if ($update_note) {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_branch` WHERE `id`='$id'"));
			$select['path'] = $this->path;
			$select['message'] = "Successfully Updated";
		} else {
			$select['message'] = "Failed";
		}
		echo json_encode($select, JSON_UNESCAPED_SLASHES);
	}
	//----------------------------------------show_branch----------------------------------
	
	function show_branch()
	{
		extract($_POST);
		$validation = [
			'user_id' => $user_id,
		];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is Empty";
			echo json_encode($msz);
			die();
		} else {
			$deep = array();
			$fetch = mysqli_query($this->conn, "SELECT * FROM `add_branch` WHERE `user_id`='$user_id' ");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$fetch_cate['path'] = $this->path;
				array_push($deep, $fetch_cate);
			}
			if ($fetch) {
				$msz['data'] = $deep;
				$msz['message'] = " Branch showing Is Successfully";
			} else {
				$msz['message'] = "Faild To Show ";
			}
			echo json_encode($msz);
		}
	}

	//---------------------------------add_branch--------------------------------
	
	function add_branch()
	{
		extract($_POST);
		$valid = array(
			'user_id' => $user_id,
			'btanch_name' => $btanch_name,

		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$branch_count = mysqli_num_rows(mysqli_query($this->conn, "SELECT * FROM `add_branch` WHERE `user_id`='$user_id'"));
			if ($branch_count >= 6) {
				$msz['message'] = "Limit Exceeded";
			} else {
				$booking = mysqli_query($this->conn, "INSERT INTO `add_branch` SET  `user_id`='$user_id',`btanch_name`='$btanch_name'");
				$insert_id = mysqli_insert_id($this->conn);
				if ($insert_id != '') {
					$vals = mysqli_query($this->conn, "SELECT * FROM `add_branch` where id='$insert_id'");
					$msz = mysqli_fetch_assoc($vals);
					$msz['message'] = "Successfully Added";
				} else {
					$msz['message'] = "FAILD";
				}
			}
		}
		echo json_encode($msz);
	}
	
	//------------------------------------------------delete_individual_team------------------------------
	
	function delete_individual_team()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(
			null,
			$valid
		);
		if ($valid_check) {
			$message['message'] = $valid_check . " is empty";
		} else {
			$booking = mysqli_query($this->conn, "Delete from `individual_team` where `id` ='$id'");
			if ($booking) {
				$message['message'] = " Successfully Deleted";
			} else {
				$message['message'] = "FAILD";
			}
		}
		echo json_encode($message);
	}
	
	//-------------------------------------show_individual_team--------------------------------
	
	function show_individual_team()
	{
		extract($_POST);
		$validation = ['user_id' => $user_id,];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is Empty";
			echo json_encode($msz);
			die();
		} else {
			$deep = array();
			$fetch = mysqli_query($this->conn, "SELECT * FROM `individual_team`  WHERE `user_id`='$user_id' ");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$fetch_cate['path'] = $this->path;
				array_push($deep, $fetch_cate);
			}
			if ($fetch) {
				$msz['data'] = $deep;
				$msz['message'] = " Team showing Is Successfully";
			} else {
				$msz['message'] = "Faild To Show ";
			}
			echo json_encode($msz);
		}
	}

	//-------------------------------------------------individual_target_update-----------------
	
	function individual_target_update()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `individual_team` WHERE `id`='$id'"));
			if ($target_amount == "") {
				$target_amount = $select['target_amount'];
			}
			$amount = $select['target_amount'] + $target_amount;
			$update_note = mysqli_query($this->conn, "UPDATE `individual_team` SET `target_amount`='$amount' WHERE `id`='$id'");
		}
		if ($update_note) {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `individual_team` WHERE `id`='$id'"));

			$select['path'] = $this->path;
			$select['message'] = "Update Successfully";
		} else {
			$select['message'] = "Failed";
		}
		echo json_encode($select, JSON_UNESCAPED_SLASHES);
	}
	
	//-------------------------------------------------edit_individual_team-----------------
	
	function edit_individual_team()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `individual_team` WHERE `id`='$id'"));
			if ($amount == "") {
				$amount = $select['amount'];
			}
			$b = $select['amount'] + $amount;
			$update_note = mysqli_query($this->conn, "UPDATE `individual_team` SET `amount`='$b' WHERE `id`='$id'");
		}
		if ($update_note) {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `individual_team` WHERE `id`='$id'"));

			$select['path'] = $this->path;
			$select['message'] = "Update Successfully";
		} else {
			$select['message'] = "Failed";
		}
		echo json_encode($select, JSON_UNESCAPED_SLASHES);
	}
	
	//---------------------------------add_individual_team--------------------------------
	
	function add_individual_team()
	{
		extract($_POST);
		$valid = array(
			'user_id' => $user_id,
			'team_name' => $team_name,
			'target_amount' => $target_amount,
			'month_year' => $month_year,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$team_id = rand(1000, 9999);
			// $team= Team.$team_id;
			$booking = mysqli_query($this->conn, "INSERT INTO `individual_team` SET  `user_id`='$user_id',`team_id`='$team_id',`team_name`='$team_name',`target_amount`='$target_amount',`month_year`='$month_year'");
			$insert_id = mysqli_insert_id($this->conn);
			if ($insert_id != '') {
				$vals = mysqli_query($this->conn, "SELECT * FROM `individual_team` where id='$insert_id'");
				$msz = mysqli_fetch_assoc($vals);
				$msz['message'] = "Team Add Successfully ";
			} else {
				$msz['message'] = "FAILD";
			}
		}
		echo json_encode($msz);
	}
	
	//------------------------------------------------delete_sub_team------------------------------
	
	function delete_sub_team()
	{
		extract($_POST);
		$valid = array(
			'team_id' => $team_id,
		);
		$valid_check = array_search(
			null,
			$valid
		);
		if ($valid_check) {
			$message['message'] = $valid_check . " is empty";
		} else {
			$booking = mysqli_query($this->conn, "Delete from `add_sub_team` where `team_id` ='$team_id'");
			if ($booking) {
				$message['message'] = " Successfully Deleted";
			} else {
				$message['message'] = "FAILD";
			}
		}
		echo json_encode($message);
	}
	
	//------------------------------------------------delete_team------------------------------
	
	function delete_team()
	{
		extract($_POST);
		$valid = array(
			     'team_id' => $team_id,
			  );
		$valid_check = array_search(
			null,$valid );
		if ($valid_check) {
			$message['message'] = $valid_check . " is empty";
		} else {
			$booking = mysqli_query($this->conn, "DELETE a,ast FROM add_team AS a LEFT JOIN add_sub_team AS ast ON a.team_id = ast.team_id WHERE a.team_id = '$team_id'");
			// $booking = mysqli_query($this->conn, "DELETE a,ast FROM add_team AS a LEFT JOIN add_sub_team AS ast ON a.team_id = ast.team_id WHERE a.team_id = '$team_id'");
			if ($booking) {
				$message['message'] = " Successfully Deleted";
			} else {
				$message['message'] = "FAILD";
			}
		}
		echo json_encode($message);
	}

	function update_member_target($team_id, $target_amount) {
		
		$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `member_unqiue_id`='$team_id'"));

		// $child_data = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `team_id`='$team_id'"));

		if(!empty($select)) {

			$total_mytarget_amount = $select['my_amount'] + $target_amount;
			
			$update_note = mysqli_query($this->conn, "UPDATE `add_sub_team` SET `member_target`='$total_mytarget_amount' WHERE `member_unqiue_id`='$team_id'");
			
			$this->update_member_target($select['team_id'], $total_mytarget_amount);
		} else {
			
			$this->update_parent_member_target($team_id, $target_amount);
		}

		return true;
	}

	function update_parent_member_target($team_id, $target_amount) {
		
		$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_team` WHERE `team_id`='$team_id'"));

		if(!empty($select)) {
			
			$total_mytarget_amount = $select['my_amount'] + $target_amount;
	
			$update_note = mysqli_query($this->conn, "UPDATE `add_team` SET `target_amount`='$total_mytarget_amount' WHERE `team_id`='$team_id'");

		}

		return true;
	}
function update_actual_business_amount()
{
    $id = $_POST['id'] ?? null;
    $new_amount = $_POST['amount'] ?? null;

    if (empty($id) || $new_amount === null) {
        echo json_encode(['status' => false, 'message' => "Missing required fields"], JSON_UNESCAPED_UNICODE);
        return;
    }

    // Fetch existing team record
    $query = mysqli_query($this->conn, "SELECT * FROM `add_team` WHERE `id` = '$id'");
    if (!$query || mysqli_num_rows($query) === 0) {
        echo json_encode(['status' => false, 'message' => "Team record not found"], JSON_UNESCAPED_UNICODE);
        return;
    }

    $existing = mysqli_fetch_assoc($query);
    $MyTeamId = $existing['team_id'];

    // Get total from add_sub_team
    $sub_team_query = mysqli_query($this->conn, "
        SELECT SUM(member_completed) AS team_amount 
        FROM `add_sub_team` 
        WHERE `team_id` = '$MyTeamId'
    ");
    
    $existingTeamAmount = mysqli_fetch_assoc($sub_team_query);
    $total_team_amount = $existingTeamAmount['team_amount'] ?? 0;

    // Update values in add_team
    $update = mysqli_query($this->conn, "
        UPDATE `add_team`
        SET `my_target` = '$new_amount', `amount` = '$total_team_amount'
        WHERE `id` = '$id'
    ");

    if ($update) {
        http_response_code(200);
        echo json_encode([
            'status' => true,
            'message' => "Business amount updated successfully",
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['status' => false, 'message' => "Failed to update"], JSON_UNESCAPED_UNICODE);
    }
}


	//-------------------------------------------------member_target_team-----------------
	
	function member_target_team()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
		 	$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `id`='$id'"));
			if ($member_target == "") {
				$member_target = $select['member_target'];
			}
			
			$data1 = mysqli_fetch_assoc(mysqli_query($this->conn, "select SUM(`member_target`) AS `ms` from `add_sub_team` where `team_id`='$team_id'"));

			$count_data = $data1['ms'];
			
			$total_taget = $member_target + $count_data;

			$update_note = mysqli_query($this->conn, "UPDATE `add_sub_team` SET `member_target`='$total_taget',`my_amount`='$member_target' WHERE `id`='$id'");
			
			$this->update_member_target($select['team_id'], $total_taget);

		}
		if ($update_note) {
			$select123 = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `id`='$id'"));

			$select123['path'] = $this->path;
			$select123['message'] = "Update  Member Target Successfully";
		} else {
			$select123['message'] = "Failed";
		}
		echo json_encode($select123, JSON_UNESCAPED_SLASHES);
	}
	
	//------------------------------------------target_update_team-------------------------------------

	function target_update_team()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
			'target_amount' => $target_amount,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {

			$data = mysqli_fetch_assoc(mysqli_query($this->conn, "select * from `add_team` where `id`='$id'"));
			$get_teamid = $data['team_id'];
			$check = mysqli_num_rows(mysqli_query($this->conn, "select * from `add_sub_team` where `team_id`='$get_teamid'"));
			if ($check > 0) {
				$data1 = mysqli_fetch_assoc(mysqli_query($this->conn, "select SUM(`member_target`)AS `ms` from `add_sub_team` where `team_id`='$get_teamid'"));
				$count_data = $data1['ms'];
				$total_taget = $target_amount + $count_data;
				    
				$update_note = mysqli_query($this->conn, "UPDATE `add_team` SET `target_amount`='$total_taget',`my_amount`='$target_amount' WHERE `id`='$id'");
				if ($update_note) {
					$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_team` WHERE `id`='$id'"));
					$select['path'] = $this->path;
					$select['message'] = " Target Amount Update Successfully";
				} else {

					$select['message'] = "Failed";
				}
			} else {
				$update_note = mysqli_query($this->conn, "UPDATE `add_team` SET `target_amount`='$target_amount',`my_amount`='$target_amount' WHERE `id`='$id'");
				if ($update_note) {
					$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_team` WHERE `id`='$id'"));
					$select['path'] = $this->path;
					$select['message'] = " Target Amount Update Successfully";
				} else {

					$select['message'] = "Failed";
				}
			}
		}
		echo json_encode($select, JSON_UNESCAPED_SLASHES);
	}

	//----------------------------------------------search_book--------------------------
	function search_book()
	{
		extract($_REQUEST);
		$deep = array();
		if ($word != '') {
			$fetch = mysqli_query($this->conn, "SELECT * FROM `book` WHERE `book_name` LIKE '%$word%' OR `writer_name` LIKE '%$word%' ");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$fetch_cate['path'] = $this->path;
				array_push($deep, $fetch_cate);
			}
			if ($fetch) {
				$msz['data'] = $deep;
				$msz['message'] = "success fully";
			} else {
				$msz['message'] = "faild to show ";
			}
		}
		echo json_encode($msz);
	}

	/**
	 * New Code Updated By Sawan
	 * 
	 * 
	 */
	function update_parent_target($target, $team_id, $subtract = 0)
	{

		$add_team1 = mysqli_fetch_assoc(mysqli_query($this->conn, "select * from `add_team` WHERE `team_id`='" . $team_id . "'"));

		$childs = mysqli_fetch_assoc(mysqli_query($this->conn, "select SUM(member_completed) as total from `add_sub_team` WHERE `team_id`='" . $team_id . "'"));

		$nebal1 = abs($add_team1['amount'] - $subtract) + $target;

		mysqli_query($this->conn, "UPDATE `add_team` SET `amount`='$nebal1' WHERE `team_id`='" . $team_id . "'");
		return true;
	}

	function update_sub_parent_target($target, $team_id, $subtract = 0)
	{

		$add_team1 = mysqli_fetch_assoc(mysqli_query($this->conn, "select * from `add_sub_team` WHERE `member_unqiue_id`='" . $team_id . "'"));

		$childs = mysqli_fetch_assoc(mysqli_query($this->conn, "select SUM(member_completed) as total from `add_sub_team` WHERE `team_id`='" . $team_id . "'"));

		if (!empty($add_team1)) {
			$nebal1 = abs($add_team1['member_completed'] - $subtract) + $target;
			mysqli_query($this->conn, "UPDATE `add_sub_team` SET `member_completed`='$nebal1' WHERE `member_unqiue_id`='" . $team_id . "'");
			$member_unqiue_id_team_id = $add_team1['team_id'];
			return $this->update_sub_parent_target($target, $member_unqiue_id_team_id, $subtract);
		} else {
			return $this->update_parent_target($target, $team_id, $subtract);
		}
		return false;
	}
	/**
	 * New Code Updated By Sawan
	 * 
	 */

	//-------------------------------------new_update_sub_team--------------------------------------------
	function new_update_sub_team()
	{
		extract($_POST);

		$valid = array(
			'id' => $id,
			'team_id' => $team_id,
			'member_completed' => $member_completed,
		);


		$test = null;
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {

			/**
			 * Child Id
			 * id
			 * team_unique_id
			 * 
			 */
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `id`='$id'"));
			if ($member_completed == "") {
				$member_completed = $select['member_completed'];
			}

			$a = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `id`='$id'"));
			// $amtreser = $a;
			$amt1 = $a['member_completed'] + $member_completed;
			$super_team_id = $select['team_id'];

			$childs = mysqli_fetch_assoc(mysqli_query($this->conn, "select SUM(member_completed) as total from `add_sub_team` WHERE `team_id`='" . $team_id . "'"));

			$tt = $childs['total'] + $member_completed;

			// $add_team1 = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * from `add_sub_team` WHERE `id`='$id'"));
			// $nebal1 = abs($add_team1['member_completed']) + $member_completed;
			// $c = mysqli_query($this->conn, "UPDATE `add_sub_team` SET `member_completed`='$member_completed' WHERE `id`='$id'");

			/**
			 * New Code Updated By Sawan
			 */

			$subtract = $a['my_target'];

			$this->update_sub_parent_target($member_completed, $super_team_id, $subtract);

			$update_note = mysqli_query($this->conn, "UPDATE `add_sub_team` SET `member_completed` = '$tt', `my_target` = '$member_completed' WHERE `id`='$id'");
			/**
			 * New Code Updated By Sawan
			 */

		}
		if ($update_note) {
			$select3 = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `id`='$id'"));

			$select3['path'] = $this->path;
			$select3['message'] = "Update  Sub Team Successfully";
		} else {
			$select3['message'] = "Failed";
		}
		echo json_encode($select3, JSON_UNESCAPED_SLASHES);
	}

	//-------------------------------------new_show_sub_team--------------------------------
	function new_show_sub_team()
	{
		extract($_POST);
		$validation = [
			'user_id' => $user_id,
		];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is Empty";
			echo json_encode($msz);
			die();
		} else {
			$deep = array();
			$fetch = mysqli_query($this->conn, "SELECT * FROM `add_sub_team`  WHERE `user_id`='$user_id' ");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$fetch_cate['path'] = $this->path;
				array_push($deep, $fetch_cate);
			}
			if ($fetch) {
				$msz['data'] = $deep;
				$msz['message'] = " Sub Team showing Is Successfully";
			} else {
				$msz['message'] = "Faild To Show ";
			}
			echo json_encode($msz);
		}
	}

	//-------------------------------------show_sub_team_member--------------------------------
	function show_sub_team_member()
	{
		extract($_POST);
		$validation = ['team_id' => $team_id,];
		$valid_check = array_search(null, $validation);
		$members = $this->get_sub_team_members_recursive($team_id);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is Empty";
			echo json_encode($msz);
			die();
		} else {
			// $deep = array();
			// $fetch = mysqli_query($this->conn, "SELECT * FROM `add_sub_team`  WHERE `team_id`='$team_id' ");
			// while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
			// 	$fetch_cate['path'] = $this->path;
			// 	array_push($deep, $fetch_cate);
			// }
			// if ($fetch) {
			// 	$msz['data'] = $deep;
			// 	$msz['message'] = " Sub Team showing Is Successfully";
			// } else {
			// 	$msz['message'] = "Faild To Show ";
			// }
			// echo json_encode($msz);
			 if (!empty($members)) {
				$msz = [
					'data' => $members,
					'message' => 'Sub Team Members Fetched Successfully'
				];
			} else {
				$msz = [
					'data' => [],
					'message' => 'No Sub Team Members Found'
				];
			}

			echo json_encode($msz);
		}
	}

	function get_sub_team_members_recursive($team_id)
	{
		$deep = [];

		$query = "SELECT * FROM `add_sub_team` WHERE `team_id`='" . mysqli_real_escape_string($this->conn, $team_id) . "'";
		$fetch = mysqli_query($this->conn, $query);

		while ($row = mysqli_fetch_assoc($fetch)) {
			$row['path'] = $this->path;

			// Recursively fetch sub-members using their unique member ID
			$sub_members = $this->get_sub_team_members_recursive($row['member_unqiue_id']);

			if (!empty($sub_members)) {
				$row['sub_members'] = $sub_members;
			} else {
				$row['sub_members'] = [];
			}

			$deep[] = $row;
		}

		return $deep;
	}
	//-------------------------------------show_sub_team--------------------------------
	function show_sub_team()
	{
		extract($_POST);
		$validation = ['user_id' => $user_id];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is Empty";
			echo json_encode($msz);
			die();
		} else {
			$deep = array();
			$total_amount = 0;
			$grant_total = 0;
			$total_completed1 = 0;
			$t1 = 0;
			$fetch_ = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `user_id`='$user_id'"));
			$fetch = mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `team_id`='" . $fetch_['member_unqiue_id'] . "'");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$value1 = explode(',', $fetch_cate['member_completed']);
				$total_completed1 += array_sum($value1);

				$deep1 = array(); // initialize the team data array for each member
				$fetch1 = mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `user_id`='" . $fetch_cate['user_id'] . "'");
				while ($fetch_cate1 = mysqli_fetch_assoc($fetch1)) {

					$fetch_amount = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `team_id`='" . $fetch_cate1['member_unqiue_id'] . "' "));
					$value = explode(',', $fetch_amount['member_completed']);
					$total_amount += array_sum($value);
					if ($fetch_amount) {
						$update2 = mysqli_query($this->conn, "UPDATE `add_sub_team` SET `total_amount`='$total_amount' WHERE `user_id`='" . $fetch_cate1['user_id'] . "' and `member_unqiue_id`='" . $fetch_cate1['member_unqiue_id'] . "'");
					}

					// add the team data to the array for each member
					$fetch_cate1['path'] = $this->path;
					array_push($deep1, $fetch_cate1);
				}
				$grant_total = $total_amount + $total_completed1; // calculate the grant total
				$fetch_cate['path'] = $this->path;
				// $fetch_cate['teamdata'] = $deep1;
				$fetch_cate['total_amount'] = $grant_total;
				array_push($deep, $fetch_cate);
			}
			$fetch_['teamdata'] = $deep;
			$t1 = $total_completed1 + $total_amount;
			$update = mysqli_query($this->conn, "UPDATE `add_sub_team` SET `total_amount`='$t1' WHERE `user_id`='$user_id'");
			if ($fetch) {
				$msz['data'] = $fetch_;
				$msz['message'] = "Sub Team showing Is Successfully";
			} else {
				$msz['message'] = "Faild To Show";
			}
			echo json_encode($msz);
		}
	}

	//-------------------------------------edit_sub_team--------------------------------------------
	function sub_team_completed()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `id`='$id'"));
			if ($member_completed == "") {
				$member_completed = $select['member_completed'];
			}
			if ($month_year == "") {
				$month_year = $select['month_year'];
			}

			$update_note = mysqli_query($this->conn, "UPDATE `add_sub_team` SET `member_completed`='$member_completed',`month_year`='$month_year' WHERE `id`='$id'");
		}
		if ($update_note) {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `id`='$id'"));

			$select['path'] = $this->path;
			$select['message'] = "Update  Sub Team Successfully";
		} else {
			$select['message'] = "Failed";
		}
		echo json_encode($select, JSON_UNESCAPED_SLASHES);
	}
	//-------------------------------------edit_sub_team--------------------------------------------
	function edit_sub_team()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
			'team_id' => $team_id,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `id`='$id' and `team_id`='$team_id'"));

			if ($member_target == "") {
				$member_target = $select['member_target'];
			}

			$update_note = mysqli_query($this->conn, "UPDATE `add_sub_team` SET `member_target`='$member_target' WHERE `id`='$id'");
		}
		if ($update_note) {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `id`='$id'"));
			$select['path'] = $this->path;
			$select['message'] = "Update  Sub Team Successfully";
		} else {
			$select['message'] = "Failed";
		}
		echo json_encode($select, JSON_UNESCAPED_SLASHES);
	}
	//---------------------------------add_sub_team--------------------------------
	function add_sub_team()
	{
		error_reporting(0); // Disable PHP warnings
		extract($_POST);
		$valid = array(
			'user_id' => $user_id,
			'team_id' => $team_id,
			'member_name' => $member_name,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$check2 = mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `user_id`='$user_id' ");
			$check3 = mysqli_query($this->conn, "SELECT * FROM `add_team` WHERE `user_id`='$user_id' ");
			//  if(mysqli_num_rows($check2) > 0){

			//       $msz['message'] = "  User Already Exists";
			//  }
			////  }if(mysqli_num_rows($check3) > 0){

			////       $msz['message'] = "  User Already Exists";
			////  }
			//  else{
			$test1 = mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `member_unqiue_id`='$team_id'");
$test2 = mysqli_query($this->conn, "SELECT * FROM `add_team` WHERE `team_id`='$team_id'");

$row1 = mysqli_fetch_assoc($test1);
$row2 = mysqli_fetch_assoc($test2);

if (
    ($row1 && ($row1['target_status'] == 1 || $row1['status'] == 1)) ||
    ($row2 && ($row2['target_status'] == 1 || $row2['status'] == 1))
) {
    $msz['message'] = "Team Locked, Cannot add more members.";
    $msz['status'] = "false";
    echo json_encode($msz, JSON_UNESCAPED_UNICODE);
    die;
}
			$check = mysqli_query($this->conn, "SELECT * FROM `add_team` WHERE `team_id`='$team_id'");
			$check1 = mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `member_unqiue_id`='$team_id' ");
			$check2 = mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `user_id`='$user_id' ");
			$get = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_team` WHERE `team_id`='$team_id'"));
			$get_name  = $get['team_name'];
			$get_mnt_year  = $get['month_year'];
			$target_amount  = $get['target_amount'];
			$update_target_amt = $target_amount + $member_target;
			$get_second = mysqli_fetch_assoc($check1);
			$get_name1  = $get_second['team_name'];
			$get_mnt_year1  = $get_second['month_year'];
			$target_amount1  = $get_second['member_target'];
			$update_target_amt1 = $target_amount1 + $member_target;
			$member_unqiue_id = rand(1000000, 9999999);
			$add_team_id = $get['id'];


			//  if(mysqli_num_rows($check2) > 0){
			//   $msz['message'] = "User Id Already exists";  
			//     $msz['status'] = "false";  

			//     }else
			if (mysqli_num_rows($check1) > 0) {
				$get = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `member_unqiue_id`='$team_id'"));
				$add_team_id = $get['add_team_id'];


				$booking = mysqli_query($this->conn, "INSERT INTO `add_sub_team`(`user_id`,`team_id`,`team_name`, `member_name`,`my_amount`,`member_target`, `member_unqiue_id`,`month_year`,`add_team_id`) 
				VALUES ('$user_id','$team_id','$get_name1','$member_name','$member_target','$member_target','$member_unqiue_id','$get_mnt_year1','$add_team_id')");

				$xyz = mysqli_num_rows(mysqli_query($this->conn, "select * from `add_sub_team`"));
				for ($i = 0; $i < $xyz; $i++) {
					$c1 = mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `member_unqiue_id`='$team_id' ");
					$aa = mysqli_fetch_assoc($c1);
					$t_id1  = $aa['team_id'];
					$tamount1  = $aa['member_target'];
					$update_tamt1 = $tamount1 + $member_target;
					if ($c1 > 0) {


						$updates = mysqli_query($this->conn, "UPDATE `add_sub_team` SET `member_target`='$update_tamt1' WHERE `member_unqiue_id`='$team_id'");
						$c1 = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_sub_team` WHERE `member_unqiue_id`='$team_id' "));
						$team_ids  = $c1['team_id'];
						$c1 = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_team` WHERE `team_id`='$team_ids'"));
						$taget_amt_one = $c1['target_amount'] + $member_target;
						$updates = mysqli_query($this->conn, "UPDATE `add_team` SET `target_amount`='$taget_amt_one' WHERE `team_id`='$team_ids'");
						$team_id = $team_ids;
					} else {
						$c1 = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_team` WHERE `team_id`='$team_id'"));
						$taget_amt_one = $c1['target_amount'] + $member_target;
						$updates = mysqli_query($this->conn, "UPDATE `add_team` SET `target_amount`='$taget_amt_one' WHERE `team_id`='$team_id'");
					}
				}
				$msz['message'] = "Successfully Added";
				$msz['status'] = "true";
			} elseif (mysqli_num_rows($check) > 0) {
				$booking = mysqli_query($this->conn, "INSERT INTO `add_sub_team`(`user_id`,`team_id`,`team_name`, `member_name`,`my_amount`,`member_target`, `member_unqiue_id`,`month_year`,`add_team_id`) 
        			   VALUES ('$user_id','$team_id','$get_name','$member_name','$member_target','$member_target','$member_unqiue_id','$get_mnt_year','$add_team_id')");
				$updates = mysqli_query($this->conn, "UPDATE `add_team` SET `target_amount`='$update_target_amt' WHERE `team_id`='$team_id'");
				$msz['message'] = "Successfully Added";
				$msz['status'] = "true";
			} else {
				$msz['message'] = "Team Id Not Avalable";
				$msz['status'] = "false";
			}
			//  }
		}

		echo json_encode($msz);
	}
	//-------------------------------------show_team--------------------------------
	function show_team()
	{
		extract($_POST);
		$validation = ['user_id' => $user_id,];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is Empty";
			echo json_encode($msz);
			die();
		} else {
			$deep = array();
			$fetch = mysqli_query($this->conn, "SELECT * FROM `add_team`  WHERE `user_id`='$user_id' ");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$branch =  mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_branch` WHERE `id`='" . $fetch_cate['branch_id'] . "'"));
				$fetch_cate['branch_id'] = $branch['btanch_name'];
				$fetch_cate['path'] = $this->path;
				array_push($deep, $fetch_cate);
			}
			if ($fetch) {
				$msz['data'] = $deep;
				$msz['message'] = "Team showing Is Successfully";
			} else {
				$msz['message'] = "Faild To Show ";
			}
			echo json_encode($msz);
		}
	}
	//--------------------------------------edit_team--------------------------------------------------------------------------------------
	function edit_team()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
			'amount' => $amount,
		);
		
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_team` WHERE `id`='$id'"));
            

			if ($status == 1) {

				$update_note = mysqli_query($this->conn, "UPDATE `add_team` SET `amount`='$amount',`my_target`='$amount',`status`='1' WHERE `id`='$id'");

				if ($update_note && $team_id) {
					$update_note1 = mysqli_query($this->conn, "UPDATE `add_sub_team` SET `status`='1' WHERE `add_team_id`='$id'");
				}
			} else {
			   
				$data = mysqli_fetch_assoc(mysqli_query($this->conn, "select * from `add_team` where `id`='$id'"));
				$get_teamid = $data['team_id'];
				
				$check = mysqli_num_rows(mysqli_query($this->conn, "select * from `add_sub_team` where `team_id`='$get_teamid'"));
				if ($check > 0) {
				     
					$data1 = mysqli_fetch_assoc(mysqli_query($this->conn, "select SUM(`member_completed`)AS `ms` from `add_sub_team` where `team_id`='$get_teamid'"));
					
					$new_amount = $data1['ms'];
					$total_target = $amount + $new_amount;
					$update_note = mysqli_query($this->conn, "UPDATE `add_team` SET `amount`='$total_target',`my_target`='$amount' WHERE `id`='$id'");
				} else {
					$total_taget = $amount;
					
					$update_note = mysqli_query($this->conn, "UPDATE `add_team` SET `amount`='$total_taget',`my_target`='$amount' WHERE `id`='$id'");
				}
			}
		}
		if ($update_note) {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_team` WHERE `id`='$id'"));
			
			$select['path'] = $this->path;
			$select['message'] = "Update Team Successfully";
		} else {
			$select['message'] = "Failed update notes";
		}
		echo json_encode($select, JSON_UNESCAPED_SLASHES);
	}
	//---------------------------------add_team--------------------------------
	function add_team()
	{
		extract($_POST);
		$valid = array(
			'user_id' => $user_id,
			'branch_id' => $branch_id,
			'team_name' => $team_name,
			'target_amount' => $target_amount,
			'my_amount' => $my_amount,
			'month_year' => $month_year,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$branch_count = mysqli_num_rows(mysqli_query($this->conn, "SELECT * FROM `add_team` WHERE `branch_id`='$branch_id'"));
			if ($branch_count >= 10) {
				$msz['message'] = "Limit Exceeded This Branch";
			} else {
				$team_id = rand(1000000, 9999999);
				$booking = mysqli_query($this->conn, "INSERT INTO `add_team` SET `branch_id`='$branch_id',`user_id`='$user_id',`team_id`='$team_id',`team_name`='$team_name',`target_amount`='$target_amount',`my_amount`='$my_amount',`month_year`='$month_year'");
				$insert_id = mysqli_insert_id($this->conn);
				if ($insert_id != '') {
					$vals = mysqli_query($this->conn, "SELECT * FROM `add_team` where id='$insert_id'");
					$msz = mysqli_fetch_assoc($vals);
					$msz['message'] = "Successfully Added";
				} else {
					$msz['message'] = "FAILD";
				}
			}
		}
		echo json_encode($msz);
	}
	//-------------------------------------show_expense--------------------------------
	function show_expense()
	{
		extract($_POST);
		$validation = ['user_id' => $user_id,];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is Empty";
			echo json_encode($msz);
			die();
		} else {
			$deep = array();
			$fetch = mysqli_query($this->conn, "SELECT * FROM `add_expense`  WHERE `user_id`='$user_id' ");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$fetch_cate['path'] = $this->path;
				array_push($deep, $fetch_cate);
			}
			if ($fetch) {
				$msz['data'] = $deep;
				$msz['message'] = "Expense showing Is Successfully";
			} else {
				$msz['message'] = "Faild To Show ";
			}
			echo json_encode($msz);
		}
	}
	//---------------------------------add_expense--------------------------------
	function add_expense()
	{
		extract($_POST);
		$valid = array(
			'user_id' => $user_id,
			'amount' => $amount,
			'reason' => $reason,
			'date' => $date,
			'priority' => $priority,

		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$strtotime = strtotime('now');
			$booking = mysqli_query($this->conn, "INSERT INTO `add_expense` SET `user_id`='$user_id',`amount`='$amount',`reason`='$reason',
			`date`='$date',`priority`='$priority'");
			$insert_id = mysqli_insert_id($this->conn);
			if ($insert_id != '') {
				$vals = mysqli_query($this->conn, "SELECT * FROM `add_expense` where id='$insert_id'");
				$msz = mysqli_fetch_assoc($vals);
				$msz['message'] = "Expense Add Successfully ";
			} else {
				$msz['message'] = "FAILD";
			}
		}
		echo json_encode($msz);
	}
	//-------------------------------------------delete_video_download---------------------------
	function delete_video_download()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(
			null,
			$valid
		);
		if ($valid_check) {
			$message['message'] = $valid_check . " is empty";
		} else {
			$booking = mysqli_query($this->conn, "Delete from `video_download` where id =$id");
			if ($booking) {
				$message['message'] = " Successfully Deleted";
			} else {
				$message['message'] = "FAILD";
			}
		}
		echo json_encode($message);
	}
	//------------------------------------show_video_download-------------------------------
	function show_video_download()
	{
		extract($_POST);
		$validation = ['user_id' => $user_id,];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is Empty";
			echo json_encode($msz);
			die();
		} else {
			$deep = array();
			$fetch = mysqli_query($this->conn, "SELECT * FROM `video_download` WHERE `user_id`='$user_id' ");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$video = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_video` WHERE `id`='" . $fetch_cate['video_id'] . "'"));
				$fetch_cate['video'] = $video;
				$fetch_cate['path'] = $this->path;
				array_push($deep, $fetch_cate);
			}
			if ($fetch) {
				$msz['data'] = $deep;
				$msz['message'] = "Video Download showing Is Successfully";
			} else {
				$msz['message'] = "Faild To Show ";
			}
			echo json_encode($msz);
		}
	}
	//-------------------------------video_download-----------------------------------
	function video_download()
	{
		extract($_POST);
		$valid = array(
			'user_id' => $user_id,
			'video_id' => $video_id,

		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$checkinfo = mysqli_query($this->conn, "SELECT * FROM `video_download` WHERE `video_id`='$video_id'");
			if (mysqli_num_rows($checkinfo) > 0) {
				$msz['message'] = "All Already Exist";
			} else {
				$booking = mysqli_query($this->conn, "INSERT INTO `video_download` SET `user_id`='$user_id',`video_id`='$video_id'");
				$insert_id = mysqli_insert_id($this->conn);
				if ($insert_id != '') {
					$vals = mysqli_query($this->conn, "SELECT * FROM `video_download` where id='$insert_id'");
					$msz = mysqli_fetch_assoc($vals);
					$msz['message'] = "Video Download Add Successfully ";
				} else {
					$msz['message'] = "FAILD";
				}
			}
		}
		echo json_encode($msz);
	}
	//-----------------------------------order_history------------------------------------------
function order_history()
{
    extract($_POST);
    
    // Default language_key if not provided
    $language_key = !empty($language_key) ? $language_key : '1';
    
    // Validate inputs
    $validation = [
        'user_id' => $user_id,
        'language_key' => $language_key
    ];
    
    // Check if any required field is empty
    $valid_check = array_search(null, $validation);
    if ($valid_check) {
        $msz['message'] = $valid_check . " is Empty";
        echo json_encode($msz);
        die();
    } else {
        $deep = array();
        
        // Prepare SQL query with language_key filter
        $query = "SELECT * FROM `orders` WHERE `user_id` = ? AND `book_id` IN (SELECT `id` FROM `book` WHERE `language_key` = ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $user_id, $language_key);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($fetch_cate = $result->fetch_assoc()) {
            $book_query = "SELECT * FROM `book` WHERE `id` = ?";
            $book_stmt = $this->conn->prepare($book_query);
            $book_stmt->bind_param("i", $fetch_cate['book_id']);
            $book_stmt->execute();
            $Fetch_book = $book_stmt->get_result()->fetch_assoc();
            
            $fetch_cate['book'] = $Fetch_book;
            $fetch_cate['path'] = $this->path;
            array_push($deep, $fetch_cate);
        }
        
        if ($result->num_rows > 0) {
            $msz['data'] = $deep;
            $msz['message'] = "Orders History Showing Is Successfully";
        } else {
            $msz['message'] = "Failed To Show";
        }
        
        echo json_encode($msz);
    }
}


	//---------------------------------------order_now------------------------------
	function order_now()
	{
		extract($_POST);
		$array = array();
		$array1 = array();
		$validation = [
			'user_id' => $user_id,
			'book_id' => $book_id,
			'type' => $type,
			'payment_status' => $payment_status,
			'grand_total' => $grand_total,
		];
		$validation_check = array_search(null, $validation);
		if ($validation_check) {
			$message['result'] = $validation_check . ' is empty';
			echo json_encode($message, JSON_UNESCAPED_UNICODE);
			die();
		}
		$str = strtotime('now');
		$order_id = "ORD" . strtotime('now');
		$sql1 = mysqli_query($this->conn, "INSERT INTO `orders` SET  `user_id`='$user_id',`order_id`='$order_id',`book_id`='$book_id',`type`='$type',`grand_total`='$grand_total',`payment_status`='$payment_status',`strtotime`='$str'");
		$insert_id = mysqli_insert_id($this->conn);
		if ($insert_id != '') {
			$vals = mysqli_query($this->conn, "SELECT * FROM `orders` where id='$insert_id'");
			$message = mysqli_fetch_assoc($vals);
			$message["result"] = "Order Successfull";
		} else {
			$message["result"] = "Somthing Went Wrong";
		}
		echo json_encode($message);
		die();
	}
	//-------------------------------------------delete_cart---------------------------
	function delete_cart()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(
			null,
			$valid
		);
		if ($valid_check) {
			$message['message'] = $valid_check . " is empty";
		} else {
			$booking = mysqli_query($this->conn, "Delete from `add_cart` where id =$id");
			if ($booking) {
				$message['message'] = " Successfully Deleted";
			} else {
				$message['message'] = "FAILD";
			}
		}
		echo json_encode($message);
	}
	//------------------------------------show_cart-------------------------------
	function show_cart()
	{
		extract($_POST);
		$row = array();
		$validation = [
			'user_id' => $user_id,
		];
		$validation_check = array_search(null, $validation);
		if ($validation_check) {
			$message['result'] = $validation_check . ' is empty';
			echo json_encode($message, JSON_UNESCAPED_UNICODE);
			die();
		}
		$total_price = 0;
		$sql = mysqli_query($this->conn, "select * from `add_cart` where  `user_id`='$user_id' and `status`='0'");
		while ($fetch = mysqli_fetch_assoc($sql)) {
			$Fetch_book = mysqli_fetch_assoc(mysqli_query($this->conn, "select * from `book` where id='" . $fetch['book_id'] . "'"));
			$fetch['book_name'] = $Fetch_book['book_name'];
			$fetch['image'] = $Fetch_book['image'];
			$fetch['writer_name'] = $Fetch_book['writer_name'];
			$total_price = $total_price + $fetch['book_price'];
			$fetch['path'] = $this->path;
			array_push($row, $fetch);
		}
		if ($sql) {
			$message['data'] = $row;
			$message['total_price'] = $total_price;
			$message['message'] = "Cart Showing Is Successfully";
		} else {
			$message['message'] = "Faild To Show ";
		}
		echo json_encode($message);
	}
	//----------------------------------------add_cart------------------------------------------------
	function add_cart()
	{
		extract($_POST);
		$validation = [
			'user_id' => $user_id,
			'book_id' => $book_id,
			'book_price' => $book_price,
		];
		$validation_check = array_search(null, $validation);
		if ($validation_check) {
			$message['result'] = $validation_check . ' is empty';
			echo json_encode($message, JSON_UNESCAPED_UNICODE);
			die();
		}
		$sql_search = mysqli_query($this->conn, "SELECT * FROM `add_cart` where `book_id`='$book_id' and `status`='0' ");
		$count = mysqli_num_rows($sql_search);
		if ($count > 0) {
			$message["result"] = "Book Already Added";
		} else {
			$strtotime = strtotime('now');
			$sql1 = mysqli_query($this->conn, "INSERT INTO `add_cart`(`user_id`, `book_id`,`book_price`,`strtotime`, `status`) 
                                                     VALUES ('$user_id','$book_id','$book_price','$strtotime','0')");
			$insert_id = mysqli_insert_id($this->conn);
			if ($insert_id != '') {
				$vals = mysqli_query($this->conn, "SELECT * FROM `add_cart` where id='$insert_id'");
				$message = mysqli_fetch_assoc($vals);
				$message["result"] = "Add To Cart Successfull";
			} else {
				$message["result"] = "somthing went wrong";
			}
		}
		echo json_encode($message);
		die();
	}
	//-------------------------------------------multipal_delete_event---------------------------
	function multipal_delete_event()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(
			null,
			$valid
		);
		if ($valid_check) {
			$message['message'] = $valid_check . " is empty";
		} else {
			$ids = array();
			$ids = explode(",", $id);
			foreach ($ids as $key) {
				$booking = mysqli_query($this->conn, "Delete from `add_event` where `id` ='$key'");
				if ($booking) {
					$message['message'] = " Successfully Deleted";
				} else {
					$message['message'] = "FAILD";
				}
			}
		}
		echo json_encode($message);
	}
	//--------------------------------------------------event_delete---------------------------------
	function event_delete()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(
			null,
			$valid
		);
		if ($valid_check) {
			$message['message'] = $valid_check . " is empty";
		} else {
			$booking = mysqli_query($this->conn, "Delete from `add_event` where id =$id");
			if ($booking) {
				$message['message'] = " Successfully Deleted";
			} else {
				$message['message'] = "FAILD";
			}
		}
		echo json_encode($message);
	}
	//-------------------------------------show_event--------------------------------
	
	function show_event()
	{
		extract($_POST);
		$validation = ['user_id' => $user_id,];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is Empty";
			echo json_encode($msz);
			die();
		} else {
			$deep = array();
			$deep1 = array();
			if (!empty($type)) {
				$fetch = mysqli_query($this->conn, "SELECT `date` FROM `add_event` WHERE `user_id`='$user_id' and `type`='$type' group by `date`");

				while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
					$fetch1 = mysqli_query($this->conn, "SELECT * FROM `add_event` where `date`='" . $fetch_cate['date'] . "' and `type`='$type'");
					$deep2 = array();
					while ($fetch_cate1 = mysqli_fetch_assoc($fetch1)) {
						$prod1 = $fetch_cate1;
						array_push($deep2, $prod1);
						$fetch_cate['data'] = $deep2;
						$fetch_cate['path'] = $this->path;
					}
					array_push($deep1, $fetch_cate);
				}
				if ($fetch) {
					$msz['data'] = $deep1;

					$msz['message'] = "Show successfully";
				} else {
					$msz['message'] = "faild to show";
				}
			} else {
				$fetch = mysqli_query($this->conn, "SELECT `date` FROM `add_event` WHERE `user_id`='$user_id' group by `date`");
				while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
					$fetch1 = mysqli_query($this->conn, "SELECT * FROM `add_event` where `date`='" . $fetch_cate['date'] . "'");
					$deep2 = array();
					while ($fetch_cate1 = mysqli_fetch_assoc($fetch1)) {
						$prod1 = $fetch_cate1;
						array_push($deep2, $prod1);
						$fetch_cate['data'] = $deep2;
						$fetch_cate['path'] = $this->path;
					}
					array_push($deep1, $fetch_cate);
				}
				if ($fetch) {
					$msz['data'] = $deep1;
					$msz['message'] = "Show successfully";
				} else {
					$msz['message'] = "faild to show";
				}
			}
		}
		echo json_encode($msz);
	}
	
	//---------------------------------------------edit_event--------------------------------------
	function edit_event()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_event` WHERE `id`='$id'"));
			if ($user_id == "") {
				$user_id = $select['user_id'];
			}
			if ($title == "") {
				$title = $select['title'];
			}
			if ($description == "") {
				$description = $select['description'];
			}
			if ($date == "") {
				$date = $select['date'];
			}
			if ($time == "") {
				$time = $select['time'];
			}
			if ($type == "") {
				$type = $select['type'];
			}
			if ($meeting_type == "") {
				$meeting_type = $select['meeting_type'];
			}
			if ($meeting_place == "") {
				$meeting_place = $select['meeting_place'];
			}
			if ($birthday_parson == "") {
				$birthday_parson = $select['birthday_parson'];
			}
			if ($place == "") {
				$place = $select['place'];
			}
			if ($remind_me == "") {
				$remind_me = $select['remind_me'];
			}
			$update_note = mysqli_query($this->conn, "UPDATE `add_event` SET `user_id`='$user_id',`title`='$title',`description`='$description',`date`='$date',`time`='$time',
			`type`='$type',`meeting_type`='$meeting_type',`meeting_place`='$meeting_place',`birthday_parson`='$birthday_parson',`place`='$place',`remind_me`='$remind_me' WHERE `id`='$id'");
		}
		if ($update_note) {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_event` WHERE `id`='$id'"));
			$select['path'] = $this->path;
			$select['message'] = "Event Edit Successfully";
		} else {
			$select['message'] = "Failed";
		}
		echo json_encode($select, JSON_UNESCAPED_SLASHES);
	}
	//--------------------------------add_event_type----------------------------
	
	function add_event_type()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_event` WHERE `id`='$id'"));
			if ($user_id == "") {
				$user_id = $select['user_id'];
			}
			if ($title == "") {
				$title = $select['title'];
			}
			if ($description == "") {
				$description = $select['description'];
			}
			if ($date == "") {
				$date = $select['date'];
			}
			if ($time == "") {
				$time = $select['time'];
			}
			if ($type == "") {
				$type = $select['type'];
			}
			if ($meeting_type == "") {
				$meeting_type = $select['meeting_type'];
			}
			if ($meeting_place == "") {
				$meeting_place = $select['meeting_place'];
			}
			if ($birthday_parson == "") {
				$birthday_parson = $select['birthday_parson'];
			}
			if ($place == "") {
				$place = $select['place'];
			}
			if ($remind_me == "") {
				$remind_me = $select['remind_me'];
			}
			$update_note = mysqli_query($this->conn, "UPDATE `add_event` SET `user_id`='$user_id',`title`='$title',`description`='$description',`date`='$date',`time`='$time',
			`type`='$type',`meeting_type`='$meeting_type',`meeting_place`='$meeting_place',`birthday_parson`='$birthday_parson',`place`='$place',`remind_me`='$remind_me' WHERE `id`='$id'");
		}
		if ($update_note) {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_event` WHERE `id`='$id'"));
			$select['path'] = $this->path;
			$select['message'] = "Event Type Add Successfully";
		} else {
			$select['message'] = "Failed";
		}
		echo json_encode($select, JSON_UNESCAPED_SLASHES);
	}
	
	//---------------------------------add_event--------------------------------
	
	
	function add_event()
	{
	
		extract($_POST);
		$valid = array(
			'user_id' => $user_id,
			'title' => $title,
			'description' => $description,
			'date' => $date,
			'time' => $time,
			'type' => $type,
			'remind_me' => $remind_me,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
		
			$strtotime = strtotime('now');
			$booking = mysqli_query($this->conn, "INSERT INTO `add_event` SET `user_id`='$user_id',`title`='$title',`description`='$description',`date`='$date',`time`='$time',
			`type`='$type',`meeting_type`='$meeting_type',`meeting_place`='$meeting_place',`birthday_parson`='$birthday_parson',`place`='$place',`remind_me`='$remind_me',`connection_id`='$connection_id',`connecion_type`='$connection_type'");
			$insert_id = mysqli_insert_id($this->conn);
			if ($insert_id != '') {
				$vals = mysqli_query($this->conn, "SELECT * FROM `add_event` where id='$insert_id'");
				$msz = mysqli_fetch_assoc($vals);
				$msz['message'] = "Event Add Successfully ";
			} else {
				$msz['message'] = "FAILD";
			}
		}
		echo json_encode($msz);
	}

// 	function add_event_new()
// 	{
	
// 		extract($_POST);
// 		$valid = array(
// 			'user_id' => $user_id,
// 			'title' => $title,
// 			'description' => $description,
// 			'date' => $date,
// 			'time' => $time,
// 			'type' => $type,
// 		);
// 		$valid_check = array_search(null, $valid);
// 		if ($valid_check) {
// 			$msz['message'] = $valid_check . " is empty";
// 			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
// 			die;
// 		} else {
		
// 			$strtotime = strtotime('now');
// 			$booking = mysqli_query($this->conn, "INSERT INTO `add_event` SET `user_id`='$user_id',`title`='$title',`description`='$description',`date`='$date',`time`='$time',
// 			`type`='$type',`meeting_type`='$meeting_type',`meeting_place`='$meeting_place',`birthday_parson`='$birthday_parson',`place`='$place',`remind_me`='$remind_me',`connection_id`='$connection_id',`connecion_type`='$connection_type'");
// 			$insert_id = mysqli_insert_id($this->conn);
// 			if ($insert_id != '') {
// 				$vals = mysqli_query($this->conn, "SELECT * FROM `add_event` where id='$insert_id'");
// 				$msz = mysqli_fetch_assoc($vals);
// 				$msz['message'] = "Event Add Successfully ";
// 			} else {
// 				$msz['message'] = "FAILD";
// 			}
// 		}
// 		echo json_encode($msz);
// 	}
	
	//-------------------------------------------multipal_delete_task---------------------------
	function multipal_delete_task()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(
			null,
			$valid
		);
		if ($valid_check) {
			$message['message'] = $valid_check . " is empty";
		} else {
			$ids = array();
			$ids = explode(",", $id);
			foreach ($ids as $key) {
				$booking = mysqli_query($this->conn, "Delete from `add_task` where `id` ='$key'");
				if ($booking) {
					$message['message'] = " Successfully Deleted";
				} else {
					$message['message'] = "FAILD";
				}
			}
		}
		echo json_encode($message);
	}
	//-------------------------------------------task_delete---------------------------
	function task_delete()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(
			null,
			$valid
		);
		if ($valid_check) {
			$message['message'] = $valid_check . " is empty";
		} else {
			$booking = mysqli_query($this->conn, "Delete from `add_task` where id =$id");
			if ($booking) {
				$message['message'] = " Successfully Deleted";
			} else {
				$message['message'] = "FAILD";
			}
		}
		echo json_encode($message);
	}
	//--------------------------------------edit_task-------------------------------
	function edit_task()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_task` WHERE `id`='$id'"));
			if ($summary == "") {
				$summary = $select['summary'];
			}
			if ($date == "") {
				$date = $select['date'];
			}
			if ($time == "") {
				$time = $select['time'];
			}
			if ($color == "") {
				$color = $select['color'];
			}
			if ($type == "") {
				$type = $select['type'];
			}
			if ($reminder == "") {
				$reminder = $select['reminder'];
			}
			$strtotime = strtotime('now');
			$update_note = mysqli_query($this->conn, "UPDATE `add_task` SET `summary`='$summary',`date`='$date',`time`='$time',`color`='$color',`type`='$type',`reminder`='$reminder',`strtotime`='$strtotime' WHERE `id`='$id'");
		}
		if ($update_note) {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_task` WHERE `id`='$id'"));
			$select['path'] = $this->path;
			$select['message'] = "Update Task Successfully";
		} else {
			$select['message'] = "Failed";
		}
		echo json_encode($select, JSON_UNESCAPED_SLASHES);
	}
	//-------------------------------------show_task--------------------------------

function show_task()
{
    extract($_POST);
    $validation = ['user_id' => $user_id];
    $valid_check = array_search(null, $validation);
    if ($valid_check) {
        $msz['message'] = $valid_check . " is Empty";
        echo json_encode($msz);
        die();
    } else {
        $deep = array();
        date_default_timezone_set('Asia/Kolkata'); 
        $current_date = date("Y-m-d"); 
        $current_time = date("h:i A"); 

        $fetch = mysqli_query($this->conn, "SELECT * FROM `add_task` WHERE `user_id`='$user_id' ORDER BY `date` DESC, `time` DESC");

        while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
            $task_date = $fetch_cate['date'];
            $task_time = $fetch_cate['time'];
            $task_type = $fetch_cate['type'];

            $task_datetime = strtotime($task_date . " " . $task_time);
            $current_datetime = strtotime($current_date . " " . $current_time);

            if ($task_datetime <= $current_datetime) {
                $new_date = null;

                if ($task_type == 'Daily') {
                    $new_date = date("Y-m-d", strtotime($task_date . ' +1 day'));
                } elseif ($task_type == 'Weekly') {
                    $new_date = date("Y-m-d", strtotime($task_date . ' +1 week'));
                } elseif ($task_type == 'Monthly') {
                    $new_date = date("Y-m-d", strtotime($task_date . ' +1 month'));
                }

                if (!is_null($new_date)) {
                    $old_date = $fetch_cate['date'];
                    $task_id = $fetch_cate['id'];

                    $update_query = "UPDATE `add_task` SET `date`='$new_date', `old_date`='$old_date' WHERE `id`='$task_id'";
                    $update_result = mysqli_query($this->conn, $update_query);

                    if ($update_result) {
                        // Update local array also
                        $fetch_cate['date'] = $new_date;
                        $fetch_cate['old_date'] = $old_date;
                    } else {
                        error_log("Update Failed for ID $task_id: " . mysqli_error($this->conn));
                    }
                }
            }

            // Convert date to d-m-Y format before sending it to the response
            $fetch_cate['date'] = date("d-m-Y", strtotime($fetch_cate['date']));
            $fetch_cate['path'] = $this->path;
            array_push($deep, $fetch_cate);
        }

        if ($fetch) {
            $msz['data'] = $deep;
            $msz['message'] = "Task showing Is Successfully";
        } else {
            $msz['message'] = "Failed To Show";
        }
        echo json_encode($msz);
    }
}


	
	//---------------------------------add_task--------------------------------
	function add_task()
	{
		extract($_POST);
		$valid = array(
			'user_id' => $user_id,
			'summary' => $summary,
			'date' => $date,
			'time' => $time,
			'color' => $color,
			'type' => $type,
			'reminder' => $reminder,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$strtotime = strtotime('now');
			$booking = mysqli_query($this->conn, "INSERT INTO `add_task` SET `user_id`='$user_id',`summary`='$summary',`date`='$date',
			`time`='$time',`color`='$color',`type`='$type',`reminder`='$reminder',`strtotime`='$strtotime'");
			$insert_id = mysqli_insert_id($this->conn);
			if ($insert_id != '') {
				$vals = mysqli_query($this->conn, "SELECT * FROM `add_task` where id='$insert_id'");
				$msz = mysqli_fetch_assoc($vals);
				$msz['message'] = "Task Add Successfully ";
			} else {
				$msz['message'] = "FAILD";
			}
		}
		echo json_encode($msz);
	}
	//--------------------------------------notes_move-------------------------------
	function notes_move()
	{
		extract($_POST);
		$valid = array(
			'user_id' => $user_id,
			'note_id' => $note_id,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_note` WHERE `id`='$note_id' and `user_id`='$user_id'"));
			if ($folder_id == "") {
				$folder_id = $select['folder_id'];
			}
			$update_note = mysqli_query($this->conn, "UPDATE `add_note` SET `folder_id`='$folder_id' WHERE `id`='$note_id' and `user_id`='$user_id'");
		}
		if ($update_note) {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_note` WHERE `id`='$note_id' and `user_id`='$user_id'"));
			$select['path'] = $this->path;
			$select['message'] = "Note Move Successfully";
		} else {
			$select['message'] = "Failed";
		}
		echo json_encode($select, JSON_UNESCAPED_SLASHES);
	}
	//-------------------------------------------multipal_delete_note---------------------------
	function multipal_delete_note()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(
			null,
			$valid
		);
		if ($valid_check) {
			$message['message'] = $valid_check . " is empty";
		} else {
			$ids = array();
			$ids = explode(",", $id);
			foreach ($ids as $key) {
				$booking = mysqli_query($this->conn, "Delete from `add_note` where `id` ='$key'");
				if ($booking) {
					$message['message'] = "  Successfully Deleted";
				} else {
					$message['message'] = "FAILD";
				}
			}
		}
		echo json_encode($message);
	}
	//-------------------------------------------delete_note---------------------------
	
	
    function delete_note()
    {
        // Ensure $id is set before using it
        $id = isset($_POST['id']) ? $_POST['id'] : null;
    
        // Validate that ID is provided
        if (empty($id)) {
            $message['message'] = "id is empty";
        } else {
            // Execute delete query
            $booking = mysqli_query($this->conn, "DELETE FROM `add_note` WHERE id = '$id'");
    
            // Check if deletion was successful
            if ($booking) {
                $message['message'] = "Successfully Deleted";
            } else {
                $message['message'] = "FAILED";
            }
        }
    
        // Return response in JSON format
        echo json_encode($message);
    }
	
	
	
	//-----------------------------------show_note----------------------------------
	function show_note()
	{
		extract($_POST);
		$validation = [
			'user_id' => $user_id,
			'folder_id' => $folder_id
		];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$message['result'] = $valid_check . " is Empty";
			echo json_encode($message);
			die();
		} else {
			$deep = array();
			date_default_timezone_set("Asia/Calcutta");
			$fetch = mysqli_query($this->conn, "SELECT * FROM `add_note` where `user_id`='$user_id' and `folder_id`='$folder_id'");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$fetch_cate['date'] = date('d-m-Y', $fetch_cate['strtotime']);
				$fetch_cate['time'] = date('h:i a', $fetch_cate['strtotime']);
				$fetch_cate['path'] = $this->path;
				array_push($deep, $fetch_cate);
			}
			if ($fetch) {
				$msz['data'] = $deep;
				$msz['message'] = " Note's Showing Is Successfully";
			} else {
				$msz['message'] = "Faild To Show ";
			}
			echo json_encode($msz);
		}
	}
	//--------------------------------------edit_note-------------------------------


    function edit_note()
    {
        // Ensure 'id' exists before using it
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            echo json_encode(["message" => "id is empty"], JSON_UNESCAPED_UNICODE);
            die;
        }
    
        extract($_POST); // Extracting $_POST values
    
        // Initialize other variables to prevent "Undefined variable" warnings
        $folder_id = isset($folder_id) ? $folder_id : "";
        $new_folder_id = isset($new_folder_id) ? $new_folder_id : "";
        $title = isset($title) ? $title : "";
        $description = isset($description) ? $description : "";
        $date = isset($date) ? $date : date('Y-m-d'); // Default to current date
        $time = isset($time) ? $time : date('H:i:s'); // Default to current time
    
        // Fetch existing note details
        $select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_note` WHERE `id`='$id'"));
    
        if (!$select) {
            echo json_encode(["message" => "Note not found"], JSON_UNESCAPED_UNICODE);
            die;
        }
    
        // Preserve existing values if not provided
        if (empty($folder_id)) {
            $folder_id = $select['folder_id'];
        }
        if (empty($new_folder_id)) {
            $new_folder_id = $select['new_folder_id'];
        }
        if (empty($title)) {
            $title = $select['title'];
        }
        if (empty($description)) {
            $description = $select['description'];
        }
    
        $strtotime = strtotime('now');
    
        $update_note = mysqli_query($this->conn, "UPDATE `add_note` SET 
            `folder_id`='$folder_id',
            `new_folder_id`='$new_folder_id', 
            `title`='$title',
            `description`='$description',
            `date`='$date',
            `time`='$time',
            `strtotime`='$strtotime' 
            WHERE `id`='$id'");
    
        if ($update_note) {
            $select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_note` WHERE `id`='$id'"));
            $select['path'] = $this->path;
            $select['message'] = "Update Note Successfully";
        } else {
            $select['message'] = "Failed to update note";
        }
    
        echo json_encode($select, JSON_UNESCAPED_SLASHES);
    }



	//----------------------------------add_note------------------------------------
	
function add_note()
{
    // Ensure $_POST variables exist before using them
    $user_id = $_POST['user_id'] ?? null;
    $folder_id = $_POST['folder_id'] ?? null;
    $title = $_POST['title'] ?? null;
    $description = $_POST['description'] ?? null;
    $new_folder_id = $_POST['new_folder_id'] ?? null;
    $date = $_POST['date'] ?? date('Y-m-d'); // Default to today's date if not provided
    $time = $_POST['time'] ?? date('H:i:s'); // Default to current time if not provided

    $valid = array(
        'user_id' => $user_id,
        'folder_id' => $folder_id,
        'title' => $title,
        'description' => $description,
    );

    // Validate required fields
    if (in_array(null, $valid, true)) {
        $msz['message'] = "One or more required fields are empty";
        echo json_encode($msz, JSON_UNESCAPED_UNICODE);
        die;
    }

    $strtotime = strtotime('now');
    
    // Insert into database
    $booking = mysqli_query($this->conn, "INSERT INTO `add_note` (`user_id`, `folder_id`, `new_folder_id`, `title`, `description`, `date`, `time`, `strtotime`) 
    VALUES ('$user_id', '$folder_id', '$new_folder_id', '$title', '$description', '$date', '$time', '$strtotime')");

    $insert_id = mysqli_insert_id($this->conn);

    if ($insert_id) {
        $vals = mysqli_query($this->conn, "SELECT * FROM `add_note` WHERE id='$insert_id'");
        $msz = mysqli_fetch_assoc($vals);
        $msz['message'] = "Note Added Successfully";
    } else {
        $msz['message'] = "FAILED";
    }

    echo json_encode($msz);
}


	//-------------------------------------show_static_folder--------------------------------

function show_static_folder()
{
    extract($_POST);
    $valid = array();
    $valid_check = array_search(null, $valid);
    if ($valid_check) {
        $msz['message'] = $valid_check . " is empty";
    } else {
        $deep = array();
        $fetch = mysqli_query($this->conn, "SELECT * FROM `folders`");

        while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
            // Agar image column empty nahi hai to full path banao
            if (!empty($fetch_cate['image'])) {
                $fetch_cate['path'] = $this->path . $fetch_cate['image'];
            } else {
                $fetch_cate['path'] = $this->path . "default.png"; // Default image if image is empty
            }

            // Count notes in each folder
            $note = mysqli_query($this->conn, "SELECT * FROM `add_note` WHERE `new_folder_id`='" . $fetch_cate['id'] . "'");
            $count = mysqli_num_rows($note);
            $fetch_cate['note_count'] = $count;

            array_push($deep, $fetch_cate);
        }

        if ($fetch) {
            $msz['data'] = $deep;
            $msz['message'] = "Static Folder showing successfully";
        } else {
            $msz['message'] = "Failed to show";
        }
        echo json_encode($msz);
    }
}

	//-------------------------------------------delete_folder---------------------------
	function delete_folder()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(
			null,
			$valid
		);
		if ($valid_check) {
			$message['message'] = $valid_check . " is empty";
		} else {
			$booking = mysqli_query($this->conn, "Delete from `add_folder` where id =$id");
			if ($booking) {
				$message['message'] = " Successfully Deleted";
			} else {
				$message['message'] = "FAILD";
			}
		}
		echo json_encode($message);
	}

	//-----------------------------------show_folder----------------------------------
	function show_folder()
	{
		extract($_POST);
		$validation = ['user_id' => $user_id,];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is Empty";
			echo json_encode($msz);
			die();
		} else {
			$deep = array();
			$fetch = mysqli_query($this->conn, "SELECT * FROM `add_folder` where `user_id`='$user_id'");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$note = mysqli_query($this->conn, "SELECT * FROM `add_note` where `folder_id`='" . $fetch_cate['id'] . "'");
				$count = mysqli_num_rows($note);
				$fetch_cate['note_count'] = $count;
				$fetch_cate['path'] = $this->path;
				array_push($deep, $fetch_cate);
			}
			if ($fetch) {
				$msz['data'] = $deep;
				$msz['message'] = " Folder Showing Is Successfully";
			} else {
				$msz['message'] = "Faild To Show ";
			}
			echo json_encode($msz);
		}
	}
	//-----------------------------------add_folder----------------------------------
	function add_folder()
	{
		extract($_POST);
		$valid = array(
			'user_id' => $user_id,
			'folder_name' => $folder_name,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$strtotime = strtotime('now');
			$booking = mysqli_query($this->conn, "INSERT INTO `add_folder` SET `user_id`='$user_id',`folder_name`='$folder_name',`strtotime`='$strtotime'");
			$insert_id = mysqli_insert_id($this->conn);
			if ($insert_id != '') {
				$vals = mysqli_query($this->conn, "SELECT * FROM `add_folder` where id='$insert_id'");
				$msz = mysqli_fetch_assoc($vals);
				$msz['message'] = "Folder Add Successfully ";
			} else {
				$msz['message'] = "FAILD";
			}
		}
		echo json_encode($msz);
	}
	//----------------------------------------------search_meeting--------------------------
	function search_meeting()
	{
		extract($_POST);
		$validation = [];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is Empty";
			echo json_encode($msz);
			die();
		} else {
			$deep = array();
			$fetch = mysqli_query($this->conn, "SELECT * FROM `add_meeting` ");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$fetch_cate['path'] = $this->path;
				array_push($deep, $fetch_cate);
			}
			if ($fetch) {
				$msz['data'] = $deep;
				$msz['message'] = "Search Meeting showing Is Successfully";
			} else {
				$msz['message'] = "Faild To Show ";
			}
			echo json_encode($msz);
		}
	}
	//-------------------------------------------multipal_delete_meeting---------------------------
	function multipal_delete_meeting()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(
			null,
			$valid
		);
		if ($valid_check) {
			$message['message'] = $valid_check . " is empty";
		} else {
			$ids = array();
			$ids = explode(",", $id);
			foreach ($ids as $key) {
				$booking = mysqli_query($this->conn, "Delete from `add_meeting` where `id` ='$key'");
				if ($booking) {
					$message['message'] = "  Successfully Deleted";
				} else {
					$message['message'] = "FAILD";
				}
			}
		}
		echo json_encode($message);
	}
	//---------------------------------------------show_meeting------------------------
	function show_meeting()
	{
		extract($_POST);
		$deep = array();
		$deep1 = array();
		$fetch = mysqli_query($this->conn, "SELECT `date` FROM `add_meeting` group by `date`");
		while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
			$fetch1 = mysqli_query($this->conn, "SELECT * FROM `add_meeting` where `date`='" . $fetch_cate['date'] . "'");
			$deep2 = array();
			while ($fetch_cate1 = mysqli_fetch_assoc($fetch1)) {
				$prod1 = $fetch_cate1;
				array_push($deep2, $prod1);
				$fetch_cate['data'] = $deep2;
				$fetch_cate['path'] = $this->path;
			}
			array_push($deep1, $fetch_cate);
		}
		if ($fetch) {
			$msz['data'] = $deep1;
			$msz['message'] = "Show successfully";
		} else {
			$msz['message'] = "faild to show";
		}
		echo json_encode($msz);
	}
	//---------------------------------------add_meeting---------------------------
	function add_meeting()
	{
		extract($_POST);
		$valid = array(
			'user_id' => $user_id,
			'select_type' => $select_type,
			'date' => $date,
			'leader_name' => $leader_name,
			'title' => $title,
			'time' => $time,
			'reminder' => $reminder
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$strtotime = strtotime('now');
			$booking = mysqli_query($this->conn, "INSERT INTO `add_meeting` SET `user_id`='$user_id',`select_type`='$select_type',`date`='$date',
                             `leader_name`='$leader_name',`title`='$title',`time`='$time',`reminder`='$reminder',`strtotime`='$strtotime'");
			$insert_id = mysqli_insert_id($this->conn);
			if ($insert_id != '') {
				$vals = mysqli_query($this->conn, "SELECT * FROM `add_meeting` where id='$insert_id'");
				$msz = mysqli_fetch_assoc($vals);
				$msz['message'] = "Meeting Add Successfully ";
			} else {
				$msz['message'] = "FAILD";
			}
		}
		echo json_encode($msz);
	}
	//-------------------------------------video_detail--------------------
	function video_detail()
	{
		extract($_POST);
		$validation = ['id' => $id,];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is Empty";
			echo json_encode($msz);
			die();
		} else {
			// $deep = array();
			$fetch = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `add_video` WHERE `id`='$id'"));

			if ($fetch) {
				$msz['data'] = $fetch;
				$msz['message'] = " Video Deatail Showing Is Successfully";
			} else {
				$msz['message'] = "Faild To Show ";
			}
			echo json_encode($msz);
		}
	}
	//-------------------------------------show_video--------------------
  
    function show_video()
    {
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $language_name = isset($_GET['language_key']) ? trim($_GET['language_key']) : '';
    
        $deep = array();
        $conditions = [];
    
        // Sanitize inputs
        $keyword = mysqli_real_escape_string($this->conn, $keyword);
        $language_name = mysqli_real_escape_string($this->conn, $language_name);
    
        // Base SQL with JOIN
        $sql = "SELECT add_video.*, 
                list.value AS language_name, 
                tags.tags AS tag_name
                FROM add_video
                LEFT JOIN list ON add_video.language_key = list.id
                LEFT JOIN tags ON add_video.tag_id = tags.id
                WHERE 1=1"; 
    
        // Keyword condition
        if (!empty($keyword)) {
            $conditions[] = "add_video.video_name LIKE '%$keyword%'";
        }
    
        // Language condition
        if (!empty($language_name)) {
            $lang_sql = "SELECT id FROM list WHERE value = '$language_name' LIMIT 1";
            $lang_res = mysqli_query($this->conn, $lang_sql);
    
            if ($lang_res && mysqli_num_rows($lang_res) > 0) {
                $lang_row = mysqli_fetch_assoc($lang_res);
                $language_id = $lang_row['id'];
                $conditions[] = "add_video.language_key = '$language_id'";
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => "Language not found"
                ]);
                return;
            }
        }
    
        // Add conditions to query
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }
    
        $sql .= " ORDER BY add_video.id DESC"; // Optional sorting
    
        // Execute query
        $fetch = mysqli_query($this->conn, $sql);
    
        if ($fetch && mysqli_num_rows($fetch) > 0) {
            while ($book = mysqli_fetch_assoc($fetch)) {
                $book['path'] = $this->path;
            
                $deep[] = $book;
            }
    
            echo json_encode([
                'status' => true,
                'message' => "Video retrieved successfully",
                'data' => $deep
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => "No Video found"
            ]);
        }
    }



	//-------------------------------------show_free_video--------------------
  
    function show_free_video()
    {
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $language_name = isset($_GET['language_key']) ? trim($_GET['language_key']) : '';
    
        $deep = array();
        $conditions = [];
    
        // Sanitize inputs
        $keyword = mysqli_real_escape_string($this->conn, $keyword);
        $language_name = mysqli_real_escape_string($this->conn, $language_name);
    
        // Base SQL with JOIN
        $sql = "SELECT add_free_videos.*, 
                list.value AS language_name, 
                tags.tags AS tag_name
                FROM add_free_videos
                LEFT JOIN list ON add_free_videos.language_key = list.id
                LEFT JOIN tags ON add_free_videos.tag_id = tags.id
                WHERE 1=1"; 
    
        // Keyword condition
        if (!empty($keyword)) {
            $conditions[] = "add_free_videos.name LIKE '%$keyword%'";
        }
    
        // Language condition
        if (!empty($language_name)) {
            $lang_sql = "SELECT id FROM list WHERE value = '$language_name' LIMIT 1";
            $lang_res = mysqli_query($this->conn, $lang_sql);
    
            if ($lang_res && mysqli_num_rows($lang_res) > 0) {
                $lang_row = mysqli_fetch_assoc($lang_res);
                $language_id = $lang_row['id'];
                $conditions[] = "add_free_videos.language_key = '$language_id'";
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => "Language not found"
                ]);
                return;
            }
        }
    
        // Add conditions to query
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }
    
        $sql .= " ORDER BY add_free_videos.id DESC"; // Optional sorting
    
        // Execute query
        $fetch = mysqli_query($this->conn, $sql);
    
        if ($fetch && mysqli_num_rows($fetch) > 0) {
            while ($book = mysqli_fetch_assoc($fetch)) {
                $book['path'] = $this->path;
            
                $deep[] = $book;
            }
    
            echo json_encode([
                'status' => true,
                'message' => "data retrieved successfully",
                'data' => $deep
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => "No data found"
            ]);
        }
    }


	//-------------------------------------get_video_names--------------------


    function get_video_names()
    {
        extract($_POST); 
    
        $msz = [];
        $video_list = [];
        $conditions = [];
    
        if (!empty($id)) {
            $conditions[] = "`id` = '" . mysqli_real_escape_string($this->conn, $id) . "'";
        }
    
        if (!empty($video_name)) {
            $conditions[] = "`video_name` LIKE '%" . mysqli_real_escape_string($this->conn, $video_name) . "%'";
        }
    
        $query = "SELECT DISTINCT `id`, `video_name` FROM `add_video`";
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
    
        $fetch = mysqli_query($this->conn, $query);
    
        if ($fetch) {
            while ($row = mysqli_fetch_assoc($fetch)) {
                $video_list[] = [
                    'id' => $row['id'],
                    'video_name' => $row['video_name']
                ];
            }
    
            if (!empty($video_list)) {
                $msz['status'] = true;
                $msz['data'] = $video_list;
                $msz['message'] = "Video names retrieved successfully";
            } else {
                $msz['status'] = false;
                $msz['message'] = "No video names found for the given filters";
            }
        } else {
            $msz['status'] = false; 
            $msz['message'] = "Failed to execute query";
        }
    
        echo json_encode($msz);
    }

	//-------------------------------------ebook_detail--------------------
	function ebook_detail()
	{
		extract($_POST);
		$valid = array();
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
		} else {
			$deep = array();
			$fetch = mysqli_query($this->conn, "SELECT * FROM `ebook`");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$book = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `book` WHERE `id`='" . $fetch_cate['book_id'] . "'"));
				$msz['book'] = $book;
				$fetch_cate['path'] = $this->path;
				array_push($deep, $fetch_cate);
			}
			if ($fetch) {
				$msz['data'] = $deep;
				$msz['message'] = " E-Book Showing Is Successfully";
			} else {
				$msz['message'] = "Faild To Show ";
			}
			echo json_encode($msz);
		}
	}
	//-------------------------------------New_edit_book--------------------
	function New_edit_book()
	{
		extract($_POST);
		$valid = array();
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
		} else {
			$deep = array();
			$fetch = mysqli_query($this->conn, "SELECT * FROM `book` ORDER BY `id` DESC");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$tags = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `tags` WHERE `id`='" . $fetch_cate['tag_id'] . "'"));
				$fetch_cate['tag_id'] = $tags['tags'];
				$fetch_cate['path'] = $this->path;
				array_push($deep, $fetch_cate);
			}
			if ($fetch) {
				$msz['data'] = $deep;
				$msz['message'] = "Book Showing Is Successfully";
			} else {
				$msz['message'] = "Faild To Show ";
			}
			echo json_encode($msz);
		}
	}
	//-----------------------------------------show_tags----------------------------
	function show_tags()
	{
		extract($_POST);
		$valid = array();
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$deep = array();
			$fetch = mysqli_query($this->conn, "SELECT * FROM `tags`");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$fetch_cate['path'] = $this->path;
				array_push($deep, $fetch_cate);
			}
			if ($fetch) {
				$msz['data'] = $deep;
				$msz['message'] = "Tags Showing Is Successfully";
			} else {
				$msz['message'] = "Faild To Show ";
			}
			echo json_encode($msz);
		}
	}
	//-------------------------------------show_book--------------------

    function show_book()
    {
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $language_name = isset($_GET['language_key']) ? trim($_GET['language_key']) : '';
    
        $deep = array();
        $conditions = [];
    
        // Sanitize inputs
        $keyword = mysqli_real_escape_string($this->conn, $keyword);
        $language_name = mysqli_real_escape_string($this->conn, $language_name);
    
        // Base SQL with JOIN
        $sql = "SELECT book.*, list.value AS language_name FROM book 
                LEFT JOIN list ON book.language_key = list.id 
                WHERE 1=1"; // 🛠️ THIS LINE IS ESSENTIAL
    
        // Keyword condition
        if (!empty($keyword)) {
            $conditions[] = "book.book_name LIKE '%$keyword%'";
        }
    
        // Language condition
        if (!empty($language_name)) {
            $lang_sql = "SELECT id FROM list WHERE value = '$language_name' LIMIT 1";
            $lang_res = mysqli_query($this->conn, $lang_sql);
    
            if ($lang_res && mysqli_num_rows($lang_res) > 0) {
                $lang_row = mysqli_fetch_assoc($lang_res);
                $language_id = $lang_row['id'];
                $conditions[] = "book.language_key = '$language_id'";
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => "Language not found"
                ]);
                return;
            }
        }
    
        // Add conditions to query
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }
    
        $sql .= " ORDER BY book.id DESC"; // Optional sorting
    
        // Execute query
        $fetch = mysqli_query($this->conn, $sql);
    
        if ($fetch && mysqli_num_rows($fetch) > 0) {
            while ($book = mysqli_fetch_assoc($fetch)) {
                $book['path'] = $this->path;
                
               // Optional: remove null or empty string fields
                foreach ($book as $key => $value) {
                    if (is_null($value) || $value === '') {
                        unset($book[$key]);
                    }
                }
    
                $deep[] = $book;
            }
    
            echo json_encode([
                'status' => true,
                'message' => "Books retrieved successfully",
                'data' => $deep
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => "No books found"
            ]);
        }
    }

	//--------------------------------------------connection_delete-------------------------
	function connection_delete()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(
			null,
			$valid
		);
		if ($valid_check) {
			$message['message'] = $valid_check . " is empty";
		} else {
			$booking = mysqli_query($this->conn, "Delete from `connection` where id =$id");
			if ($booking) {
				$message['message'] = " Successfully Deleted";
			} else {
				$message['message'] = "FAILD";
			}
		}
		echo json_encode($message);
	}
	//-----------------------------------------edit_connection-----------------------------------------
	function edit_connection()
	{
		extract($_POST);
		$valid = array(
			'id' => $id,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `connection` WHERE `id`='$id'"));
			if ($name == "") {
				$name = $select['name'];
			}
			if ($mobile == "") {
				$mobile = $select['mobile'];
			}
			if ($occupation	 == "") {
				$occupation	 = $select['occupation	'];
			}
			if ($connection_type_id == "") {
				$connection_type_id = $select['connection_type_id'];
			}
			if ($time == "") {
				$time = $select['time'];
			}
			if ($date == "") {
				$date = $select['date'];
			}
			if ($remind == "") {
				$remind = $select['remind'];
			}
			if ($notes == "") {
				$notes = $select['notes'];
			}

			if ($meeting_required == "") {
				$meeting_required = $select['meeting_required'];
			}
			if ($meeting_count == "") {
				$meeting_count = $select['meeting_count'];
			}
			if ($notes == "") {
				$notes = $select['notes'];
			}
			if ($meeting_happen == "") {
				$meeting_happen = $select['meeting_happen'];
			}
			if ($list_type == "") {
				$list_type = $select['list_type'];
			}
			$update_note = mysqli_query($this->conn, "UPDATE `connection` SET `name`='$name',`mobile`='$mobile',`occupation`='$occupation',`connection_type_id`='$connection_type_id',
			`time`='$time',`date`='$date',`remind`='$remind',`meeting_required`='$meeting_required',`meeting_count`='$meeting_count',`list_type`='$list_type',`notes`='$notes',
			`meeting_happen`='$meeting_happen' WHERE `id`='$id'");
		}
		if ($update_note) {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `connection` WHERE `id`='$id'"));
			$select['path'] = $this->path;
			$select['message'] = "Update Connection Successfully";
		} else {
			$select['message'] = "Failed";
		}
		echo json_encode($select, JSON_UNESCAPED_SLASHES);
	}
	//-----------------------------------------show_connection----------------------------
	function show_connection()
	{
		extract($_POST);
		$valid = array(
			'user_id' => $user_id,
			'connection_type_id' => $connection_type_id,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$deep = array();
			$fetch = mysqli_query($this->conn, "SELECT * FROM `connection` where `user_id`='$user_id' and `connection_type_id`='$connection_type_id'");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$fetch_cate['path'] = $this->path;
				array_push($deep, $fetch_cate);
			}
			if ($fetch) {
				$msz['data'] = $deep;
				$msz['message'] = "Connection Showing Is Successfully";
			} else {
				$msz['message'] = "Faild To Show ";
			}
			echo json_encode($msz);
		}
	}
	//---------------------------------------------connection------------------------------------

	function connection()
	{
		extract($_POST);
		$valid = array(
			'user_id' => $user_id,
			'name' => $name,
			'mobile' => $mobile,
			'occupation' => $occupation,
			'connection_type_id' => $connection_type_id,
			'list_type' => $list_type,
			'meeting_required' => $meeting_required,
			'meeting_count' => $meeting_count,
		);

		// Notes is optional
		if (isset($notes)) {
			$valid['notes'] = $notes;
		}

		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		}

		// Initialize meeting-related fields as null by default
		$time_value = 'NULL';
		$date_value = 'NULL'; 
		$remind_value = 'NULL';

		// Only use meeting fields if meeting is required
		if ($meeting_required === 'Yes') {
			// Validate required meeting fields
			if (empty($time) || empty($date) || empty($remind)) {
				$msz['message'] = "time, date and remind fields are required when meeting is required";
				echo json_encode($msz, JSON_UNESCAPED_UNICODE);
				die;
			}
			$time_value = "'$time'";
			$date_value = "'$date'";
			$remind_value = "'$remind'";
		}

		$booking = mysqli_query($this->conn, "INSERT INTO `connection` SET 
			`user_id`='$user_id',
			`name`='$name',
			`mobile`='$mobile',
			`occupation`='$occupation',
			`connection_type_id`='$connection_type_id',
			`time`=" . $time_value . ",
			`date`=" . $date_value . ",
			`remind`=" . $remind_value . ",
			`notes`=" . (isset($notes) ? "'$notes'" : "NULL") . ",
			`list_type`='$list_type',
			`meeting_required`='$meeting_required',
			`meeting_count`='$meeting_count'");

		$insert_id = mysqli_insert_id($this->conn);
		if ($insert_id != '') {
			$vals = mysqli_query($this->conn, "SELECT * FROM `connection` where id='$insert_id'");
			$msz = mysqli_fetch_assoc($vals);
			$msz['message'] = "Connection Add Successfully";
		} else {
			$msz['message'] = "FAILED";
		}
		echo json_encode($msz);
	}

	//--------------------------------------------connection_type_delete-------------------------


    function connection_type_delete()
    {
        // Securely fetching POST data
        $id = $_POST['id'] ?? null;
    
        if (empty($id)) {
            $message['message'] = "id is empty";
            echo json_encode($message, JSON_UNESCAPED_UNICODE);
            die;
        }
    
        // Delete query
        $booking = mysqli_query($this->conn, "DELETE FROM `connection_type` WHERE id = '$id'");
    
        if ($booking) {
            $message['message'] = "Successfully Deleted";
        } else {
            $message['message'] = "FAILED";
        }
    
        echo json_encode($message);
    }

	//-----------------------------------------show_connection_type----------------------------

    function show_connection_type()
    {
        extract($_POST);
        $valid = array(
            'user_id' => $user_id,
        );
        $valid_check = array_search(null, $valid);
        if ($valid_check) {
            $msz['message'] = $valid_check . " is empty";
            echo json_encode($msz, JSON_UNESCAPED_UNICODE);
            die;
        } else {
            $deep = array();
            
            $total_conn_sql = "SELECT COUNT(*) AS total_connections FROM connection WHERE user_id = ? AND connection_type_id IS NOT NULL AND connection_type_id <> ''";
            $total_conn_stmt = $this->conn->prepare($total_conn_sql);
            $total_conn_stmt->bind_param("i", $user_id);
            $total_conn_stmt->execute();
            $total_conn_result = $total_conn_stmt->get_result();
            $total_connections = $total_conn_result->fetch_assoc()['total_connections'] ?? 0;
    
    
            $fetch = mysqli_query($this->conn, "SELECT * FROM `connection_type` ");
            while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
                $note = mysqli_query($this->conn, "SELECT * FROM `connection` where `connection_type_id`='" . $fetch_cate['id'] . "' and `user_id`='$user_id'");
                $count = mysqli_num_rows($note);
                $fetch_cate['connection_count'] = $count;
                $fetch_cate['path'] = $this->path . $fetch_cate['image'];
                array_push($deep, $fetch_cate);
            }
    
            if ($fetch) {   
                $msz['total_connections'] = $total_connections; 
                $msz['data'] = $deep;
                $msz['message'] = "Connection Type Showing Is Successfully";
            } else {
                $msz['message'] = "Failed To Show";
            }
            echo json_encode($msz);
        }
    }

	//---------------------------------------------add_connection_type------------------------------------
	function add_connection_type()
	{
		extract($_POST);
		$valid = array(

			'name' => $name,

		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$booking = mysqli_query($this->conn, "INSERT INTO `connection_type` SET `name`='$name'");
			$insert_id = mysqli_insert_id($this->conn);
			if ($insert_id != '') {
				$vals = mysqli_query($this->conn, "SELECT * FROM `connection_type` where id='$insert_id'");
				$msz = mysqli_fetch_assoc($vals);
				$msz['message'] = "Connection Type Add Successfully ";
			} else {
				$msz['message'] = "FAILD";
			}
		}
		echo json_encode($msz);
	}
	//---------------------------------------------contact_us------------------------------------
	function contact_us()
	{
		extract($_POST);
		$valid = array(
			'user_id' => $user_id,
			'name' => $name,
			'email' => $email,
			'subject' => $subject,
			'mobile' => $mobile,
			'message' => $message,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$booking = mysqli_query($this->conn, "INSERT INTO `contact_us` SET `user_id`='$user_id',`name`='$name',`mobile`='$mobile',
                             `email`='$email',`subject`='$subject',`message`='$message'");
			$insert_id = mysqli_insert_id($this->conn);
			if ($insert_id != '') {
				$vals = mysqli_query($this->conn, "SELECT * FROM `contact_us` where id='$insert_id'");
				$msz = mysqli_fetch_assoc($vals);
				$msz['massage'] = " Successfully Added";
			} else {
				$msz['massage'] = "FAILD";
			}
		}
		echo json_encode($msz);
	}
	//-------------------------------------privacy_policy---------------------------
	function privacy_policy()
	{
		extract($_POST);
		$valid = array();
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$fetch = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `privacy_policy`"));
			if ($fetch) {
				$fetch['message'] = "Showing privacy Policy Successfully";
			} else {
				$fetch['message'] = "Faild To Show ";
			}
		}
		echo json_encode($fetch);
	}
	//-------------------------------------terms_condition---------------------------
	function terms_condition()
	{
		header('Content-Type: text/html'); 
	
	    // Description column in the mysql table holds the html content
	    $query = "SELECT description FROM `terms_condition` LIMIT 1";
	    $result = mysqli_query($this->conn, $query);
	    $fetch = mysqli_fetch_assoc($result);
	
	    if ($fetch && !empty($fetch['description'])) {
	        // display termsn and conditions as plain html
	        echo $fetch['description']; 
	    } else {
	        // Fallback HTML error message
	        echo "<h1>Error</h1><p>Terms and conditions content not found.</p>";
	    }
	    
	    die;
	}
	//-----------------------------------------show About-----------------------------
	function About_us()
	{
		extract($_POST);
		$valid = array();
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$fetch = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `about`"));
			if ($fetch) {
				$fetch['message'] = "Showing About Successfully";
			} else {
				$fetch['message'] = "Faild To Show ";
			}
		}
		echo json_encode($fetch);
	}
	//-----------------------------------------show banner--------------------------
	function banner()
	{
		extract($_POST);
		$valid = array();
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
		} else {
			$deep = array();
			$fetch = mysqli_query($this->conn, "SELECT * FROM `banner`");
			while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
				$fetch_cate['path'] = $this->path;
				array_push($deep, $fetch_cate);
			}
			if ($fetch) {
				$msz['data'] = $deep;
				$msz['message'] = "Slider Showing Is Successfully";
			} else {
				$msz['message'] = "Faild To Show ";
			}
			echo json_encode($msz);
		}
	}
	//--------------------------------------name update---------------------------
	function update_name()
	{
		extract($_POST);
		$valid = array(
			'user_id' => $user_id,
		);
		$valid_check = array_search(null, $valid);
		if ($valid_check) {
			$msz['message'] = $valid_check . " is empty";
			echo json_encode($msz, JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `signup` WHERE `id`='$user_id'"));
			if ($username == "") {
				$username = $select['username'];
			}
			$update_profile = mysqli_query($this->conn, "UPDATE `signup` SET `username`='$username' WHERE `id`='$user_id'");
		}
		if ($update_profile) {
			$select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `signup` WHERE `id`='$user_id'"));
			$select['path'] = $this->path;
			$select['message'] = "Update Name Successfully";
		} else {
			$select['message'] = "Failed";
		}
		echo json_encode($select, JSON_UNESCAPED_SLASHES);
	}
	
	
    function get_profile()
    {
        extract($_GET); 
    
        $validation = ['user_id' => $user_id ?? null]; 
        $valid_check = array_search(null, $validation);
    
        if ($valid_check) {
            $message['result'] = $valid_check . " is Empty";
            echo json_encode($message);
            die();
        } else {
            $fetch = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `signup` WHERE `id` = '$user_id'"));
            if ($fetch) {
                $fetch['path'] = $this->path;
                $msz['data'] = $fetch;
                $msz['message'] = "Show profile is successfully";
            } else {
                $msz['message'] = "Failed to show";
            }
        }
    
        echo json_encode($msz);
    }

	//--------------------------------------show_profile---------------------------------------
	function show_profile()
	{
		extract($_POST);
		$validation = ['user_id' => $user_id,];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$message['result'] = $valid_check . " is Empty";
			echo json_encode($message);
			die();
		} else {
			$fetch = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `signup`  WHERE `id`='$user_id'"));
			if ($fetch) {
				$fetch['path'] = $this->path;
				$msz['data'] = $fetch;
				$msz['message'] = " Show profile is successfully";
			} else {
				$msz['message'] = "faild to show ";
			}
		}
		echo json_encode($msz);
	}
	//-----------------------------------profile update--------------------------
// 	function update_profile()
//     {
//         extract($_POST);
    
//         // Required fields validation
//         $requiredFields = [
//             'user_id'   => $user_id ?? null,
//             'username'  => $username ?? null,
//             'phone'     => $phone ?? null,
//         ];
    
//         $missing = array_search(null, $requiredFields);
//         if ($missing) {
//             echo json_encode(['message' => "$missing is empty"], JSON_UNESCAPED_UNICODE);
//             die;
//         }
    

//         // Handle file upload
//         $file = '';
//         if (!empty($_FILES['file']['name'])) {
//             $file = $_FILES['file']['name'];
//             $targetPath = '../images/' . $file;
//             move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);
//         }
    
//         // Build update query
//         $query = "UPDATE `signup` SET 
//                     `username` = '$username', 
//                     `phone` = '$phone'";
    
//         if (!empty($file)) {
//             $query .= ", `file` = '$file'";
//         }
    
//         $query .= " WHERE `id` = '$user_id'";
    
//         $update = mysqli_query($this->conn, $query);
    
//         if ($update) {
//             $select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `signup` WHERE `id`='$user_id'"));
//             $select['path'] = $this->path ?? ''; // Set path if defined in your class
//             $select['message'] = "Update Successfully";
//         } else {
//             $select['message'] = "Update Failed";
//         }
    
//         echo json_encode($select, JSON_UNESCAPED_SLASHES);
//     }

function update_profile()
{
    extract($_POST);

    // Required fields validation
    $requiredFields = [
        'user_id'   => $user_id ?? null,
        'username'  => $username ?? null,
        'phone'     => $phone ?? null,
    ];

    $missing = array_search(null, $requiredFields);
    if ($missing) {
        echo json_encode(['message' => "$missing is empty"], JSON_UNESCAPED_UNICODE);
        die;
    }

    // Handle file upload
    $file = '';
    if (!empty($_FILES['file']['name'])) {
        $file = $_FILES['file']['name'];
        $targetPath = '../images/' . $file;
        move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);
    }

    // Build update query for signup
    $query = "UPDATE `signup` SET 
                `username` = '$username', 
                `phone` = '$phone'";

    if (!empty($file)) {
        $query .= ", `file` = '$file'";
    }

    $query .= " WHERE `id` = '$user_id'";

    $update = mysqli_query($this->conn, $query);

    if ($update) {
        // Update add_sub_team member_name also
        $updateSubTeam = "UPDATE `add_sub_team` SET `member_name` = '$username' WHERE `user_id` = '$user_id'";
        mysqli_query($this->conn, $updateSubTeam);

        $select = mysqli_fetch_assoc(mysqli_query($this->conn, "SELECT * FROM `signup` WHERE `id`='$user_id'"));
        $select['path'] = $this->path ?? '';
        $select['message'] = "Update Successfully";
    } else {
        $select['message'] = "Update Failed";
    }

    echo json_encode($select, JSON_UNESCAPED_SLASHES);
}

	//----------------------------social_login-----------------------------------
	
	function social_login()
	{
		extract($_POST);
		$validation = ['auth_id' => $auth_id, 'provider' => $provider,];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$message['result'] = $valid_check . " is Empty";
			echo json_encode($message);
			die();
		}
		$sql = mysqli_query($this->conn, "select * from `signup` where auth_id='$auth_id' and provider='$provider'");
		$count = mysqli_num_rows($sql);
		if ($count > 0) {
			$message = mysqli_fetch_assoc(mysqli_query($this->conn, "select * from `signup` where `auth_id`='$auth_id' and `provider`='$provider'"));
			$message['result'] = "Login Successfull";
		} else {
			// 	$image = $_FILES['image']['name'];
			//    if (!empty($_FILES['image']['name']))
			// 	move_uploaded_file($_FILES['image']['tmp_name'], '../images/'. $image);
			$insert = mysqli_query($this->conn, "INSERT INTO `signup`(`auth_id`, `provider`, `username`, `email`, `file`) VALUES ('$auth_id','$provider','$username','$email','$file')");
			$insert_id = mysqli_insert_id($this->conn);
			if ($insert_id !== "") {
				$message = mysqli_fetch_assoc(mysqli_query($this->conn, "select * from `signup` where id='$insert_id'"));
				$message['result'] = "Login Successfull";
			} else {
				$message['result'] = "Failed";
			}
		}
		echo json_encode($message);
	}
	
	
	//---------------------------------sign_up----------------------------------

        function send_otp_email($email, $otp)
        {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'smartleaderflutter@gmail.com';  
                $mail->Password   = 'pbhp hvaw mpyo lilq';      
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;
        
                // Sender info
                $mail->setFrom('smartleaderflutter@gmail.com', 'Smartleader');
                $mail->addReplyTo('smartleaderflutter@gmail.com', 'Smartleader Support');
                $mail->addAddress($email);
        
                // Headers
                $mail->MessageID = "<" . uniqid() . "@gmail.com>";
                $mail->isHTML(true);
                $mail->Subject = 'Your Smartleader OTP Code';
        
                // HTML Body
                $mail->Body = "
                    <html>
                        <body>
                            <p>Hello,</p>
                            <p>Your <strong>Smartleader OTP</strong> is: <strong>$otp</strong></p>
                            <p>Please use this code to verify your email.</p>
                            <br>
                            <p>Thanks,<br>Smartleader Team</p>
                        </body>
                    </html>";
        
                // Plain Text fallback
                $mail->AltBody = "Your Smartleader OTP is: $otp. Please use this to verify your email.";
        
                $mail->send();
                return true;
            } catch (Exception $e) {
                return false;
            }
        }

    
    
    	
        // function user_signup()
        // {
        //     extract($_POST); 
        
        //     $validation = ['username' => $username, 'email' => $email, 'phone' => $phone];
        //     $valid_check = array_search(null, $validation);
        //     if ($valid_check) {
        //         $message['result'] = $valid_check . " is Empty";
        //         echo json_encode($message);
        //         die();
        //     }
        
        //     $check = mysqli_query($this->conn, "SELECT * FROM `signup` WHERE `email`='$email'");
        //     if (mysqli_num_rows($check) > 0) {
        //         $message['result'] = "Email already registered";
        //         echo json_encode($message);
        //         die();
        //     }
        
        //     $otp = rand(100000, 999999);
        
        //     if (!$this->send_otp_email($email, $otp)) {
        //         $message['result'] = "Failed to send OTP";
        //         echo json_encode($message);
        //         die();
        //     }
        
        //     $insert = mysqli_query($this->conn, "INSERT INTO `signup`(`username`, `email`, `phone`, `otp`, `created_at`) 
        //     VALUES ('$username','$email','$phone','$otp', NOW())");
        
        //     if ($insert) {
        //         $message['result'] = "OTP Sent to your Email";
        //     } else {
        //         $message['result'] = "Failed to initiate signup";
        //     }
        
        //     echo json_encode($message);
        // }

	
        function user_signup()
        {
            extract($_POST); 
        
            // Input validation
            $validation = ['username' => $username, 'email' => $email, 'phone' => $phone, 'password' => $password];
            $valid_check = array_search(null, $validation);
            if ($valid_check) {
                $message['result'] = $valid_check . " is Empty";
                echo json_encode($message);
                die();
            }
        
            // Email already exists check
            $check = mysqli_query($this->conn, "SELECT * FROM `signup` WHERE `email`='$email'");
            if (mysqli_num_rows($check) > 0) {
                $message['result'] = "Email already registered";
                echo json_encode($message);
                die();
            }
        
            // Generate OTP
            $otp = rand(100000, 999999);
        
            // Send OTP
            if (!$this->send_otp_email($email, $otp)) {
                $message['result'] = "Failed to send OTP";
                echo json_encode($message);
                die();
            }
        
            // ✅ Hash the password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
            // Insert user
            $insert = mysqli_query($this->conn, "INSERT INTO `signup`(`username`, `email`, `phone`, `otp`, `password`,`fcm_token`, `created_at`) 
            VALUES ('$username','$email','$phone','$otp','$hashed_password','$fcm_token', NOW())");
        
            if ($insert) {
                $message['result'] = "OTP Sent to your Email";
            } else {
                $message['result'] = "Failed to initiate signup";
            }
        
            echo json_encode($message);
        }

	
	
        function verify_otp()
        {
            extract($_POST); 
        
            if (empty($email) || empty($otp)) {
                echo json_encode(['result' => 'Email or OTP is missing']);
                return;
            }
        
            $email = trim($email);
            $otp = trim($otp);
        
            $check = mysqli_query($this->conn, "SELECT * FROM `signup` WHERE `email`='$email' AND `otp`='$otp'");
        
            if (mysqli_num_rows($check) > 0) {
                // Optional: mark as verified
                mysqli_query($this->conn, "UPDATE `signup` SET `is_verified`=1 WHERE `email`='$email'");
        
                echo json_encode(['result' => 'OTP Verified']);
            } else {
                echo json_encode(['result' => 'Invalid OTP or Email']);
            }
        }
        
	//---------------------------------forgot_password-------------------------
        
        function forgot_password_request()
        {
            extract($_POST);
        
            if (empty($email)) {
                echo json_encode(['result' => 'Email is required']);
                die();
            }
        
            // Check if email exists
            $check = mysqli_query($this->conn, "SELECT * FROM `signup` WHERE `email` = '$email'");
            if (mysqli_num_rows($check) == 0) {
                echo json_encode(['result' => 'Email not registered']);
                die();
            }
        
            // Generate OTP
            $otp = rand(100000, 999999);
        
            // Send OTP to email
            if (!$this->send_otp_email($email, $otp)) {
                echo json_encode(['result' => 'Failed to send OTP']);
                die();
            }
        
            // Save OTP in DB
            $update = mysqli_query($this->conn, "UPDATE `signup` SET `otp` = '$otp' WHERE `email` = '$email'");
        
            if ($update) {
                echo json_encode(['result' => 'OTP sent to your email']);
            } else {
                echo json_encode(['result' => 'Failed to process request']);
            }
        }
        
        function reset_password()
        {
            extract($_POST);
        
            if (empty($email) || empty($otp) || empty($new_password)) {
                echo json_encode(['result' => 'All fields are required']);
                die();
            }
        
            // Check if OTP is correct
            $check = mysqli_query($this->conn, "SELECT * FROM `signup` WHERE `email` = '$email' AND `otp` = '$otp'");
            if (mysqli_num_rows($check) == 0) {
                echo json_encode(['result' => 'Invalid OTP or Email']);
                die();
            }
        
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
            // Update password and clear OTP
            $update = mysqli_query($this->conn, "UPDATE `signup` SET `password` = '$hashed_password', `otp` = NULL WHERE `email` = '$email'");
        
            if ($update) {
                echo json_encode(['result' => 'Password has been reset successfully']);
            } else {
                echo json_encode(['result' => 'Failed to reset password']);
            }
        }



	//---------------------------------login----------------------------------
	
	
// function user_login()
// {
//     extract($_POST); // expects 'email', 'password', and 'fcm_token'

//     if (empty($email) || empty($password)) {
//         echo json_encode(['result' => 'Email and Password are required']);
//         return;
//     }

//     $email = trim($email);
//     $password = trim($password);
//     $fcm_token = isset($fcm_token) ? trim($fcm_token) : null;

//     // Get user from database
//     $query = mysqli_query($this->conn, "SELECT * FROM `signup` WHERE `email`='$email'");

//     if (mysqli_num_rows($query) > 0) {
//         $user = mysqli_fetch_assoc($query);

//         if ($user['is_verified'] != 1) {
//             echo json_encode(['result' => 'Email is not verified']);
//             return;
//         }

//         if (password_verify($password, $user['password'])) {
//             // ✅ Update FCM token if provided
//             if (!empty($fcm_token)) {
//                 mysqli_query($this->conn, "UPDATE `signup` SET `fcm_token`='$fcm_token' WHERE `id`='{$user['id']}'");
//             }

//             // Success response
//             $response = [
//                 'result' => 'Login Successful',
//                 'user' => [
//                     'id' => $user['id'],
//                     'username' => $user['username'],
//                     'email' => $user['email'],
//                     'phone' => $user['phone'],
//                     'fcm_token' => $fcm_token ?? $user['fcm_token']
//                 ]
//             ];
//         } else {
//             $response = ['result' => 'Incorrect password'];
//         }
//     } else {
//         $response = ['result' => 'Email not found'];
//     }

//     echo json_encode($response);
// }

function user_login()
{
    extract($_POST); // expects 'email', 'password', and 'fcm_token'

    if (empty($email) || empty($password)) {
        echo json_encode(['result' => 'Email and Password are required']);
        return;
    }

    $email = trim($email);
    $password = trim($password);
    $fcm_token = isset($_POST['fcm_token']) ? trim($_POST['fcm_token']) : null;

    // Get user from database
    $query = mysqli_query($this->conn, "SELECT * FROM `signup` WHERE `email`='$email'");

    if (mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);

        if ($user['is_verified'] != 1) {
            echo json_encode(['result' => 'Email is not verified']);
            return;
        }

        if (password_verify($password, $user['password'])) {
            $sub_query="SELECT * FROM subscriptions WHERE user_id=".$user['id']." and status='active' limit 1;";
            $sub_data=mysqli_query($this->conn,$sub_query);
            if ($sub_data && mysqli_num_rows($sub_data) > 0) {
                     $subscription = mysqli_fetch_assoc($sub_data);
                     $sub_arr=array("status"=>"active","start_at"=>$subscription["start_at"] ,"end_at"=>$subscription["end_at"],"plan_id"=>$subscription["plan_id"]);                  
            }else{ $sub_arr=array();  }
            // Update FCM token if provided
            if (!empty($fcm_token)) {
                mysqli_query($this->conn, "UPDATE `signup` SET `fcm_token`='$fcm_token' WHERE `id`='{$user['id']}'");
            }

            $tokenToSend = $fcm_token ?? $user['fcm_token'];
            

            // Send notification directly here
            if (!empty($tokenToSend)) {
                $fcmService = new FirebaseNotificationService();

                $title = "Welcome back, {$user['username']}!";
                $body  = "You have successfully logged in.";
                $image = null; // optional: set an image URL if you want

                $fcmService->send($tokenToSend, $title, $body, $image);
            }

            // Success response
            $response = [
                'result' => 'Login Successful',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'fcm_token' => $tokenToSend
                ],
               "sub"=>$sub_arr
            ];
        } else {
            $response = ['result' => 'Incorrect password'];
        }
    } else {
        $response = ['result' => 'Email not found'];
    }

    echo json_encode($response);
}


	
	
	
	
	// 	==============================================
	function delete_subteam()
	{
		extract($_POST);
		$validation = [
			'member_unqiue_id' => $member_unqiue_id,

		];
		$valid_check = array_search(null, $validation);
		if ($valid_check) {
			$message['result'] = $valid_check . " is Empty";
			echo json_encode($message);
			die();
		} else {

			$data = mysqli_query($this->conn, "DELETE FROM `add_sub_team` WHERE `team_id`='$member_unqiue_id'");
			$del = mysqli_query($this->conn, "DELETE FROM `add_sub_team` WHERE `member_unqiue_id`='$member_unqiue_id'");

			if ($del) {
				$message['result'] = "Deleted Successfully";
				$message['status'] = "1";
			} else {
				$message['result'] = "Failed";
				$message['status'] = "0";
			}
		}
		echo json_encode($message);
	}
	
	
function LanguageList()
{

    // SQL query to select all records from the list table
    $query = "SELECT * FROM list";
    
    $result = mysqli_query($this->conn, $query);
    
   

    if ($result) {
        $languages = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $languages[] = $row; // Collecting results into an array
        }
         
        echo json_encode(['status'=>true,'languages'=>$languages]);
    } else {
        // Handle query failure
        echo json_encode([]);
    }
}

//   Search   //

// function videoSearch()
// {
//     extract($_GET);

//     $deep = [];
//     $msz = [];

//     if (!empty($keyword)) {
//         // Escape keyword for security
//         $keyword = mysqli_real_escape_string($this->conn, $keyword);

//         $sql = "SELECT *
//                 FROM add_video
//                 LEFT JOIN list ON add_video.language_key = list.id
//                 WHERE add_video.video_name LIKE '%$keyword%'
//                   OR list.value LIKE '%$keyword%'";

//         $fetch = mysqli_query($this->conn, $sql);

//         if ($fetch) {
//             while ($row = mysqli_fetch_assoc($fetch)) {
//                 $row['path'] = $this->path;
//                 $deep[] = $row;
//             }

//             if (!empty($deep)) {
//                 $msz['status'] = true;
//                 $msz['message'] = "successfully";
//                 $msz['data'] = $deep;
                
//             } else {
//                 $msz['message'] = "not found";
//             }
//         } else {
//             $msz['message'] = "query error: " . mysqli_error($this->conn);
//         }
//     } else {
//         $msz['message'] = "keyword is required";
//     }

//     echo json_encode($msz);
// }


// function videoSearch()
// {
//     extract($_GET);

//     $deep = [];
//     $msz = [];

//     // Escape inputs
//     $keyword = isset($keyword) ? mysqli_real_escape_string($this->conn, $keyword) : '';
//     $language_name = isset($language_key) ? mysqli_real_escape_string($this->conn, $language_key) : '';

//     if (!empty($keyword) && !empty($language_name)) {
//         // Step 1: Get language ID from name
//         $lang_sql = "SELECT id FROM list WHERE value = '$language_name' LIMIT 1";
//         $lang_res = mysqli_query($this->conn, $lang_sql);

//         if ($lang_res && mysqli_num_rows($lang_res) > 0) {
//             $lang_row = mysqli_fetch_assoc($lang_res);
//             $language_id = $lang_row['id'];

//             // Step 2: Main search using keyword and language_id
//             $sql = "SELECT *
//                     FROM add_video
//                     LEFT JOIN list ON add_video.language_key = list.id
//                     WHERE (add_video.video_name LIKE '%$keyword%' OR list.value LIKE '%$keyword%')
//                       AND add_video.language_key = '$language_id'";

//             $fetch = mysqli_query($this->conn, $sql);

//             if ($fetch) {
//                 while ($row = mysqli_fetch_assoc($fetch)) {
//                     $row['path'] = $this->path;
//                     $deep[] = $row;
//                 }

//                 if (!empty($deep)) {
//                     $msz['status'] = true;
//                     $msz['message'] = "successfully";
//                     $msz['data'] = $deep;
//                 } else {
//                     $msz['message'] = "not found";
//                 }
//             } else {
//                 $msz['message'] = "query error: " . mysqli_error($this->conn);
//             }
//         } else {
//             $msz['message'] = "language not found";
//         }
//     } else {
//         $msz['message'] = "keyword and language name are required";
//     }

//     echo json_encode($msz);
// }

function videoSearch()
{
    extract($_GET);

    $deep = [];
    $msz = [];

    // Sanitize inputs
    $keyword = isset($keyword) ? mysqli_real_escape_string($this->conn, $keyword) : '';
    $language_name = isset($language_key) ? mysqli_real_escape_string($this->conn, $language_key) : '';

    // No inputs given
    if (empty($keyword) && empty($language_name)) {
        $msz['message'] = "at least one search parameter is required";
        echo json_encode($msz);
        return;
    }

    // Start base SQL
    $sql = "SELECT * FROM add_video 
            LEFT JOIN list ON add_video.language_key = list.id 
            WHERE 1=1";

    // Add keyword condition if provided
    if (!empty($keyword)) {
        $sql .= " AND (add_video.video_name LIKE '%$keyword%' OR list.value LIKE '%$keyword%')";
    }

    // Add language name condition if provided
    if (!empty($language_name)) {
        // Get ID from language name
        $lang_sql = "SELECT id FROM list WHERE value = '$language_name' LIMIT 1";
        $lang_res = mysqli_query($this->conn, $lang_sql);

        if ($lang_res && mysqli_num_rows($lang_res) > 0) {
            $lang_row = mysqli_fetch_assoc($lang_res);
            $language_id = $lang_row['id'];
            $sql .= " AND add_video.language_key = '$language_id'";
        } else {
            $msz['message'] = "language not found";
            echo json_encode($msz);
            return;
        }
    }

    // Run final query
    $fetch = mysqli_query($this->conn, $sql);

    if ($fetch) {
        while ($row = mysqli_fetch_assoc($fetch)) {
            $row['path'] = $this->path;
            $deep[] = $row;
        }

        if (!empty($deep)) {
            $msz['status'] = true;
            $msz['message'] = "successfully";
            $msz['data'] = $deep;
        } else {
            $msz['message'] = "not found";
        }
    } else {
        $msz['message'] = "query error: " . mysqli_error($this->conn);
    }

    echo json_encode($msz);
}



//     function bookSearch()
// {
//     extract($_GET);
    
//     $deep = array();
//     $msz = array();

//     if (!empty($keyword)) {
//         $keyword = mysqli_real_escape_string($this->conn, $keyword);

//         $fetch = mysqli_query($this->conn, "SELECT id, language_key, book_name, writer_name, image, book_audio FROM `book` WHERE `book_name` LIKE '%$keyword%'");

//         if ($fetch && mysqli_num_rows($fetch) > 0) {
//             while ($fetch_cate = mysqli_fetch_assoc($fetch)) {
//                 $fetch_cate['path'] = $this->path;
//                 $deep[] = $fetch_cate;
//             }
//             $msz['data'] = $deep;
//             $msz['message'] = "successfully";
//         } else {
//             $msz['data'] = [];
//             $msz['message'] = "no results found";
//         }
//     } else {
//         $msz['data'] = [];
//         $msz['message'] = "keyword is required";
//     }

//     echo json_encode($msz);
// }


function bookSearch()
{
    extract($_GET);

    $deep = [];
    $msz = [];

    // Sanitize inputs
    $keyword = isset($keyword) ? mysqli_real_escape_string($this->conn, $keyword) : '';
    $language_name = isset($language_key) ? mysqli_real_escape_string($this->conn, $language_key) : '';

    // No inputs given
    if (empty($keyword) && empty($language_name)) {
        $msz['message'] = "at least one search parameter is required";
        echo json_encode($msz);
        return;
    }

    // Start base SQL
    $sql = "SELECT book.id, language_key, book_name, writer_name, image, book_audio, list.value as language_name FROM book 
            LEFT JOIN list ON book.language_key = list.id 
            WHERE 1=1";

    // Add keyword condition
    if (!empty($keyword)) {
        $sql .= " AND (book.book_name LIKE '%$keyword%' OR book.writer_name LIKE '%$keyword%' OR list.value LIKE '%$keyword%')";
    }

    // Add language name condition
    if (!empty($language_name)) {
        $lang_sql = "SELECT id FROM list WHERE value = '$language_name' LIMIT 1";
        $lang_res = mysqli_query($this->conn, $lang_sql);

        if ($lang_res && mysqli_num_rows($lang_res) > 0) {
            $lang_row = mysqli_fetch_assoc($lang_res);
            $language_id = $lang_row['id'];
            $sql .= " AND book.language_key = '$language_id'";
        } else {
            $msz['message'] = "language not found";
            echo json_encode($msz);
            return;
        }
    }

    // Run final query
    $fetch = mysqli_query($this->conn, $sql);

    if ($fetch) {
        while ($row = mysqli_fetch_assoc($fetch)) {
            $row['path'] = $this->path;
            $deep[] = $row;
        }

        if (!empty($deep)) {
            $msz['status'] = true;
            $msz['message'] = "successfully";
            $msz['data'] = $deep;
        } else {
            $msz['message'] = "not found";
        }
    } else {
        $msz['message'] = "query error: " . mysqli_error($this->conn);
    }

    echo json_encode($msz);
}

// plan

function get_plan()
{
    // Query to fetch all plans
    $query = "SELECT * FROM `plans`";
    $result = mysqli_query($this->conn, $query);

    $data = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        $response = [
            'status'  => !empty($data) ? 'success' : 'failed',
            'message' => !empty($data) ? 'Showing Successfully' : 'No plans found',
            'data'    => $data
        ];
    } else {
        $response = [
            'status'  => 'failed',
            'message' => 'Database query failed: ' . mysqli_error($this->conn),
            'data'    => []
        ];
    }

    // Set JSON header and output
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}




public function createSubscription() {
	header('Content-Type: application/json');
	try {
		error_log('[createSubscription] invoked');
		// Accept both snake_case and lower camelCase param names from POST and REQUEST
		$userid = isset($_POST['userid']) ? $_POST['userid']
			: (isset($_POST['user_id']) ? $_POST['user_id']
			: (isset($_REQUEST['userid']) ? $_REQUEST['userid']
			: (isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : null)));
		$planid = isset($_POST['planid']) ? $_POST['planid']
			: (isset($_POST['plan_id']) ? $_POST['plan_id']
			: (isset($_REQUEST['planid']) ? $_REQUEST['planid']
			: (isset($_REQUEST['plan_id']) ? $_REQUEST['plan_id'] : null)));

		$debug = [
			"received_userid" => $userid,
			"received_planid" => $planid,
			"post_data" => $_POST,
			"request_data" => $_REQUEST,
			"method" => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
			"content_type" => $_SERVER['CONTENT_TYPE'] ?? 'not set'
		];

		// Basic validation
		if (empty($userid) || empty($planid)) {
			echo json_encode([
				"status" => false,
				"message" => "User ID and Plan ID are required",
				"debug" => $debug
			]);
			exit;
		}

		$userid = (int)$userid;
		if ($userid <= 0) {
			echo json_encode([
				"status" => false,
				"message" => "Invalid User ID",
				"debug" => $debug
			]);
			exit;
		}

		// Optional: validate that plan exists in local DB
		$planExists = false;
		$planStmt = mysqli_prepare($this->conn, "SELECT id FROM plans WHERE id = ? LIMIT 1");
		if ($planStmt) {
			mysqli_stmt_bind_param($planStmt, "s", $planid);
			mysqli_stmt_execute($planStmt);
			$planRes = mysqli_stmt_get_result($planStmt);
			$planExists = $planRes && mysqli_fetch_assoc($planRes) ? true : false;
		}

		if (!$planExists) {
			// Not fatal for Razorpay call, but useful to know
			$debug['warning'] = 'Plan not found in local DB; continuing with Razorpay';
		}

		// Create subscription in Razorpay
		$razorpay = new RazorpayService();
		$subscription = $razorpay->createSubscription($planid, 1, 12);

		if ($subscription === false) {
			echo json_encode([
				"status" => false,
				"message" => "Failed to create subscription with Razorpay",
				"debug" => $debug
			]);
			flush();
			exit;
		}

		// Extract subscription fields safely (object/array compatibility)
		$subId = null;
		$status = null;
		$planFromGateway = null;
		$startAt = null;
		$endAt = null;
		$nextChargeAt = null;
		$amount = 0; // integer in INR as per schema
		$currency = 'INR';
		$paymentId = null; // not available at creation time

		if (is_array($subscription)) {
			$subId = $subscription['id'] ?? null;
			$status = $subscription['status'] ?? null;
			$planFromGateway = $subscription['plan_id'] ?? ($subscription['plan']['id'] ?? null);
			$startAt = isset($subscription['start_at']) ? date('Y-m-d H:i:s', (int)$subscription['start_at']) : null;
			$endAt = isset($subscription['end_at']) ? date('Y-m-d H:i:s', (int)$subscription['end_at']) : null;
			$nextChargeAt = isset($subscription['charge_at']) ? date('Y-m-d H:i:s', (int)$subscription['charge_at']) : null;
			// plan amount is in paise; convert to INR
			if (isset($subscription['plan']['item']['amount'])) {
				$amount = (int) round(((int)$subscription['plan']['item']['amount']) / 100);
			}
			if (isset($subscription['plan']['item']['currency'])) {
				$currency = $subscription['plan']['item']['currency'] ?: 'INR';
			}
		} else {
			// Razorpay SDK returns entity objects supporting property access
			$subId = isset($subscription->id) ? $subscription->id : null;
			$status = isset($subscription->status) ? $subscription->status : null;
			$planFromGateway = isset($subscription->plan_id) ? $subscription->plan_id : (isset($subscription->plan->id) ? $subscription->plan->id : null);
			$startAt = isset($subscription->start_at) ? date('Y-m-d H:i:s', (int)$subscription->start_at) : null;
			$endAt = isset($subscription->end_at) ? date('Y-m-d H:i:s', (int)$subscription->end_at) : null;
			$nextChargeAt = isset($subscription->charge_at) ? date('Y-m-d H:i:s', (int)$subscription->charge_at) : null;
			// plan item fields
			if (isset($subscription->plan) && isset($subscription->plan->item)) {
				if (isset($subscription->plan->item->amount)) {
					$amount = (int) round(((int)$subscription->plan->item->amount) / 100);
				}
				if (isset($subscription->plan->item->currency) && !empty($subscription->plan->item->currency)) {
					$currency = $subscription->plan->item->currency;
				}
			}
		}

		if (empty($subId)) {
			echo json_encode([
				"status" => false,
				"message" => "Invalid subscription response from Razorpay",
				"debug" => $debug
			]);
			flush();
			exit;
		}

		// Persist subscription locally per schema
		$insertSql = "INSERT INTO subscriptions (subscription_id, user_id, plan_id, payment_id, status, amount, currency, start_at, end_at, next_charge_at, created_at, updated_at)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
			ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), plan_id = VALUES(plan_id), payment_id = VALUES(payment_id), status = VALUES(status), amount = VALUES(amount), currency = VALUES(currency), start_at = VALUES(start_at), end_at = VALUES(end_at), next_charge_at = VALUES(next_charge_at), updated_at = NOW()";
		$insert = mysqli_prepare($this->conn, $insertSql);
		if ($insert) {
			// Normalize optional timestamps to NULL explicitly if empty strings
			$startAtParam = !empty($startAt) ? $startAt : NULL;
			$endAtParam = !empty($endAt) ? $endAt : NULL;
			$nextChargeAtParam = !empty($nextChargeAt) ? $nextChargeAt : NULL;
			mysqli_stmt_bind_param($insert, "sisssissss", $subId, $userid, $planid, $paymentId, $status, $amount, $currency, $startAtParam, $endAtParam, $nextChargeAtParam);
			mysqli_stmt_execute($insert);
		}

		echo json_encode([
			"status" => true,
			"message" => "Subscription created successfully",
			"subscription_id" => $subId,
			"data" => [
				"user_id" => $userid,
				"plan_id" => $planid,
				"status" => $status,
				"short_url" => $subscription['short_url'] ?? null,  // ← ADD THIS LINE
					"amount" => $amount,
					"currency" => $currency,
				"start_at" => $startAt,
				"end_at" => $endAt,
				"next_charge_at" => $nextChargeAt
			],
			"debug" => $debug
		]);
		flush();
		exit;

	} catch (Exception $e) {
		error_log('[createSubscription] exception: ' . $e->getMessage());
		echo json_encode([
			"status" => false,
			"message" => "Exception: " . $e->getMessage(),
			"file" => $e->getFile(),
			"line" => $e->getLine()
		]);
		flush();
		exit;
	}
}







public function capturePaymentStatus()
{
    header('Content-Type: application/json');

    if (empty($_POST['order_id']) || empty($_POST['payment_id']) || empty($_POST['signature'])) {
        echo json_encode([
           "status" => false,
           "message" => "Order ID, Payment ID, and Signature are required"
        ]);
        exit;
    }

    $order_id   = $_POST['order_id'];
    $payment_id = $_POST['payment_id'];
    $signature  = $_POST['signature'];

    try {
        $razorPay   = new RazorpayService();
        $attributes = [
            'razorpay_order_id'   => $order_id,
            'razorpay_payment_id' => $payment_id,
            'razorpay_signature'  => $signature
        ];
        $razorPay->verifySignature($attributes);

    } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
        http_response_code(400);
        echo json_encode([
            "status" => false,
            "message" => "Payment verification failed: " . $e->getMessage()
        ]);
        exit;
    }

    // Fetch subscription safely
    $stmt = mysqli_prepare($this->conn, "SELECT * FROM subscriptions WHERE subscription_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $subscription = mysqli_fetch_assoc($result);

    if (!$subscription) {
        http_response_code(404);
        echo json_encode([
            "status" => false,
            "message" => "Subscription not found"
        ]);
        exit;
    }

    $status = 'active';
    $end_at = date('Y-m-d H:i:s', strtotime('+1 month')); 
    $next_charge_at = date('Y-m-d H:i:s', strtotime('+1 month'));

    $update_stmt = mysqli_prepare(
        $this->conn,
        "UPDATE subscriptions SET payment_id = ?, status = ?, end_at = ?, next_charge_at = ?, updated_at = NOW() WHERE subscription_id = ?"
    );
    mysqli_stmt_bind_param($update_stmt, "sssss", $payment_id, $status, $end_at, $next_charge_at, $order_id);
    mysqli_stmt_execute($update_stmt);

    http_response_code(200);
    echo json_encode([
        "status"          => true,
        "message"         => "Payment verified and subscription activated",
    ]);
    exit;
}








public function handleWebhook()
{
    header('Content-Type: application/json');

    // 1️⃣ Read raw payload & Razorpay signature
    $payload   = file_get_contents('php://input');
    $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? null;

    if (!$payload || !$signature) {
        echo json_encode([
            "status"  => "error",
            "message" => "Missing payload or signature"
        ]);
        exit;
    }

    // 2️⃣ Verify webhook
    $razorpay = new RazorpayService();
    if (!$razorpay->verifyWebhook($payload, $signature)) {
        echo json_encode([
            "status"  => "error",
            "message" => "Invalid webhook signature"
        ]);
        exit;
    }

    // 3️⃣ Decode webhook JSON
    $event = $razorpay->parseWebhookEvent($payload);

    // 4️⃣ (optional) Debug log (write into file for testing)
    $logFile = __DIR__ . "/razorpay_webhook.log";
    file_put_contents(
        $logFile,
        date('Y-m-d H:i:s') . " | Signature: $signature | Event: " . ($event['event'] ?? 'unknown') . PHP_EOL .
        $payload . PHP_EOL . "-------------------" . PHP_EOL,
        FILE_APPEND
    );

    // 5️⃣ Handle webhook events
    switch ($event['event']) {
        case 'payment.captured':
            $payment = $event['payload']['payment']['entity'];
            $paymentId = $payment['id'];
            $orderId   = $payment['order_id'];
            $amount    = $payment['amount'] / 100; // convert from paise
            $status    = $payment['status'];

            // 👉 Update your subscriptions/payments table in DB here
            break;

        case 'payment.failed':
            $payment = $event['payload']['payment']['entity'];
            $paymentId = $payment['id'];
            $orderId   = $payment['order_id'];
            $status    = $payment['status'];

            // 👉 Update DB: mark payment as failed
            break;

        case 'order.paid':
            $order = $event['payload']['order']['entity'];
            $orderId = $order['id'];

            // 👉 Mark order as fully paid in DB
            break;

        default:
            // Log unhandled events only
            break;
    }

    // 6️⃣ Always respond 200 OK to Razorpay
    echo json_encode([
        "status"  => "success",
        "message" => "Webhook received and processed"
    ]);
    exit;
}

public function notification()
{
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Create Firebase service
    $fcmService = new FirebaseNotificationService();

    // Get message details safely
    $title = isset($_POST['title']) ? $_POST['title'] : 'New Notification';
    $body = isset($_POST['body']) ? $_POST['body'] : 'This is a broadcast message to all users.';
    $imageUrl = isset($_POST['image']) ? $_POST['image'] : null;

    // Fetch all users with a non-empty token
    $query = mysqli_query($this->conn, "SELECT fcm_token FROM `signup` WHERE fcm_token IS NOT NULL AND fcm_token != ''");

    $successCount = 0;
    $failCount = 0;

    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $token = $row['fcm_token'];

            if (!empty($token)) {
                $result = $fcmService->send($token, $title, $body, $imageUrl);

                if ($result) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }
        }
    }

    // Return result as array (not echo)
    return [
        'status' => true,
        'message' => 'Notification process completed',
        'sent_to' => $successCount,
        'failed' => $failCount
    ];
}


}
