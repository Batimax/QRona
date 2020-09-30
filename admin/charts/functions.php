<?php
// try {
// 	$dtb = new PDO(
// 		'mysql:
// 			host=localhost;
// 			dbname=QRona;
// 			charset=utf8',
// 		'covid',
// 		'19',
// 		array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
// 	);
// } catch (Exception $e) {
// 	die('Erreur : ' . $e->getMessage());
// }

try {
	$dtb = new PDO(
		'mysql:
			host=localhost;
			dbname=QRona;
			charset=utf8',
		'root',
		'root',
		array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
	);
} catch (Exception $e) {
	die('Erreur : ' . $e->getMessage());
}

function getDataDaily($dtb)
{
	$hours = range(7, 22);
	$hour_p = [];
	$hour_m = [];

	date_default_timezone_set('Europe/Paris');
	$today = new DateTime(date("Y-m-d"));

	$begin = new DateTime(date("Y-m-d"));
	$begin->add(new DateInterval('PT7H'));
	$end = new DateTime(date("Y-m-d"));
	$end->add(new DateInterval('PT22H'));


	$interval = DateInterval::createFromDateString('30 minutes');
	$period = new DatePeriod($begin, $interval, $end);

	$data_points = array();

	foreach ($period as $dt) {

		$req = $dtb->prepare('SELECT COUNT(*) AS x FROM data_scans
		WHERE DATE(date_scan) = CURDATE()
		AND date_scan BETWEEN :dt AND DATE_SUB(:dt, INTERVAL -30 MINUTE)');
		$req->execute(array(
			'dt' => $dt->format("Y-m-d H:i:s"),
		));
		// $data_db = $req->fetchALL(PDO::FETCH_ASSOC);
		$data_db = $req->fetch();
		// $data_db = $req->fetchALL(\PDO::FETCH_OBJ);
		$req->closeCursor();

		array_push($data_points, array("x" => $data_db['x'], "y" => $dt->format("Y-m-d H:i:s")));

	}
	// print_r($data_points);


	return $data_points;
}
