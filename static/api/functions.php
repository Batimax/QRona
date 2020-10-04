<?php

require_once __DIR__.'/../../admin/init.php';

// env. variables

// Functions
function updateUserLastConnection($user_key, $dtb)
{
	$query = 'UPDATE users SET last_connection = (NOW()) WHERE id = :user_key';
	$req = $dtb->prepare($query);
	$req->execute(array(
		'user_key' => $user_key
	));
	$req->closeCursor();
}

function getUserKey($userID, $dtb)
{
	$req = $dtb->prepare('SELECT id FROM users WHERE userID = :userID');
	$req->execute(array(
		'userID' => $userID
	));
	$user_key = $req->fetch();
	$req->closeCursor();

	if (!$user_key) {
		$user_key = false;
	} else {
		$user_key = $user_key[0];
	}
	return $user_key;
}

function insertDateScan($user_key, $dtb, $user_table)
{
	// New log only if last log older than x minutes or no date scan
	$last_log = checkLastLog($user_key, $dtb);

	// If new user
	if (!$last_log) {
		$req = $dtb->prepare('INSERT INTO logs(user, date_scan, user_table) VALUES(:user_key, NOW(), :user_table)');
		$req->execute(array(
			'user_key' => $user_key,
			'user_table' => $user_table
		));
		$req->closeCursor();
		$older_x_min = true;

		$req = $dtb->prepare('INSERT INTO data_scans(date_scan, user_table) VALUES(NOW(), :user_table)');
		$req->execute(array(
			'user_table' => $user_table,
		));
		$req->closeCursor();

	} else {
		$last_scan = $last_log['last_scan'];
		$last_table = $last_log['last_table'];

		date_default_timezone_set('Europe/Paris');
		$date_compare = (new DateTime(date("Y-m-d H:i:s")));
		$date_compare->sub(new DateInterval('PT30M'));
		$date_compare = $date_compare->format('Y-m-d H:i:s ');

		$date_compare_dt = new DateTime($date_compare);
		$last_scan_dt = new DateTime($last_scan);

		if (($date_compare_dt > $last_scan_dt || $user_table != $last_table) || !$last_scan_dt) {
			$req = $dtb->prepare('INSERT INTO logs(user, date_scan, user_table) VALUES(:user_key, NOW(), :user_table)');
			$req->execute(array(
				'user_key' => $user_key,
				'user_table' => $user_table
			));
			$req->closeCursor();
			$older_x_min = true;

			$req = $dtb->prepare('INSERT INTO data_scans(date_scan, user_table) VALUES(NOW(), :user_table)');
			$req->execute(array(
				'user_table' => $user_table
			));
			$req->closeCursor();
		} else {
			$older_x_min = false;
		}
	}

	updateUserLastConnection($user_key, $dtb);

	return $older_x_min;
}

function checkMailExist($email, $dtb)
{
	// Check if user already exist in database with email
	$req = $dtb->prepare('SELECT userID FROM users WHERE email = :email');
	$req->execute(array(
		'email' => $email
	));
	$db_userID = $req->fetch();
	$req->closeCursor();

	if ($db_userID) {
		$userID = $db_userID['userID'];
	} else {
		$userID = false;
	}
	return $userID;
}

function createNewAccount($userID, $firstname, $lastname, $address, $city, $zipcode, $email, $dtb)
{
	print_r($dtb);
	// Put new user in db
	$req = $dtb->prepare('INSERT INTO users(userID, firstname, lastname, adress, city, zipcode, email, last_connection) VALUES(:userID, :firstname, :lastname, :adress, :city, :zipcode, :email, NOW())');
	$req->execute(array(
		'userID' => $userID,
		'firstname' => $firstname,
		'lastname' => $lastname,
		'adress' => $address,
		'city' => $city,
		'zipcode' => $zipcode,
		'email' => $email,
	));
	$req->closeCursor();

	// Get user key id
	$user_key = $dtb->lastInsertId();
	return $user_key;
}

function checkLastLog($user_key, $dtb)
{
	// Check lst log date
	$req = $dtb->prepare('SELECT date_scan, user_table FROM logs WHERE user = :user_key ORDER BY id DESC LIMIT 1');
	$req->execute(array(
		'user_key' => $user_key
	));
	$logs = $req->fetch();
	$req->closeCursor();

	if ($logs) {
		$last_scan = $logs['date_scan'];
		$last_table = $logs['user_table'];
		$last_log = [
			"last_scan" => $last_scan,
			"last_table" => $last_table
		];
	} else {
		$last_log = false;
	}
	return $last_log;
}

function checkVerifyNumber($user_key, $dtb)
{
	// Check if user phone number verified
	$req = $dtb->prepare('SELECT nbr_verified FROM users WHERE id = :user_key');
	$req->execute(array(
		'user_key' => $user_key
	));
	$nbr_verified = $req->fetch();
	$req->closeCursor();

	$nbr_verified = $nbr_verified[0];
	if ($nbr_verified == 0){
		$nbr_verified = false;
	} else {
		$nbr_verified = true;
	}
	return $nbr_verified;
}

function getOldUsers($dtb)
{
	$req = $dtb->prepare('SELECT id FROM users WHERE DATE(last_connection) < DATE_SUB(CURDATE(), INTERVAL 3 MONTH)');
	$req->execute(array());
	$users_to_clear = $req->fetch();

	if (!$users_to_clear) {
		$users_to_clear = false;
	} else {
		while ($user_to_clear = $req->fetch()) {
			$users_to_clear[] = $user_to_clear['id'];
		}
		unset($users_to_clear['id']);
	}
	$req->closeCursor();

	return $users_to_clear;
}


function getLogs($dtb, $user)
{
	$req = $dtb->prepare("SELECT user, DATE_FORMAT(date_scan,'%H:%i') AS date_scan
					FROM logs
					WHERE user = :user_key AND DATE(date_scan) = CURDATE()");
	$req->execute(array(
		'user_key' => $user["id"],
	));
	// $date_users = $req->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
	$date_users[$user["id"]] = $req->fetchAll(PDO::FETCH_ASSOC);
	$req->closeCursor();

	return $date_users;
}

// function userDataValidation($firstname, $lastname, $address, $city, $phone, $email)
// {


// }
