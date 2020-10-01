<?php

include("../static/api/functions.php");
$days_ago = $_GET['days_ago']
?>


<body>
	<!-- <div class="container col-md-12"> -->
		<!-- <h1>
			<?php
			date_default_timezone_set('Europe/Paris');
			$now = (new DateTime(date("Y-m-d")))->format('Y-m-d');

			echo 'Clients Satellite du ' . $now;
			?>

		</h1> -->
		<!-- <div class="table-responsive-sm"> -->
			<table class="table table-striped">
				<?php
				date_default_timezone_set('Europe/Paris');
				$now = (new DateTime(date("Y-m-d")))->format('Y-m-d');

				$req = $dtb->prepare("SELECT DISTINCT user
					FROM logs
					WHERE DATE(DATE_FORMAT(date_scan, '%Y-%m-%d') ) = DATE_SUB(CURDATE(), INTERVAL :days_ago DAY)");
				$req->execute(array(
					'days_ago' => $days_ago,
				));
				$data_users = $req->fetchAll(PDO::FETCH_ASSOC);
				$req->closeCursor();

				if(!$data_users){
					$data_users = false;
					echo "<h2> No client this day </h2>";
				} else {
					foreach ($data_users as $id) {
						$users_id[] = $id['user'];
					}


				$in  = str_repeat('?,', count($users_id) - 1) . '?';

				$req = $dtb->prepare("SELECT *
					FROM users
					WHERE users.id IN ($in)");
				$req->execute(
					$users_id
				);
				$data_users = $req->fetchAll();
				$req->closeCursor();



				if (!$data_users) {
					$data_users = false;
					echo "<h2> No client this day </h2>";
				} else {
					foreach ($data_users as $user) {

						$req = $dtb->prepare("SELECT user, user_table, DATE_FORMAT(date_scan,'%H:%i') AS date_scan
					FROM logs
					WHERE user = :user_key
					AND DATE(DATE_FORMAT(date_scan, '%Y-%m-%d') ) =  DATE_SUB(CURDATE(), INTERVAL :days_ago DAY)
					ORDER BY date_scan");
						$req->execute(array(
							'user_key' => $user["id"],
							'days_ago' => $days_ago
						));
						// $date_users = $req->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
						$date_users[$user["id"]] = $req->fetchAll(PDO::FETCH_ASSOC);
						$req->closeCursor();
					}
					// print_r($date_users);
					//print_r($data_users);
				}
				if ($data_users) { { ?>

						<thead>
							<!-- En-tête du tableau -->
							<tr>
								<th>Nom</th>
								<th>Rue</th>
								<th>Ville </th>
								<th>Téléphone </th>
								<th>Email </th>
								<th>Date </th>
								<th>Table </th>
							</tr>
						</thead>

				<?php }

					foreach ($data_users as $data_user) {
						// $row_nbr = sizeof($date_users[$data_user["id"]]);
						echo '<tr> <td> ' . $data_user["lastname"] . ' ' . $data_user["firstname"] . '</td>';
						echo '<td> ' . $data_user["adress"] . '</td>';
						echo '<td> ' . $data_user["city"] . '</td>';
						echo '<td> ' . $data_user["phone"] . '</td>';
						echo '<td> ' . $data_user["email"] . '</td>';
						echo '<td>';
						foreach ($date_users[$data_user["id"]] as $date_user) {
							echo ($date_user["date_scan"]) . '<br />';
						}
						echo '</td><td>';
						foreach ($date_users[$data_user["id"]] as $line_table) {
							echo ($line_table["user_table"]) . '<br />';
						}
						echo '</td></tr>';
					}
				}
				}
				?>
			</table>
		<!-- </div> -->
	<!-- </div> -->

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js"></script>

	<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.2/dist/jquery.validate.js"></script>

</body>
