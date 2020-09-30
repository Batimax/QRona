<?php
include("functions.php");
header('Content-type: application/json');
$status = 0;
$error_log = 0;
$action = false;
$answer = 0;

if (isset($_POST['action'])) {
	if ($_POST['action'] == 'send_sms') {
		$action = 'send_sms';
		$userID = $_POST['userID'];
		$phone_number = $_POST['phone'];
	} else if ($_POST['action'] == 'check_code') {
		$action = 'check_code';
		$userID = $_POST['userID'];
		$code = $_POST['code'];
		$phone_number = $_POST['phone'];
	}
}

if ($action == 'send_sms') {

	$status_sendSMS = sendSMS($dtb, $userID, $phone_number);
	if ($status_sendSMS == 'success'){
		$status = 'success_sms';
	} else {
		$error_log = $status_sendSMS;
	}

	$answer = ['status' => $status, 'error' => $error_log];
}

if ($action == 'check_code') {

	$result = checkCode($dtb, $userID, $code);
	if ($result == 'success_check_code') {
		$status = $result;
		userValidatePhoneNumber($dtb, $userID, $phone_number);
	} else if ($result == 'wrong_code'){
		$error_log = $result;
	}else if ($result == 'no_code_for_user'){
		$error_log = $result;
	}

	$answer = ['status' => $status, 'error' => $error_log];
}

$answer_json = json_encode($answer);
echo $answer_json;
