<?php
require_once __DIR__.'/init.php';

// Functions
function clearOldUsers($dtb)
{
	// Delete users which havent connect since 3 monthes
	$req = $dtb->prepare('DELETE FROM users WHERE DATE(last_connection) < DATE_SUB(CURDATE(), INTERVAL 3 MONTH)');
	$req->execute(array());
}

function clearOldlogs($dtb)
{
	// Delete logs older than 14 days
	$req = $dtb->prepare('DELETE FROM logs WHERE DATE(date_scan) < DATE_SUB(CURDATE(), INTERVAL 14 DAY)');
	$req->execute(array());
}

function deleteUser($dtb, $user_id)
{
	// Check if user find in db
	$req = $dtb->prepare('	SELECT id
							FROM users
							WHERE id = :user_key');
	$req->execute(array(
		'user_key' => $user_id,
	));
	$user_check = $req->fetch();
	$req->closeCursor();

	if (!$user_check) {
		$response = "no_user";
	} else {
		// Delete logs from user
		$req = $dtb->prepare('DELETE FROM logs WHERE user = :user_key');
		$req->execute(array(
			'user_key' => $user_id,
		));

		// Delete user profil
		$req = $dtb->prepare('DELETE FROM users WHERE id = :user_key');
		$req->execute(array(
			'user_key' => $user_id
		));
		$response = 'success';
	}
	return $response;
}

function getInfoDailyHTML($days_ago) {
	// Get html mail content from a page of the server
	$url = 'http://localhost:8888/Satellite/QRona/admin/daily_users.php?days_ago=' . $days_ago;
	$username = "max-yoyou"; // AIE AIE AIE AIE VITE .ENV
	$password = "CQL!8]M?+&^AumJ^";
	$context = stream_context_create(array(
		'http' => array(
			'header' => 'Authorization: Basic ' . base64_encode("$username:$password")
		)
	));

	$message = file_get_contents($url, false, $context);
	return $message;
}

function getUserNames($dtb)
{
	// Get all names in database
	$req = $dtb->prepare('SELECT lastname,firstname,id FROM users');
	$req->execute(array());
	$users_name = $req->fetchAll(PDO::FETCH_ASSOC);
	$req->closeCursor();

	if (!$users_name) {
		$users_name = false;
	} else {
		$toto = array();
		foreach ($users_name as $user_name) {
			$toto[] = $user_name['lastname'] . " " . $user_name['firstname'] . " id:" . $user_name['id'];
		}
		$users_name = $toto;
	}

	return $users_name;
}

function getTables($dtb)
{
	// Get all different tables
	$req = $dtb->prepare('SELECT DISTINCT(user_table) as user_table FROM logs ORDER BY user_table');
	$req->execute(array());
	$tables_db = $req->fetchALL(PDO::FETCH_ASSOC);
	$req->closeCursor();

	if (!$tables_db) {
		$all_tables = false;
	} else {
		foreach ($tables_db as $line) {
			$all_tables[] = $line['user_table'];
		}
	}
	return $all_tables;
}

function getUserData($dtb, $user_id)
{
	// Get all names in database
	$req = $dtb->prepare('SELECT * FROM users WHERE id = :user_key');
	$req->execute(array(
		'user_key' => $user_id,
	));
	$user_data = $req->fetchAll(PDO::FETCH_ASSOC);
	$req->closeCursor();

	if (!$user_data) {
		$user_data = "no_user";
	}
	return $user_data;
}


function getUsersContaminatedFromID($dtb, $id, $hour, $table, $days_ago)
{
	// Get scan dates of concerned user
	$req = $dtb->prepare('	SELECT date_scan
							FROM logs
							WHERE user = :user_key
							AND logs.user_table = :user_table
							AND date_scan >= DATE_SUB(NOW(), INTERVAL :days_ago DAY)');
	$req->execute(array(
		'user_key' => $id,
		'days_ago' => $days_ago,
		'user_table' => $table
	));
	$scan_dates = $req->fetchAll(PDO::FETCH_ASSOC);
	$req->closeCursor();

	if (!$scan_dates) {
		$scan_dates = false;
		$total_user_contaminated_infos = "no_scan_date_for_user";
		// echo "Client not in Satellite during previous 14 days.";
	} else {

		$hour = $hour / 2;

		$contaminated_user_date_scan_id = array();
		$contaminated_user_date_scan_id[0] = 0;

		foreach ($scan_dates as $date_scan_wanted_user) {
			$date_scan_wanted_user = $date_scan_wanted_user['date_scan'];

			//Create variables containing as much ? as in $contaminated_user_date_scan_id
			$in  = str_repeat('?,', count($contaminated_user_date_scan_id) - 1) . '?';

			$query = "SELECT logs.user, logs.date_scan, logs.id, users.lastname, users.firstname, users.adress, users.city, users.phone, users.email
				FROM users
				INNER JOIN logs
				ON logs.user = users.id
				WHERE logs.user != ? AND logs.user_table = ?
				AND logs.date_scan BETWEEN DATE_SUB(?, INTERVAL ? HOUR) AND DATE_SUB(?, INTERVAL -? HOUR)
				AND logs.id NOT IN ($in)";

			$req = $dtb->prepare($query);
			$req->execute(
				array_merge(
					[
						$id,
						$table,
						$date_scan_wanted_user,
						$hour,
						$date_scan_wanted_user,
						$hour
					],
					$contaminated_user_date_scan_id
				)
			);

			$contaminated_user_infos = $req->fetchAll(PDO::FETCH_ASSOC);
			$req->closeCursor();

			// user contaminated found in date interval
			if ($contaminated_user_infos) {
				// First array in $contaminated_user_info is date_scan of wanted user
				$total_user_contaminated_infos[] = $date_scan_wanted_user;

				// Get date_scan ids to keep only the date_scan not appened yet
				foreach ($contaminated_user_infos as $ids) {
					$contaminated_user_date_scan_id[] = $ids['id'];
				}

				// Add the multiple dates of a user around the wanted date on only one array
				if (sizeof($contaminated_user_infos) > 1) {
					foreach ($contaminated_user_infos as $k => &$array_user) {

						// Loop through $all contaminated users to see if this user appears multiple times
						foreach ($contaminated_user_infos as $k_child => &$array_user_child) {

							if ($array_user['user'] == $array_user_child['user'] & $k != $k_child) {
								$contaminated_user_infos[$k]['date_scan'] .= "<br />";
								$contaminated_user_infos[$k]['date_scan'] .= $array_user_child['date_scan'];

								// Remove array with date get back
								unset($contaminated_user_infos[$k_child]);
							}
						} // Remove pointer
						unset($array_user_child);
					}
					unset($array_user);
					$contaminated_user_infos = array_values($contaminated_user_infos);
				}
				// Then array with data of potentially contaminated users
				$total_user_contaminated_infos[] = $contaminated_user_infos;
			}
		}

		// print_r($total_user_contaminated_infos);
		if (!isset($total_user_contaminated_infos)) {
			$total_user_contaminated_infos = "no_users_in_contact_with_wanted_user";
			// echo "No client in contact with contaminated client between " . $hour . " hours.";
		}
	}
	// print_r($total_user_contaminated_infos);
	return $total_user_contaminated_infos;
}

function getUniqueUsersContaminatedFromID($dtb, $id, $hour, $table, $days_ago)
{
	// Get scan dates of concerned user
	$req = $dtb->prepare('	SELECT date_scan
							FROM logs
							WHERE user = :user_key
							AND logs.user_table = :user_table
							AND date_scan >= DATE_SUB(NOW(), INTERVAL :days_ago DAY)');
	$req->execute(array(
		'user_key' => $id,
		'days_ago' => $days_ago,
		'user_table' => $table
	));
	$scan_dates = $req->fetchAll(PDO::FETCH_ASSOC);
	$req->closeCursor();

	if (!$scan_dates) {
		$scan_dates = false;
		$total_user_contaminated_infos = "no_scan_date_for_user";
	} else {

		$hour = $hour / 2;
		$contaminated_user_date_scan_id = array();
		$contaminated_user_date_scan_id[0] = 0;
		$contaminated_users_id = array();
		$contaminated_users_id[0] = 0;


		foreach ($scan_dates as $date_scan_wanted_user) {
			$date_scan_wanted_user = $date_scan_wanted_user['date_scan'];

			//Create variables containing as much ? as in $contaminated_user_date_scan_id
			$in_log_id  = str_repeat('?,', count($contaminated_user_date_scan_id) - 1) . '?';
			$in_log_user  = str_repeat('?,', count($contaminated_users_id) - 1) . '?';

			$query = "SELECT logs.user, logs.date_scan, logs.id, users.lastname, users.firstname, users.adress, users.city, users.phone, users.email
				FROM users
				INNER JOIN logs
				ON logs.user = users.id
				WHERE logs.user != ? AND logs.user_table = ?
				AND logs.date_scan BETWEEN DATE_SUB(?, INTERVAL ? HOUR) AND DATE_SUB(?, INTERVAL -? HOUR)
				AND logs.id NOT IN ($in_log_id)
				AND logs.user NOT IN ($in_log_user)";

			$req = $dtb->prepare($query);
			$req->execute(
				array_merge(
					[
						$id,
						$table,
						$date_scan_wanted_user,
						$hour,
						$date_scan_wanted_user,
						$hour
					],
					$contaminated_user_date_scan_id,
					$contaminated_users_id
				)
			);

			$contaminated_user_infos = $req->fetchAll(PDO::FETCH_ASSOC);
			$req->closeCursor();

			// user contaminated found in date interval
			if ($contaminated_user_infos) {
				// First array in $contaminated_user_info is date_scan of wanted user
				$total_user_contaminated_infos[] = $date_scan_wanted_user;

				if (sizeof($contaminated_user_infos) == 1) {
					$contaminated_users_id[] = $contaminated_user_infos[0]['user'];
				} else {
					// Add the multiple dates of a user around the looked for date on only one array

					foreach ($contaminated_user_infos as $k => &$array_user) {
						// Get id of all contaminated users when multiple users contaminated
						$contaminated_users_id[] = $array_user['user'];

						// Loop through $all contaminated users to see if this user appears multiple times
						foreach ($contaminated_user_infos as $k_child => &$array_user_child) {

							if ($array_user['user'] == $array_user_child['user'] & $k != $k_child) {
								$contaminated_user_infos[$k]['date_scan'] .= "<br />";
								$contaminated_user_infos[$k]['date_scan'] .= $array_user_child['date_scan'];

								// Remove array with date get back
								unset($contaminated_user_infos[$k_child]);
							}
						} // Remove pointer
						unset($array_user_child);
					}
					unset($array_user);
					$contaminated_user_infos = array_values($contaminated_user_infos);
				}
				// Then array with data of potentially contaminated users
				$total_user_contaminated_infos[] = $contaminated_user_infos;

				// Get date_scan ids to keep only the date_scan not appened yet
				foreach ($contaminated_user_infos as $ids) {
					$contaminated_user_date_scan_id[] = $ids['id'];
				}
			}
		}

		// print_r($total_user_contaminated_infos);
		if (!isset($total_user_contaminated_infos)) {
			$total_user_contaminated_infos = "no_users_in_contact_with_wanted_user";
			// echo "No client in contact with contaminated client between " . $hour . " hours.";
		}
	}
	// print_r($total_user_contaminated_infos);
	return $total_user_contaminated_infos;
}
