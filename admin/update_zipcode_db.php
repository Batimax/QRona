<?php
require_once __DIR__.'/functions.php';

$users_city = getAllUsersCity($dtb);

echo "<br />";
$regex_zipcode = '/[0-9]{4,6}/';
// $regex_city = '/[^0-9]/';
foreach ($users_city as $user_city) {
	preg_match($regex_zipcode, $user_city['city'],$match_zipcode);
	// preg_match($regex_city, $user_city['city'],$match_city);

	if (!empty($match_zipcode)) {
		$city = str_replace($match_zipcode[0], '', $user_city['city']);
		print_r($city);
		echo "<br /> " . $match_zipcode[0] . " city: " . $city;
	}
}
