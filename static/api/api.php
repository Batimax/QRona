<?php

require_once __DIR__.'/functions.php';
header('Content-type: application/json');

$error_log = 0;
$status = 0;

// Find case new user or already in db
if (isset($_POST['firstname'])) {
	$new_user = true;
}

// Check user table
if (isset($_POST['user_table'])) {
	$user_table = $_POST['user_table'];
	if ($user_table == 'undefined') {
		$user_table = 51;
		$error_log = 'no_table';
	}
} else {
	$user_table = 1;
	$error_log = 'no_table';
}

// CASE ALREADY KNOWN USER
if (!isset($new_user)) {

	$userID = $_POST['userID'];

	$user_key = getUserKey($userID, $dtb);

	if (!$user_key) {
		$error_log = 'user_not_found'; // Error: Cannot find userID in db
	} else {
		$older_x_min = insertDateScan($user_key, $dtb, $user_table);
		if (!$older_x_min) {
			$error_log = 'last_log_too_soon';
		} else {
			$status = 'succes_login'; // Succesfull login
		}
		// Check if phone number verified
		if (!checkVerifyNumber($user_key, $dtb)) {
			$error_log = 'nbr_not_verified';
			$status = 0;
		}
	}
	$answer = ['status' => $status, 'error' => $error_log];
}

// CASE NEW USER
if (isset($new_user)) {
	// Treat data
	$firstname = $_POST['firstname'];
	$lastname = $_POST['lastname'];
	$address = $_POST['address'];
	$city = $_POST['city'];
	$zipcode = $_POST['zipcode'];
	$email = $_POST['email'];

	// Value treatment
	$firstname = ucwords($firstname);
	$lastname = ucwords($lastname);
	$city = ucwords($city);
	$email = strtolower($email);

	// Server side value validation
	// $data_validated = userDataValidation($firstname, $lastname, $address, $city, $phone, $email);

	// Check if user already exists
	$userID = checkMailExist($email, $dtb);

	if (!$userID) {
		$userID = uniqid('', true);
		$user_key = createNewAccount($userID, $firstname, $lastname, $address, $city, $zipcode, $email, $dtb);
	} else { // Cookie not present but account exists
		$user_key = getUserKey($userID, $dtb);
		$error_log = 'cookie_deleted';
	}

	$older_x_min = insertDateScan($user_key, $dtb, $user_table);
	if (!$older_x_min) {
		$error_log = 'last_log_too_soon';
	} else {
		$status = 'succes_login'; // Succesfull login
	}

	// Check if phone number verified
	if (!checkVerifyNumber($user_key, $dtb)) {
		$error_log = 'nbr_not_verified';
		$status = $userID;
	}
	$answer = ['status' => $status, 'userID' => $userID, 'error' => $error_log];
};

$answer_json = json_encode($answer);
echo $answer_json;
