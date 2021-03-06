<?php

include("functions.php");
?>
<!DOCTYPE html>
<html lang="en" class="h-100">

	<head>
		<meta name="viewport" charset="utf-8" content="initial-scale=1" ; />
		<meta name="author" content="Batimax" />
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous" />

		<title>Satellite - Tables</title>

		<style>
			.footer,
			.header {
				bottom: 0;
				background-color: #181818;
			}

			.no_colapse {
				white-space: nowrap;
			}
			footer a{
				color: hotpink;
			}
			#not_okay{
				color: red;
			}
			#okay{
				color: green;
			}
		</style>
	</head>

	<body class="d-flex flex-column h-100">

		<!-- Header -->
		<header class="header shadow mb-2 p-2">
			<div class="container flex-md-nowrap">
				<img src="../static/logos/long_trans_blanc.png" height="45" class="d-inline-block align-top" alt="" />
			</div>
		</header>


		<main class="flex-shrink-0">

			<div class="container col-md-11">
				<h1>
					<?php
					echo 'Tables Satellite';
					?>

				</h1>
				<div class="table-responsive-sm pb-4">
					<table class="table table-striped table-hover">
						<thead>
							<!-- En-tête du tableau -->
							<tr>
								<th>Table</th>
								<th>Nom</th>
								<th>Verifié</th>
								<th>Heure Scan</th>
							</tr>
						</thead>
						<tbody>

						<?php

						// Get all tables
						$all_tables = getTables($dtb);

						foreach ($all_tables as $table) {

							$req = $dtb->prepare("SELECT users.lastname, users.firstname, users.nbr_verified, users.mail_verified, DATE_FORMAT(logs.date_scan,'%H:%i') AS date_scan
							FROM users
							INNER JOIN logs
							ON logs.user = users.id
							WHERE logs.date_scan >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
							AND logs.user_table = :tables
							ORDER BY users.last_connection");
							$req->execute(array(
								'tables' => $table
							));
							$data_users = $req->fetchAll();
							$req->closeCursor();

							// New line and print table nuber
							echo '<tr href="select_table.php?table='.$table.'"> <td>' . $table . '</td>';

							if (!$data_users) {
								// Nobody at the table
								$data_users = false;
								echo '<td> - </td><td> - </td><td> - </td></tr>';
							} else {
								// Check if verified mail or number
								echo '<td>';
								foreach ($data_users as $data_user) {
									echo $data_user["lastname"] . ' ' . $data_user["firstname"] . '<br />';
								}
								echo '</td><td>';

								foreach ($data_users as $data_user) {

									if ($data_user["nbr_verified"] == 1 OR $data_user["mail_verified"]){
										$data_user["verified"] = '<span id="okay">OK</span>';
									} else {
										$data_user["verified"] = '<span id="not_okay">No</span>';
									};

									echo $data_user["verified"] . '<br />';
								}
								echo '</td><td>';

								foreach ($data_users as $data_user) {
									echo ($data_user["date_scan"]) . '<br />';
								}
								echo '</td></tr>';
							}
						}
						?>
						</tbody>
					</table>
				</div>
			</div>
		</main>

		<!-- Footer -->
		<footer class="footer py-2 mt-auto">
			<div
				class="container row align-items-center justify-content-between"
			>
				<div class="ml-3">
					<a href="mailto:qrona@satellite.bar?subject=QRona"
						><small> Questions, remarks?</small></a
					>
				</div>
				<div class="text-muted">
					<small>©2020 Batimax</small>
				</div>
			</div>
		</footer>



		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js"></script>

		<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.2/dist/jquery.validate.js"></script>

			<script type="text/javascript">
			$(document).ready(function(){
				$('tbody tr').click(function(){
					window.location = $(this).attr('href');
					return false;
				});
			});
		</script>

	</body>
</html>
