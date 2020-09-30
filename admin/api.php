<?php
require_once __DIR__.'/functions.php';
header('Content-type: application/json');
$status = 0;
$error_log = 0;
$action = false;
$answer = 0;

if (isset($_POST['action'])) {
	if ($_POST['action'] == 'get_names') {
		$action = 'get_names';
	} else if ($_POST['action'] == 'send_selected_id') {
		$action = 'send_selected_id';
	} else if ($_POST['action'] == 'send_selected_unique_id') {
		$action = 'send_selected_unique_id';
	} else if ($_POST['action'] == 'get_tables') {
		$action = 'get_tables';
	} else if ($_POST['action'] == 'delete_user') {
		$action = 'delete_user';
	} else if ($_POST['action'] == 'get_user_data') {
		$action = 'get_user_data';
	}else if ($_POST['action'] == 'print_table_daily') {
		$action = 'print_table_daily';
	}
}

if ($action == 'get_names') {
	$users_name = getUserNames($dtb);
	$status = $users_name;
	$answer = ['status' => $status, 'error' => $error_log];
}

if ($action == 'print_table_daily') {
	$days_ago = $_POST['selected_daily_info_days'];
	$table_content = getInfoDailyHTML($days_ago);

	$status = $table_content;

	$answer = ['status' => $status, 'error' => $error_log];
}

if ($action == 'get_tables') {
	$all_tables = getTables($dtb);
	$status = $all_tables;
	$answer = ['status' => $status, 'error' => $error_log];
}

if ($action == 'send_selected_id' || $action == 'send_selected_unique_id') {

	if (isset($_POST['selected_id'])) {

		$selected_id = $_POST['selected_id'];
		$selected_hour = $_POST['selected_hour'];
		$selected_table = $_POST['selected_table'];
		$selected_days_ago = $_POST['selected_days_ago'];

		if ($action == 'send_selected_id') {
			$user_contaminated_infos = getUsersContaminatedFromID($dtb, $selected_id, $selected_hour, $selected_table,  $selected_days_ago);
		} else {
			$user_contaminated_infos = getUniqueUsersContaminatedFromID($dtb, $selected_id, $selected_hour, $selected_table, $selected_days_ago);
		}

		if ($user_contaminated_infos == "no_scan_date_for_user") {
			$error_log = "no_scan_date_for_user";
		} else if ($user_contaminated_infos == 'no_users_in_contact_with_wanted_user') {
			$error_log = "no_users_in_contact_with_wanted_user";
		} else {
			// Get html form of the table
			$status = $user_contaminated_infos;
		}
	} else {
		$error_log = "no_id_selected";
	}

	$answer = ['status' => $status, 'error' => $error_log];
}

// Delete user
if ($action == 'delete_user') {

	$user_id = $_POST['selected_id'];

	$response = deleteUser($dtb, $user_id);

	if ($response == "no_user") {
		$error_log = "no_user";
	} else if ($response == 'success') {
		$status = "success";
	}
	$answer = ['status' => $status, 'error' => $error_log];
}

// Get user data
if ($action == 'get_user_data') {

	$user_id = $_POST['selected_id'];

	$user_data = getUserData($dtb, $user_id);

	if ($user_data == "no_user") {
		$error_log = "no_user";
	} else {
		$status = $user_data;
	}
	$answer = ['status' => $status, 'error' => $error_log];
}

$answer_json = json_encode($answer);
echo $answer_json;
