<?php

include("../static/api/functions.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta name="viewport" charset="utf-8" content="initial-scale=1" ; />
	<meta name="author" content="Batimax" />
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous" />
</head>

<body>
	<div class="container col-md-12">
		<h1>
			<?php
			date_default_timezone_set('Europe/Paris');
			$now = (new DateTime(date("Y-m-d")))->format('Y-m-d');

			echo 'Clients Satellite du ' . $now;
			?>

		</h1>
		<div class="table-responsive-sm">
			<table class="table table-striped">
				<?php
				date_default_timezone_set('Europe/Paris');
				$now = (new DateTime(date("Y-m-d")))->format('Y-m-d');

				$req = $dtb->prepare("SELECT *
					FROM users
					WHERE DATE(last_connection) = CURDATE()
					ORDER BY last_connection");
				$req->execute(array());
				$data_users = $req->fetchAll();
				$req->closeCursor();

				if (!$data_users) {
					$data_users = false;
					echo "No client today";
				} else {
					foreach ($data_users as $user) {

						$req = $dtb->prepare("SELECT user, user_table, DATE_FORMAT(date_scan,'%H:%i') AS date_scan
					FROM logs
					WHERE user = :user_key AND DATE(date_scan) = CURDATE()
					ORDER BY date_scan");
						$req->execute(array(
							'user_key' => $user["id"],
						));
						$date_users[$user["id"]] = $req->fetchAll(PDO::FETCH_ASSOC);
						$req->closeCursor();
					}
				}
				if ($data_users) { { ?>

						<thead>
							<!-- En-tête du tableau -->
							<tr>
								<th>Nom</th>
								<th>Rue</th>
								<th>Zipcode</th>
								<th>Ville </th>
								<th>Téléphone </th>
								<th>Email </th>
								<th>Date </th>
								<th>Table </th>
							</tr>
						</thead>

				<?php }

					foreach ($data_users as $data_user) {
						echo '<tr> <td> ' . $data_user["lastname"] . ' ' . $data_user["firstname"] . '</td>';
						echo '<td> ' . $data_user["adress"] . '</td>';
						echo '<td> ' . $data_user["zipcode"] . '</td>';
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
				?>
			</table>
		</div>
	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js"></script>

	<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.2/dist/jquery.validate.js"></script>

</body>
