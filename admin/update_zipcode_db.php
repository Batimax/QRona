<?php
require_once __DIR__.'/functions.php';

$users_city = getAllUsersCity($dtb);

$regex_zipcode = '/[0-9]{4,6}/';
foreach ($users_city as $user_city) {
	preg_match($regex_zipcode, $user_city['city'],$match_zipcode);
	// preg_match($regex_city, $user_city['city'],$match_city);

	if (!empty($match_zipcode)) {
		$city = str_replace($match_zipcode[0], '', $user_city['city']);
		// echo "<br /> " . $match_zipcode[0] . " city: " . $city;
		$userid_city_zipcode[] = array('id' => $user_city['id'], 'city' => trim($city), 'zipcode' => $match_zipcode[0]);

	}
}
if (isset($userid_city_zipcode)) {
	foreach ($userid_city_zipcode as $userid_city_zipcode_child) {

		updateCityZipcode($dtb, $userid_city_zipcode_child);
	}
	echo "Succesfully modified " . sizeof($userid_city_zipcode) . " user city.";
} else {
	echo "No column city to modified.";
}

